<?php

namespace App\Http\Controllers\Crm;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DiningSession;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\PurchaseOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(Request $request): Response
    {
        [$companyId, $branchIds, $branchNames] = $this->scope($request);

        return Inertia::render('Crm/Dashboard', [
            'operationDashboard' => [
                'generatedAt' => now()->format('Y-m-d H:i'),
                'scope' => [
                    'companyId' => $companyId,
                    'branchIds' => $branchIds->values(),
                    'branchNames' => $branchNames->values(),
                ],
                'kpis' => $this->kpis($companyId, $branchIds),
                'salesStatus' => $this->salesStatus($companyId, $branchIds),
                'procurementStatus' => $this->procurementStatus($companyId, $branchIds),
                'stockStatus' => $this->stockStatus($companyId, $branchIds),
                'recentQueues' => $this->recentQueues($companyId, $branchIds),
                'recentMovements' => $this->recentMovements($companyId, $branchIds),
            ],
        ]);
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user?->company_id ?? Company::query()->value('id');

        $branchIds = collect();
        if ($user && $companyId) {
            $branchIds = $user->branches()
                ->where('branches.company_id', $companyId)
                ->pluck('branches.id')
                ->push($user->branch_id)
                ->filter()
                ->unique()
                ->values();
        }

        if ($branchIds->isEmpty() && $companyId) {
            $branchIds = Branch::query()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->values();
        }

        $branchNames = Branch::query()
            ->whereIn('id', $branchIds)
            ->orderBy('name')
            ->pluck('name');

        return [(int) $companyId, $branchIds, $branchNames];
    }

    private function kpis(int $companyId, Collection $branchIds): array
    {
        $today = now()->toDateString();

        $unpaidInvoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->whereNotIn('status', ['paid', 'cancelled']);

        return [
            'openPosSessions' => PosSession::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->where('status', 'open')
                ->whereNull('closed_at')
                ->count(),
            'openDiningSessions' => DiningSession::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->where('status', 'open')
                ->whereNull('closed_at')
                ->count(),
            'activeOrders' => Order::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->whereIn('status', ['draft', 'sent_to_kitchen', 'preparing', 'ready'])
                ->count(),
            'unpaidInvoices' => (clone $unpaidInvoices)->count(),
            'unpaidBalance' => (float) (clone $unpaidInvoices)->sum('balance_amount'),
            'pendingPurchases' => PurchaseOrder::query()
                ->where('company_id', $companyId)
                ->where(function (Builder $query) use ($branchIds): void {
                    $query->whereNull('branch_id')->orWhereIn('branch_id', $branchIds);
                })
                ->whereIn('status', ['created', 'approved', 'in_progress_receipt', 'partially_received'])
                ->count(),
            'receiptsInProgress' => GoodsReceipt::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->whereIn('status', ['draft', 'approved', 'in_progress', 'partially_received', 'received'])
                ->count(),
            'putawayTasks' => StockTransfer::query()
                ->where('company_id', $companyId)
                ->whereIn('to_branch_id', $branchIds)
                ->where('transfer_type', 'internal_transfer')
                ->whereNotNull('goods_receipt_id')
                ->whereIn('status', ['draft', 'submitted', 'approved', 'in_transit'])
                ->count(),
            'pendingStockSettlements' => Invoice::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->whereHas('lines')
                ->where(function (Builder $query): void {
                    $query->where('stock_settlement_status', 'pending')
                        ->orWhereNull('stock_settlement_status');
                })
                ->count(),
            'stockMovementsToday' => StockMovement::query()
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->whereDate('movement_date', $today)
                ->count(),
        ];
    }

    private function salesStatus(int $companyId, Collection $branchIds): array
    {
        return [
            'orders' => $this->statusCounts(
                Order::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds),
                ['draft', 'sent_to_kitchen', 'preparing', 'ready', 'served', 'cancelled']
            ),
            'invoices' => $this->statusCounts(
                Invoice::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds),
                ['draft', 'issued', 'partially_paid', 'paid', 'pay_later', 'cancelled']
            ),
            'stockSettlements' => $this->statusCounts(
                Invoice::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds)
                    ->whereHas('lines'),
                ['pending', 'approved', 'rejected'],
                'stock_settlement_status'
            ),
        ];
    }

    private function procurementStatus(int $companyId, Collection $branchIds): array
    {
        return [
            'purchaseOrders' => $this->statusCounts(
                PurchaseOrder::query()
                    ->where('company_id', $companyId)
                    ->where(function (Builder $query) use ($branchIds): void {
                        $query->whereNull('branch_id')->orWhereIn('branch_id', $branchIds);
                    }),
                ['created', 'approved', 'in_progress_receipt', 'partially_received', 'received', 'closed', 'rejected', 'cancelled']
            ),
            'goodsReceipts' => $this->statusCounts(
                GoodsReceipt::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds),
                ['draft', 'approved', 'in_progress', 'partially_received', 'received', 'rejected', 'cancelled']
            ),
            'putaway' => $this->statusCounts(
                StockTransfer::query()
                    ->where('company_id', $companyId)
                    ->whereIn('to_branch_id', $branchIds)
                    ->where('transfer_type', 'internal_transfer')
                    ->whereNotNull('goods_receipt_id'),
                ['draft', 'submitted', 'approved', 'in_transit', 'received', 'rejected', 'cancelled']
            ),
        ];
    }

    private function stockStatus(int $companyId, Collection $branchIds): array
    {
        return [
            'movementsByType' => $this->statusCounts(
                StockMovement::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds),
                ['purchase_receipt', 'pos_settlement', 'adjustment_in', 'adjustment_out', 'internal_transfer', 'customer_stock_keep', 'write_off'],
                'movement_type'
            ),
            'adjustments' => $this->statusCounts(
                StockAdjustment::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds)
                    ->whereIn('adjustment_type', ['adjustment_in', 'adjustment_out']),
                ['draft', 'confirmed', 'cancelled']
            ),
            'writeOffs' => $this->statusCounts(
                StockAdjustment::query()
                    ->where('company_id', $companyId)
                    ->whereIn('branch_id', $branchIds)
                    ->where('adjustment_type', 'write_off'),
                ['draft', 'confirmed', 'cancelled']
            ),
            'internalTransfers' => $this->statusCounts(
                StockTransfer::query()
                    ->where('company_id', $companyId)
                    ->where(function (Builder $query) use ($branchIds): void {
                        $query->whereIn('from_branch_id', $branchIds)
                            ->orWhereIn('to_branch_id', $branchIds);
                    })
                    ->where('transfer_type', 'internal_transfer')
                    ->whereNull('goods_receipt_id'),
                ['draft', 'submitted', 'approved', 'in_transit', 'received', 'rejected', 'cancelled']
            ),
            'customerKeepStock' => $this->statusCounts(
                StockTransfer::query()
                    ->where('company_id', $companyId)
                    ->where(function (Builder $query) use ($branchIds): void {
                        $query->whereIn('from_branch_id', $branchIds)
                            ->orWhereIn('to_branch_id', $branchIds);
                    })
                    ->where('transfer_type', 'customer_stock_keep'),
                ['draft', 'submitted', 'approved', 'in_transit', 'received', 'rejected', 'cancelled']
            ),
        ];
    }

    private function recentQueues(int $companyId, Collection $branchIds): array
    {
        return [
            'purchases' => PurchaseOrder::query()
                ->where('company_id', $companyId)
                ->where(function (Builder $query) use ($branchIds): void {
                    $query->whereNull('branch_id')->orWhereIn('branch_id', $branchIds);
                })
                ->whereIn('status', ['created', 'approved', 'in_progress_receipt', 'partially_received'])
                ->latest()
                ->limit(5)
                ->get(['id', 'po_no', 'supplier_name', 'status', 'grand_total', 'expected_date'])
                ->map(fn (PurchaseOrder $order): array => [
                    'id' => $order->id,
                    'number' => $order->po_no,
                    'name' => $order->supplier_name ?: 'Supplier',
                    'status' => $order->status,
                    'amount' => (float) $order->grand_total,
                    'date' => $order->expected_date?->toDateString(),
                    'href' => '/purchase',
                ]),
            'receipts' => GoodsReceipt::query()
                ->with('purchaseOrder:id,po_no')
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $branchIds)
                ->whereIn('status', ['draft', 'approved', 'in_progress', 'partially_received', 'received'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (GoodsReceipt $receipt): array => [
                    'id' => $receipt->id,
                    'number' => $receipt->receipt_no,
                    'name' => $receipt->purchaseOrder?->po_no ?? 'Direct Receipt',
                    'status' => $receipt->status,
                    'amount' => null,
                    'date' => $receipt->received_at?->format('Y-m-d H:i'),
                    'href' => '/goods-receipts',
                ]),
            'putaway' => StockTransfer::query()
                ->with('goodsReceipt:id,receipt_no')
                ->where('company_id', $companyId)
                ->whereIn('to_branch_id', $branchIds)
                ->where('transfer_type', 'internal_transfer')
                ->whereNotNull('goods_receipt_id')
                ->whereIn('status', ['draft', 'submitted', 'approved', 'in_transit'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (StockTransfer $transfer): array => [
                    'id' => $transfer->id,
                    'number' => $transfer->transfer_no,
                    'name' => $transfer->goodsReceipt?->receipt_no ?? 'Putaway',
                    'status' => $transfer->status,
                    'amount' => null,
                    'date' => $transfer->transfer_date?->format('Y-m-d H:i'),
                    'href' => '/putaway',
                ]),
        ];
    }

    private function recentMovements(int $companyId, Collection $branchIds): Collection
    {
        return StockMovement::query()
            ->with(['branch:id,name', 'item:id,name,code'])
            ->where('company_id', $companyId)
            ->whereIn('branch_id', $branchIds)
            ->latest('movement_date')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (StockMovement $movement): array => [
                'id' => $movement->id,
                'type' => $movement->movement_type,
                'referenceNo' => $movement->reference_no,
                'item' => $movement->item?->name ?? 'Stock item',
                'branch' => $movement->branch?->name ?? 'Branch',
                'quantity' => (float) $movement->quantity,
                'totalCost' => (float) $movement->total_cost,
                'date' => $movement->movement_date?->format('Y-m-d H:i') ?? $movement->created_at?->format('Y-m-d H:i'),
            ]);
    }

    private function statusCounts(Builder $query, array $statuses, string $column = 'status'): array
    {
        $counts = $query
            ->select($column, DB::raw('COUNT(*) as total'))
            ->groupBy($column)
            ->pluck('total', $column);

        return collect($statuses)
            ->map(fn (string $status): array => [
                'key' => $status,
                'label' => str($status)->replace('_', ' ')->title()->toString(),
                'count' => (int) ($counts[$status] ?? 0),
            ])
            ->values()
            ->all();
    }
}

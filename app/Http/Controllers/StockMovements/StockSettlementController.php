<?php

namespace App\Http\Controllers\StockMovements;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\StockBalance;
use App\Models\StockLog;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StockSettlementController extends Controller
{
    public function index(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);
        $filters = $this->filters($request);

        $invoices = $this->invoiceQuery($companyId, $branchId, $filters)
            ->latest('issued_at')
            ->latest('id')
            ->get()
            ->map(fn (Invoice $invoice): array => $this->formatInvoice($invoice));

        return Inertia::render('StockSettlements/Index', [
            'settlements' => $invoices,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Invoice $invoice): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $invoice->company_id !== $companyId || (int) $invoice->branch_id !== $branchId, 403);

        $invoice = $this->invoiceQuery($companyId, $branchId, [])->findOrFail($invoice->id);
        $formatted = $this->formatInvoice($invoice, true);
        $itemIds = collect($formatted['requirements'])->pluck('itemId')->unique()->values();

        return Inertia::render('StockSettlements/View', [
            'settlement' => $formatted,
            'balancesByItem' => $this->balancesForItems($companyId, $branchId, $itemIds),
        ]);
    }

    public function approve(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', Rule::exists('invoices', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'gt:0'],
            'allocations' => ['nullable', 'array'],
            'allocations.*' => ['nullable', 'array'],
            'allocations.*.*' => ['nullable', 'array'],
            'allocations.*.*.*.stock_balance_id' => ['required_with:allocations.*.*.*', 'integer'],
            'allocations.*.*.*.quantity' => ['required_with:allocations.*.*.*', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $data, $companyId, $branchId) {
            $invoices = $this->invoiceQuery($companyId, $branchId, [])
                ->whereIn('id', $data['invoice_ids'])
                ->lockForUpdate()
                ->get();

            foreach ($invoices as $invoice) {
                if (($invoice->stock_settlement_status ?? 'pending') !== 'pending') {
                    throw ValidationException::withMessages([
                        'invoice_ids' => "Invoice {$invoice->invoice_no} has already been {$invoice->stock_settlement_status}.",
                    ]);
                }

                $saleQty = $this->posSaleQuantity($invoice);
                $settleQty = (float) ($data['quantities'][$invoice->id] ?? $saleQty);
                $scale = $saleQty > 0 ? $settleQty / $saleQty : 1;
                $requirements = $this->requirementsForInvoice($invoice, $scale)
                    ->groupBy('item_id')
                    ->map(fn (Collection $rows): array => [
                        'item_id' => (int) $rows->first()['item_id'],
                        'unit_id' => (int) $rows->first()['unit_id'],
                        'quantity' => (float) $rows->sum('quantity'),
                        'menu_names' => $rows->pluck('menu_name')->unique()->values()->implode(', '),
                    ])
                    ->values();

                foreach ($requirements as $requirement) {
                    $this->deductRequirement(
                        $request,
                        $invoice,
                        $requirement,
                        $data['allocations'][$invoice->id][$requirement['item_id']] ?? [],
                        $data['note'] ?? null,
                    );
                }

                $invoice->update([
                    'stock_settlement_status' => 'approved',
                    'stock_settled_quantity' => $settleQty,
                    'stock_settled_at' => now(),
                    'stock_settled_by' => $request->user()->id,
                    'stock_settlement_note' => $data['note'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Selected POS sale stock settlement has been approved.');
    }

    public function reject(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $request->validate([
            'invoice_ids' => ['required', 'array', 'min:1'],
            'invoice_ids.*' => ['integer', Rule::exists('invoices', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        Invoice::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('id', $data['invoice_ids'])
            ->where('stock_settlement_status', 'pending')
            ->update([
                'stock_settlement_status' => 'rejected',
                'stock_rejected_at' => now(),
                'stock_rejected_by' => $request->user()->id,
                'stock_settlement_note' => $data['note'] ?? 'Rejected by approver',
            ]);

        return back()->with('success', 'Selected POS sale stock settlement has been rejected.');
    }

    private function invoiceQuery(int $companyId, int $branchId, array $filters)
    {
        return Invoice::query()
            ->with([
                'branch:id,name,code',
                'posTerminal:id,name,code',
                'customer:id,name,phone_number',
                'issuer:id,name',
                'lines.menu.activeBom.lines.item:id,name,code,unit_id,cost',
                'lines.menu.activeBom.lines.unit:id,name,code',
            ])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereNot('status', 'cancelled')
            ->whereHas('lines')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%")->orWhere('phone_number', 'like', "%{$search}%"))
                        ->orWhereHas('lines', fn ($line) => $line->where('menu_name_snapshot', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('stock_settlement_status', $status))
            ->when($filters['pos_terminal_id'] ?? null, fn ($query, $terminalId) => $query->where('pos_terminal_id', $terminalId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('issued_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('issued_at', '<=', $date));
    }

    private function formatInvoice(Invoice $invoice, bool $withRequirements = false): array
    {
        $saleQty = $this->posSaleQuantity($invoice);
        $requirements = $this->requirementsForInvoice($invoice);
        $missingBomCount = $invoice->lines->filter(fn (InvoiceLine $line) => ! $line->menu?->activeBom)->count();

        return [
            'id' => $invoice->id,
            'invoiceNo' => $invoice->invoice_no,
            'invoiceDate' => $invoice->issued_at?->toDateString(),
            'displayDate' => $invoice->issued_at?->format('Y-m-d H:i'),
            'customer' => $invoice->customer?->name ?? 'Walk-in Customer',
            'terminal' => $invoice->posTerminal?->name ?? '-',
            'status' => $invoice->stock_settlement_status ?? 'pending',
            'invoiceStatus' => $invoice->status,
            'posSaleQty' => $saleQty,
            'qtyToSettle' => $saleQty,
            'lineCount' => $invoice->lines->count(),
            'requirementCount' => $requirements->count(),
            'missingBomCount' => $missingBomCount,
            'grandTotal' => (float) $invoice->grand_total,
            'settledAt' => $invoice->stock_settled_at?->format('Y-m-d H:i'),
            'rejectedAt' => $invoice->stock_rejected_at?->format('Y-m-d H:i'),
            'note' => $invoice->stock_settlement_note,
            'lines' => $withRequirements ? $invoice->lines->map(fn (InvoiceLine $line): array => [
                'id' => $line->id,
                'menuName' => $line->menu_name_snapshot,
                'quantity' => (float) $line->quantity,
                'lineTotal' => (float) $line->line_total,
                'hasBom' => (bool) $line->menu?->activeBom,
            ])->values() : [],
            'requirements' => $withRequirements ? $requirements->values() : [],
        ];
    }

    private function posSaleQuantity(Invoice $invoice): float
    {
        return (float) $invoice->lines->sum(fn (InvoiceLine $line) => (float) $line->quantity);
    }

    private function requirementsForInvoice(Invoice $invoice, float $scale = 1): Collection
    {
        return $invoice->lines
            ->flatMap(function (InvoiceLine $line) use ($scale) {
                $bom = $line->menu?->activeBom;

                if (! $bom || (float) $bom->output_quantity <= 0) {
                    return collect();
                }

                return $bom->lines->map(function ($bomLine) use ($line, $bom, $scale) {
                    $base = ((float) $bomLine->quantity / (float) $bom->output_quantity) * (float) $line->quantity * $scale;
                    $quantity = $base * (1 + ((float) $bomLine->wastage_percent / 100));

                    return [
                        'invoiceLineId' => $line->id,
                        'menuName' => $line->menu_name_snapshot,
                        'itemId' => $bomLine->item_id,
                        'item_id' => $bomLine->item_id,
                        'itemCode' => $bomLine->item?->code ?? 'ITEM-'.$bomLine->item_id,
                        'itemName' => $bomLine->item?->name ?? 'Inventory Item',
                        'unitId' => $bomLine->unit_id,
                        'unit_id' => $bomLine->unit_id,
                        'unit' => $bomLine->unit?->code ?? $bomLine->unit?->name ?? 'Unit',
                        'quantity' => round($quantity, 4),
                        'menu_name' => $line->menu_name_snapshot,
                    ];
                });
            })
            ->values();
    }

    private function deductRequirement(Request $request, Invoice $invoice, array $requirement, array $allocations, ?string $note): void
    {
        $remaining = round((float) $requirement['quantity'], 4);
        $balances = $this->settlementBalances($invoice, (int) $requirement['item_id'])->keyBy('id');

        $allocationRows = collect($allocations)
            ->filter(fn ($row) => isset($row['stock_balance_id'], $row['quantity']) && (float) $row['quantity'] > 0);

        if ($allocationRows->isNotEmpty()) {
            foreach ($allocationRows as $row) {
                if ($remaining <= 0) {
                    break;
                }

                $balance = $balances->get((int) $row['stock_balance_id']);
                $quantity = min($remaining, (float) $row['quantity']);

                if (! $balance || (float) $balance->quantity_available < $quantity) {
                    throw ValidationException::withMessages(['allocations' => 'Selected settlement location does not have enough available stock.']);
                }

                $this->deductBalance($request, $invoice, $balance, $quantity, $requirement, $note);
                $remaining = round($remaining - $quantity, 4);
            }
        }

        foreach ($balances as $balance) {
            if ($remaining <= 0) {
                break;
            }

            $available = (float) $balance->quantity_available;
            if ($available <= 0) {
                continue;
            }

            $quantity = min($remaining, $available);
            $this->deductBalance($request, $invoice, $balance, $quantity, $requirement, $note);
            $remaining = round($remaining - $quantity, 4);
        }

        if ($remaining > 0.0001) {
            throw ValidationException::withMessages([
                'invoice_ids' => "Not enough stock to settle {$requirement['menu_names']} ingredient quantity.",
            ]);
        }
    }

    /** @return EloquentCollection<int, StockBalance> */
    private function settlementBalances(Invoice $invoice, int $itemId): EloquentCollection
    {
        return StockBalance::query()
            ->with(['warehouse:id,name,code', 'stockLocation:id,name,code'])
            ->where('company_id', $invoice->company_id)
            ->where('branch_id', $invoice->branch_id)
            ->where('item_id', $itemId)
            ->where('quantity_available', '>', 0)
            ->lockForUpdate()
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->get();
    }

    private function deductBalance(Request $request, Invoice $invoice, StockBalance $balance, float $quantity, array $requirement, ?string $note): void
    {
        $before = (float) $balance->quantity_available;

        $balance->update([
            'quantity_on_hand' => (float) $balance->quantity_on_hand - $quantity,
            'quantity_available' => $before - $quantity,
        ]);

        $movement = StockMovement::create([
            'company_id' => $invoice->company_id,
            'branch_id' => $invoice->branch_id,
            'warehouse_id' => $balance->warehouse_id,
            'from_location_id' => $balance->stock_location_id,
            'to_location_id' => null,
            'item_id' => $balance->item_id,
            'unit_id' => $balance->unit_id,
            'movement_type' => 'pos_settlement',
            'quantity' => -$quantity,
            'unit_cost' => $balance->average_cost,
            'total_cost' => -($quantity * (float) $balance->average_cost),
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'reference_no' => $invoice->invoice_no,
            'movement_date' => now(),
            'created_by' => $request->user()->id,
            'note' => $note ?: 'POS sale stock settlement for '.$requirement['menu_names'],
        ]);

        StockLog::create([
            'company_id' => $invoice->company_id,
            'branch_id' => $invoice->branch_id,
            'stock_movement_id' => $movement->id,
            'item_id' => $balance->item_id,
            'action' => 'pos_settlement',
            'quantity_before' => $before,
            'quantity_after' => $before - $quantity,
            'quantity_changed' => -$quantity,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'reference_no' => $invoice->invoice_no,
            'performed_by' => $request->user()->id,
            'note' => $note,
        ]);
    }

    private function balancesForItems(int $companyId, int $branchId, Collection $itemIds): array
    {
        return StockBalance::query()
            ->with(['warehouse:id,name,code', 'stockLocation:id,name,code', 'item:id,name,code'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('item_id', $itemIds)
            ->where('quantity_available', '>', 0)
            ->orderBy('warehouse_id')
            ->orderBy('stock_location_id')
            ->get()
            ->groupBy('item_id')
            ->map(fn (Collection $balances) => $balances->map(fn (StockBalance $balance): array => [
                'id' => $balance->id,
                'warehouse' => $balance->warehouse?->name ?? 'Warehouse',
                'location' => $balance->stockLocation?->name ?? 'Location',
                'locationCode' => $balance->stockLocation?->code ?? 'LOC',
                'available' => (float) $balance->quantity_available,
            ])->values())
            ->toArray();
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'pos_terminal_id' => ['nullable', 'integer'],
        ]);
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for stock settlement.');
        abort_if(! $branchId, 422, 'No branch is available for stock settlement.');

        return [(int) $companyId, (int) $branchId];
    }
}

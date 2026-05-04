<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Unit;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for purchase orders.');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $orders = PurchaseOrder::query()
            ->with(['lines.item', 'lines.unit'])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('po_no', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(fn (PurchaseOrder $order) => $this->formatOrder($order));

        $items = Item::query()
            ->with('unit')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_stockable', true)
            ->when($branchId, fn ($query) => $query->where(function ($inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->orderBy('name')
            ->get()
            ->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit_id' => $item->unit_id,
                'unit_code' => $item->unit?->code,
                'cost' => (float) $item->cost,
            ]);

        $units = Unit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Purchase/Index', [
            'orders' => $orders,
            'items' => $items,
            'units' => $units,
            'nextPoNo' => DocumentNumber::make(PurchaseOrder::class, 'po_no', 'PO'),
            'filters' => [
                'search' => $filters['search'] ?? null,
                'status' => $filters['status'] ?? null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for purchase orders.');

        $data = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_phone' => ['nullable', 'string', 'max:255'],
            'supplier_address' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', Rule::exists('items', 'id')->where('company_id', $companyId)],
            'lines.*.unit_id' => ['required', Rule::exists('units', 'id')],
            'lines.*.quantity_ordered' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $companyId, $branchId, $user) {
            $subtotal = collect($data['lines'])->sum(function (array $line) {
                return (float) $line['quantity_ordered'] * (float) $line['unit_cost'];
            });

            $order = PurchaseOrder::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'po_no' => DocumentNumber::make(PurchaseOrder::class, 'po_no', 'PO'),
                'purchase_scope' => $branchId ? 'branch' : 'company_group',
                'supplier_name' => $data['supplier_name'],
                'supplier_phone' => $data['supplier_phone'] ?? null,
                'supplier_address' => $data['supplier_address'] ?? null,
                'status' => 'created',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => $subtotal,
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'created_by' => $user->id,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $quantity = (float) $line['quantity_ordered'];
                $unitCost = (float) $line['unit_cost'];

                PurchaseOrderLine::create([
                    'purchase_order_id' => $order->id,
                    'branch_id' => $branchId,
                    'item_id' => $line['item_id'],
                    'unit_id' => $line['unit_id'],
                    'quantity_ordered' => $quantity,
                    'quantity_received' => 0,
                    'quantity_remaining' => $quantity,
                    'unit_cost' => $unitCost,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $quantity * $unitCost,
                    'status' => 'open',
                    'note' => $line['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase.index')->with('success', 'Purchase order created.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditableOrder($request, $purchaseOrder);

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase order approved.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditableOrder($request, $purchaseOrder);

        $purchaseOrder->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Purchase order rejected.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureEditableOrder($request, $purchaseOrder);

        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
        ]);

        $purchaseOrder->lines()->where('status', 'open')->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Purchase order cancelled.');
    }

    private function ensureEditableOrder(Request $request, PurchaseOrder $purchaseOrder): void
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');

        abort_if((int) $purchaseOrder->company_id !== (int) $companyId, 403);
        abort_if($purchaseOrder->status !== 'created', 422, 'This purchase order is already closed.');
    }

    private function formatOrder(PurchaseOrder $order): array
    {
        return [
            'id' => $order->id,
            'po_no' => $order->po_no,
            'supplier_name' => $order->supplier_name,
            'supplier_phone' => $order->supplier_phone,
            'supplier_address' => $order->supplier_address,
            'status' => $order->status,
            'order_date' => $order->order_date?->toDateString(),
            'display_order_date' => $order->order_date?->format('M d, Y'),
            'expected_date' => $order->expected_date?->toDateString(),
            'display_expected_date' => $order->expected_date?->format('M d, Y'),
            'subtotal' => (float) $order->subtotal,
            'discount_amount' => (float) $order->discount_amount,
            'tax_amount' => (float) $order->tax_amount,
            'grand_total' => (float) $order->grand_total,
            'lines' => $order->lines->map(fn (PurchaseOrderLine $line) => [
                'id' => $line->id,
                'item_name' => $line->item?->name,
                'item_code' => $line->item?->code,
                'unit_code' => $line->unit?->code,
                'quantity_ordered' => (float) $line->quantity_ordered,
                'quantity_received' => (float) $line->quantity_received,
                'quantity_remaining' => (float) $line->quantity_remaining,
                'unit_cost' => (float) $line->unit_cost,
                'line_total' => (float) $line->line_total,
                'status' => $line->status,
                'note' => $line->note,
            ]),
        ];
    }
}

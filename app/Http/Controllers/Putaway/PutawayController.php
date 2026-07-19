<?php

namespace App\Http\Controllers\Putaway;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptLine;
use App\Models\StockLocation;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Models\User;
use App\Services\PutawayPostingService;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PutawayController extends Controller
{
    public function index(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $transfers = StockTransfer::query()
            ->with([
                'goodsReceipt.purchaseOrder',
                'goodsReceipt.stockLocation',
                'goodsReceipt.lines.item',
                'goodsReceipt.lines.unit',
                'creator',
                'assignee',
                'approver',
                'rejecter',
                'canceller',
                'lines.item',
                'lines.unit',
                'lines.toLocation',
            ])
            ->where('company_id', $companyId)
            ->where('from_branch_id', $branchId)
            ->where('transfer_type', 'internal_transfer')
            ->whereNotNull('goods_receipt_id')
            ->latest()
            ->get()
            ->map(fn (StockTransfer $transfer) => $this->formatTransfer($transfer));

        $pendingReceipts = $this->formattedPutawayReceipts($companyId, $branchId)->count();

        return Inertia::render('Putaway/Index', [
            'putaways' => $transfers,
            'storageLocations' => $this->storageLocations($companyId, $branchId),
            'staff' => $this->staff($companyId, $branchId),
            'stats' => [
                'pendingReceipts' => $pendingReceipts,
                'activeTasks' => $transfers->whereIn('status', ['draft', 'submitted', 'approved', 'in_transit'])->count(),
                'todayGoal' => 85,
                'averageMinutes' => 14,
            ],
        ]);
    }

    public function receipts(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);
        $itemId = $request->integer('item_id');
        $receipts = $this->formattedPutawayReceipts($companyId, $branchId);

        if ($itemId) {
            $receipts = $receipts
                ->filter(fn (array $receipt) => collect($receipt['lines'])->contains(
                    fn (array $line) => (int) $line['item_id'] === $itemId
                        && (float) $line['quantity_remaining'] > 0
                ))
                ->values();
        }

        return Inertia::render('Putaway/CompleteGR_forPutaway', [
            'receipts' => $receipts,
        ]);
    }

    public function create(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $putaway = null;
        $receipt = null;
        if ($request->integer('stock_transfer_id')) {
            $putaway = StockTransfer::query()
                ->with(['goodsReceipt.purchaseOrder', 'goodsReceipt.stockLocation', 'goodsReceipt.lines.item', 'goodsReceipt.lines.unit', 'lines.toLocation'])
                ->where('company_id', $companyId)
                ->where('from_branch_id', $branchId)
                ->where('transfer_type', 'internal_transfer')
                ->where('status', 'draft')
                ->findOrFail($request->integer('stock_transfer_id'));
            $receipt = $putaway->goodsReceipt;
        }

        if ($request->integer('goods_receipt_id')) {
            $receipt = $this->putawayReceipts($companyId, $branchId)
                ->whereKey($request->integer('goods_receipt_id'))
                ->first();
        }

        return Inertia::render('Putaway/CreatePutaway', [
            'nextTransferNo' => $putaway?->transfer_no ?? DocumentNumber::preview(StockTransfer::class, 'transfer_no', 'PTW', $branchId),
            'putaway' => $putaway ? $this->formatEditableTransfer($putaway) : null,
            'receipt' => $receipt ? $this->formatReceipt($receipt) : null,
            'receipts' => $this->formattedPutawayReceipts($companyId, $branchId),
            'storageLocations' => $this->storageLocations($companyId, $branchId),
            'staff' => $this->staff($companyId, $branchId),
        ]);
    }

    public function store(Request $request)
    {
        [$companyId, $branchId] = $this->scope($request);

        $data = $this->validatePutaway($request, $companyId);

        $transfer = DB::transaction(function () use ($request, $data, $companyId, $branchId) {
            $receipt = $this->putawayReceipts($companyId, $branchId)
                ->lockForUpdate()
                ->findOrFail($data['goods_receipt_id']);

            $receiptLines = $receipt->lines->groupBy('item_id');
            $alreadyPutaway = $this->putawayQuantities($receipt);
            $selectedLines = collect($data['lines'])
                ->filter(fn (array $line) => ($line['selected'] ?? true) && (float) $line['quantity'] > 0);

            abort_if($selectedLines->isEmpty(), 422, 'Move at least one received item.');

            foreach ($selectedLines->groupBy('item_id') as $itemId => $lines) {
                $received = $receiptLines->get($itemId, collect())->sum(fn (GoodsReceiptLine $receiptLine) => (float) $receiptLine->quantity_received);
                $remaining = $received - (float) ($alreadyPutaway[$itemId] ?? 0);
                $requested = $lines->sum(fn (array $line) => (float) $line['quantity']);

                abort_if($requested > $remaining, 422, 'Putaway quantity is greater than the remaining staging quantity.');
            }

            $firstDestinationId = (int) $selectedLines->first()['to_location_id'];
            $firstDestination = $this->putawayLocation($companyId, $branchId, $firstDestinationId);

            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'goods_receipt_id' => $receipt->id,
                'from_branch_id' => $branchId,
                'to_branch_id' => $branchId,
                'from_warehouse_id' => $receipt->warehouse_id,
                'to_warehouse_id' => $firstDestination->warehouse_id,
                'from_location_id' => $receipt->stock_location_id,
                'to_location_id' => $firstDestination->id,
                'transfer_no' => DocumentNumber::make(StockTransfer::class, 'transfer_no', 'PTW', $branchId),
                'transfer_type' => 'internal_transfer',
                'status' => 'draft',
                'transfer_date' => now(),
                'created_by' => $request->user()->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'note' => $this->putawayNote($data),
            ]);

            foreach ($selectedLines as $line) {
                $quantity = (float) $line['quantity'];
                $receiptLine = $receiptLines->get((int) $line['item_id'])->first();
                $destination = $this->putawayLocation($companyId, $branchId, (int) $line['to_location_id']);
                $totalCost = $quantity * (float) $receiptLine->unit_cost;

                StockTransferLine::create([
                    'stock_transfer_id' => $transfer->id,
                    'item_id' => $receiptLine->item_id,
                    'unit_id' => $receiptLine->unit_id,
                    'to_location_id' => $destination->id,
                    'quantity_requested' => $quantity,
                    'quantity_dispatched' => 0,
                    'quantity_received' => 0,
                    'unit_cost' => $receiptLine->unit_cost,
                    'total_cost' => $totalCost,
                    'status' => 'open',
                ]);
            }

            return $transfer;
        });

        return redirect()
            ->route('putaway.index')
            ->with('success', "Putaway movement {$transfer->transfer_no} saved as draft.");
    }

    public function update(Request $request, StockTransfer $stockTransfer)
    {
        [$companyId, $branchId] = $this->scope($request);
        $this->ensureDraftTransfer($request, $stockTransfer);

        $data = $this->validatePutaway($request, $companyId);

        DB::transaction(function () use ($data, $companyId, $branchId, $stockTransfer) {
            $receipt = $this->putawayReceipts($companyId, $branchId)
                ->lockForUpdate()
                ->findOrFail($data['goods_receipt_id']);

            $receiptLines = $receipt->lines->groupBy('item_id');
            $alreadyPutaway = $this->putawayQuantities($receipt);
            $selectedLines = collect($data['lines'])
                ->filter(fn (array $line) => ($line['selected'] ?? true) && (float) $line['quantity'] > 0);

            abort_if($selectedLines->isEmpty(), 422, 'Move at least one received item.');

            foreach ($selectedLines->groupBy('item_id') as $itemId => $lines) {
                $received = $receiptLines->get($itemId, collect())->sum(fn (GoodsReceiptLine $receiptLine) => (float) $receiptLine->quantity_received);
                $remaining = $received - (float) ($alreadyPutaway[$itemId] ?? 0);
                $requested = $lines->sum(fn (array $line) => (float) $line['quantity']);

                abort_if($requested > $remaining, 422, 'Putaway quantity is greater than the remaining staging quantity.');
            }

            $firstDestination = $this->putawayLocation($companyId, $branchId, (int) $selectedLines->first()['to_location_id']);

            $stockTransfer->update([
                'goods_receipt_id' => $receipt->id,
                'from_warehouse_id' => $receipt->warehouse_id,
                'to_warehouse_id' => $firstDestination->warehouse_id,
                'from_location_id' => $receipt->stock_location_id,
                'to_location_id' => $firstDestination->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'note' => $this->putawayNote($data),
            ]);

            $stockTransfer->lines()->delete();

            foreach ($selectedLines as $line) {
                $receiptLine = $receiptLines->get((int) $line['item_id'])->first();
                $quantity = (float) $line['quantity'];

                StockTransferLine::create([
                    'stock_transfer_id' => $stockTransfer->id,
                    'item_id' => $receiptLine->item_id,
                    'unit_id' => $receiptLine->unit_id,
                    'to_location_id' => (int) $line['to_location_id'],
                    'quantity_requested' => $quantity,
                    'quantity_dispatched' => 0,
                    'quantity_received' => 0,
                    'unit_cost' => $receiptLine->unit_cost,
                    'total_cost' => $quantity * (float) $receiptLine->unit_cost,
                    'status' => 'open',
                ]);
            }
        });

        return redirect()
            ->route('putaway.index')
            ->with('success', "Putaway movement {$stockTransfer->transfer_no} updated.");
    }

    public function approve(Request $request, StockTransfer $stockTransfer, PutawayPostingService $postingService)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        $postingService->post($stockTransfer, $request->user()->id);

        return back()->with('success', "Putaway movement {$stockTransfer->transfer_no} completed.");
    }

    public function cancelReceipt(Request $request, GoodsReceipt $goodsReceipt)
    {
        [$companyId, $branchId] = $this->scope($request);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        abort_if((int) $goodsReceipt->company_id !== (int) $companyId, 403);
        abort_if((int) $goodsReceipt->branch_id !== (int) $branchId, 403);
        abort_unless(in_array($goodsReceipt->status, ['approved', 'received'], true), 422, 'Only a received GR can have putaway cancelled.');
        abort_if($goodsReceipt->putaway_cancelled_at, 422, 'Putaway has already been cancelled for this GR.');

        DB::transaction(function () use ($request, $data, $goodsReceipt): void {
            $goodsReceipt->update([
                'putaway_cancel_reason' => $data['reason'],
                'putaway_cancelled_at' => now(),
                'putaway_cancelled_by' => $request->user()->id,
            ]);

            $goodsReceipt->putawayTransfers()
                ->where('status', 'draft')
                ->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $data['reason'],
                    'cancelled_at' => now(),
                    'cancelled_by' => $request->user()->id,
                ]);
        });

        return redirect()
            ->route('putaway.index')
            ->with('success', "Putaway for {$goodsReceipt->receipt_no} was cancelled. The stock remains in staging.");
    }

    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        $stockTransfer->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
        ]);

        return back()->with('success', "Putaway movement {$stockTransfer->transfer_no} rejected.");
    }

    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        $this->ensureDraftTransfer($request, $stockTransfer);

        $stockTransfer->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
        ]);

        return back()->with('success', "Putaway movement {$stockTransfer->transfer_no} cancelled.");
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for putaway.');
        abort_if(! $branchId, 422, 'No branch is available for putaway.');

        return [$companyId, $branchId];
    }

    private function ensureDraftTransfer(Request $request, StockTransfer $stockTransfer): void
    {
        [$companyId, $branchId] = $this->scope($request);

        abort_if((int) $stockTransfer->company_id !== (int) $companyId, 403);
        abort_if((int) $stockTransfer->from_branch_id !== (int) $branchId, 403);
        abort_if($stockTransfer->transfer_type !== 'internal_transfer' || ! $stockTransfer->goods_receipt_id, 403);
        abort_if($stockTransfer->status !== 'draft', 422, 'Only draft putaway movements can be changed.');
    }

    private function validatePutaway(Request $request, int $companyId): array
    {
        return $request->validate([
            'goods_receipt_id' => ['required', Rule::exists('goods_receipts', 'id')->where('company_id', $companyId)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'priority' => ['required', Rule::in(['low', 'normal', 'urgent'])],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'integer'],
            'lines.*.unit_id' => ['required', 'integer'],
            'lines.*.to_location_id' => ['required', Rule::exists('stock_locations', 'id')->where('company_id', $companyId)->where('location_type', 'putaway')],
            'lines.*.quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.selected' => ['boolean'],
        ]);
    }

    private function putawayLocation(int $companyId, int $branchId, int $locationId): StockLocation
    {
        return StockLocation::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('location_type', 'putaway')
            ->findOrFail($locationId);
    }

    private function putawayNote(array $data): string
    {
        return trim(($data['note'] ?? '')."\nPriority: {$data['priority']}\nAssigned staff ID: ".($data['assigned_to'] ?? ''));
    }

    private function putawayReceipts(int $companyId, int $branchId)
    {
        return GoodsReceipt::query()
            ->with(['purchaseOrder', 'stockLocation', 'lines.item', 'lines.unit', 'putawayTransfers.lines'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['approved', 'received'])
            ->whereNull('putaway_cancelled_at')
            ->whereHas('stockLocation', fn ($query) => $query->where('location_type', 'inbound_staging'))
            ->whereHas('lines')
            ->latest('received_at');
    }

    private function storageLocations(int $companyId, int $branchId)
    {
        return StockLocation::query()
            ->with('warehouse')
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('location_type', 'putaway')
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (StockLocation $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'warehouse_name' => $location->warehouse?->name,
            ]);
    }

    private function formattedPutawayReceipts(int $companyId, int $branchId): Collection
    {
        return $this->putawayReceipts($companyId, $branchId)
            ->get()
            ->map(fn (GoodsReceipt $receipt) => $this->formatReceipt($receipt))
            ->filter(fn (array $receipt) => $receipt['total_remaining'] > 0)
            ->values();
    }

    private function staff(int $companyId, int $branchId)
    {
        return User::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->orWhereHas('branches', fn ($inner) => $inner->whereKey($branchId));
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name]);
    }

    private function formatTransfer(StockTransfer $transfer): array
    {
        $note = (string) $transfer->note;
        $priority = str_contains($note, 'Priority: urgent') ? 'urgent'
            : (str_contains($note, 'Priority: low') ? 'low' : 'normal');
        $cleanNote = trim(preg_replace('/\n?Priority:.*|\n?Assigned staff ID:.*/', '', $note));
        $assignedToId = null;
        if (preg_match('/Assigned staff ID:\s*(\d+)/', $note, $matches)) {
            $assignedToId = (int) $matches[1];
        }

        $assignedStaff = $transfer->assignee;
        if (! $assignedStaff && $assignedToId) {
            $assignedStaff = User::query()->find($assignedToId);
        }

        return [
            'id' => $transfer->id,
            'transfer_no' => $transfer->transfer_no,
            'goods_receipt_id' => $transfer->goods_receipt_id,
            'goods_receipt_no' => $transfer->goodsReceipt?->receipt_no,
            'priority' => $priority,
            'assigned_to_id' => $assignedToId,
            'note' => $cleanNote,
            'created_at' => $transfer->created_at?->format('M d, Y H:i'),
            'updated_at' => $transfer->updated_at?->format('M d, Y H:i'),
            'approved_at' => $transfer->approved_at?->format('M d, Y H:i'),
            'rejected_at' => $transfer->rejected_at?->format('M d, Y H:i'),
            'cancelled_at' => $transfer->cancelled_at?->format('M d, Y H:i'),
            'assigned_staff' => $assignedStaff?->name ?? 'Unassigned',
            'created_by' => $transfer->creator?->name,
            'approved_by' => $transfer->approver?->name,
            'rejected_by' => $transfer->rejecter?->name,
            'cancelled_by' => $transfer->canceller?->name,
            'item_count' => $transfer->lines->count(),
            'total_quantity' => $transfer->lines->sum(fn (StockTransferLine $line) => (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested)),
            'status' => $transfer->status,
            'receipt' => $transfer->status === 'draft' && $transfer->goodsReceipt
                ? $this->formatReceipt($transfer->goodsReceipt)
                : null,
            'lines' => $transfer->lines->map(fn (StockTransferLine $line) => [
                'id' => $line->id,
                'item_id' => $line->item_id,
                'unit_id' => $line->unit_id,
                'item_name' => $line->item?->name,
                'item_code' => $line->item?->code,
                'unit_code' => $line->unit?->code,
                'to_location' => $line->toLocation?->code ?? $line->toLocation?->name,
                'to_location_id' => $line->to_location_id,
                'quantity' => (float) ($transfer->status === 'received' ? $line->quantity_received : $line->quantity_requested),
                'unit_cost' => (float) $line->unit_cost,
                'total_cost' => (float) $line->total_cost,
            ]),
        ];
    }

    private function formatEditableTransfer(StockTransfer $transfer): array
    {
        return [
            'id' => $transfer->id,
            'transfer_no' => $transfer->transfer_no,
            'goods_receipt_id' => $transfer->goods_receipt_id,
            'priority' => str_contains((string) $transfer->note, 'Priority: urgent')
                ? 'urgent'
                : (str_contains((string) $transfer->note, 'Priority: low') ? 'low' : 'normal'),
            'note' => trim(preg_replace('/\n?Priority:.*|\n?Assigned staff ID:.*/', '', (string) $transfer->note)),
            'lines' => $transfer->lines->map(fn (StockTransferLine $line) => [
                'allocation_key' => "existing-{$line->id}",
                'item_id' => $line->item_id,
                'unit_id' => $line->unit_id,
                'to_location_id' => $line->to_location_id,
                'quantity' => (float) $line->quantity_requested,
                'selected' => true,
            ]),
        ];
    }

    private function formatReceipt(GoodsReceipt $receipt): array
    {
        $alreadyPutaway = $this->putawayQuantities($receipt);
        $lines = $receipt->lines
            ->groupBy('item_id')
            ->map(function (Collection $lines, int $itemId) use ($alreadyPutaway) {
                $first = $lines->first();
                $received = $lines->sum(fn (GoodsReceiptLine $line) => (float) $line->quantity_received);
                $putaway = (float) ($alreadyPutaway[$itemId] ?? 0);

                return [
                    'item_id' => $itemId,
                    'unit_id' => $first->unit_id,
                    'item_name' => $first->item?->name,
                    'item_code' => $first->item?->code,
                    'unit_code' => $first->unit?->code,
                    'quantity_received' => $received,
                    'quantity_putaway' => $putaway,
                    'quantity_remaining' => max(0, $received - $putaway),
                    'unit_cost' => (float) $first->unit_cost,
                ];
            })
            ->values();

        $totalReceived = $lines->sum('quantity_received');
        $totalRemaining = $lines->sum('quantity_remaining');

        return [
            'id' => $receipt->id,
            'receipt_no' => $receipt->receipt_no,
            'received_at' => $receipt->received_at?->format('M d, Y'),
            'purchase_order_no' => $receipt->purchaseOrder?->po_no,
            'staging_area' => $receipt->stockLocation?->code ?? $receipt->stockLocation?->name,
            'line_count' => $lines->count(),
            'total_received' => $totalReceived,
            'total_remaining' => $totalRemaining,
            'progress' => $totalReceived > 0 ? round((($totalReceived - $totalRemaining) / $totalReceived) * 100) : 0,
            'lines' => $lines,
        ];
    }

    private function putawayQuantities(GoodsReceipt $receipt): array
    {
        return StockTransferLine::query()
            ->whereHas('stockTransfer', fn ($query) => $query
                ->where('goods_receipt_id', $receipt->id)
                ->whereNotIn('status', ['draft', 'rejected', 'cancelled']))
            ->selectRaw('item_id, sum(quantity_requested) as quantity')
            ->groupBy('item_id')
            ->pluck('quantity', 'item_id')
            ->map(fn ($quantity) => (float) $quantity)
            ->all();
    }
}

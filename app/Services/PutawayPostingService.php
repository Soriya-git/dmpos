<?php

namespace App\Services;

use App\Models\GoodsReceiptLine;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use Illuminate\Support\Facades\DB;

class PutawayPostingService
{
    public function post(StockTransfer $transfer, int $userId, bool $allowApprovedUnposted = false): void
    {
        DB::transaction(function () use ($transfer, $userId, $allowApprovedUnposted): void {
            $transfer = StockTransfer::query()
                ->with('goodsReceipt.lines')
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            $allowedStatuses = $allowApprovedUnposted ? ['draft', 'approved'] : ['draft'];

            abort_unless(in_array($transfer->status, $allowedStatuses, true), 422, 'Only an unposted putaway movement can be completed.');
            abort_if(
                StockMovement::query()
                    ->where('reference_type', 'stock_transfer')
                    ->where('reference_id', $transfer->id)
                    ->exists(),
                422,
                'This putaway movement has already been posted.'
            );

            $receipt = $transfer->goodsReceipt;
            abort_if(! $receipt, 422, 'The goods receipt for this putaway movement was not found.');

            $receiptLines = $receipt->lines->groupBy('item_id');
            $lines = StockTransferLine::query()
                ->where('stock_transfer_id', $transfer->id)
                ->lockForUpdate()
                ->get();

            abort_if($lines->isEmpty(), 422, 'This putaway movement has no lines to post.');

            foreach ($lines as $line) {
                $quantity = (float) $line->quantity_requested;

                if ($quantity <= 0) {
                    continue;
                }

                /** @var GoodsReceiptLine|null $receiptLine */
                $receiptLine = $receiptLines->get($line->item_id)?->first();
                abort_if(! $receiptLine, 422, 'A goods receipt line for the putaway item was not found.');

                $destination = StockLocation::query()
                    ->where('company_id', $transfer->company_id)
                    ->where('branch_id', $transfer->to_branch_id)
                    ->where('location_type', 'putaway')
                    ->find($line->to_location_id);
                abort_if(! $destination, 422, 'The selected putaway location is not valid.');

                $this->moveBalance($transfer, $receiptLine, $destination, $quantity);

                StockMovement::create([
                    'company_id' => $transfer->company_id,
                    'branch_id' => $transfer->to_branch_id,
                    'warehouse_id' => $destination->warehouse_id,
                    'from_location_id' => $transfer->from_location_id,
                    'to_location_id' => $destination->id,
                    'item_id' => $line->item_id,
                    'unit_id' => $line->unit_id,
                    'movement_type' => 'internal_transfer',
                    'quantity' => $quantity,
                    'unit_cost' => $line->unit_cost,
                    'total_cost' => $quantity * (float) $line->unit_cost,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $transfer->id,
                    'reference_no' => $transfer->transfer_no,
                    'movement_date' => now(),
                    'created_by' => $userId,
                    'note' => 'Putaway from goods receipt staging to saleable storage',
                ]);

                $line->update([
                    'quantity_dispatched' => $quantity,
                    'quantity_received' => $quantity,
                    'status' => 'received',
                ]);
            }

            $transfer->update([
                'status' => 'approved',
                'approved_at' => $transfer->approved_at ?? now(),
                'dispatched_at' => $transfer->dispatched_at ?? now(),
                'received_at' => $transfer->received_at ?? now(),
                'approved_by' => $transfer->approved_by ?? $userId,
                'dispatched_by' => $transfer->dispatched_by ?? $userId,
                'received_by' => $transfer->received_by ?? $userId,
            ]);
        });
    }

    private function moveBalance(
        StockTransfer $transfer,
        GoodsReceiptLine $receiptLine,
        StockLocation $destination,
        float $quantity
    ): void {
        $source = StockBalance::query()
            ->where('company_id', $transfer->company_id)
            ->where('branch_id', $transfer->from_branch_id)
            ->where('warehouse_id', $transfer->from_warehouse_id)
            ->where('stock_location_id', $transfer->from_location_id)
            ->where('item_id', $receiptLine->item_id)
            ->lockForUpdate()
            ->first();

        abort_if(! $source || (float) $source->quantity_available < $quantity, 422, 'Not enough stock in staging for putaway.');

        $source->update([
            'quantity_on_hand' => (float) $source->quantity_on_hand - $quantity,
            'quantity_available' => (float) $source->quantity_available - $quantity,
        ]);

        $target = StockBalance::firstOrCreate(
            [
                'company_id' => $transfer->company_id,
                'branch_id' => $transfer->to_branch_id,
                'warehouse_id' => $destination->warehouse_id,
                'stock_location_id' => $destination->id,
                'item_id' => $receiptLine->item_id,
            ],
            [
                'unit_id' => $receiptLine->unit_id,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'average_cost' => $receiptLine->unit_cost,
            ]
        );

        $target = StockBalance::query()->lockForUpdate()->findOrFail($target->id);
        $target->update([
            'quantity_on_hand' => (float) $target->quantity_on_hand + $quantity,
            'quantity_available' => (float) $target->quantity_available + $quantity,
            'average_cost' => $receiptLine->unit_cost,
        ]);
    }
}

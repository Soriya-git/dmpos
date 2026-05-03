<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\DiningSession;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\SessionLog;
use App\Models\StockLog;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (!$company || !$branch) {
            return;
        }

        $session = DiningSession::first();
        $invoice = Invoice::first();
        $payment = Payment::first();
        $movement = StockMovement::first();

        ActivityLog::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'module' => 'POS',
            'action' => 'demo_seeded',
            'reference_type' => 'system',
            'reference_no' => 'DEMO-ACTIVITY',
            'new_values' => [
                'message' => 'Demo restaurant POS data seeded successfully.',
            ],
            'description' => 'Initial demo activity log created by seeder.',
        ]);

        if ($session) {
            SessionLog::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'dining_session_id' => $session->id,
                'action' => 'opened',
                'payload' => [
                    'session_no' => $session->session_no,
                    'status' => $session->status,
                ],
                'note' => 'Demo session log.',
            ]);
        }

        if ($movement) {
            StockLog::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'stock_movement_id' => $movement->id,
                'item_id' => $movement->item_id,
                'action' => $movement->movement_type,
                'quantity_before' => 0,
                'quantity_changed' => $movement->quantity,
                'quantity_after' => $movement->quantity,
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'reference_no' => $movement->reference_no,
                'payload' => [
                    'movement_type' => $movement->movement_type,
                    'movement_date' => $movement->movement_date,
                ],
                'note' => 'Demo stock log.',
            ]);
        }

        if ($invoice && $payment) {
            PaymentLog::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'action' => 'paid',
                'amount_before' => 0,
                'amount_changed' => $payment->amount_paid,
                'amount_after' => $payment->amount_paid,
                'currency' => $payment->currency,
                'exchange_rate_snapshot' => $payment->exchange_rate_snapshot,
                'payload' => [
                    'payment_no' => $payment->payment_no,
                    'invoice_no' => $invoice->invoice_no,
                ],
                'note' => 'Demo payment log.',
            ]);
        }
    }
}
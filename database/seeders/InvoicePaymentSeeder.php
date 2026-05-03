<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DiningSession;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;

class InvoicePaymentSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();
        $session = DiningSession::first();
        $order = Order::with('orderLines')->first();
        $paymentMethod = PaymentMethod::where('code', 'CASH_USD')->first();

        if (! $company || ! $branch || ! $session || ! $order || ! $paymentMethod) {
            return;
        }

        $subtotal = $order->orderLines->sum('line_subtotal');
        $taxAmount = $order->orderLines->sum('tax_amount');
        $grandTotal = $order->orderLines->sum('line_total');

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'pos_terminal_id' => $session->pos_terminal_id,
            'dining_session_id' => $session->id,
            'customer_id' => $session->customer_id,
            'invoice_no' => DocumentNumber::make(Invoice::class, 'invoice_no', 'IN'),
            'status' => 'paid',
            'currency' => 'USD',
            'exchange_rate_snapshot' => 4100,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'paid_amount' => $grandTotal,
            'balance_amount' => 0,
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        foreach ($order->orderLines as $line) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'order_line_id' => $line->id,
                'menu_id' => $line->menu_id,
                'menu_name_snapshot' => $line->menu_name_snapshot,
                'menu_type_snapshot' => $line->menu_type_snapshot,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'discount_amount' => $line->discount_amount,
                'tax_id' => $line->tax_id,
                'tax_rate_snapshot' => $line->tax_rate_snapshot,
                'tax_amount' => $line->tax_amount,
                'line_subtotal' => $line->line_subtotal,
                'line_total' => $line->line_total,
                'note' => $line->note,
            ]);
        }

        Payment::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_no' => DocumentNumber::make(Payment::class, 'payment_no', 'PY'),
            'status' => 'paid',
            'currency' => 'USD',
            'amount_paid' => $grandTotal,
            'exchange_rate_snapshot' => 4100,
            'amount_usd_equivalent' => $grandTotal,
            'amount_khr_equivalent' => $grandTotal * 4100,
            'paid_at' => now(),
        ]);

        $session->update([
            'status' => 'paid',
            'closed_at' => now(),
        ]);
    }
}

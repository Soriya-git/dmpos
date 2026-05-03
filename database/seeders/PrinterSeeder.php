<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Printer;
use App\Models\PrintTemplate;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();

        if (! $company || ! $branch) {
            return;
        }

        Printer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Cashier Receipt Printer',
            'code' => 'PRN-RECEIPT-01',
            'printer_type' => 'receipt',
            'connection_type' => 'browser_dialog',
            'paper_size' => '80mm',
            'is_default' => true,
            'settings' => [
                'description' => 'Default browser print dialog printer for receipt.',
            ],
        ]);

        Printer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Kitchen Printer',
            'code' => 'PRN-KITCHEN-01',
            'printer_type' => 'kitchen',
            'connection_type' => 'network',
            'ip_address' => '192.168.1.100',
            'port' => 9100,
            'paper_size' => '80mm',
            'is_default' => false,
            'settings' => [
                'description' => 'Demo network kitchen printer.',
            ],
        ]);

        PrintTemplate::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Default POS Receipt 80mm',
            'code' => 'TPL-RECEIPT-80',
            'template_type' => 'receipt',
            'paper_size' => '80mm',
            'is_default' => true,
            'layout_config' => [
                'show_logo' => true,
                'show_customer' => true,
                'show_phone' => true,
                'show_room_table' => true,
                'show_cashier' => true,
                'show_qr_code' => true,
                'show_exchange_rate' => true,
                'show_tax_summary' => true,
                'footer_text' => 'Thank you. Please come again.',
            ],
        ]);

        PrintTemplate::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Default POS Invoice 80mm',
            'code' => 'TPL-INVOICE-80',
            'template_type' => 'invoice',
            'paper_size' => '80mm',
            'is_default' => true,
            'layout_config' => [
                'show_logo' => true,
                'show_customer' => true,
                'show_phone' => true,
                'show_room_table' => true,
                'show_invoice_no' => true,
                'show_payment_status' => true,
                'show_qr_code' => true,
                'show_exchange_rate' => true,
                'show_tax_summary' => true,
                'footer_text' => 'Official invoice.',
            ],
        ]);

        PrintTemplate::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Default Kitchen Ticket 80mm',
            'code' => 'TPL-KITCHEN-80',
            'template_type' => 'kitchen_ticket',
            'paper_size' => '80mm',
            'is_default' => true,
            'layout_config' => [
                'show_room_table' => true,
                'show_order_no' => true,
                'show_order_time' => true,
                'show_item_note' => true,
                'show_staff_name' => true,
            ],
        ]);
    }
}

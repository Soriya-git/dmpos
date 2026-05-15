<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Menu;
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

        $receiptPrinter = Printer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Cashier Receipt Printer',
            'code' => 'PRN-RECEIPT-01',
            'printer_type' => 'receipt',
            'printer_role' => 'cashier',
            'connection_type' => 'browser_dialog',
            'network_protocol' => 'browser',
            'paper_size' => '80mm',
            'is_default' => true,
            'settings' => [
                'description' => 'Default browser print dialog printer for receipt.',
            ],
        ]);

        $kitchenPrinter = Printer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Kitchen Printer',
            'code' => 'PRN-KITCHEN-01',
            'printer_type' => 'kitchen',
            'printer_role' => 'kitchen',
            'connection_type' => 'network',
            'network_protocol' => 'raw_tcp',
            'ip_address' => '192.168.1.100',
            'port' => 9100,
            'timeout_ms' => 5000,
            'paper_size' => '80mm',
            'is_default' => false,
            'settings' => [
                'description' => 'Demo network kitchen printer.',
            ],
        ]);

        $stockPrinter = Printer::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Stock Counter Printer',
            'code' => 'PRN-STOCK-01',
            'printer_type' => 'general',
            'printer_role' => 'stock',
            'connection_type' => 'network',
            'network_protocol' => 'raw_tcp',
            'ip_address' => '192.168.1.101',
            'port' => 9100,
            'timeout_ms' => 5000,
            'paper_size' => '80mm',
            'is_default' => false,
            'settings' => [
                'description' => 'Demo network stock printer for beer, wine, and direct inventory items.',
            ],
        ]);

        Menu::query()
            ->where('company_id', $company->id)
            ->whereIn('name', ['Coca Cola', 'Mineral Water', 'Fresh Orange Juice', 'Iced Lemon Tea'])
            ->update([
                'print_route' => 'stock',
                'printer_id' => $stockPrinter->id,
            ]);

        Menu::query()
            ->where('company_id', $company->id)
            ->whereNotIn('name', ['Coca Cola', 'Mineral Water', 'Fresh Orange Juice', 'Iced Lemon Tea'])
            ->where('menu_type', 'product')
            ->update([
                'print_route' => 'kitchen',
                'printer_id' => $kitchenPrinter->id,
            ]);

        Menu::query()
            ->where('company_id', $company->id)
            ->where('menu_type', 'service')
            ->update([
                'print_route' => 'cashier',
                'printer_id' => $receiptPrinter->id,
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

        PrintTemplate::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Default Stock Pick Ticket 80mm',
            'code' => 'TPL-STOCK-80',
            'template_type' => 'stock_ticket',
            'paper_size' => '80mm',
            'is_default' => true,
            'layout_config' => [
                'show_room_table' => true,
                'show_order_no' => true,
                'show_order_time' => true,
                'show_item_note' => true,
            ],
        ]);

        PrintTemplate::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Default Cancel Slip 80mm',
            'code' => 'TPL-CANCEL-80',
            'template_type' => 'cancel_slip',
            'paper_size' => '80mm',
            'is_default' => true,
            'layout_config' => [
                'show_room_table' => true,
                'show_order_no' => true,
                'show_order_time' => true,
                'show_item_note' => true,
                'title' => 'CANCEL SLIP',
            ],
        ]);
    }
}

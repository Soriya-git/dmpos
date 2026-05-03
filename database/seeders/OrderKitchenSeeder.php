<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DiningResource;
use App\Models\DiningSession;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketLine;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\PosTerminal;
use App\Support\DocumentNumber;
use Illuminate\Database\Seeder;

class OrderKitchenSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $branch = Branch::first();
        $customer = Customer::where('is_general_customer', true)->first();
        $resource = DiningResource::first();
        $terminal = PosTerminal::first();

        if (! $company || ! $branch || ! $customer || ! $resource || ! $terminal) {
            return;
        }

        $session = DiningSession::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'pos_terminal_id' => $terminal->id,
            'customer_id' => $customer->id,
            'dining_resource_id' => $resource->id,
            'session_no' => DocumentNumber::make(DiningSession::class, 'session_no', 'DS'),
            'guest_count' => 4,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'dining_session_id' => $session->id,
            'order_no' => DocumentNumber::make(Order::class, 'order_no', 'OR'),
            'status' => 'sent_to_kitchen',
            'sent_to_kitchen_at' => now(),
            'note' => 'Demo order',
        ]);

        $menus = Menu::take(2)->get();

        foreach ($menus as $menu) {
            $qty = 1;

            $subtotal = $menu->base_price * $qty;

            OrderLine::create([
                'order_id' => $order->id,
                'menu_id' => $menu->id,
                'menu_name_snapshot' => $menu->name,
                'menu_type_snapshot' => $menu->menu_type,
                'quantity' => $qty,
                'unit_price' => $menu->base_price,
                'discount_amount' => 0,
                'tax_rate_snapshot' => 0,
                'tax_amount' => 0,
                'line_subtotal' => $subtotal,
                'line_total' => $subtotal,
                'status' => 'ordered',
            ]);
        }

        $ticket = KitchenTicket::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'order_id' => $order->id,
            'dining_session_id' => $session->id,
            'dining_resource_id' => $resource->id,
            'ticket_no' => DocumentNumber::make(KitchenTicket::class, 'ticket_no', 'KT'),
            'status' => 'pending',
        ]);

        foreach ($order->orderLines as $line) {
            KitchenTicketLine::create([
                'kitchen_ticket_id' => $ticket->id,
                'order_line_id' => $line->id,
                'menu_id' => $line->menu_id,
                'menu_name_snapshot' => $line->menu_name_snapshot,
                'quantity' => $line->quantity,
                'status' => 'pending',
                'note' => $line->note,
            ]);
        }

        $resource->update([
            'status' => 'occupied',
        ]);
    }
}

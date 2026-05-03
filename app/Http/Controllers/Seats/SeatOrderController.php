<?php

namespace App\Http\Controllers\Seats;

use App\Http\Controllers\Controller;
use App\Models\DiningSession;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeatOrderController extends Controller
{
    public function show(Request $request, DiningSession $diningSession)
    {
        $activePosSession = $this->activePosSession($request);

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        if ($diningSession->status !== 'open') {
            return redirect()
                ->route('seats.index')
                ->with('error', 'This dining session is not open.');
        }

        $diningSession->load([
            'diningResource.diningResourceType',
            'customer',
            'orders.orderLines.menu',
        ]);

        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $menus = Menu::query()
            ->with(['menuCategory', 'defaultPrice'])
            ->where('company_id', $activePosSession->company_id)
            ->where(function ($q) use ($activePosSession) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $activePosSession->branch_id);
            })
            ->where('is_active', true)
            ->where('is_available', true)
            ->when($categoryId, fn ($q) => $q->where('menu_category_id', $categoryId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($menu) => [
                'id' => $menu->id,
                'name' => $menu->name,
                'code' => $menu->code,
                'image' => $menu->image,
                'category_name' => $menu->menuCategory?->name,
                'menu_type' => $menu->menu_type,
                'unit_price' => (float) ($menu->defaultPrice?->price ?? $menu->base_price ?? 0),
            ]);

        $categories = MenuCategory::query()
            ->where('company_id', $activePosSession->company_id)
            ->where(function ($q) use ($activePosSession) {
                $q->whereNull('branch_id')
                    ->orWhere('branch_id', $activePosSession->branch_id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $cartOrder = $this->getCartOrder($diningSession);
        $cartOrder->load(['orderLines.menu']);

        return Inertia::render('Seats/Orders', [
            'posSession' => [
                'id' => $activePosSession->id,
                'session_no' => $activePosSession->session_no ?? $activePosSession->session_number ?? ('POS-'.$activePosSession->id),
            ],
            'diningSession' => [
                'id' => $diningSession->id,
                'session_no' => $diningSession->session_no,
                'seat_name' => $diningSession->diningResource?->name,
                'seat_type' => $diningSession->diningResource?->diningResourceType?->name,
                'customer_name' => $diningSession->customer?->name
                    ?? $diningSession->customer?->customer_name
                    ?? 'Walk-in / General Customer',
                'customer_phone' => $diningSession->customer?->phone_number
                    ?? $diningSession->customer?->phone
                    ?? $diningSession->customer?->customer_phone
                    ?? $diningSession->customer?->mobile
                    ?? null,
            ],
            'menus' => $menus,
            'categories' => $categories,
            'cart' => $this->formatOrder($cartOrder),
            'historyOrders' => $diningSession->orders()
                ->with(['orderLines.menu'])
                ->where('id', '!=', $cartOrder->id)
                ->latest()
                ->get()
                ->map(fn ($order) => $this->formatOrder($order)),
            'filters' => [
                'category_id' => $categoryId,
                'search' => $search,
            ],
        ]);
    }

    public function addItem(Request $request, DiningSession $diningSession)
    {
        $data = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'qty' => ['nullable', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $menu = Menu::with('defaultPrice')->findOrFail($data['menu_id']);

        DB::transaction(function () use ($diningSession, $menu, $data) {
            $qty = (float) ($data['qty'] ?? 1);
            $unitPrice = (float) ($menu->defaultPrice?->price ?? $menu->base_price ?? 0);
            $taxRate = 10;

            $order = $this->getCartOrder($diningSession);

            $line = OrderLine::where('order_id', $order->id)
                ->where('menu_id', $menu->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($line) {
                $line->quantity = (float) $line->quantity + $qty;
                $line->note = $data['note'] ?? $line->note;
                $this->calculateLine($line);
                $line->save();

                return;
            }

            $line = new OrderLine([
                'order_id' => $order->id,
                'menu_id' => $menu->id,
                'menu_name_snapshot' => $menu->name,
                'menu_type_snapshot' => $menu->menu_type,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'tax_id' => $menu->tax_id,
                'tax_rate_snapshot' => $taxRate,
                'note' => $data['note'] ?? null,
                'status' => 'ordered',
            ]);

            $this->calculateLine($line);
            $line->save();
        });

        return back();
    }

    public function updateItem(Request $request, DiningSession $diningSession, OrderLine $orderLine)
    {
        $data = $request->validate([
            'qty' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        DB::transaction(function () use ($orderLine, $data) {
            $orderLine->quantity = (float) $data['qty'];
            if (array_key_exists('note', $data)) {
                $orderLine->note = $data['note'] ?? null;
            }
            $this->calculateLine($orderLine);
            $orderLine->save();
        });

        return back();
    }

    public function removeItem(DiningSession $diningSession, OrderLine $orderLine)
    {
        $this->ensureLineBelongsToSession($diningSession, $orderLine);

        $orderLine->delete();

        return back();
    }

    public function clearItems(DiningSession $diningSession)
    {
        $order = $this->getCartOrder($diningSession);

        $order->orderLines()->delete();

        return back();
    }

    public function sendToKitchen(DiningSession $diningSession)
    {
        $order = $this->getCartOrder($diningSession);
        $order->load('orderLines');

        if ($order->orderLines->isEmpty()) {
            return back()->with('error', 'Please add menu items before sending to kitchen.');
        }

        $order->update([
            'status' => 'sent_to_kitchen',
            'sent_to_kitchen_at' => now(),
        ]);

        return back()->with('success', 'Order sent to kitchen.');
    }

    private function activePosSession(Request $request): ?PosSession
    {
        return PosSession::where('opened_by', $request->user()->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();
    }

    private function getCartOrder(DiningSession $diningSession): Order
    {
        return Order::firstOrCreate(
            [
                'dining_session_id' => $diningSession->id,
                'status' => 'draft',
            ],
            [
                'company_id' => $diningSession->company_id,
                'branch_id' => $diningSession->branch_id,
                'order_no' => $this->makeDiningSessionOrderNumber($diningSession),
                'created_by' => auth()->id(),
            ]
        );
    }

    private function makeDiningSessionOrderNumber(DiningSession $diningSession): string
    {
        $prefix = $diningSession->session_no.'-OR';

        $lastOrderNo = Order::query()
            ->where('dining_session_id', $diningSession->id)
            ->where('order_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_no')
            ->value('order_no');

        $sequence = 1;

        if (is_string($lastOrderNo) && str_starts_with($lastOrderNo, $prefix)) {
            $sequence = ((int) substr($lastOrderNo, strlen($prefix))) + 1;
        }

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function calculateLine(OrderLine $line): void
    {
        $subtotal = ((float) $line->quantity * (float) $line->unit_price) - (float) $line->discount_amount;
        $taxAmount = $subtotal * ((float) $line->tax_rate_snapshot / 100);

        $line->line_subtotal = $subtotal;
        $line->tax_amount = $taxAmount;
        $line->line_total = $subtotal + $taxAmount;
    }

    private function ensureLineBelongsToSession(DiningSession $diningSession, OrderLine $orderLine): void
    {
        $orderLine->loadMissing('order');

        abort_if(
            ! $orderLine->order || $orderLine->order->dining_session_id !== $diningSession->id,
            403,
            'This order line does not belong to this dining session.'
        );
    }

    private function formatOrder(Order $order): array
    {
        $lines = $order->orderLines;

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'subtotal' => (float) $lines->sum('line_subtotal'),
            'tax_amount' => (float) $lines->sum('tax_amount'),
            'total_amount' => (float) $lines->sum('line_total'),
            'created_at' => $order->created_at?->format('Y-m-d H:i'),
            'lines' => $lines->map(fn ($line) => [
                'id' => $line->id,
                'menu_id' => $line->menu_id,
                'menu_name' => $line->menu_name_snapshot,
                'qty' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'subtotal' => (float) $line->line_subtotal,
                'tax_amount' => (float) $line->tax_amount,
                'total_amount' => (float) $line->line_total,
                'note' => $line->note,
                'status' => $line->status,
            ]),
        ];
    }
}

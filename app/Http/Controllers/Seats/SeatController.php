<?php

namespace App\Http\Controllers\Seats;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DiningResource;
use App\Models\DiningResourceType;
use App\Models\DiningSession;
use App\Models\MenuPriceList;
use App\Models\PosSession;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SeatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activePosSession = PosSession::with(['branch', 'posTerminal', 'opener'])
            ->where('opened_by', $user->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first before accessing orders.');
        }

        $status = $request->query('status');
        $typeId = $request->query('type_id');
        $search = $request->query('search');

        $resources = DiningResource::query()
            ->with([
                'diningResourceType',
                'activeSession.customer',
                'activeSession.invoices',
                'activeSession.menuPriceList',
                'activeSession.orders.orderLines.invoiceLines',
                'activeSession.resourceBooking.customer',
            ])
            ->where('branch_id', $activePosSession->branch_id)
            ->where('is_active', true)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($typeId, fn ($q) => $q->where('dining_resource_type_id', $typeId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($resource) {
                $session = $resource->activeSession;
                $customer = $this->customerFor($session?->customer_id);
                $customerName = null;
                $customerPhone = null;

                if ($customer) {
                    $customerName = $customer->name ?? $customer->customer_name;
                    $customerPhone = $customer->phone_number
                        ?? $customer->phone
                        ?? $customer->customer_phone
                        ?? $customer->mobile;
                }

                return [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'code' => $resource->code,
                    'capacity' => $resource->capacity,
                    'status' => $resource->status,
                    'image' => $resource->image,
                    'description' => $resource->description,
                    'type' => [
                        'id' => $resource->diningResourceType?->id,
                        'name' => $resource->diningResourceType?->name,
                    ],
                    'active_session' => $session ? [
                        'id' => $session->id,
                        'session_no' => $session->session_no,
                        'status' => $session->status,
                        'guest_count' => $session->guest_count,
                        'opened_at' => $session->opened_at?->format('Y-m-d H:i'),
                        'customer_name' => $customerName,
                        'customer_phone' => $customerPhone,
                        'price_list_name' => $session->menuPriceList?->name,
                        'can_close_order' => $this->canCloseOrder($session),
                    ] : null,
                ];
            });

        $types = DiningResourceType::query()
            ->where('company_id', $activePosSession->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $customers = Customer::query()
            ->where('company_id', $activePosSession->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('phone_number')
            ->limit(250)
            ->get(['id', 'name', 'phone_number']);

        $priceLists = MenuPriceList::query()
            ->where('company_id', $activePosSession->company_id)
            ->where('is_active', true)
            ->where(function ($query) use ($activePosSession): void {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $activePosSession->branch_id);
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_default'])
            ->map(fn (MenuPriceList $priceList): array => [
                'id' => $priceList->id,
                'name' => $priceList->name,
                'code' => $priceList->code,
                'isDefault' => $priceList->is_default,
            ]);

        return Inertia::render('Seats/Index', [
            'posSession' => [
                'id' => $activePosSession->id,
                'session_no' => $activePosSession->session_no ?? $activePosSession->session_number ?? ('POS-'.$activePosSession->id),
                'branch_name' => $activePosSession->branch?->name,
                'terminal_name' => $activePosSession->posTerminal?->name,
                'opened_by' => $activePosSession->opener?->name,
                'opened_at' => $activePosSession->opened_at?->format('Y-m-d H:i'),
            ],
            'resources' => $resources,
            'types' => $types,
            'customers' => $customers,
            'priceLists' => $priceLists,
            'filters' => [
                'status' => $status,
                'type_id' => $typeId,
                'search' => $search,
            ],
        ]);
    }

    public function checkIn(Request $request, DiningResource $resource)
    {
        $user = $request->user();

        $activePosSession = PosSession::where('opened_by', $user->id)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if (! $activePosSession) {
            return redirect()
                ->route('pos-sessions.index')
                ->with('error', 'Please open POS session first.');
        }

        $data = $request->validate([
            'guest_count' => ['nullable', 'integer', 'min:1'],
            'customer_id' => [
                'nullable',
                'integer',
                Rule::exists('customers', 'id')->where('company_id', $activePosSession->company_id),
            ],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'menu_price_list_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_price_lists', 'id')->where('company_id', $activePosSession->company_id),
            ],
        ]);

        if ($resource->status === 'occupied') {
            return back()->with('error', 'This seat is already occupied.');
        }

        DB::transaction(function () use ($resource, $activePosSession, $data, $user) {
            $customerId = $data['customer_id'] ?? null;
            $phoneNumber = trim((string) ($data['customer_phone'] ?? ''));

            if (! $customerId && $phoneNumber !== '') {
                $customer = Customer::firstOrCreate(
                    [
                        'company_id' => $activePosSession->company_id,
                        'phone_number' => $phoneNumber,
                    ],
                    [
                        'branch_id' => $activePosSession->branch_id,
                        'name' => $data['customer_name'] ?: null,
                        'is_active' => true,
                    ]
                );

                if (($data['customer_name'] ?? null) && ! $customer->name) {
                    $customer->update(['name' => $data['customer_name']]);
                }

                $customerId = $customer->id;
            }

            DiningSession::create([
                'company_id' => $activePosSession->company_id,
                'branch_id' => $activePosSession->branch_id,
                'pos_terminal_id' => $activePosSession->pos_terminal_id,
                'customer_id' => $customerId,
                'dining_resource_id' => $resource->id,
                'menu_price_list_id' => $data['menu_price_list_id'] ?? $this->defaultPriceListId($activePosSession),
                'session_no' => DocumentNumber::make(DiningSession::class, 'session_no', 'DS'),
                'guest_count' => $data['guest_count'] ?? null,
                'status' => 'open',
                'opened_at' => now(),
                'opened_by' => $user->id,
                'note' => $data['note'] ?? null,
            ]);

            $resource->update([
                'status' => 'occupied',
            ]);
        });

        return back()->with('success', 'Seat checked in successfully.');
    }

    private function customerFor(?int $customerId): ?Customer
    {
        if (! $customerId) {
            return null;
        }

        return Customer::query()->find($customerId);
    }

    private function canCloseOrder(DiningSession $session): bool
    {
        $hasSelectedItems = $session->orders->contains(function ($order): bool {
            return $order->orderLines->isNotEmpty();
        });

        if (! $hasSelectedItems) {
            return true;
        }

        if (! in_array($session->status, ['invoiced', 'paid', 'pay_later'], true)) {
            return false;
        }

        return ! $session->orders
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->contains(function ($order): bool {
                return $order->orderLines->contains(function ($line): bool {
                    return $line->invoiceLines->isEmpty();
                });
            });
    }

    private function defaultPriceListId(PosSession $posSession): ?int
    {
        return MenuPriceList::query()
            ->where('company_id', $posSession->company_id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->where(function ($query) use ($posSession): void {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $posSession->branch_id);
            })
            ->orderByRaw('case when branch_id = ? then 0 when branch_id is null then 1 else 2 end', [$posSession->branch_id])
            ->value('id');
    }
}

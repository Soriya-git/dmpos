<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\MembershipCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $customers = Customer::query()
            ->with([
                'customerGroup:id,name,code',
                'membershipCards' => fn ($query) => $query
                    ->with('balances')
                    ->orderBy('card_no'),
            ])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderByDesc('is_general_customer')
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'code' => 'CUS-'.str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT),
                'name' => $customer->name ?: 'Unnamed Customer',
                'phone' => $customer->phone_number,
                'email' => $customer->email,
                'address' => $customer->address,
                'group' => $customer->customerGroup?->name ?? 'Ungrouped',
                'status' => $customer->is_active ? 'approved' : 'cancelled',
                'membership_cards' => $customer->membershipCards
                    ->map(fn ($card): array => [
                        'id' => $card->id,
                        'customerId' => $card->customer_id,
                        'cardNo' => $card->card_no,
                        'cardName' => $card->card_name ?: 'Member Card',
                        'status' => $card->status,
                        'issuedDate' => $card->issued_date?->toDateString() ?? '',
                        'expiredDate' => $card->expired_date?->toDateString() ?? '',
                        'remark' => $card->remark ?? '',
                        'balances' => $card->balances
                            ->map(fn ($balance): array => [
                                'currency' => $balance->currency,
                                'balance' => (float) $balance->balance,
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all(),
                'membership_card_count' => $customer->membershipCards->count(),
            ]);

        $groups = CustomerGroup::query()
            ->withCount(['customers' => fn ($query) => $query->when($companyId, fn ($query) => $query->where('company_id', $companyId))])
            ->orderBy('name')
            ->get()
            ->map(fn (CustomerGroup $group): array => [
                'id' => $group->id,
                'code' => $group->code ?: 'GRP-'.str_pad((string) $group->id, 3, '0', STR_PAD_LEFT),
                'name' => $group->name,
                'description' => $group->description,
                'members' => $group->customers_count,
                'status' => 'approved',
            ]);

        return Inertia::render('MasterData/Customers', [
            'customers' => $customers,
            'groups' => $groups,
        ]);
    }

    public function storeMembershipCard(Request $request, Customer $customer)
    {
        $this->ensureCustomerAccess($request, $customer);

        $data = $this->validateMembershipCard($request);

        DB::transaction(function () use ($request, $customer, $data): void {
            $card = $customer->membershipCards()->create([
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'card_no' => $data['card_no'],
                'card_name' => $data['card_name'] ?? null,
                'status' => $data['status'],
                'issued_date' => $data['issued_date'] ?? null,
                'expired_date' => $data['expired_date'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);

        });

        return back()->with('success', 'Membership card saved.');
    }

    public function updateMembershipCard(Request $request, Customer $customer, MembershipCard $membershipCard)
    {
        $this->ensureCustomerAccess($request, $customer);

        abort_if((int) $membershipCard->customer_id !== (int) $customer->id, 404);

        $data = $this->validateMembershipCard($request, $membershipCard);

        DB::transaction(function () use ($request, $membershipCard, $data): void {
            $membershipCard->update([
                'card_no' => $data['card_no'],
                'card_name' => $data['card_name'] ?? null,
                'status' => $data['status'],
                'issued_date' => $data['issued_date'] ?? null,
                'expired_date' => $data['expired_date'] ?? null,
                'remark' => $data['remark'] ?? null,
                'updated_by' => $request->user()?->id,
            ]);

        });

        return back()->with('success', 'Membership card saved.');
    }

    private function validateMembershipCard(Request $request, ?MembershipCard $membershipCard = null): array
    {
        $ignoreId = $membershipCard?->id;

        return $request->validate([
            'card_no' => ['required', 'string', 'max:255', 'unique:membership_cards,card_no'.($ignoreId ? ','.$ignoreId : '')],
            'card_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,blocked,expired,cancelled'],
            'issued_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'remark' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function ensureCustomerAccess(Request $request, Customer $customer): void
    {
        $companyId = $request->user()?->company_id;

        abort_if($companyId && (int) $customer->company_id !== (int) $companyId, 403);
    }
}

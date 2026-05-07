<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $customers = Customer::query()
            ->with('customerGroup:id,name,code')
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
}

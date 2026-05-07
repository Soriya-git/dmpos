<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $suppliers = Supplier::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'contactPerson' => $supplier->contact_person,
                'phone' => $supplier->phone_number,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'status' => $supplier->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/Suppliers', [
            'suppliers' => $suppliers,
        ]);
    }
}

<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:draft,pending,approved'],
        ]);

        Supplier::create([
            'company_id' => $request->user()?->company_id,
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone_number' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => ($validated['status'] ?? 'pending') === 'approved',
        ]);

        return redirect()
            ->route('master-data.suppliers')
            ->with('success', 'Supplier created successfully.');
    }

    public function approve(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => true]);

        return redirect()->route('master-data.suppliers')
            ->with('success', 'Supplier approved.');
    }

    public function reject(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => false]);

        return redirect()->route('master-data.suppliers')
            ->with('success', 'Supplier rejected.');
    }

    public function cancel(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => false]);

        return redirect()->route('master-data.suppliers')
            ->with('success', 'Supplier deactivated.');
    }

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

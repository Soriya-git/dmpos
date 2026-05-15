<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyBranchController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $companies = Company::query()
            ->withCount('branches')
            ->when($companyId, fn ($query) => $query->whereKey($companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company): array => [
                'id' => $company->id,
                'code' => $company->code ?: 'COM-'.str_pad((string) $company->id, 3, '0', STR_PAD_LEFT),
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'branchesCount' => $company->branches_count,
                'status' => $company->is_active ? 'approved' : 'cancelled',
            ]);

        $branches = Branch::query()
            ->with('company:id,name,code')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (Branch $branch): array => [
                'id' => $branch->id,
                'code' => $branch->code ?: 'BR-'.str_pad((string) $branch->id, 3, '0', STR_PAD_LEFT),
                'name' => $branch->name,
                'company' => $branch->company?->name ?? 'Unassigned',
                'companyId' => $branch->company_id,
                'phone' => $branch->phone,
                'vatNumber' => $branch->vat_number,
                'address' => $branch->address,
                'logo' => $branch->logo,
                'logoUrl' => $branch->logo ? Storage::url($branch->logo) : null,
                'paymentQrcode' => $branch->payment_qrcode,
                'paymentQrcodeUrl' => $branch->payment_qrcode ? Storage::url($branch->payment_qrcode) : null,
                'status' => $branch->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/CompanyBranches', [
            'companies' => $companies,
            'branches' => $branches,
        ]);
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $companyId = $request->user()?->company_id;

        abort_if($companyId && (int) $branch->company_id !== (int) $companyId, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', Rule::exists('companies', 'id')],
            'phone' => ['nullable', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'payment_qrcode' => ['nullable', 'image', 'max:2048'],
        ]);

        abort_if($companyId && (int) $data['company_id'] !== (int) $companyId, 403);

        if ($request->hasFile('logo')) {
            if ($branch->logo) {
                Storage::disk('public')->delete($branch->logo);
            }

            $data['logo'] = $request->file('logo')->store('branches/logos', 'public');
        } else {
            unset($data['logo']);
        }

        if ($request->hasFile('payment_qrcode')) {
            if ($branch->payment_qrcode) {
                Storage::disk('public')->delete($branch->payment_qrcode);
            }

            $data['payment_qrcode'] = $request->file('payment_qrcode')->store('branches/payment-qrcodes', 'public');
        } else {
            unset($data['payment_qrcode']);
        }

        $branch->update([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Branch has been updated.');
    }
}

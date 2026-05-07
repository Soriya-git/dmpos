<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
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
                'phone' => $branch->phone,
                'address' => $branch->address,
                'status' => $branch->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/CompanyBranches', [
            'companies' => $companies,
            'branches' => $branches,
        ]);
    }
}

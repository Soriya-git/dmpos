<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $taxes = Tax::query()
            ->with('company:id,name,code')
            ->withCount(['menus', 'orderLines'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (Tax $tax): array => [
                'id' => $tax->id,
                'code' => $tax->code ?: 'TAX-'.str_pad((string) $tax->id, 3, '0', STR_PAD_LEFT),
                'name' => $tax->name,
                'company' => $tax->company?->name ?? 'Company',
                'rate' => number_format((float) $tax->rate, 4),
                'rateLabel' => number_format((float) $tax->rate, 2).'%',
                'isDefault' => $tax->is_default,
                'menusCount' => $tax->menus_count,
                'orderLinesCount' => $tax->order_lines_count,
                'status' => $tax->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/Tax', [
            'taxes' => $taxes,
        ]);
    }
}

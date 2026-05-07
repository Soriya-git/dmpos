<?php

namespace App\Http\Controllers\MasterData;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRateController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $rates = ExchangeRate::query()
            ->with(['company:id,name,code', 'creator:id,name'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderByDesc('effective_date')
            ->orderBy('from_currency')
            ->get()
            ->map(fn (ExchangeRate $rate): array => [
                'id' => $rate->id,
                'code' => 'EXR-'.str_pad((string) $rate->id, 4, '0', STR_PAD_LEFT),
                'company' => $rate->company?->name ?? 'Company',
                'fromCurrency' => $rate->from_currency,
                'toCurrency' => $rate->to_currency,
                'pair' => $rate->from_currency.' / '.$rate->to_currency,
                'rate' => number_format((float) $rate->rate, 4),
                'effectiveDate' => $rate->effective_date?->toDateString(),
                'createdBy' => $rate->creator?->name,
                'status' => $rate->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/ExchangeRate', [
            'rates' => $rates,
        ]);
    }
}

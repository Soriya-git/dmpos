<?php

namespace App\Http\Controllers\MasterData;

use App\Models\PosSession;
use App\Models\PosTerminal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosTerminalController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $openSessions = PosSession::query()
            ->with(['branch:id,name,code', 'posTerminal:id,name,code', 'opener:id,name'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('status', 'open')
            ->latest('opened_at')
            ->get();

        $openSessionsByTerminal = $openSessions->keyBy('pos_terminal_id');

        $terminals = PosTerminal::query()
            ->with('branch:id,name,code')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(function (PosTerminal $terminal) use ($openSessionsByTerminal): array {
                $session = $openSessionsByTerminal->get($terminal->id);

                return [
                    'id' => $terminal->id,
                    'code' => $terminal->code ?: 'POS-'.str_pad((string) $terminal->id, 3, '0', STR_PAD_LEFT),
                    'name' => $terminal->name,
                    'branch' => $terminal->branch?->name ?? 'Unassigned',
                    'deviceType' => $terminal->device_type ?? 'terminal',
                    'sessionStatus' => $session ? 'open' : 'available',
                    'currentSessionNo' => $session?->session_no,
                    'openedBy' => $session?->opener?->name,
                    'openedAt' => $session?->opened_at?->toDateTimeString(),
                    'status' => $terminal->is_active ? 'approved' : 'cancelled',
                ];
            });

        $sessions = $openSessions->map(fn (PosSession $session): array => [
            'id' => $session->id,
            'code' => $session->session_no,
            'terminal' => $session->posTerminal?->name ?? 'POS Terminal',
            'branch' => $session->branch?->name ?? 'Branch',
            'openedBy' => $session->opener?->name ?? 'Unknown',
            'openedAt' => $session->opened_at?->toDateTimeString(),
            'openingCashUsd' => number_format((float) $session->opening_cash_usd, 2),
            'openingCashKhr' => number_format((float) $session->opening_cash_khr, 2),
            'totalSalesUsd' => number_format((float) $session->total_sales_usd, 2),
            'status' => 'approved',
        ]);

        return Inertia::render('MasterData/POS', [
            'terminals' => $terminals,
            'sessions' => $sessions,
        ]);
    }
}

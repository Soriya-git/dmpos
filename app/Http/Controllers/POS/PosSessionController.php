<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosSessionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $currentSession = $this->currentUserOpenSession($request);

        return Inertia::render('POS/Index', [
            'sessions' => PosSession::with(['branch', 'posTerminal', 'opener', 'closer'])
                ->where('branch_id', $user->branch_id)
                ->latest()
                ->limit(50)
                ->get(),

            'terminals' => PosTerminal::with(['branch'])
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'currentSession' => $currentSession,
        ]);
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'pos_terminal_id' => ['required', 'exists:pos_terminals,id'],
            'opening_cash_usd' => ['required', 'numeric', 'min:0'],
            'opening_cash_khr' => ['required', 'numeric', 'min:0'],
            'opening_note' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $user = $request->user();

            if (! $user->branch_id) {
                return back()->withErrors([
                    'pos_terminal_id' => 'Your user account is not assigned to a branch.',
                ]);
            }

            $userAlreadyOpen = PosSession::where('opened_by', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->exists();

            if ($userAlreadyOpen) {
                return back()->withErrors([
                    'pos_terminal_id' => 'You already have an open POS session.',
                ]);
            }

            $terminal = PosTerminal::where('id', $data['pos_terminal_id'])
                ->where('branch_id', $user->branch_id)
                ->where('is_active', true)
                ->first();

            if (! $terminal) {
                return back()->withErrors([
                    'pos_terminal_id' => 'Please select an active POS terminal under your branch.',
                ]);
            }

            $alreadyOpen = PosSession::where('pos_terminal_id', $terminal->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->exists();

            if ($alreadyOpen) {
                return back()->withErrors([
                    'pos_terminal_id' => 'This POS terminal already has an open session.',
                ]);
            }

            PosSession::create([
                'company_id' => $terminal->company_id,
                'branch_id' => $terminal->branch_id,
                'pos_terminal_id' => $terminal->id,
                'session_no' => DocumentNumber::make(PosSession::class, 'session_no', 'PS'),
                'status' => 'open',
                'opening_cash_usd' => $data['opening_cash_usd'] ?? 0,
                'opening_cash_khr' => $data['opening_cash_khr'] ?? 0,
                'opened_at' => now(),
                'opened_by' => $user->id,
                'opening_note' => $data['opening_note'] ?? null,
            ]);

            return redirect()
                ->route('seats.index')
                ->with('success', 'POS session opened successfully.');
        });
    }

    public function close(Request $request, PosSession $posSession)
    {
        $data = $request->validate([
            'actual_cash_usd' => ['nullable', 'numeric', 'min:0'],
            'actual_cash_khr' => ['nullable', 'numeric', 'min:0'],
            'closing_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($posSession->status !== 'open') {
            return back()->withErrors([
                'session' => 'Only open sessions can be closed.',
            ]);
        }

        if ($posSession->opened_by !== $request->user()->id) {
            return back()->withErrors([
                'session' => 'Only the user who opened this POS session can close it.',
            ]);
        }

        $expectedUsd = $posSession->opening_cash_usd + $posSession->total_cash_usd;
        $expectedKhr = $posSession->opening_cash_khr + $posSession->total_cash_khr;
        $actualUsd = $data['actual_cash_usd'] ?? $expectedUsd;
        $actualKhr = $data['actual_cash_khr'] ?? $expectedKhr;

        $posSession->update([
            'status' => 'closed',
            'expected_cash_usd' => $expectedUsd,
            'expected_cash_khr' => $expectedKhr,
            'actual_cash_usd' => $actualUsd,
            'actual_cash_khr' => $actualKhr,
            'cash_variance_usd' => $actualUsd - $expectedUsd,
            'cash_variance_khr' => $actualKhr - $expectedKhr,
            'closed_at' => now(),
            'closed_by' => $request->user()->id,
            'closing_note' => $data['closing_note'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'POS session closed successfully. The terminal is available for the next shift.');
    }

    private function currentUserOpenSession(Request $request): ?PosSession
    {
        return PosSession::with(['branch', 'posTerminal', 'opener', 'closer'])
            ->where('opened_by', $request->user()->id)
            ->where('status', 'open')
            ->latest()
            ->first();
    }
}

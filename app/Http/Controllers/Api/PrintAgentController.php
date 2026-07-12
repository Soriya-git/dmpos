<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PrintJob;
use App\Services\RawTcpPrinter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrintAgentController extends Controller
{
    public function claim(Request $request, RawTcpPrinter $printerService): JsonResponse
    {
        $data = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);
        $branch = $this->branch($request);
        $limit = (int) ($data['limit'] ?? 10);

        $jobs = DB::transaction(function () use ($branch, $limit) {
            $jobs = PrintJob::query()
                ->with('printer')
                ->where('branch_id', $branch->id)
                ->where('company_id', $branch->company_id)
                ->where('status', 'pending')
                ->whereNotNull('printer_id')
                ->whereHas('printer', fn ($query) => $query
                    ->where('is_active', true)
                    ->where('connection_type', 'network')
                    ->where('network_protocol', 'raw_tcp'))
                ->where(fn ($query) => $query
                    ->whereNull('claimed_at')
                    ->orWhere('claimed_at', '<', now()->subMinutes(2)))
                ->orderBy('id')
                ->lockForUpdate()
                ->limit($limit)
                ->get();

            $jobs->each(function (PrintJob $job): void {
                $job->forceFill([
                    'claim_token' => (string) Str::uuid(),
                    'claimed_at' => now(),
                    'error_message' => null,
                ])->save();
            });

            return $jobs;
        });

        return response()->json(['data' => $jobs->map(fn (PrintJob $job) => [
            'id' => $job->id,
            'job_no' => $job->job_no,
            'claim_token' => $job->claim_token,
            'printer_ip' => $job->printer->ip_address ?: $job->printer->host_name,
            'printer_port' => (int) ($job->printer->port ?: 9100),
            'timeout_ms' => (int) ($job->printer->timeout_ms ?: 5000),
            'payload_b64' => base64_encode($printerService->payload($job)),
        ])->values()]);
    }

    public function printed(Request $request, PrintJob $printJob): JsonResponse
    {
        $this->authorizeJob($request, $printJob);
        $data = $request->validate(['claim_token' => ['required', 'uuid']]);
        abort_unless(hash_equals((string) $printJob->claim_token, $data['claim_token']), 409, 'Print-job claim is no longer valid.');

        $printJob->forceFill([
            'status' => 'printed',
            'print_count' => $printJob->print_count + 1,
            'printed_at' => now(),
            'claim_token' => null,
            'claimed_at' => null,
            'error_message' => null,
        ])->save();

        return response()->json(['message' => 'Print job marked as printed.']);
    }

    public function failed(Request $request, PrintJob $printJob): JsonResponse
    {
        $this->authorizeJob($request, $printJob);
        $data = $request->validate([
            'claim_token' => ['required', 'uuid'],
            'error' => ['required', 'string', 'max:2000'],
        ]);
        abort_unless(hash_equals((string) $printJob->claim_token, $data['claim_token']), 409, 'Print-job claim is no longer valid.');

        $printJob->forceFill([
            'status' => 'failed',
            'claim_token' => null,
            'claimed_at' => null,
            'error_message' => $data['error'],
        ])->save();

        return response()->json(['message' => 'Print job marked as failed.']);
    }

    private function authorizeJob(Request $request, PrintJob $printJob): void
    {
        $branch = $this->branch($request);
        abort_unless($printJob->branch_id === $branch->id && $printJob->company_id === $branch->company_id, 404);
        abort_unless($printJob->status === 'pending', 409, 'Print job is no longer pending.');
    }

    private function branch(Request $request): Branch
    {
        $branch = $request->attributes->get('print_agent_branch');
        abort_unless($branch instanceof Branch, 401);

        return $branch;
    }
}

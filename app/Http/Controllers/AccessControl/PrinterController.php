<?php

namespace App\Http\Controllers\AccessControl;

use App\Models\Branch;
use App\Models\Printer;
use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PrinterController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;
        $userBranchId = $request->user()?->branch_id;

        $printers = Printer::query()
            ->with('branch:id,name,code')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderByDesc('is_default')
            ->orderBy('printer_role')
            ->orderBy('name')
            ->get()
            ->map(fn (Printer $printer): array => [
                'id' => $printer->id,
                'branchId' => $printer->branch_id,
                'branch' => $printer->branch?->name ?? 'Unassigned',
                'code' => $printer->code,
                'name' => $printer->name,
                'printerType' => $printer->printer_type,
                'printerRole' => $printer->printer_role,
                'connectionType' => $printer->connection_type,
                'networkProtocol' => $printer->network_protocol,
                'ipAddress' => $printer->ip_address,
                'hostName' => $printer->host_name,
                'port' => $printer->port,
                'timeoutMs' => $printer->timeout_ms,
                'paperSize' => $printer->paper_size,
                'isDefault' => $printer->is_default,
                'isActive' => $printer->is_active,
                'description' => data_get($printer->settings, 'description'),
            ]);

        $branchOptions = Branch::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Branch $branch): array => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ]);

        return Inertia::render('AccessControl/PrinterIndex', [
            'printers' => $printers,
            'branchOptions' => $branchOptions,
            'defaultBranchId' => $userBranchId,
        ]);
    }

    public function logs(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $logs = PrintJob::query()
            ->with([
                'branch:id,name,code',
                'printer:id,name,code,printer_role',
                'printedBy:id,name',
            ])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->latest()
            ->limit(250)
            ->get()
            ->map(fn (PrintJob $job): array => [
                'id' => $job->id,
                'jobNo' => $job->job_no,
                'jobType' => $job->job_type,
                'status' => $job->status,
                'branch' => $job->branch?->name ?? 'Unassigned',
                'printer' => $job->printer?->name ?? 'Default Printer',
                'printerRole' => $job->printer?->printer_role ?? data_get($job->payload, 'route'),
                'referenceType' => $job->reference_type,
                'referenceNo' => $job->reference_no,
                'printCount' => $job->print_count,
                'printedAt' => $job->printed_at?->format('Y-m-d H:i'),
                'printedBy' => $job->printedBy?->name,
                'errorMessage' => $job->error_message,
                'createdAt' => $job->created_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('AccessControl/PrinterLogs', [
            'logs' => $logs,
        ]);
    }

    public function destroyLogs(Request $request): RedirectResponse
    {
        $companyId = $request->user()?->company_id;

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $deleted = PrintJob::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereIn('id', $data['ids'])
            ->delete();

        return redirect()
            ->route('access-control.printer-logs')
            ->with('success', "{$deleted} printer log(s) deleted.");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePrinter($request);
        $companyId = $request->user()?->company_id;
        $description = $data['description'] ?? null;
        unset($data['description']);

        if ((bool) $data['is_default']) {
            $this->clearDefaultForRole((int) $data['branch_id'], $data['printer_role']);
        }

        Printer::create([
            ...$data,
            'company_id' => $companyId,
            'settings' => [
                'description' => $description,
            ],
        ]);

        return redirect()
            ->route('access-control.printers')
            ->with('success', 'Printer configuration created.');
    }

    public function update(Request $request, Printer $printer): RedirectResponse
    {
        $companyId = $request->user()?->company_id;

        abort_if($companyId && (int) $printer->company_id !== (int) $companyId, 403);

        $data = $this->validatePrinter($request, $printer);
        $description = $data['description'] ?? null;
        unset($data['description']);

        if ((bool) $data['is_default']) {
            $this->clearDefaultForRole((int) $data['branch_id'], $data['printer_role'], $printer->id);
        }

        $printer->update([
            ...$data,
            'settings' => [
                ...($printer->settings ?? []),
                'description' => $description,
            ],
        ]);

        return redirect()
            ->route('access-control.printers')
            ->with('success', 'Printer configuration updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePrinter(Request $request, ?Printer $printer = null): array
    {
        $companyId = $request->user()?->company_id;

        return $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('printers', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($printer),
            ],
            'printer_type' => ['required', Rule::in(['receipt', 'invoice', 'kitchen', 'general'])],
            'printer_role' => ['required', Rule::in(['cashier', 'kitchen', 'stock', 'bar', 'general'])],
            'connection_type' => ['required', Rule::in(['native_app', 'usb_bridge', 'network', 'browser_dialog'])],
            'network_protocol' => ['required', 'string', 'max:50'],
            'ip_address' => ['nullable', 'ip'],
            'host_name' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'timeout_ms' => ['required', 'integer', 'between:500,60000'],
            'paper_size' => ['required', 'string', 'max:50'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function clearDefaultForRole(int $branchId, string $printerRole, ?int $exceptId = null): void
    {
        Printer::query()
            ->where('branch_id', $branchId)
            ->where('printer_role', $printerRole)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }
}

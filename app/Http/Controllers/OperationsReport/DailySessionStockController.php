<?php

namespace App\Http\Controllers\OperationsReport;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\InvoiceLine;
use App\Models\OrderLine;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DailySessionStockController extends Controller
{
    public function __invoke(Request $request): Response
    {
        [$companyId, $branchId] = $this->scope($request);

        $posSession = PosSession::query()
            ->with(['branch:id,name,code', 'posTerminal:id,name,code'])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('status', 'open')
            ->latest('opened_at')
            ->first()
            ?? PosSession::query()
                ->with(['branch:id,name,code', 'posTerminal:id,name,code'])
                ->where('company_id', $companyId)
                ->where('status', 'open')
                ->latest('opened_at')
                ->first();

        $reportBranchId = $posSession?->branch_id ?? $branchId;

        $sessionDate = $request->date('date')?->toDateString()
            ?? $posSession?->opened_at?->toDateString()
            ?? now()->toDateString();

        $orderLines = OrderLine::query()
            ->with([
                'order:id,company_id,branch_id,dining_session_id,order_no,status,pos_open_date,sent_to_kitchen_at,created_at',
                'order.diningSession:id,dining_resource_id',
                'order.diningSession.diningResource:id,name,code',
                'menu:id,menu_category_id,item_id,name,code,print_route',
                'menu.menuCategory:id,name',
                'menu.item:id,unit_id,name,code,print_route,is_stockable',
                'menu.item.unit:id,name,code',
                'invoiceLines' => fn ($query) => $query->with('invoice:id,invoice_no,status,issued_at,paid_at,created_at'),
            ])
            ->where('status', '!=', 'cancelled')
            ->whereHas('order', function ($query) use ($companyId, $reportBranchId, $sessionDate) {
                $query->where('company_id', $companyId)
                    ->when($reportBranchId, fn ($query) => $query->where('branch_id', $reportBranchId))
                    ->whereDate('pos_open_date', $sessionDate)
                    ->whereNotIn('status', ['draft', 'cancelled']);
            })
            ->whereHas('menu', fn ($query) => $query->where('print_route', 'stock'))
            ->get();

        $rows = $orderLines
            ->groupBy('menu_id')
            ->map(fn (Collection $lines) => $this->stockRow($lines))
            ->sortBy('menuName', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return Inertia::render('OperationsReport/DailySessionStock', [
            'session' => [
                'date' => $sessionDate,
                'status' => $posSession?->status ?? 'no_open_session',
                'sessionNo' => $posSession?->session_no,
                'branchName' => $posSession?->branch?->name ?? Branch::query()->find($reportBranchId)?->name,
                'terminalName' => $posSession?->posTerminal?->name,
                'openedAt' => $posSession?->opened_at?->toDateTimeString(),
            ],
            'rows' => $rows,
            'stats' => [
                'stockMenus' => $rows->count(),
                'confirmedQty' => $rows->sum('confirmedQty'),
                'invoicedQty' => $rows->sum('invoicedQty'),
                'paidQty' => $rows->sum('paidQty'),
                'pendingQty' => $rows->sum('pendingPaymentQty') + $rows->sum('pendingInvoiceQty'),
            ],
        ]);
    }

    private function stockRow(Collection $lines): array
    {
        /** @var OrderLine $first */
        $first = $lines->first();
        $menu = $first->menu;
        $item = $menu?->item;
        $validInvoiceLines = $lines
            ->flatMap(fn (OrderLine $line) => $line->invoiceLines)
            ->filter(fn (InvoiceLine $line) => $line->invoice && $line->invoice->status !== 'cancelled');
        $paidInvoiceLines = $validInvoiceLines
            ->filter(fn (InvoiceLine $line) => in_array($line->invoice?->status, ['paid', 'partially_paid'], true));

        $confirmedQty = $lines->sum(fn (OrderLine $line) => (float) $line->quantity);
        $invoicedQty = $validInvoiceLines->sum(fn (InvoiceLine $line) => (float) $line->quantity);
        $paidQty = $paidInvoiceLines->sum(fn (InvoiceLine $line) => (float) $line->quantity);
        $pendingInvoiceQty = max($confirmedQty - $invoicedQty, 0);
        $pendingPaymentQty = max($invoicedQty - $paidQty, 0);
        $latestConfirmedAt = $lines
            ->map(fn (OrderLine $line) => $line->order?->sent_to_kitchen_at ?? $line->order?->created_at)
            ->filter()
            ->sortDesc()
            ->first();
        $latestInvoiceAt = $validInvoiceLines
            ->map(fn (InvoiceLine $line) => $line->invoice?->issued_at ?? $line->invoice?->created_at)
            ->filter()
            ->sortDesc()
            ->first();
        $latestPaidAt = $paidInvoiceLines
            ->map(fn (InvoiceLine $line) => $line->invoice?->paid_at)
            ->filter()
            ->sortDesc()
            ->first();
        $latestInvoice = $validInvoiceLines
            ->sortByDesc(fn (InvoiceLine $line) => $line->invoice?->issued_at ?? $line->invoice?->created_at)
            ->first()?->invoice;

        return [
            'id' => (int) $first->menu_id,
            'menuId' => (int) $first->menu_id,
            'menuCode' => $menu?->code ?: 'MENU-'.str_pad((string) $first->menu_id, 4, '0', STR_PAD_LEFT),
            'menuName' => $menu?->name ?? $first->menu_name_snapshot,
            'category' => $menu?->menuCategory?->name ?? 'Uncategorized',
            'itemCode' => $item?->code,
            'itemName' => $item?->name,
            'unit' => $item?->unit?->code ?? $item?->unit?->name ?? 'Unit',
            'route' => 'Menu stock route',
            'confirmedQty' => $confirmedQty,
            'invoicedQty' => $invoicedQty,
            'paidQty' => $paidQty,
            'pendingInvoiceQty' => $pendingInvoiceQty,
            'pendingPaymentQty' => $pendingPaymentQty,
            'orderCount' => $lines->pluck('order_id')->unique()->count(),
            'invoiceCount' => $validInvoiceLines->pluck('invoice_id')->unique()->count(),
            'totalAmount' => $lines->sum(fn (OrderLine $line) => (float) $line->line_total),
            'latestOrderNo' => $lines
                ->sortByDesc(fn (OrderLine $line) => $line->order?->sent_to_kitchen_at ?? $line->order?->created_at)
                ->first()?->order?->order_no,
            'latestInvoiceNo' => $latestInvoice?->invoice_no,
            'latestSeat' => $lines
                ->pluck('order.diningSession.diningResource.name')
                ->filter()
                ->unique()
                ->values()
                ->join(', '),
            'latestConfirmedAt' => $latestConfirmedAt?->toDateTimeString(),
            'latestInvoiceAt' => $latestInvoiceAt?->toDateTimeString(),
            'latestPaidAt' => $latestPaidAt?->toDateTimeString(),
            'status' => $this->rowStatus($pendingInvoiceQty, $pendingPaymentQty),
        ];
    }

    private function rowStatus(float $pendingInvoiceQty, float $pendingPaymentQty): string
    {
        if ($pendingInvoiceQty > 0) {
            return 'confirmed';
        }

        if ($pendingPaymentQty > 0) {
            return 'invoiced';
        }

        return 'paid';
    }

    private function scope(Request $request): array
    {
        $user = $request->user();
        $companyId = $user->company_id ?? Company::query()->value('id');
        $branchId = $user->branch_id ?? Branch::query()->where('company_id', $companyId)->value('id');

        abort_if(! $companyId, 422, 'No company is available for daily session stock.');

        return [$companyId, $branchId];
    }
}

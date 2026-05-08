<?php

use App\Http\Controllers\BalanceOnHand\BalanceOnHandController;
use App\Http\Controllers\Crm;
use App\Http\Controllers\Feature;
use App\Http\Controllers\GoodsReceipt\GoodsReceiptController;
use App\Http\Controllers\MasterData\CompanyBranchController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\DiningResourceController;
use App\Http\Controllers\MasterData\ExchangeRateController;
use App\Http\Controllers\MasterData\MenuController;
use App\Http\Controllers\MasterData\PosTerminalController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\TaxController;
use App\Http\Controllers\MasterData\WarehouseLocationController;
use App\Http\Controllers\POS\PosSessionController;
use App\Http\Controllers\ProfilePictureController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Putaway\PutawayController;
use App\Http\Controllers\Sales\SaleInvoiceController;
use App\Http\Controllers\Seats\SeatController;
use App\Http\Controllers\Seats\SeatOrderController;
use App\Http\Controllers\StockMovements\InternalTransferController;
use App\Http\Controllers\StockMovements\StockAdjustmentController;
use App\Http\Controllers\StockMovements\StockWriteOffController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Crm\DashboardController::class)->name('dashboard');

    Route::post('/profile/picture', [ProfilePictureController::class, 'update'])
        ->name('profile.picture.update');

    // POS session routes
    Route::get('/pos-sessions', [PosSessionController::class, 'index'])
        ->name('pos-sessions.index');

    Route::post('/pos-sessions/open', [PosSessionController::class, 'open'])
        ->name('pos-sessions.open');

    Route::post('/pos-sessions/{posSession}/close', [PosSessionController::class, 'close'])
        ->name('pos-sessions.close');

    // Sales invoice routes
    Route::get('/sales', [SaleInvoiceController::class, 'index'])->name('sales.index');
    Route::post('/sales/invoices/{invoice}/receive', [SaleInvoiceController::class, 'receive'])
        ->name('sales.invoices.receive');

    // Purchase order routes
    Route::get('/purchase', [PurchaseOrderController::class, 'index'])->name('purchase.index');
    Route::post('/purchase', [PurchaseOrderController::class, 'store'])->name('purchase.store');
    Route::patch('/purchase/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
        ->name('purchase.approve');
    Route::patch('/purchase/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])
        ->name('purchase.reject');
    Route::patch('/purchase/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase.cancel');

    // Goods receipt routes
    Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
    Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    Route::patch('/goods-receipts/{goodsReceipt}/approve', [GoodsReceiptController::class, 'approve'])
        ->name('goods-receipts.approve');
    Route::patch('/goods-receipts/{goodsReceipt}/reject', [GoodsReceiptController::class, 'reject'])
        ->name('goods-receipts.reject');
    Route::patch('/goods-receipts/{goodsReceipt}/cancel', [GoodsReceiptController::class, 'cancel'])
        ->name('goods-receipts.cancel');
    Route::get('/goods-receipts/approved-purchase-orders', [GoodsReceiptController::class, 'approvedPurchaseOrders'])
        ->name('goods-receipts.approved-purchase-orders');

    // Putaway routes
    Route::get('/putaway', [PutawayController::class, 'index'])->name('putaway.index');
    Route::get('/putaway/create', [PutawayController::class, 'create'])->name('putaway.create');
    Route::post('/putaway', [PutawayController::class, 'store'])->name('putaway.store');
    Route::patch('/putaway/{stockTransfer}', [PutawayController::class, 'update'])->name('putaway.update');
    Route::patch('/putaway/{stockTransfer}/approve', [PutawayController::class, 'approve'])
        ->name('putaway.approve');
    Route::patch('/putaway/{stockTransfer}/reject', [PutawayController::class, 'reject'])
        ->name('putaway.reject');
    Route::patch('/putaway/{stockTransfer}/cancel', [PutawayController::class, 'cancel'])
        ->name('putaway.cancel');
    Route::get('/putaway/completed-goods-receipts', [PutawayController::class, 'receipts'])
        ->name('putaway.completed-goods-receipts');

    // Inventory balance routes
    Route::get('/balance-on-hand', [BalanceOnHandController::class, 'index'])
        ->name('balance-on-hand.index');
    Route::get('/balance-on-hand/{item}', [BalanceOnHandController::class, 'show'])
        ->name('balance-on-hand.show');

    // Stock movement routes
    Route::get('/stock-movements/internal-transfer', [InternalTransferController::class, 'index'])
        ->name('stock-movements.internal-transfer');
    Route::get('/stock-movements/internal-transfer/create', [InternalTransferController::class, 'create'])
        ->name('stock-movements.internal-transfer.create');
    Route::post('/stock-movements/internal-transfer', [InternalTransferController::class, 'store'])
        ->name('stock-movements.internal-transfer.store');
    Route::patch('/stock-movements/internal-transfer/{stockTransfer}/approve', [InternalTransferController::class, 'approve'])
        ->name('stock-movements.internal-transfer.approve');
    Route::patch('/stock-movements/internal-transfer/{stockTransfer}/reject', [InternalTransferController::class, 'reject'])
        ->name('stock-movements.internal-transfer.reject');
    Route::patch('/stock-movements/internal-transfer/{stockTransfer}/cancel', [InternalTransferController::class, 'cancel'])
        ->name('stock-movements.internal-transfer.cancel');
    Route::get('/stock-movements/stock-adjustments', [StockAdjustmentController::class, 'index'])
        ->name('stock-movements.stock-adjustments');
    Route::get('/stock-movements/stock-adjustments/create', [StockAdjustmentController::class, 'create'])
        ->name('stock-movements.stock-adjustments.create');
    Route::post('/stock-movements/stock-adjustments', [StockAdjustmentController::class, 'store'])
        ->name('stock-movements.stock-adjustments.store');
    Route::patch('/stock-movements/stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])
        ->name('stock-movements.stock-adjustments.approve');
    Route::patch('/stock-movements/stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])
        ->name('stock-movements.stock-adjustments.reject');
    Route::patch('/stock-movements/stock-adjustments/{stockAdjustment}/cancel', [StockAdjustmentController::class, 'cancel'])
        ->name('stock-movements.stock-adjustments.cancel');
    Route::get('/stock-movements/write-off', [StockWriteOffController::class, 'index'])
        ->name('stock-movements.write-off');
    Route::get('/stock-movements/write-off/create', [StockWriteOffController::class, 'create'])
        ->name('stock-movements.write-off.create');
    Route::post('/stock-movements/write-off', [StockWriteOffController::class, 'store'])
        ->name('stock-movements.write-off.store');
    Route::patch('/stock-movements/write-off/{stockAdjustment}/approve', [StockWriteOffController::class, 'approve'])
        ->name('stock-movements.write-off.approve');
    Route::patch('/stock-movements/write-off/{stockAdjustment}/reject', [StockWriteOffController::class, 'reject'])
        ->name('stock-movements.write-off.reject');
    Route::patch('/stock-movements/write-off/{stockAdjustment}/cancel', [StockWriteOffController::class, 'cancel'])
        ->name('stock-movements.write-off.cancel');

    // Master data routes
    Route::inertia('/master-data/products', 'MasterData/Products')->name('master-data.products');
    Route::get('/master-data/company-branches', [CompanyBranchController::class, 'index'])->name('master-data.company-branches');
    Route::get('/master-data/customers', [CustomerController::class, 'index'])->name('master-data.customers');
    Route::get('/master-data/exchange-rates', [ExchangeRateController::class, 'index'])->name('master-data.exchange-rates');
    Route::get('/master-data/menu', [MenuController::class, 'index'])->name('master-data.menu');
    Route::get('/master-data/pos-terminals', [PosTerminalController::class, 'index'])->name('master-data.pos-terminals');
    Route::get('/master-data/seats', [DiningResourceController::class, 'index'])->name('master-data.seats');
    Route::get('/master-data/suppliers', [SupplierController::class, 'index'])->name('master-data.suppliers');
    Route::get('/master-data/taxes', [TaxController::class, 'index'])->name('master-data.taxes');
    Route::get('/master-data/warehouse-locations', [WarehouseLocationController::class, 'index'])->name('master-data.warehouse-locations');

    // Seat; Resource routes
    Route::get('/orders', [SeatController::class, 'index'])->name('seats.index');
    Route::post('/orders/{resource}/check-in', [SeatController::class, 'checkIn'])->name('seats.check-in');

    // Seat order routes
    Route::get('/orders/{diningSession}/menu', [SeatOrderController::class, 'show'])
        ->name('seats.orders.show');

    Route::post('/orders/{diningSession}/items', [SeatOrderController::class, 'addItem'])
        ->name('seats.orders.items.add');

    Route::patch('/orders/{diningSession}/items/{orderLine}', [SeatOrderController::class, 'updateItem'])
        ->name('seats.orders.items.update');

    Route::delete('/orders/{diningSession}/items', [SeatOrderController::class, 'clearItems'])
        ->name('seats.orders.items.clear');

    Route::delete('/orders/{diningSession}/items/{orderLine}', [SeatOrderController::class, 'removeItem'])
        ->name('seats.orders.items.remove');

    Route::post('/orders/{diningSession}/send-kitchen', [SeatOrderController::class, 'sendToKitchen'])
        ->name('seats.orders.send-kitchen');

    Route::post('/orders/{diningSession}/settle', [SeatOrderController::class, 'settle'])
        ->name('seats.orders.settle');

    Route::patch('/orders/{diningSession}/customer', [SeatOrderController::class, 'updateCustomer'])
        ->name('seats.orders.customer.update');

    Route::post('/orders/{diningSession}/close', [SeatOrderController::class, 'closeOrder'])
        ->name('seats.orders.close');

    // CRM Routes
    Route::resource('contacts', Crm\ContactController::class);
    Route::post('contacts/{contact}/favorite', [Crm\ContactController::class, 'favorite'])->name('contacts.favorite');
    Route::resource('organizations', Crm\OrganizationController::class)->only(['index', 'show', 'update']);
    Route::resource('contacts.notes', Crm\NoteController::class)->only(['store']);

    // Feature Showcase Routes
    Route::prefix('features')->name('features.')->group(function () {
        // Forms
        Route::prefix('forms')->name('forms.')->group(function () {
            Route::get('use-form', [Feature\FormController::class, 'useForm'])->name('use-form');
            Route::post('use-form', [Feature\FormController::class, 'submitUseForm']);

            Route::get('form-component', [Feature\FormController::class, 'formComponent'])->name('form-component');
            Route::post('form-component', [Feature\FormController::class, 'submitFormComponent']);

            Route::get('file-uploads', [Feature\FormController::class, 'fileUploads'])->name('file-uploads');
            Route::post('file-uploads', [Feature\FormController::class, 'submitFileUploads']);

            Route::get('validation', [Feature\FormController::class, 'validation'])->name('validation');
            Route::post('validation', [Feature\FormController::class, 'submitValidation']);
            Route::post('validation/secondary', [Feature\FormController::class, 'submitValidationSecondary'])->name('validation.secondary');

            Route::get('precognition', [Feature\FormController::class, 'precognition'])->name('precognition');
            Route::post('precognition', [Feature\FormController::class, 'storeAccount'])->middleware('precognitive');

            Route::get('optimistic-updates', [Feature\FormController::class, 'optimisticUpdates'])->name('optimistic-updates');
            Route::post('optimistic-toggle/{contact}', [Feature\FormController::class, 'toggleFavorite'])->name('optimistic-toggle');

            Route::get('use-form-context', [Feature\FormController::class, 'useFormContext'])->name('use-form-context');

            Route::get('dotted-keys', [Feature\FormController::class, 'dottedKeys'])->name('dotted-keys');
            Route::post('dotted-keys', [Feature\FormController::class, 'submitDottedKeys']);

            Route::get('wayfinder', [Feature\FormController::class, 'wayfinder'])->name('wayfinder');
        });

        // Navigation
        Route::prefix('navigation')->name('navigation.')->group(function () {
            Route::get('links', [Feature\NavigationController::class, 'links'])->name('links');
            Route::match(['post', 'put', 'patch', 'delete'], 'links', [Feature\NavigationController::class, 'linksAction']);

            Route::get('preserve-state', [Feature\NavigationController::class, 'preserveState'])->name('preserve-state');
            Route::get('preserve-scroll', [Feature\NavigationController::class, 'preserveScroll'])->name('preserve-scroll');
            Route::get('view-transitions', [Feature\NavigationController::class, 'viewTransitions'])->name('view-transitions');

            Route::get('history-management', [Feature\NavigationController::class, 'historyManagement'])->name('history-management');
            Route::post('history-management', [Feature\NavigationController::class, 'historyAction']);

            Route::get('async-requests', [Feature\NavigationController::class, 'asyncRequests'])->name('async-requests');
            Route::get('async-slow', [Feature\NavigationController::class, 'asyncSlow'])->name('async-slow');

            Route::get('manual-visits', [Feature\NavigationController::class, 'manualVisits'])->name('manual-visits');

            Route::get('redirects', [Feature\NavigationController::class, 'redirectDemo'])->name('redirects');
            Route::post('redirects/back', [Feature\NavigationController::class, 'redirectStandard'])->name('redirects.back');
            Route::post('redirects/to-route', [Feature\NavigationController::class, 'redirectToRoute'])->name('redirects.to-route');
            Route::post('redirects/external', [Feature\NavigationController::class, 'redirectExternal'])->name('redirects.external');

            Route::get('scroll-management', [Feature\NavigationController::class, 'scrollManagement'])->name('scroll-management');

            Route::get('instant-visits', [Feature\NavigationController::class, 'instantVisits'])->name('instant-visits');
            Route::get('instant-visit-target', [Feature\NavigationController::class, 'instantVisitTarget'])->name('instant-visit-target');

            Route::get('url-fragments', [Feature\NavigationController::class, 'urlFragments'])->name('url-fragments');
            Route::get('url-fragments/redirect-hash', [Feature\NavigationController::class, 'redirectWithHash']);
            Route::post('url-fragments/redirect-hash', [Feature\NavigationController::class, 'redirectWithHash']);
            Route::get('url-fragments/preserve-target', [Feature\NavigationController::class, 'preserveFragmentTarget']);
            Route::get('url-fragments/preserve-redirect', [Feature\NavigationController::class, 'preserveFragmentRedirect']);
        });

        // Data Loading
        Route::prefix('data-loading')->name('data-loading.')->group(function () {
            Route::get('deferred-props', [Feature\DataLoadingController::class, 'deferredProps'])->name('deferred-props');
            Route::get('partial-reloads', [Feature\DataLoadingController::class, 'partialReloads'])->name('partial-reloads');
            Route::get('infinite-scroll', [Feature\DataLoadingController::class, 'infiniteScroll'])->name('infinite-scroll');
            Route::get('when-visible', [Feature\DataLoadingController::class, 'whenVisible'])->name('when-visible');
            Route::get('polling', [Feature\DataLoadingController::class, 'polling'])->name('polling');
            Route::get('prop-merging', [Feature\DataLoadingController::class, 'propMerging'])->name('prop-merging');
            Route::get('once-props/{page?}', [Feature\DataLoadingController::class, 'onceProps'])->name('once-props')->where('page', '[12]');
            Route::get('optional-props', [Feature\DataLoadingController::class, 'optionalProps'])->name('optional-props');
        });

        // Prefetching
        Route::prefix('prefetching')->name('prefetching.')->group(function () {
            Route::get('link-prefetch', [Feature\PrefetchingController::class, 'linkPrefetch'])->name('link-prefetch');
            Route::get('stale-while-revalidate', [Feature\PrefetchingController::class, 'staleWhileRevalidate'])->name('stale-while-revalidate');
            Route::get('manual-prefetch', [Feature\PrefetchingController::class, 'manualPrefetch'])->name('manual-prefetch');
            Route::get('cache-management', [Feature\PrefetchingController::class, 'cacheManagement'])->name('cache-management');
        });

        // State Management
        Route::prefix('state')->name('state.')->group(function () {
            Route::get('remember', [Feature\StateController::class, 'remember'])->name('remember');
            Route::get('flash-data', [Feature\StateController::class, 'flashData'])->name('flash-data');
            Route::post('flash-data', [Feature\StateController::class, 'storeFlashData']);
            Route::post('flash-data/error', [Feature\StateController::class, 'storeFlashDataError'])->name('flash-data.error');
            Route::post('flash-data/warning', [Feature\StateController::class, 'storeFlashDataWarning'])->name('flash-data.warning');
            Route::get('shared-props', [Feature\StateController::class, 'sharedProps'])->name('shared-props');
        });

        // Layouts & Head
        Route::prefix('layouts')->name('layouts.')->group(function () {
            Route::get('persistent-layouts', [Feature\LayoutController::class, 'persistentLayouts'])->name('persistent-layouts');
            Route::get('persistent-layouts/page-2', [Feature\LayoutController::class, 'persistentLayoutsPageTwo'])->name('persistent-layouts.page-2');
            Route::get('nested-layouts', [Feature\LayoutController::class, 'nestedLayouts'])->name('nested-layouts');
            Route::get('head', [Feature\LayoutController::class, 'head'])->name('head');
            Route::get('layout-props', [Feature\LayoutController::class, 'layoutProps'])->name('layout-props');
        });

        // Events & Lifecycle
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('global-events', [Feature\EventController::class, 'globalEvents'])->name('global-events');
            Route::post('global-events/action', [Feature\EventController::class, 'globalEventsAction'])->name('global-events.action');

            Route::get('visit-callbacks', [Feature\EventController::class, 'visitCallbacks'])->name('visit-callbacks');
            Route::post('visit-callbacks/action', [Feature\EventController::class, 'visitCallbacksAction'])->name('visit-callbacks.action');

            Route::get('progress', [Feature\EventController::class, 'progress'])->name('progress');
            Route::get('progress/slow', [Feature\EventController::class, 'progressSlow'])->name('progress.slow');
        });

        // Error Handling
        Route::prefix('errors')->name('errors.')->group(function () {
            Route::get('http-exceptions', [Feature\NetworkErrorController::class, 'httpExceptions'])->name('http-exceptions');
            Route::get('http-exceptions/403', [Feature\NetworkErrorController::class, 'httpException403'])->name('http-exceptions.403');
            Route::get('http-exceptions/404', [Feature\NetworkErrorController::class, 'httpException404'])->name('http-exceptions.404');
            Route::get('http-exceptions/500', [Feature\NetworkErrorController::class, 'httpException500'])->name('http-exceptions.500');
            Route::get('http-exceptions/unhandled', [Feature\NetworkErrorController::class, 'httpExceptionUnhandled'])->name('http-exceptions.unhandled');

            Route::get('network-errors', [Feature\NetworkErrorController::class, 'networkErrors'])->name('network-errors');
        });

        // HTTP
        Route::prefix('http')->name('http.')->group(function () {
            Route::get('use-http', [Feature\HttpController::class, 'useHttp'])->name('use-http');
            Route::post('use-http/api', [Feature\HttpController::class, 'useHttpApi'])->name('use-http.api');
        });
    });
});

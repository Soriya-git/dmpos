import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::index
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/stock-settlements',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::index
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::index
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::index
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::approve
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:57
 * @route '/stock-movements/stock-settlements/approve'
 */
export const approve = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: approve.url(options),
    method: 'post',
});

approve.definition = {
    methods: ['post'],
    url: '/stock-movements/stock-settlements/approve',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::approve
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:57
 * @route '/stock-movements/stock-settlements/approve'
 */
approve.url = (options?: RouteQueryOptions) => {
    return approve.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::approve
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:57
 * @route '/stock-movements/stock-settlements/approve'
 */
approve.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::reject
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:123
 * @route '/stock-movements/stock-settlements/reject'
 */
export const reject = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: reject.url(options),
    method: 'post',
});

reject.definition = {
    methods: ['post'],
    url: '/stock-movements/stock-settlements/reject',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::reject
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:123
 * @route '/stock-movements/stock-settlements/reject'
 */
reject.url = (options?: RouteQueryOptions) => {
    return reject.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::reject
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:123
 * @route '/stock-movements/stock-settlements/reject'
 */
reject.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::show
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:41
 * @route '/stock-movements/stock-settlements/{invoice}'
 */
export const show = (
    args:
        | { invoice: number | { id: number } }
        | [invoice: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/stock-settlements/{invoice}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::show
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:41
 * @route '/stock-movements/stock-settlements/{invoice}'
 */
show.url = (
    args:
        | { invoice: number | { id: number } }
        | [invoice: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { invoice: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            invoice: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        invoice:
            typeof args.invoice === 'object' ? args.invoice.id : args.invoice,
    };

    return (
        show.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::show
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:41
 * @route '/stock-movements/stock-settlements/{invoice}'
 */
show.get = (
    args:
        | { invoice: number | { id: number } }
        | [invoice: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::show
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:41
 * @route '/stock-movements/stock-settlements/{invoice}'
 */
show.head = (
    args:
        | { invoice: number | { id: number } }
        | [invoice: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});
const StockSettlementController = { index, approve, reject, show };

export default StockSettlementController;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::create
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:31
 * @route '/stock-movements/stock-adjustments/create'
 */
export const create = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});

create.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/stock-adjustments/create',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::create
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:31
 * @route '/stock-movements/stock-adjustments/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::create
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:31
 * @route '/stock-movements/stock-adjustments/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::create
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:31
 * @route '/stock-movements/stock-adjustments/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::store
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:36
 * @route '/stock-movements/stock-adjustments'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/stock-movements/stock-adjustments',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::store
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:36
 * @route '/stock-movements/stock-adjustments'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::store
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:36
 * @route '/stock-movements/stock-adjustments'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::approve
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:73
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/approve'
 */
export const approve = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

approve.definition = {
    methods: ['patch'],
    url: '/stock-movements/stock-adjustments/{stockAdjustment}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::approve
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:73
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/approve'
 */
approve.url = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockAdjustment: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockAdjustment: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockAdjustment: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockAdjustment:
            typeof args.stockAdjustment === 'object'
                ? args.stockAdjustment.id
                : args.stockAdjustment,
    };

    return (
        approve.definition.url
            .replace('{stockAdjustment}', parsedArgs.stockAdjustment.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::approve
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:73
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/approve'
 */
approve.patch = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::reject
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:106
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/reject'
 */
export const reject = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

reject.definition = {
    methods: ['patch'],
    url: '/stock-movements/stock-adjustments/{stockAdjustment}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::reject
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:106
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/reject'
 */
reject.url = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockAdjustment: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockAdjustment: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockAdjustment: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockAdjustment:
            typeof args.stockAdjustment === 'object'
                ? args.stockAdjustment.id
                : args.stockAdjustment,
    };

    return (
        reject.definition.url
            .replace('{stockAdjustment}', parsedArgs.stockAdjustment.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::reject
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:106
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/reject'
 */
reject.patch = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::cancel
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:120
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/cancel'
 */
export const cancel = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

cancel.definition = {
    methods: ['patch'],
    url: '/stock-movements/stock-adjustments/{stockAdjustment}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::cancel
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:120
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/cancel'
 */
cancel.url = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockAdjustment: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockAdjustment: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockAdjustment: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockAdjustment:
            typeof args.stockAdjustment === 'object'
                ? args.stockAdjustment.id
                : args.stockAdjustment,
    };

    return (
        cancel.definition.url
            .replace('{stockAdjustment}', parsedArgs.stockAdjustment.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::cancel
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:120
 * @route '/stock-movements/stock-adjustments/{stockAdjustment}/cancel'
 */
cancel.patch = (
    args:
        | { stockAdjustment: number | { id: number } }
        | [stockAdjustment: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});
const stockAdjustments = {
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    approve: Object.assign(approve, approve),
    reject: Object.assign(reject, reject),
    cancel: Object.assign(cancel, cancel),
};

export default stockAdjustments;

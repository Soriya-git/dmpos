import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::index
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/write-off',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::index
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::index
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::index
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::create
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:30
 * @route '/stock-movements/write-off/create'
 */
export const create = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});

create.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/write-off/create',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::create
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:30
 * @route '/stock-movements/write-off/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::create
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:30
 * @route '/stock-movements/write-off/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::create
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:30
 * @route '/stock-movements/write-off/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::store
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:194
 * @route '/stock-movements/write-off'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/stock-movements/write-off',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::store
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:194
 * @route '/stock-movements/write-off'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::store
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:194
 * @route '/stock-movements/write-off'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::approve
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:292
 * @route '/stock-movements/write-off/{stockAdjustment}/approve'
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
    url: '/stock-movements/write-off/{stockAdjustment}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::approve
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:292
 * @route '/stock-movements/write-off/{stockAdjustment}/approve'
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
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::approve
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:292
 * @route '/stock-movements/write-off/{stockAdjustment}/approve'
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
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::reject
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:375
 * @route '/stock-movements/write-off/{stockAdjustment}/reject'
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
    url: '/stock-movements/write-off/{stockAdjustment}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::reject
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:375
 * @route '/stock-movements/write-off/{stockAdjustment}/reject'
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
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::reject
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:375
 * @route '/stock-movements/write-off/{stockAdjustment}/reject'
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
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::cancel
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:389
 * @route '/stock-movements/write-off/{stockAdjustment}/cancel'
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
    url: '/stock-movements/write-off/{stockAdjustment}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::cancel
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:389
 * @route '/stock-movements/write-off/{stockAdjustment}/cancel'
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
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::cancel
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:389
 * @route '/stock-movements/write-off/{stockAdjustment}/cancel'
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
const StockWriteOffController = {
    index,
    create,
    store,
    approve,
    reject,
    cancel,
};

export default StockWriteOffController;

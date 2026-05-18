import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::index
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/internal-transfer',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::index
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::index
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::index
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::create
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:33
 * @route '/stock-movements/internal-transfer/create'
 */
export const create = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});

create.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/internal-transfer/create',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::create
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:33
 * @route '/stock-movements/internal-transfer/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::create
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:33
 * @route '/stock-movements/internal-transfer/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::create
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:33
 * @route '/stock-movements/internal-transfer/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::store
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:173
 * @route '/stock-movements/internal-transfer'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/stock-movements/internal-transfer',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::store
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:173
 * @route '/stock-movements/internal-transfer'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::store
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:173
 * @route '/stock-movements/internal-transfer'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::approve
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:295
 * @route '/stock-movements/internal-transfer/{stockTransfer}/approve'
 */
export const approve = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

approve.definition = {
    methods: ['patch'],
    url: '/stock-movements/internal-transfer/{stockTransfer}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::approve
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:295
 * @route '/stock-movements/internal-transfer/{stockTransfer}/approve'
 */
approve.url = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockTransfer: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockTransfer: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockTransfer: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockTransfer:
            typeof args.stockTransfer === 'object'
                ? args.stockTransfer.id
                : args.stockTransfer,
    };

    return (
        approve.definition.url
            .replace('{stockTransfer}', parsedArgs.stockTransfer.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::approve
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:295
 * @route '/stock-movements/internal-transfer/{stockTransfer}/approve'
 */
approve.patch = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::reject
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:438
 * @route '/stock-movements/internal-transfer/{stockTransfer}/reject'
 */
export const reject = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

reject.definition = {
    methods: ['patch'],
    url: '/stock-movements/internal-transfer/{stockTransfer}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::reject
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:438
 * @route '/stock-movements/internal-transfer/{stockTransfer}/reject'
 */
reject.url = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockTransfer: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockTransfer: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockTransfer: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockTransfer:
            typeof args.stockTransfer === 'object'
                ? args.stockTransfer.id
                : args.stockTransfer,
    };

    return (
        reject.definition.url
            .replace('{stockTransfer}', parsedArgs.stockTransfer.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::reject
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:438
 * @route '/stock-movements/internal-transfer/{stockTransfer}/reject'
 */
reject.patch = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::cancel
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:450
 * @route '/stock-movements/internal-transfer/{stockTransfer}/cancel'
 */
export const cancel = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

cancel.definition = {
    methods: ['patch'],
    url: '/stock-movements/internal-transfer/{stockTransfer}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::cancel
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:450
 * @route '/stock-movements/internal-transfer/{stockTransfer}/cancel'
 */
cancel.url = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { stockTransfer: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { stockTransfer: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            stockTransfer: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        stockTransfer:
            typeof args.stockTransfer === 'object'
                ? args.stockTransfer.id
                : args.stockTransfer,
    };

    return (
        cancel.definition.url
            .replace('{stockTransfer}', parsedArgs.stockTransfer.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::cancel
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:450
 * @route '/stock-movements/internal-transfer/{stockTransfer}/cancel'
 */
cancel.patch = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});
const InternalTransferController = {
    index,
    create,
    store,
    approve,
    reject,
    cancel,
};

export default InternalTransferController;

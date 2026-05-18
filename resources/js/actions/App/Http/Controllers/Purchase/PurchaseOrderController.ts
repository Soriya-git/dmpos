import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::index
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:20
 * @route '/purchase'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/purchase',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::index
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:20
 * @route '/purchase'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::index
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:20
 * @route '/purchase'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::index
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:20
 * @route '/purchase'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::store
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:86
 * @route '/purchase'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/purchase',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::store
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:86
 * @route '/purchase'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::store
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:86
 * @route '/purchase'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::approve
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:158
 * @route '/purchase/{purchaseOrder}/approve'
 */
export const approve = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

approve.definition = {
    methods: ['patch'],
    url: '/purchase/{purchaseOrder}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::approve
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:158
 * @route '/purchase/{purchaseOrder}/approve'
 */
approve.url = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { purchaseOrder: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { purchaseOrder: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            purchaseOrder: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        purchaseOrder:
            typeof args.purchaseOrder === 'object'
                ? args.purchaseOrder.id
                : args.purchaseOrder,
    };

    return (
        approve.definition.url
            .replace('{purchaseOrder}', parsedArgs.purchaseOrder.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::approve
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:158
 * @route '/purchase/{purchaseOrder}/approve'
 */
approve.patch = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::reject
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:171
 * @route '/purchase/{purchaseOrder}/reject'
 */
export const reject = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

reject.definition = {
    methods: ['patch'],
    url: '/purchase/{purchaseOrder}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::reject
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:171
 * @route '/purchase/{purchaseOrder}/reject'
 */
reject.url = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { purchaseOrder: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { purchaseOrder: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            purchaseOrder: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        purchaseOrder:
            typeof args.purchaseOrder === 'object'
                ? args.purchaseOrder.id
                : args.purchaseOrder,
    };

    return (
        reject.definition.url
            .replace('{purchaseOrder}', parsedArgs.purchaseOrder.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::reject
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:171
 * @route '/purchase/{purchaseOrder}/reject'
 */
reject.patch = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::cancel
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:184
 * @route '/purchase/{purchaseOrder}/cancel'
 */
export const cancel = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

cancel.definition = {
    methods: ['patch'],
    url: '/purchase/{purchaseOrder}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::cancel
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:184
 * @route '/purchase/{purchaseOrder}/cancel'
 */
cancel.url = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { purchaseOrder: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { purchaseOrder: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            purchaseOrder: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        purchaseOrder:
            typeof args.purchaseOrder === 'object'
                ? args.purchaseOrder.id
                : args.purchaseOrder,
    };

    return (
        cancel.definition.url
            .replace('{purchaseOrder}', parsedArgs.purchaseOrder.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Purchase\PurchaseOrderController::cancel
 * @see app/Http/Controllers/Purchase/PurchaseOrderController.php:184
 * @route '/purchase/{purchaseOrder}/cancel'
 */
cancel.patch = (
    args:
        | { purchaseOrder: number | { id: number } }
        | [purchaseOrder: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});
const PurchaseOrderController = { index, store, approve, reject, cancel };

export default PurchaseOrderController;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::index
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:23
 * @route '/goods-receipts'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/goods-receipts',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::index
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:23
 * @route '/goods-receipts'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::index
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:23
 * @route '/goods-receipts'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::index
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:23
 * @route '/goods-receipts'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::create
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:48
 * @route '/goods-receipts/create'
 */
export const create = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});

create.definition = {
    methods: ['get', 'head'],
    url: '/goods-receipts/create',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::create
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:48
 * @route '/goods-receipts/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::create
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:48
 * @route '/goods-receipts/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::create
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:48
 * @route '/goods-receipts/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::store
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:67
 * @route '/goods-receipts'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/goods-receipts',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::store
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:67
 * @route '/goods-receipts'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::store
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:67
 * @route '/goods-receipts'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approve
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:180
 * @route '/goods-receipts/{goodsReceipt}/approve'
 */
export const approve = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

approve.definition = {
    methods: ['patch'],
    url: '/goods-receipts/{goodsReceipt}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approve
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:180
 * @route '/goods-receipts/{goodsReceipt}/approve'
 */
approve.url = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsReceipt: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { goodsReceipt: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            goodsReceipt: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        goodsReceipt:
            typeof args.goodsReceipt === 'object'
                ? args.goodsReceipt.id
                : args.goodsReceipt,
    };

    return (
        approve.definition.url
            .replace('{goodsReceipt}', parsedArgs.goodsReceipt.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approve
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:180
 * @route '/goods-receipts/{goodsReceipt}/approve'
 */
approve.patch = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: approve.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::reject
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:247
 * @route '/goods-receipts/{goodsReceipt}/reject'
 */
export const reject = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

reject.definition = {
    methods: ['patch'],
    url: '/goods-receipts/{goodsReceipt}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::reject
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:247
 * @route '/goods-receipts/{goodsReceipt}/reject'
 */
reject.url = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsReceipt: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { goodsReceipt: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            goodsReceipt: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        goodsReceipt:
            typeof args.goodsReceipt === 'object'
                ? args.goodsReceipt.id
                : args.goodsReceipt,
    };

    return (
        reject.definition.url
            .replace('{goodsReceipt}', parsedArgs.goodsReceipt.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::reject
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:247
 * @route '/goods-receipts/{goodsReceipt}/reject'
 */
reject.patch = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: reject.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::cancel
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:256
 * @route '/goods-receipts/{goodsReceipt}/cancel'
 */
export const cancel = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

cancel.definition = {
    methods: ['patch'],
    url: '/goods-receipts/{goodsReceipt}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::cancel
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:256
 * @route '/goods-receipts/{goodsReceipt}/cancel'
 */
cancel.url = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsReceipt: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { goodsReceipt: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            goodsReceipt: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        goodsReceipt:
            typeof args.goodsReceipt === 'object'
                ? args.goodsReceipt.id
                : args.goodsReceipt,
    };

    return (
        cancel.definition.url
            .replace('{goodsReceipt}', parsedArgs.goodsReceipt.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::cancel
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:256
 * @route '/goods-receipts/{goodsReceipt}/cancel'
 */
cancel.patch = (
    args:
        | { goodsReceipt: number | { id: number } }
        | [goodsReceipt: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approvedPurchaseOrders
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:171
 * @route '/goods-receipts/approved-purchase-orders'
 */
export const approvedPurchaseOrders = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: approvedPurchaseOrders.url(options),
    method: 'get',
});

approvedPurchaseOrders.definition = {
    methods: ['get', 'head'],
    url: '/goods-receipts/approved-purchase-orders',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approvedPurchaseOrders
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:171
 * @route '/goods-receipts/approved-purchase-orders'
 */
approvedPurchaseOrders.url = (options?: RouteQueryOptions) => {
    return approvedPurchaseOrders.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approvedPurchaseOrders
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:171
 * @route '/goods-receipts/approved-purchase-orders'
 */
approvedPurchaseOrders.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: approvedPurchaseOrders.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\GoodsReceipt\GoodsReceiptController::approvedPurchaseOrders
 * @see app/Http/Controllers/GoodsReceipt/GoodsReceiptController.php:171
 * @route '/goods-receipts/approved-purchase-orders'
 */
approvedPurchaseOrders.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: approvedPurchaseOrders.url(options),
    method: 'head',
});
const GoodsReceiptController = {
    index,
    create,
    store,
    approve,
    reject,
    cancel,
    approvedPurchaseOrders,
};

export default GoodsReceiptController;

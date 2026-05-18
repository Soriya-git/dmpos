import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Putaway\PutawayController::index
 * @see app/Http/Controllers/Putaway/PutawayController.php:24
 * @route '/putaway'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/putaway',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::index
 * @see app/Http/Controllers/Putaway/PutawayController.php:24
 * @route '/putaway'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::index
 * @see app/Http/Controllers/Putaway/PutawayController.php:24
 * @route '/putaway'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Putaway\PutawayController::index
 * @see app/Http/Controllers/Putaway/PutawayController.php:24
 * @route '/putaway'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::create
 * @see app/Http/Controllers/Putaway/PutawayController.php:60
 * @route '/putaway/create'
 */
export const create = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});

create.definition = {
    methods: ['get', 'head'],
    url: '/putaway/create',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::create
 * @see app/Http/Controllers/Putaway/PutawayController.php:60
 * @route '/putaway/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::create
 * @see app/Http/Controllers/Putaway/PutawayController.php:60
 * @route '/putaway/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Putaway\PutawayController::create
 * @see app/Http/Controllers/Putaway/PutawayController.php:60
 * @route '/putaway/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::store
 * @see app/Http/Controllers/Putaway/PutawayController.php:93
 * @route '/putaway'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/putaway',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::store
 * @see app/Http/Controllers/Putaway/PutawayController.php:93
 * @route '/putaway'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::store
 * @see app/Http/Controllers/Putaway/PutawayController.php:93
 * @route '/putaway'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::update
 * @see app/Http/Controllers/Putaway/PutawayController.php:167
 * @route '/putaway/{stockTransfer}'
 */
export const update = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

update.definition = {
    methods: ['patch'],
    url: '/putaway/{stockTransfer}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::update
 * @see app/Http/Controllers/Putaway/PutawayController.php:167
 * @route '/putaway/{stockTransfer}'
 */
update.url = (
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
        update.definition.url
            .replace('{stockTransfer}', parsedArgs.stockTransfer.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::update
 * @see app/Http/Controllers/Putaway/PutawayController.php:167
 * @route '/putaway/{stockTransfer}'
 */
update.patch = (
    args:
        | { stockTransfer: number | { id: number } }
        | [stockTransfer: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::approve
 * @see app/Http/Controllers/Putaway/PutawayController.php:231
 * @route '/putaway/{stockTransfer}/approve'
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
    url: '/putaway/{stockTransfer}/approve',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::approve
 * @see app/Http/Controllers/Putaway/PutawayController.php:231
 * @route '/putaway/{stockTransfer}/approve'
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
 * @see \App\Http\Controllers\Putaway\PutawayController::approve
 * @see app/Http/Controllers/Putaway/PutawayController.php:231
 * @route '/putaway/{stockTransfer}/approve'
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
 * @see \App\Http\Controllers\Putaway\PutawayController::reject
 * @see app/Http/Controllers/Putaway/PutawayController.php:244
 * @route '/putaway/{stockTransfer}/reject'
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
    url: '/putaway/{stockTransfer}/reject',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::reject
 * @see app/Http/Controllers/Putaway/PutawayController.php:244
 * @route '/putaway/{stockTransfer}/reject'
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
 * @see \App\Http\Controllers\Putaway\PutawayController::reject
 * @see app/Http/Controllers/Putaway/PutawayController.php:244
 * @route '/putaway/{stockTransfer}/reject'
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
 * @see \App\Http\Controllers\Putaway\PutawayController::cancel
 * @see app/Http/Controllers/Putaway/PutawayController.php:253
 * @route '/putaway/{stockTransfer}/cancel'
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
    url: '/putaway/{stockTransfer}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::cancel
 * @see app/Http/Controllers/Putaway/PutawayController.php:253
 * @route '/putaway/{stockTransfer}/cancel'
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
 * @see \App\Http\Controllers\Putaway\PutawayController::cancel
 * @see app/Http/Controllers/Putaway/PutawayController.php:253
 * @route '/putaway/{stockTransfer}/cancel'
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

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::receipts
 * @see app/Http/Controllers/Putaway/PutawayController.php:51
 * @route '/putaway/completed-goods-receipts'
 */
export const receipts = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: receipts.url(options),
    method: 'get',
});

receipts.definition = {
    methods: ['get', 'head'],
    url: '/putaway/completed-goods-receipts',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::receipts
 * @see app/Http/Controllers/Putaway/PutawayController.php:51
 * @route '/putaway/completed-goods-receipts'
 */
receipts.url = (options?: RouteQueryOptions) => {
    return receipts.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Putaway\PutawayController::receipts
 * @see app/Http/Controllers/Putaway/PutawayController.php:51
 * @route '/putaway/completed-goods-receipts'
 */
receipts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipts.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Putaway\PutawayController::receipts
 * @see app/Http/Controllers/Putaway/PutawayController.php:51
 * @route '/putaway/completed-goods-receipts'
 */
receipts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receipts.url(options),
    method: 'head',
});
const PutawayController = {
    index,
    create,
    store,
    update,
    approve,
    reject,
    cancel,
    receipts,
};

export default PutawayController;

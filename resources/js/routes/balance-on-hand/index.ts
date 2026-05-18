import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../wayfinder';
/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::index
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:16
 * @route '/balance-on-hand'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/balance-on-hand',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::index
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:16
 * @route '/balance-on-hand'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::index
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:16
 * @route '/balance-on-hand'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::index
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:16
 * @route '/balance-on-hand'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::show
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:65
 * @route '/balance-on-hand/{item}'
 */
export const show = (
    args:
        | { item: number | { id: number } }
        | [item: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/balance-on-hand/{item}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::show
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:65
 * @route '/balance-on-hand/{item}'
 */
show.url = (
    args:
        | { item: number | { id: number } }
        | [item: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        item: typeof args.item === 'object' ? args.item.id : args.item,
    };

    return (
        show.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::show
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:65
 * @route '/balance-on-hand/{item}'
 */
show.get = (
    args:
        | { item: number | { id: number } }
        | [item: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\BalanceOnHand\BalanceOnHandController::show
 * @see app/Http/Controllers/BalanceOnHand/BalanceOnHandController.php:65
 * @route '/balance-on-hand/{item}'
 */
show.head = (
    args:
        | { item: number | { id: number } }
        | [item: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});
const balanceOnHand = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
};

export default balanceOnHand;

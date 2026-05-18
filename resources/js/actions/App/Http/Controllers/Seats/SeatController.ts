import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Seats\SeatController::index
 * @see app/Http/Controllers/Seats/SeatController.php:20
 * @route '/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/orders',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatController::index
 * @see app/Http/Controllers/Seats/SeatController.php:20
 * @route '/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Seats\SeatController::index
 * @see app/Http/Controllers/Seats/SeatController.php:20
 * @route '/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatController::index
 * @see app/Http/Controllers/Seats/SeatController.php:20
 * @route '/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Seats\SeatController::checkIn
 * @see app/Http/Controllers/Seats/SeatController.php:153
 * @route '/orders/{resource}/check-in'
 */
export const checkIn = (
    args:
        | { resource: number | { id: number } }
        | [resource: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkIn.url(args, options),
    method: 'post',
});

checkIn.definition = {
    methods: ['post'],
    url: '/orders/{resource}/check-in',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatController::checkIn
 * @see app/Http/Controllers/Seats/SeatController.php:153
 * @route '/orders/{resource}/check-in'
 */
checkIn.url = (
    args:
        | { resource: number | { id: number } }
        | [resource: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { resource: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { resource: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            resource: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        resource:
            typeof args.resource === 'object'
                ? args.resource.id
                : args.resource,
    };

    return (
        checkIn.definition.url
            .replace('{resource}', parsedArgs.resource.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatController::checkIn
 * @see app/Http/Controllers/Seats/SeatController.php:153
 * @route '/orders/{resource}/check-in'
 */
checkIn.post = (
    args:
        | { resource: number | { id: number } }
        | [resource: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: checkIn.url(args, options),
    method: 'post',
});
const SeatController = { index, checkIn };

export default SeatController;

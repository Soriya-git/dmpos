import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
export const preview = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
});

preview.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/print/current-invoice',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
preview.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        preview.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
preview.get = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
preview.head = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
});
const currentInvoice = {
    preview: Object.assign(preview, preview),
};

export default currentInvoice;

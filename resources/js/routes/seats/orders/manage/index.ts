import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::save
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
export const save = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: save.url(args, options),
    method: 'post',
});

save.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/manage',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::save
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
save.url = (
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
        save.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::save
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
save.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: save.url(args, options),
    method: 'post',
});
const manage = {
    save: Object.assign(save, save),
};

export default manage;

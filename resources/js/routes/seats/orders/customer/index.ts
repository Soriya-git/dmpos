import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
export const update = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/orders/{diningSession}/customer',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
update.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { diningSession: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    diningSession: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        diningSession: typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
                }

    return update.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
update.patch = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})
const customer = {
    update: Object.assign(update, update),
}

export default customer
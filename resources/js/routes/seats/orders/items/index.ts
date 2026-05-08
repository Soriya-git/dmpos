import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Seats\SeatOrderController::add
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
export const add = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

add.definition = {
    methods: ["post"],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::add
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
add.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return add.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::add
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
add.post = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const update = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
update.url = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    diningSession: args[0],
                    orderLine: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        diningSession: typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
                                orderLine: typeof args.orderLine === 'object'
                ? args.orderLine.id
                : args.orderLine,
                }

    return update.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::update
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
update.patch = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clear
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
export const clear = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(args, options),
    method: 'delete',
})

clear.definition = {
    methods: ["delete"],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clear
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
clear.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return clear.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clear
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
clear.delete = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::remove
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const remove = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(args, options),
    method: 'delete',
})

remove.definition = {
    methods: ["delete"],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::remove
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
remove.url = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    diningSession: args[0],
                    orderLine: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        diningSession: typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
                                orderLine: typeof args.orderLine === 'object'
                ? args.orderLine.id
                : args.orderLine,
                }

    return remove.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::remove
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
remove.delete = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(args, options),
    method: 'delete',
})
const items = {
    add: Object.assign(add, add),
update: Object.assign(update, update),
clear: Object.assign(clear, clear),
remove: Object.assign(remove, remove),
}

export default items
import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:24
 * @route '/orders/{diningSession}/menu'
 */
export const show = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/orders/{diningSession}/menu',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:24
 * @route '/orders/{diningSession}/menu'
 */
show.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:24
 * @route '/orders/{diningSession}/menu'
 */
show.get = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:24
 * @route '/orders/{diningSession}/menu'
 */
show.head = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
export const addItem = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addItem.url(args, options),
    method: 'post',
})

addItem.definition = {
    methods: ["post"],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
addItem.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return addItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:139
 * @route '/orders/{diningSession}/items'
 */
addItem.post = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: addItem.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const updateItem = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateItem.url(args, options),
    method: 'patch',
})

updateItem.definition = {
    methods: ["patch"],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
updateItem.url = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return updateItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:191
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
updateItem.patch = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateItem.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
export const clearItems = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearItems.url(args, options),
    method: 'delete',
})

clearItems.definition = {
    methods: ["delete"],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
clearItems.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return clearItems.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:221
 * @route '/orders/{diningSession}/items'
 */
clearItems.delete = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearItems.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const removeItem = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeItem.url(args, options),
    method: 'delete',
})

removeItem.definition = {
    methods: ["delete"],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
removeItem.url = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return removeItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:212
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
removeItem.delete = (args: { diningSession: number | { id: number }, orderLine: number | { id: number } } | [diningSession: number | { id: number }, orderLine: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeItem.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:230
 * @route '/orders/{diningSession}/send-kitchen'
 */
export const sendToKitchen = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendToKitchen.url(args, options),
    method: 'post',
})

sendToKitchen.definition = {
    methods: ["post"],
    url: '/orders/{diningSession}/send-kitchen',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:230
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendToKitchen.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return sendToKitchen.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:230
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendToKitchen.post = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendToKitchen.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:247
 * @route '/orders/{diningSession}/settle'
 */
export const settle = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: settle.url(args, options),
    method: 'post',
})

settle.definition = {
    methods: ["post"],
    url: '/orders/{diningSession}/settle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:247
 * @route '/orders/{diningSession}/settle'
 */
settle.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return settle.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:247
 * @route '/orders/{diningSession}/settle'
 */
settle.post = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: settle.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
export const updateCustomer = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateCustomer.url(args, options),
    method: 'patch',
})

updateCustomer.definition = {
    methods: ["patch"],
    url: '/orders/{diningSession}/customer',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
updateCustomer.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateCustomer.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:363
 * @route '/orders/{diningSession}/customer'
 */
updateCustomer.patch = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateCustomer.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:413
 * @route '/orders/{diningSession}/close'
 */
export const closeOrder = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeOrder.url(args, options),
    method: 'post',
})

closeOrder.definition = {
    methods: ["post"],
    url: '/orders/{diningSession}/close',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:413
 * @route '/orders/{diningSession}/close'
 */
closeOrder.url = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return closeOrder.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:413
 * @route '/orders/{diningSession}/close'
 */
closeOrder.post = (args: { diningSession: number | { id: number } } | [diningSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: closeOrder.url(args, options),
    method: 'post',
})
const SeatOrderController = { show, addItem, updateItem, clearItems, removeItem, sendToKitchen, settle, updateCustomer, closeOrder }

export default SeatOrderController
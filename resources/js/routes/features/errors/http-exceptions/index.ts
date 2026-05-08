import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
export const method403 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method403.url(options),
    method: 'get',
})

method403.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/403',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
method403.url = (options?: RouteQueryOptions) => {
    return method403.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
method403.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method403.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
method403.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: method403.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
export const method404 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method404.url(options),
    method: 'get',
})

method404.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/404',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
method404.url = (options?: RouteQueryOptions) => {
    return method404.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
method404.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method404.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
method404.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: method404.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
export const method500 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method500.url(options),
    method: 'get',
})

method500.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/500',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
method500.url = (options?: RouteQueryOptions) => {
    return method500.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
method500.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: method500.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::method500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
method500.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: method500.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::unhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
export const unhandled = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unhandled.url(options),
    method: 'get',
})

unhandled.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/unhandled',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::unhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
unhandled.url = (options?: RouteQueryOptions) => {
    return unhandled.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::unhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
unhandled.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: unhandled.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::unhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
unhandled.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: unhandled.url(options),
    method: 'head',
})
const httpExceptions = {
    403: Object.assign(method403, method403),
404: Object.assign(method404, method404),
500: Object.assign(method500, method500),
unhandled: Object.assign(unhandled, unhandled),
}

export default httpExceptions
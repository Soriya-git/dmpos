import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
export const httpExceptions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpExceptions.url(options),
    method: 'get',
})

httpExceptions.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.url = (options?: RouteQueryOptions) => {
    return httpExceptions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpExceptions.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: httpExceptions.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
export const httpException403 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException403.url(options),
    method: 'get',
})

httpException403.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/403',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
httpException403.url = (options?: RouteQueryOptions) => {
    return httpException403.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
httpException403.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException403.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException403
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:15
 * @route '/features/errors/http-exceptions/403'
 */
httpException403.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: httpException403.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
export const httpException404 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException404.url(options),
    method: 'get',
})

httpException404.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/404',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
httpException404.url = (options?: RouteQueryOptions) => {
    return httpException404.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
httpException404.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException404.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException404
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:20
 * @route '/features/errors/http-exceptions/404'
 */
httpException404.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: httpException404.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
export const httpException500 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException500.url(options),
    method: 'get',
})

httpException500.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/500',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
httpException500.url = (options?: RouteQueryOptions) => {
    return httpException500.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
httpException500.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpException500.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpException500
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:25
 * @route '/features/errors/http-exceptions/500'
 */
httpException500.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: httpException500.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptionUnhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
export const httpExceptionUnhandled = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpExceptionUnhandled.url(options),
    method: 'get',
})

httpExceptionUnhandled.definition = {
    methods: ["get","head"],
    url: '/features/errors/http-exceptions/unhandled',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptionUnhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
httpExceptionUnhandled.url = (options?: RouteQueryOptions) => {
    return httpExceptionUnhandled.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptionUnhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
httpExceptionUnhandled.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpExceptionUnhandled.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptionUnhandled
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:30
 * @route '/features/errors/http-exceptions/unhandled'
 */
httpExceptionUnhandled.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: httpExceptionUnhandled.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
export const networkErrors = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: networkErrors.url(options),
    method: 'get',
})

networkErrors.definition = {
    methods: ["get","head"],
    url: '/features/errors/network-errors',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.url = (options?: RouteQueryOptions) => {
    return networkErrors.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: networkErrors.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: networkErrors.url(options),
    method: 'head',
})
const NetworkErrorController = { httpExceptions, httpException403, httpException404, httpException500, httpExceptionUnhandled, networkErrors }

export default NetworkErrorController
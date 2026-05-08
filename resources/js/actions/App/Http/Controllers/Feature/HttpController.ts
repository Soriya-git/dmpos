import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\HttpController::useHttp
 * @see app/Http/Controllers/Feature/HttpController.php:12
 * @route '/features/http/use-http'
 */
export const useHttp = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useHttp.url(options),
    method: 'get',
})

useHttp.definition = {
    methods: ["get","head"],
    url: '/features/http/use-http',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\HttpController::useHttp
 * @see app/Http/Controllers/Feature/HttpController.php:12
 * @route '/features/http/use-http'
 */
useHttp.url = (options?: RouteQueryOptions) => {
    return useHttp.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\HttpController::useHttp
 * @see app/Http/Controllers/Feature/HttpController.php:12
 * @route '/features/http/use-http'
 */
useHttp.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useHttp.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\HttpController::useHttp
 * @see app/Http/Controllers/Feature/HttpController.php:12
 * @route '/features/http/use-http'
 */
useHttp.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: useHttp.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\HttpController::useHttpApi
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
export const useHttpApi = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: useHttpApi.url(options),
    method: 'post',
})

useHttpApi.definition = {
    methods: ["post"],
    url: '/features/http/use-http/api',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\HttpController::useHttpApi
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
useHttpApi.url = (options?: RouteQueryOptions) => {
    return useHttpApi.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\HttpController::useHttpApi
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
useHttpApi.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: useHttpApi.url(options),
    method: 'post',
})
const HttpController = { useHttp, useHttpApi }

export default HttpController
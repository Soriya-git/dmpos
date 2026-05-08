import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import useHttpE30dfc from './use-http'
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
const http = {
    useHttp: Object.assign(useHttp, useHttpE30dfc),
}

export default http
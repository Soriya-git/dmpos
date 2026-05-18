import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\HttpController::api
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
export const api = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: api.url(options),
    method: 'post',
});

api.definition = {
    methods: ['post'],
    url: '/features/http/use-http/api',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\HttpController::api
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
api.url = (options?: RouteQueryOptions) => {
    return api.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\HttpController::api
 * @see app/Http/Controllers/Feature/HttpController.php:17
 * @route '/features/http/use-http/api'
 */
api.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: api.url(options),
    method: 'post',
});
const useHttp = {
    api: Object.assign(api, api),
};

export default useHttp;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../wayfinder';
import httpExceptionsBa316e from './http-exceptions';
/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
export const httpExceptions = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: httpExceptions.url(options),
    method: 'get',
});

httpExceptions.definition = {
    methods: ['get', 'head'],
    url: '/features/errors/http-exceptions',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.url = (options?: RouteQueryOptions) => {
    return httpExceptions.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: httpExceptions.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::httpExceptions
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:10
 * @route '/features/errors/http-exceptions'
 */
httpExceptions.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: httpExceptions.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
export const networkErrors = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: networkErrors.url(options),
    method: 'get',
});

networkErrors.definition = {
    methods: ['get', 'head'],
    url: '/features/errors/network-errors',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.url = (options?: RouteQueryOptions) => {
    return networkErrors.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: networkErrors.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\NetworkErrorController::networkErrors
 * @see app/Http/Controllers/Feature/NetworkErrorController.php:35
 * @route '/features/errors/network-errors'
 */
networkErrors.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: networkErrors.url(options),
    method: 'head',
});
const errors = {
    httpExceptions: Object.assign(httpExceptions, httpExceptionsBa316e),
    networkErrors: Object.assign(networkErrors, networkErrors),
};

export default errors;

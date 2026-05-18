import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\StateController::error
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
export const error = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: error.url(options),
    method: 'post',
});

error.definition = {
    methods: ['post'],
    url: '/features/state/flash-data/error',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::error
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
error.url = (options?: RouteQueryOptions) => {
    return error.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::error
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
error.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: error.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::warning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
export const warning = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: warning.url(options),
    method: 'post',
});

warning.definition = {
    methods: ['post'],
    url: '/features/state/flash-data/warning',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::warning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
warning.url = (options?: RouteQueryOptions) => {
    return warning.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::warning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
warning.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: warning.url(options),
    method: 'post',
});
const flashData = {
    error: Object.assign(error, error),
    warning: Object.assign(warning, warning),
};

export default flashData;

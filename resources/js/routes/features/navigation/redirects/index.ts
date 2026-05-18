import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\NavigationController::back
 * @see app/Http/Controllers/Feature/NavigationController.php:118
 * @route '/features/navigation/redirects/back'
 */
export const back = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: back.url(options),
    method: 'post',
});

back.definition = {
    methods: ['post'],
    url: '/features/navigation/redirects/back',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\NavigationController::back
 * @see app/Http/Controllers/Feature/NavigationController.php:118
 * @route '/features/navigation/redirects/back'
 */
back.url = (options?: RouteQueryOptions) => {
    return back.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\NavigationController::back
 * @see app/Http/Controllers/Feature/NavigationController.php:118
 * @route '/features/navigation/redirects/back'
 */
back.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: back.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\NavigationController::toRoute
 * @see app/Http/Controllers/Feature/NavigationController.php:123
 * @route '/features/navigation/redirects/to-route'
 */
export const toRoute = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: toRoute.url(options),
    method: 'post',
});

toRoute.definition = {
    methods: ['post'],
    url: '/features/navigation/redirects/to-route',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\NavigationController::toRoute
 * @see app/Http/Controllers/Feature/NavigationController.php:123
 * @route '/features/navigation/redirects/to-route'
 */
toRoute.url = (options?: RouteQueryOptions) => {
    return toRoute.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\NavigationController::toRoute
 * @see app/Http/Controllers/Feature/NavigationController.php:123
 * @route '/features/navigation/redirects/to-route'
 */
toRoute.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toRoute.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\NavigationController::external
 * @see app/Http/Controllers/Feature/NavigationController.php:130
 * @route '/features/navigation/redirects/external'
 */
export const external = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: external.url(options),
    method: 'post',
});

external.definition = {
    methods: ['post'],
    url: '/features/navigation/redirects/external',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\NavigationController::external
 * @see app/Http/Controllers/Feature/NavigationController.php:130
 * @route '/features/navigation/redirects/external'
 */
external.url = (options?: RouteQueryOptions) => {
    return external.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\NavigationController::external
 * @see app/Http/Controllers/Feature/NavigationController.php:130
 * @route '/features/navigation/redirects/external'
 */
external.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: external.url(options),
    method: 'post',
});
const redirects = {
    back: Object.assign(back, back),
    toRoute: Object.assign(toRoute, toRoute),
    external: Object.assign(external, external),
};

export default redirects;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::linkPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:10
 * @route '/features/prefetching/link-prefetch'
 */
export const linkPrefetch = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: linkPrefetch.url(options),
    method: 'get',
});

linkPrefetch.definition = {
    methods: ['get', 'head'],
    url: '/features/prefetching/link-prefetch',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::linkPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:10
 * @route '/features/prefetching/link-prefetch'
 */
linkPrefetch.url = (options?: RouteQueryOptions) => {
    return linkPrefetch.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::linkPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:10
 * @route '/features/prefetching/link-prefetch'
 */
linkPrefetch.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: linkPrefetch.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::linkPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:10
 * @route '/features/prefetching/link-prefetch'
 */
linkPrefetch.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: linkPrefetch.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::staleWhileRevalidate
 * @see app/Http/Controllers/Feature/PrefetchingController.php:15
 * @route '/features/prefetching/stale-while-revalidate'
 */
export const staleWhileRevalidate = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: staleWhileRevalidate.url(options),
    method: 'get',
});

staleWhileRevalidate.definition = {
    methods: ['get', 'head'],
    url: '/features/prefetching/stale-while-revalidate',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::staleWhileRevalidate
 * @see app/Http/Controllers/Feature/PrefetchingController.php:15
 * @route '/features/prefetching/stale-while-revalidate'
 */
staleWhileRevalidate.url = (options?: RouteQueryOptions) => {
    return staleWhileRevalidate.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::staleWhileRevalidate
 * @see app/Http/Controllers/Feature/PrefetchingController.php:15
 * @route '/features/prefetching/stale-while-revalidate'
 */
staleWhileRevalidate.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: staleWhileRevalidate.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::staleWhileRevalidate
 * @see app/Http/Controllers/Feature/PrefetchingController.php:15
 * @route '/features/prefetching/stale-while-revalidate'
 */
staleWhileRevalidate.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: staleWhileRevalidate.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::manualPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:20
 * @route '/features/prefetching/manual-prefetch'
 */
export const manualPrefetch = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: manualPrefetch.url(options),
    method: 'get',
});

manualPrefetch.definition = {
    methods: ['get', 'head'],
    url: '/features/prefetching/manual-prefetch',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::manualPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:20
 * @route '/features/prefetching/manual-prefetch'
 */
manualPrefetch.url = (options?: RouteQueryOptions) => {
    return manualPrefetch.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::manualPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:20
 * @route '/features/prefetching/manual-prefetch'
 */
manualPrefetch.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: manualPrefetch.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::manualPrefetch
 * @see app/Http/Controllers/Feature/PrefetchingController.php:20
 * @route '/features/prefetching/manual-prefetch'
 */
manualPrefetch.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: manualPrefetch.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::cacheManagement
 * @see app/Http/Controllers/Feature/PrefetchingController.php:25
 * @route '/features/prefetching/cache-management'
 */
export const cacheManagement = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: cacheManagement.url(options),
    method: 'get',
});

cacheManagement.definition = {
    methods: ['get', 'head'],
    url: '/features/prefetching/cache-management',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::cacheManagement
 * @see app/Http/Controllers/Feature/PrefetchingController.php:25
 * @route '/features/prefetching/cache-management'
 */
cacheManagement.url = (options?: RouteQueryOptions) => {
    return cacheManagement.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::cacheManagement
 * @see app/Http/Controllers/Feature/PrefetchingController.php:25
 * @route '/features/prefetching/cache-management'
 */
cacheManagement.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: cacheManagement.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\PrefetchingController::cacheManagement
 * @see app/Http/Controllers/Feature/PrefetchingController.php:25
 * @route '/features/prefetching/cache-management'
 */
cacheManagement.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: cacheManagement.url(options),
    method: 'head',
});
const prefetching = {
    linkPrefetch: Object.assign(linkPrefetch, linkPrefetch),
    staleWhileRevalidate: Object.assign(
        staleWhileRevalidate,
        staleWhileRevalidate,
    ),
    manualPrefetch: Object.assign(manualPrefetch, manualPrefetch),
    cacheManagement: Object.assign(cacheManagement, cacheManagement),
};

export default prefetching;

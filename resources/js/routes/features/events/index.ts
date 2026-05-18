import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../wayfinder';
import globalEvents5439f0 from './global-events';
import progressFb9fab from './progress';
import visitCallbacks0d0e0f from './visit-callbacks';
/**
 * @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
export const globalEvents = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: globalEvents.url(options),
    method: 'get',
});

globalEvents.definition = {
    methods: ['get', 'head'],
    url: '/features/events/global-events',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.url = (options?: RouteQueryOptions) => {
    return globalEvents.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: globalEvents.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: globalEvents.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
export const visitCallbacks = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: visitCallbacks.url(options),
    method: 'get',
});

visitCallbacks.definition = {
    methods: ['get', 'head'],
    url: '/features/events/visit-callbacks',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.url = (options?: RouteQueryOptions) => {
    return visitCallbacks.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: visitCallbacks.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: visitCallbacks.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
export const progress = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: progress.url(options),
    method: 'get',
});

progress.definition = {
    methods: ['get', 'head'],
    url: '/features/events/progress',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.url = (options?: RouteQueryOptions) => {
    return progress.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progress.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: progress.url(options),
    method: 'head',
});
const events = {
    globalEvents: Object.assign(globalEvents, globalEvents5439f0),
    visitCallbacks: Object.assign(visitCallbacks, visitCallbacks0d0e0f),
    progress: Object.assign(progress, progressFb9fab),
};

export default events;

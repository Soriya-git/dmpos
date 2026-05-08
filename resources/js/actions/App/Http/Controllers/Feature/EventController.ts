import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
export const globalEvents = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: globalEvents.url(options),
    method: 'get',
})

globalEvents.definition = {
    methods: ["get","head"],
    url: '/features/events/global-events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.url = (options?: RouteQueryOptions) => {
    return globalEvents.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: globalEvents.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\EventController::globalEvents
 * @see app/Http/Controllers/Feature/EventController.php:12
 * @route '/features/events/global-events'
 */
globalEvents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: globalEvents.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\EventController::globalEventsAction
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
export const globalEventsAction = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: globalEventsAction.url(options),
    method: 'post',
})

globalEventsAction.definition = {
    methods: ["post"],
    url: '/features/events/global-events/action',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\EventController::globalEventsAction
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
globalEventsAction.url = (options?: RouteQueryOptions) => {
    return globalEventsAction.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::globalEventsAction
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
globalEventsAction.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: globalEventsAction.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
export const visitCallbacks = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: visitCallbacks.url(options),
    method: 'get',
})

visitCallbacks.definition = {
    methods: ["get","head"],
    url: '/features/events/visit-callbacks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.url = (options?: RouteQueryOptions) => {
    return visitCallbacks.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: visitCallbacks.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacks
 * @see app/Http/Controllers/Feature/EventController.php:22
 * @route '/features/events/visit-callbacks'
 */
visitCallbacks.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: visitCallbacks.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacksAction
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
export const visitCallbacksAction = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: visitCallbacksAction.url(options),
    method: 'post',
})

visitCallbacksAction.definition = {
    methods: ["post"],
    url: '/features/events/visit-callbacks/action',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacksAction
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
visitCallbacksAction.url = (options?: RouteQueryOptions) => {
    return visitCallbacksAction.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::visitCallbacksAction
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
visitCallbacksAction.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: visitCallbacksAction.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
export const progress = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progress.url(options),
    method: 'get',
})

progress.definition = {
    methods: ["get","head"],
    url: '/features/events/progress',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.url = (options?: RouteQueryOptions) => {
    return progress.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progress.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\EventController::progress
 * @see app/Http/Controllers/Feature/EventController.php:32
 * @route '/features/events/progress'
 */
progress.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: progress.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\EventController::progressSlow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
export const progressSlow = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progressSlow.url(options),
    method: 'get',
})

progressSlow.definition = {
    methods: ["get","head"],
    url: '/features/events/progress/slow',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\EventController::progressSlow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
progressSlow.url = (options?: RouteQueryOptions) => {
    return progressSlow.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\EventController::progressSlow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
progressSlow.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progressSlow.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\EventController::progressSlow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
progressSlow.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: progressSlow.url(options),
    method: 'head',
})
const EventController = { globalEvents, globalEventsAction, visitCallbacks, visitCallbacksAction, progress, progressSlow }

export default EventController
import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import flashData428386 from './flash-data'
/**
* @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
export const remember = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: remember.url(options),
    method: 'get',
})

remember.definition = {
    methods: ["get","head"],
    url: '/features/state/remember',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.url = (options?: RouteQueryOptions) => {
    return remember.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: remember.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: remember.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
export const flashData = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flashData.url(options),
    method: 'get',
})

flashData.definition = {
    methods: ["get","head"],
    url: '/features/state/flash-data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.url = (options?: RouteQueryOptions) => {
    return flashData.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flashData.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: flashData.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
export const sharedProps = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sharedProps.url(options),
    method: 'get',
})

sharedProps.definition = {
    methods: ["get","head"],
    url: '/features/state/shared-props',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.url = (options?: RouteQueryOptions) => {
    return sharedProps.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sharedProps.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sharedProps.url(options),
    method: 'head',
})
const state = {
    remember: Object.assign(remember, remember),
flashData: Object.assign(flashData, flashData428386),
sharedProps: Object.assign(sharedProps, sharedProps),
}

export default state
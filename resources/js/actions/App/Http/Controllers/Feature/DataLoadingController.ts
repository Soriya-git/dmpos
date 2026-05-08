import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults, validateParameters } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::deferredProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:16
 * @route '/features/data-loading/deferred-props'
 */
export const deferredProps = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deferredProps.url(options),
    method: 'get',
})

deferredProps.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/deferred-props',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::deferredProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:16
 * @route '/features/data-loading/deferred-props'
 */
deferredProps.url = (options?: RouteQueryOptions) => {
    return deferredProps.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::deferredProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:16
 * @route '/features/data-loading/deferred-props'
 */
deferredProps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: deferredProps.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::deferredProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:16
 * @route '/features/data-loading/deferred-props'
 */
deferredProps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: deferredProps.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::partialReloads
 * @see app/Http/Controllers/Feature/DataLoadingController.php:39
 * @route '/features/data-loading/partial-reloads'
 */
export const partialReloads = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: partialReloads.url(options),
    method: 'get',
})

partialReloads.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/partial-reloads',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::partialReloads
 * @see app/Http/Controllers/Feature/DataLoadingController.php:39
 * @route '/features/data-loading/partial-reloads'
 */
partialReloads.url = (options?: RouteQueryOptions) => {
    return partialReloads.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::partialReloads
 * @see app/Http/Controllers/Feature/DataLoadingController.php:39
 * @route '/features/data-loading/partial-reloads'
 */
partialReloads.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: partialReloads.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::partialReloads
 * @see app/Http/Controllers/Feature/DataLoadingController.php:39
 * @route '/features/data-loading/partial-reloads'
 */
partialReloads.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: partialReloads.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::infiniteScroll
 * @see app/Http/Controllers/Feature/DataLoadingController.php:56
 * @route '/features/data-loading/infinite-scroll'
 */
export const infiniteScroll = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: infiniteScroll.url(options),
    method: 'get',
})

infiniteScroll.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/infinite-scroll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::infiniteScroll
 * @see app/Http/Controllers/Feature/DataLoadingController.php:56
 * @route '/features/data-loading/infinite-scroll'
 */
infiniteScroll.url = (options?: RouteQueryOptions) => {
    return infiniteScroll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::infiniteScroll
 * @see app/Http/Controllers/Feature/DataLoadingController.php:56
 * @route '/features/data-loading/infinite-scroll'
 */
infiniteScroll.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: infiniteScroll.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::infiniteScroll
 * @see app/Http/Controllers/Feature/DataLoadingController.php:56
 * @route '/features/data-loading/infinite-scroll'
 */
infiniteScroll.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: infiniteScroll.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::whenVisible
 * @see app/Http/Controllers/Feature/DataLoadingController.php:70
 * @route '/features/data-loading/when-visible'
 */
export const whenVisible = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: whenVisible.url(options),
    method: 'get',
})

whenVisible.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/when-visible',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::whenVisible
 * @see app/Http/Controllers/Feature/DataLoadingController.php:70
 * @route '/features/data-loading/when-visible'
 */
whenVisible.url = (options?: RouteQueryOptions) => {
    return whenVisible.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::whenVisible
 * @see app/Http/Controllers/Feature/DataLoadingController.php:70
 * @route '/features/data-loading/when-visible'
 */
whenVisible.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: whenVisible.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::whenVisible
 * @see app/Http/Controllers/Feature/DataLoadingController.php:70
 * @route '/features/data-loading/when-visible'
 */
whenVisible.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: whenVisible.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::polling
 * @see app/Http/Controllers/Feature/DataLoadingController.php:104
 * @route '/features/data-loading/polling'
 */
export const polling = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: polling.url(options),
    method: 'get',
})

polling.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/polling',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::polling
 * @see app/Http/Controllers/Feature/DataLoadingController.php:104
 * @route '/features/data-loading/polling'
 */
polling.url = (options?: RouteQueryOptions) => {
    return polling.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::polling
 * @see app/Http/Controllers/Feature/DataLoadingController.php:104
 * @route '/features/data-loading/polling'
 */
polling.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: polling.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::polling
 * @see app/Http/Controllers/Feature/DataLoadingController.php:104
 * @route '/features/data-loading/polling'
 */
polling.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: polling.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::propMerging
 * @see app/Http/Controllers/Feature/DataLoadingController.php:113
 * @route '/features/data-loading/prop-merging'
 */
export const propMerging = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: propMerging.url(options),
    method: 'get',
})

propMerging.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/prop-merging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::propMerging
 * @see app/Http/Controllers/Feature/DataLoadingController.php:113
 * @route '/features/data-loading/prop-merging'
 */
propMerging.url = (options?: RouteQueryOptions) => {
    return propMerging.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::propMerging
 * @see app/Http/Controllers/Feature/DataLoadingController.php:113
 * @route '/features/data-loading/prop-merging'
 */
propMerging.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: propMerging.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::propMerging
 * @see app/Http/Controllers/Feature/DataLoadingController.php:113
 * @route '/features/data-loading/prop-merging'
 */
propMerging.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: propMerging.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::onceProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:191
 * @route '/features/data-loading/once-props/{page?}'
 */
export const onceProps = (args?: { page?: string | number } | [page: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onceProps.url(args, options),
    method: 'get',
})

onceProps.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/once-props/{page?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::onceProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:191
 * @route '/features/data-loading/once-props/{page?}'
 */
onceProps.url = (args?: { page?: string | number } | [page: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { page: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    page: args[0],
                }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
            "page",
        ])

    const parsedArgs = {
                        page: args?.page,
                }

    return onceProps.definition.url
            .replace('{page?}', parsedArgs.page?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::onceProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:191
 * @route '/features/data-loading/once-props/{page?}'
 */
onceProps.get = (args?: { page?: string | number } | [page: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onceProps.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::onceProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:191
 * @route '/features/data-loading/once-props/{page?}'
 */
onceProps.head = (args?: { page?: string | number } | [page: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: onceProps.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::optionalProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:162
 * @route '/features/data-loading/optional-props'
 */
export const optionalProps = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: optionalProps.url(options),
    method: 'get',
})

optionalProps.definition = {
    methods: ["get","head"],
    url: '/features/data-loading/optional-props',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::optionalProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:162
 * @route '/features/data-loading/optional-props'
 */
optionalProps.url = (options?: RouteQueryOptions) => {
    return optionalProps.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\DataLoadingController::optionalProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:162
 * @route '/features/data-loading/optional-props'
 */
optionalProps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: optionalProps.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\DataLoadingController::optionalProps
 * @see app/Http/Controllers/Feature/DataLoadingController.php:162
 * @route '/features/data-loading/optional-props'
 */
optionalProps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: optionalProps.url(options),
    method: 'head',
})
const DataLoadingController = { deferredProps, partialReloads, infiniteScroll, whenVisible, polling, propMerging, onceProps, optionalProps }

export default DataLoadingController
import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:10
 * @route '/features/layouts/persistent-layouts'
 */
export const persistentLayouts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: persistentLayouts.url(options),
    method: 'get',
})

persistentLayouts.definition = {
    methods: ["get","head"],
    url: '/features/layouts/persistent-layouts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:10
 * @route '/features/layouts/persistent-layouts'
 */
persistentLayouts.url = (options?: RouteQueryOptions) => {
    return persistentLayouts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:10
 * @route '/features/layouts/persistent-layouts'
 */
persistentLayouts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: persistentLayouts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:10
 * @route '/features/layouts/persistent-layouts'
 */
persistentLayouts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: persistentLayouts.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayoutsPageTwo
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
export const persistentLayoutsPageTwo = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: persistentLayoutsPageTwo.url(options),
    method: 'get',
})

persistentLayoutsPageTwo.definition = {
    methods: ["get","head"],
    url: '/features/layouts/persistent-layouts/page-2',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayoutsPageTwo
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
persistentLayoutsPageTwo.url = (options?: RouteQueryOptions) => {
    return persistentLayoutsPageTwo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayoutsPageTwo
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
persistentLayoutsPageTwo.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: persistentLayoutsPageTwo.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\LayoutController::persistentLayoutsPageTwo
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
persistentLayoutsPageTwo.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: persistentLayoutsPageTwo.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\LayoutController::nestedLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:20
 * @route '/features/layouts/nested-layouts'
 */
export const nestedLayouts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: nestedLayouts.url(options),
    method: 'get',
})

nestedLayouts.definition = {
    methods: ["get","head"],
    url: '/features/layouts/nested-layouts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\LayoutController::nestedLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:20
 * @route '/features/layouts/nested-layouts'
 */
nestedLayouts.url = (options?: RouteQueryOptions) => {
    return nestedLayouts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\LayoutController::nestedLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:20
 * @route '/features/layouts/nested-layouts'
 */
nestedLayouts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: nestedLayouts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\LayoutController::nestedLayouts
 * @see app/Http/Controllers/Feature/LayoutController.php:20
 * @route '/features/layouts/nested-layouts'
 */
nestedLayouts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: nestedLayouts.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\LayoutController::head
 * @see app/Http/Controllers/Feature/LayoutController.php:25
 * @route '/features/layouts/head'
 */
export const head = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: head.url(options),
    method: 'get',
})

head.definition = {
    methods: ["get","head"],
    url: '/features/layouts/head',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\LayoutController::head
 * @see app/Http/Controllers/Feature/LayoutController.php:25
 * @route '/features/layouts/head'
 */
head.url = (options?: RouteQueryOptions) => {
    return head.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\LayoutController::head
 * @see app/Http/Controllers/Feature/LayoutController.php:25
 * @route '/features/layouts/head'
 */
head.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: head.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\LayoutController::head
 * @see app/Http/Controllers/Feature/LayoutController.php:25
 * @route '/features/layouts/head'
 */
head.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: head.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\LayoutController::layoutProps
 * @see app/Http/Controllers/Feature/LayoutController.php:30
 * @route '/features/layouts/layout-props'
 */
export const layoutProps = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: layoutProps.url(options),
    method: 'get',
})

layoutProps.definition = {
    methods: ["get","head"],
    url: '/features/layouts/layout-props',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\LayoutController::layoutProps
 * @see app/Http/Controllers/Feature/LayoutController.php:30
 * @route '/features/layouts/layout-props'
 */
layoutProps.url = (options?: RouteQueryOptions) => {
    return layoutProps.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\LayoutController::layoutProps
 * @see app/Http/Controllers/Feature/LayoutController.php:30
 * @route '/features/layouts/layout-props'
 */
layoutProps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: layoutProps.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\LayoutController::layoutProps
 * @see app/Http/Controllers/Feature/LayoutController.php:30
 * @route '/features/layouts/layout-props'
 */
layoutProps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: layoutProps.url(options),
    method: 'head',
})
const LayoutController = { persistentLayouts, persistentLayoutsPageTwo, nestedLayouts, head, layoutProps }

export default LayoutController
import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import redirects224360 from './redirects'
/**
* @see \App\Http\Controllers\Feature\NavigationController::links
 * @see app/Http/Controllers/Feature/NavigationController.php:13
 * @route '/features/navigation/links'
 */
export const links = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: links.url(options),
    method: 'get',
})

links.definition = {
    methods: ["get","head"],
    url: '/features/navigation/links',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::links
 * @see app/Http/Controllers/Feature/NavigationController.php:13
 * @route '/features/navigation/links'
 */
links.url = (options?: RouteQueryOptions) => {
    return links.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::links
 * @see app/Http/Controllers/Feature/NavigationController.php:13
 * @route '/features/navigation/links'
 */
links.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: links.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::links
 * @see app/Http/Controllers/Feature/NavigationController.php:13
 * @route '/features/navigation/links'
 */
links.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: links.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveState
 * @see app/Http/Controllers/Feature/NavigationController.php:25
 * @route '/features/navigation/preserve-state'
 */
export const preserveState = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preserveState.url(options),
    method: 'get',
})

preserveState.definition = {
    methods: ["get","head"],
    url: '/features/navigation/preserve-state',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveState
 * @see app/Http/Controllers/Feature/NavigationController.php:25
 * @route '/features/navigation/preserve-state'
 */
preserveState.url = (options?: RouteQueryOptions) => {
    return preserveState.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveState
 * @see app/Http/Controllers/Feature/NavigationController.php:25
 * @route '/features/navigation/preserve-state'
 */
preserveState.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preserveState.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveState
 * @see app/Http/Controllers/Feature/NavigationController.php:25
 * @route '/features/navigation/preserve-state'
 */
preserveState.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preserveState.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveScroll
 * @see app/Http/Controllers/Feature/NavigationController.php:33
 * @route '/features/navigation/preserve-scroll'
 */
export const preserveScroll = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preserveScroll.url(options),
    method: 'get',
})

preserveScroll.definition = {
    methods: ["get","head"],
    url: '/features/navigation/preserve-scroll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveScroll
 * @see app/Http/Controllers/Feature/NavigationController.php:33
 * @route '/features/navigation/preserve-scroll'
 */
preserveScroll.url = (options?: RouteQueryOptions) => {
    return preserveScroll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveScroll
 * @see app/Http/Controllers/Feature/NavigationController.php:33
 * @route '/features/navigation/preserve-scroll'
 */
preserveScroll.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preserveScroll.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::preserveScroll
 * @see app/Http/Controllers/Feature/NavigationController.php:33
 * @route '/features/navigation/preserve-scroll'
 */
preserveScroll.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preserveScroll.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::viewTransitions
 * @see app/Http/Controllers/Feature/NavigationController.php:40
 * @route '/features/navigation/view-transitions'
 */
export const viewTransitions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: viewTransitions.url(options),
    method: 'get',
})

viewTransitions.definition = {
    methods: ["get","head"],
    url: '/features/navigation/view-transitions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::viewTransitions
 * @see app/Http/Controllers/Feature/NavigationController.php:40
 * @route '/features/navigation/view-transitions'
 */
viewTransitions.url = (options?: RouteQueryOptions) => {
    return viewTransitions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::viewTransitions
 * @see app/Http/Controllers/Feature/NavigationController.php:40
 * @route '/features/navigation/view-transitions'
 */
viewTransitions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: viewTransitions.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::viewTransitions
 * @see app/Http/Controllers/Feature/NavigationController.php:40
 * @route '/features/navigation/view-transitions'
 */
viewTransitions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: viewTransitions.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::historyManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:45
 * @route '/features/navigation/history-management'
 */
export const historyManagement = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historyManagement.url(options),
    method: 'get',
})

historyManagement.definition = {
    methods: ["get","head"],
    url: '/features/navigation/history-management',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::historyManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:45
 * @route '/features/navigation/history-management'
 */
historyManagement.url = (options?: RouteQueryOptions) => {
    return historyManagement.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::historyManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:45
 * @route '/features/navigation/history-management'
 */
historyManagement.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historyManagement.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::historyManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:45
 * @route '/features/navigation/history-management'
 */
historyManagement.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: historyManagement.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncRequests
 * @see app/Http/Controllers/Feature/NavigationController.php:58
 * @route '/features/navigation/async-requests'
 */
export const asyncRequests = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asyncRequests.url(options),
    method: 'get',
})

asyncRequests.definition = {
    methods: ["get","head"],
    url: '/features/navigation/async-requests',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncRequests
 * @see app/Http/Controllers/Feature/NavigationController.php:58
 * @route '/features/navigation/async-requests'
 */
asyncRequests.url = (options?: RouteQueryOptions) => {
    return asyncRequests.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncRequests
 * @see app/Http/Controllers/Feature/NavigationController.php:58
 * @route '/features/navigation/async-requests'
 */
asyncRequests.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asyncRequests.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncRequests
 * @see app/Http/Controllers/Feature/NavigationController.php:58
 * @route '/features/navigation/async-requests'
 */
asyncRequests.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: asyncRequests.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncSlow
 * @see app/Http/Controllers/Feature/NavigationController.php:69
 * @route '/features/navigation/async-slow'
 */
export const asyncSlow = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asyncSlow.url(options),
    method: 'get',
})

asyncSlow.definition = {
    methods: ["get","head"],
    url: '/features/navigation/async-slow',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncSlow
 * @see app/Http/Controllers/Feature/NavigationController.php:69
 * @route '/features/navigation/async-slow'
 */
asyncSlow.url = (options?: RouteQueryOptions) => {
    return asyncSlow.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncSlow
 * @see app/Http/Controllers/Feature/NavigationController.php:69
 * @route '/features/navigation/async-slow'
 */
asyncSlow.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asyncSlow.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::asyncSlow
 * @see app/Http/Controllers/Feature/NavigationController.php:69
 * @route '/features/navigation/async-slow'
 */
asyncSlow.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: asyncSlow.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::manualVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:103
 * @route '/features/navigation/manual-visits'
 */
export const manualVisits = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: manualVisits.url(options),
    method: 'get',
})

manualVisits.definition = {
    methods: ["get","head"],
    url: '/features/navigation/manual-visits',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::manualVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:103
 * @route '/features/navigation/manual-visits'
 */
manualVisits.url = (options?: RouteQueryOptions) => {
    return manualVisits.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::manualVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:103
 * @route '/features/navigation/manual-visits'
 */
manualVisits.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: manualVisits.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::manualVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:103
 * @route '/features/navigation/manual-visits'
 */
manualVisits.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: manualVisits.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::redirects
 * @see app/Http/Controllers/Feature/NavigationController.php:111
 * @route '/features/navigation/redirects'
 */
export const redirects = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirects.url(options),
    method: 'get',
})

redirects.definition = {
    methods: ["get","head"],
    url: '/features/navigation/redirects',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::redirects
 * @see app/Http/Controllers/Feature/NavigationController.php:111
 * @route '/features/navigation/redirects'
 */
redirects.url = (options?: RouteQueryOptions) => {
    return redirects.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::redirects
 * @see app/Http/Controllers/Feature/NavigationController.php:111
 * @route '/features/navigation/redirects'
 */
redirects.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirects.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::redirects
 * @see app/Http/Controllers/Feature/NavigationController.php:111
 * @route '/features/navigation/redirects'
 */
redirects.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: redirects.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::scrollManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:135
 * @route '/features/navigation/scroll-management'
 */
export const scrollManagement = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: scrollManagement.url(options),
    method: 'get',
})

scrollManagement.definition = {
    methods: ["get","head"],
    url: '/features/navigation/scroll-management',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::scrollManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:135
 * @route '/features/navigation/scroll-management'
 */
scrollManagement.url = (options?: RouteQueryOptions) => {
    return scrollManagement.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::scrollManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:135
 * @route '/features/navigation/scroll-management'
 */
scrollManagement.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: scrollManagement.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::scrollManagement
 * @see app/Http/Controllers/Feature/NavigationController.php:135
 * @route '/features/navigation/scroll-management'
 */
scrollManagement.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: scrollManagement.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:79
 * @route '/features/navigation/instant-visits'
 */
export const instantVisits = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: instantVisits.url(options),
    method: 'get',
})

instantVisits.definition = {
    methods: ["get","head"],
    url: '/features/navigation/instant-visits',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:79
 * @route '/features/navigation/instant-visits'
 */
instantVisits.url = (options?: RouteQueryOptions) => {
    return instantVisits.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:79
 * @route '/features/navigation/instant-visits'
 */
instantVisits.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: instantVisits.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisits
 * @see app/Http/Controllers/Feature/NavigationController.php:79
 * @route '/features/navigation/instant-visits'
 */
instantVisits.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: instantVisits.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisitTarget
 * @see app/Http/Controllers/Feature/NavigationController.php:87
 * @route '/features/navigation/instant-visit-target'
 */
export const instantVisitTarget = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: instantVisitTarget.url(options),
    method: 'get',
})

instantVisitTarget.definition = {
    methods: ["get","head"],
    url: '/features/navigation/instant-visit-target',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisitTarget
 * @see app/Http/Controllers/Feature/NavigationController.php:87
 * @route '/features/navigation/instant-visit-target'
 */
instantVisitTarget.url = (options?: RouteQueryOptions) => {
    return instantVisitTarget.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisitTarget
 * @see app/Http/Controllers/Feature/NavigationController.php:87
 * @route '/features/navigation/instant-visit-target'
 */
instantVisitTarget.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: instantVisitTarget.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::instantVisitTarget
 * @see app/Http/Controllers/Feature/NavigationController.php:87
 * @route '/features/navigation/instant-visit-target'
 */
instantVisitTarget.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: instantVisitTarget.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\NavigationController::urlFragments
 * @see app/Http/Controllers/Feature/NavigationController.php:147
 * @route '/features/navigation/url-fragments'
 */
export const urlFragments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: urlFragments.url(options),
    method: 'get',
})

urlFragments.definition = {
    methods: ["get","head"],
    url: '/features/navigation/url-fragments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\NavigationController::urlFragments
 * @see app/Http/Controllers/Feature/NavigationController.php:147
 * @route '/features/navigation/url-fragments'
 */
urlFragments.url = (options?: RouteQueryOptions) => {
    return urlFragments.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\NavigationController::urlFragments
 * @see app/Http/Controllers/Feature/NavigationController.php:147
 * @route '/features/navigation/url-fragments'
 */
urlFragments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: urlFragments.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\NavigationController::urlFragments
 * @see app/Http/Controllers/Feature/NavigationController.php:147
 * @route '/features/navigation/url-fragments'
 */
urlFragments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: urlFragments.url(options),
    method: 'head',
})
const navigation = {
    links: Object.assign(links, links),
preserveState: Object.assign(preserveState, preserveState),
preserveScroll: Object.assign(preserveScroll, preserveScroll),
viewTransitions: Object.assign(viewTransitions, viewTransitions),
historyManagement: Object.assign(historyManagement, historyManagement),
asyncRequests: Object.assign(asyncRequests, asyncRequests),
asyncSlow: Object.assign(asyncSlow, asyncSlow),
manualVisits: Object.assign(manualVisits, manualVisits),
redirects: Object.assign(redirects, redirects224360),
scrollManagement: Object.assign(scrollManagement, scrollManagement),
instantVisits: Object.assign(instantVisits, instantVisits),
instantVisitTarget: Object.assign(instantVisitTarget, instantVisitTarget),
urlFragments: Object.assign(urlFragments, urlFragments),
}

export default navigation
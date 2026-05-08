import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\POS\PosSessionController::index
 * @see app/Http/Controllers/POS/PosSessionController.php:15
 * @route '/pos-sessions'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/pos-sessions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\POS\PosSessionController::index
 * @see app/Http/Controllers/POS/PosSessionController.php:15
 * @route '/pos-sessions'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\POS\PosSessionController::index
 * @see app/Http/Controllers/POS/PosSessionController.php:15
 * @route '/pos-sessions'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\POS\PosSessionController::index
 * @see app/Http/Controllers/POS/PosSessionController.php:15
 * @route '/pos-sessions'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\POS\PosSessionController::open
 * @see app/Http/Controllers/POS/PosSessionController.php:38
 * @route '/pos-sessions/open'
 */
export const open = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: open.url(options),
    method: 'post',
})

open.definition = {
    methods: ["post"],
    url: '/pos-sessions/open',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\POS\PosSessionController::open
 * @see app/Http/Controllers/POS/PosSessionController.php:38
 * @route '/pos-sessions/open'
 */
open.url = (options?: RouteQueryOptions) => {
    return open.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\POS\PosSessionController::open
 * @see app/Http/Controllers/POS/PosSessionController.php:38
 * @route '/pos-sessions/open'
 */
open.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: open.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\POS\PosSessionController::close
 * @see app/Http/Controllers/POS/PosSessionController.php:109
 * @route '/pos-sessions/{posSession}/close'
 */
export const close = (args: { posSession: number | { id: number } } | [posSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
})

close.definition = {
    methods: ["post"],
    url: '/pos-sessions/{posSession}/close',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\POS\PosSessionController::close
 * @see app/Http/Controllers/POS/PosSessionController.php:109
 * @route '/pos-sessions/{posSession}/close'
 */
close.url = (args: { posSession: number | { id: number } } | [posSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { posSession: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { posSession: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    posSession: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        posSession: typeof args.posSession === 'object'
                ? args.posSession.id
                : args.posSession,
                }

    return close.definition.url
            .replace('{posSession}', parsedArgs.posSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\POS\PosSessionController::close
 * @see app/Http/Controllers/POS/PosSessionController.php:109
 * @route '/pos-sessions/{posSession}/close'
 */
close.post = (args: { posSession: number | { id: number } } | [posSession: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
})
const PosSessionController = { index, open, close }

export default PosSessionController
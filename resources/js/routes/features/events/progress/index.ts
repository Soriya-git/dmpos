import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\EventController::slow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
export const slow = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: slow.url(options),
    method: 'get',
});

slow.definition = {
    methods: ['get', 'head'],
    url: '/features/events/progress/slow',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::slow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
slow.url = (options?: RouteQueryOptions) => {
    return slow.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::slow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
slow.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: slow.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\EventController::slow
 * @see app/Http/Controllers/Feature/EventController.php:37
 * @route '/features/events/progress/slow'
 */
slow.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: slow.url(options),
    method: 'head',
});
const progress = {
    slow: Object.assign(slow, slow),
};

export default progress;

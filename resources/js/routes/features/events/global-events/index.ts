import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
export const action = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: action.url(options),
    method: 'post',
});

action.definition = {
    methods: ['post'],
    url: '/features/events/global-events/action',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
action.url = (options?: RouteQueryOptions) => {
    return action.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:17
 * @route '/features/events/global-events/action'
 */
action.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: action.url(options),
    method: 'post',
});
const globalEvents = {
    action: Object.assign(action, action),
};

export default globalEvents;

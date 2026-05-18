import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
export const action = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: action.url(options),
    method: 'post',
});

action.definition = {
    methods: ['post'],
    url: '/features/events/visit-callbacks/action',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
action.url = (options?: RouteQueryOptions) => {
    return action.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\EventController::action
 * @see app/Http/Controllers/Feature/EventController.php:27
 * @route '/features/events/visit-callbacks/action'
 */
action.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: action.url(options),
    method: 'post',
});
const visitCallbacks = {
    action: Object.assign(action, action),
};

export default visitCallbacks;

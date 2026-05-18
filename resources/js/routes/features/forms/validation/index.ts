import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\FormController::secondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
export const secondary = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: secondary.url(options),
    method: 'post',
});

secondary.definition = {
    methods: ['post'],
    url: '/features/forms/validation/secondary',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::secondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
secondary.url = (options?: RouteQueryOptions) => {
    return secondary.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::secondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
secondary.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: secondary.url(options),
    method: 'post',
});
const validation = {
    secondary: Object.assign(secondary, secondary),
};

export default validation;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\LayoutController::page2
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
export const page2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: page2.url(options),
    method: 'get',
});

page2.definition = {
    methods: ['get', 'head'],
    url: '/features/layouts/persistent-layouts/page-2',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\LayoutController::page2
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
page2.url = (options?: RouteQueryOptions) => {
    return page2.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\LayoutController::page2
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
page2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: page2.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\LayoutController::page2
 * @see app/Http/Controllers/Feature/LayoutController.php:15
 * @route '/features/layouts/persistent-layouts/page-2'
 */
page2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: page2.url(options),
    method: 'head',
});
const persistentLayouts = {
    page2: Object.assign(page2, page2),
};

export default persistentLayouts;

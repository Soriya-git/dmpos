import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/menu/categories',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});
const categories = {
    store: Object.assign(store, store),
};

export default categories;

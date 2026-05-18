import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/products/items',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});
const items = {
    store: Object.assign(store, store),
};

export default items;

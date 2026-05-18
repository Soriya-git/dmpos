import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/products/bom',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::store
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});
const bom = {
    store: Object.assign(store, store),
};

export default bom;

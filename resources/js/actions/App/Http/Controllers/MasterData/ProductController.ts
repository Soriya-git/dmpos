import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\ProductController::index
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/products',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::index
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::index
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\ProductController::index
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeItem
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
export const storeItem = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeItem.url(options),
    method: 'post',
});

storeItem.definition = {
    methods: ['post'],
    url: '/master-data/products/items',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeItem
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
storeItem.url = (options?: RouteQueryOptions) => {
    return storeItem.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeItem
 * @see app/Http/Controllers/MasterData/ProductController.php:81
 * @route '/master-data/products/items'
 */
storeItem.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeItem.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeBom
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
export const storeBom = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeBom.url(options),
    method: 'post',
});

storeBom.definition = {
    methods: ['post'],
    url: '/master-data/products/bom',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeBom
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
storeBom.url = (options?: RouteQueryOptions) => {
    return storeBom.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::storeBom
 * @see app/Http/Controllers/MasterData/ProductController.php:117
 * @route '/master-data/products/bom'
 */
storeBom.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBom.url(options),
    method: 'post',
});
const ProductController = { index, storeItem, storeBom };

export default ProductController;

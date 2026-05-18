import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\SupplierController::index
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/suppliers',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\SupplierController::index
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\SupplierController::index
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\SupplierController::index
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const SupplierController = { index };

export default SupplierController;

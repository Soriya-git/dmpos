import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\CustomerController::index
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/customers',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\CustomerController::index
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\CustomerController::index
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\CustomerController::index
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const CustomerController = { index };

export default CustomerController;

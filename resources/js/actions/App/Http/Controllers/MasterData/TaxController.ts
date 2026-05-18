import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\TaxController::index
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/taxes',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\TaxController::index
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\TaxController::index
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\TaxController::index
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const TaxController = { index };

export default TaxController;

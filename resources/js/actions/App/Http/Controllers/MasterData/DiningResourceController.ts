import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::index
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/seats',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::index
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::index
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::index
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const DiningResourceController = { index };

export default DiningResourceController;

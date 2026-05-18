import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::index
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/pos-terminals',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::index
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::index
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::index
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const PosTerminalController = { index };

export default PosTerminalController;

import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::index
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/exchange-rates',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::index
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::index
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::index
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});
const ExchangeRateController = { index };

export default ExchangeRateController;

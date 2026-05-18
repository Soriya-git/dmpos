import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
import prices from './prices';
/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::store
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:92
 * @route '/master-data/menu-price-lists'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/menu-price-lists',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::store
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:92
 * @route '/master-data/menu-price-lists'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::store
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:92
 * @route '/master-data/menu-price-lists'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::update
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:134
 * @route '/master-data/menu-price-lists/{priceList}'
 */
export const update = (
    args:
        | { priceList: number | { id: number } }
        | [priceList: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

update.definition = {
    methods: ['patch'],
    url: '/master-data/menu-price-lists/{priceList}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::update
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:134
 * @route '/master-data/menu-price-lists/{priceList}'
 */
update.url = (
    args:
        | { priceList: number | { id: number } }
        | [priceList: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { priceList: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { priceList: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            priceList: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        priceList:
            typeof args.priceList === 'object'
                ? args.priceList.id
                : args.priceList,
    };

    return (
        update.definition.url
            .replace('{priceList}', parsedArgs.priceList.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::update
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:134
 * @route '/master-data/menu-price-lists/{priceList}'
 */
update.patch = (
    args:
        | { priceList: number | { id: number } }
        | [priceList: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});
const menuPriceLists = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    prices: Object.assign(prices, prices),
};

export default menuPriceLists;

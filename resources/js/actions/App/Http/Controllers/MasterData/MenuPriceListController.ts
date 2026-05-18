import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::index
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/menu-price-lists',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::index
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::index
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::index
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

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

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::storePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:187
 * @route '/master-data/menu-price-lists/prices'
 */
export const storePrice = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storePrice.url(options),
    method: 'post',
});

storePrice.definition = {
    methods: ['post'],
    url: '/master-data/menu-price-lists/prices',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::storePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:187
 * @route '/master-data/menu-price-lists/prices'
 */
storePrice.url = (options?: RouteQueryOptions) => {
    return storePrice.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::storePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:187
 * @route '/master-data/menu-price-lists/prices'
 */
storePrice.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storePrice.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::updatePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:230
 * @route '/master-data/menu-price-lists/prices/{price}'
 */
export const updatePrice = (
    args:
        | { price: number | { id: number } }
        | [price: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updatePrice.url(args, options),
    method: 'patch',
});

updatePrice.definition = {
    methods: ['patch'],
    url: '/master-data/menu-price-lists/prices/{price}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::updatePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:230
 * @route '/master-data/menu-price-lists/prices/{price}'
 */
updatePrice.url = (
    args:
        | { price: number | { id: number } }
        | [price: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { price: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { price: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            price: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        price: typeof args.price === 'object' ? args.price.id : args.price,
    };

    return (
        updatePrice.definition.url
            .replace('{price}', parsedArgs.price.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::updatePrice
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:230
 * @route '/master-data/menu-price-lists/prices/{price}'
 */
updatePrice.patch = (
    args:
        | { price: number | { id: number } }
        | [price: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updatePrice.url(args, options),
    method: 'patch',
});
const MenuPriceListController = {
    index,
    store,
    update,
    storePrice,
    updatePrice,
};

export default MenuPriceListController;

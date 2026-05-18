import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\MenuController::index
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/menu',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::index
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::index
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\MenuController::index
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
export const storeMenu = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeMenu.url(options),
    method: 'post',
});

storeMenu.definition = {
    methods: ['post'],
    url: '/master-data/menu/menus',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
storeMenu.url = (options?: RouteQueryOptions) => {
    return storeMenu.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
storeMenu.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeMenu.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::updateMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
export const updateMenu = (
    args:
        | { menu: number | { id: number } }
        | [menu: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateMenu.url(args, options),
    method: 'patch',
});

updateMenu.definition = {
    methods: ['patch'],
    url: '/master-data/menu/menus/{menu}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::updateMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
updateMenu.url = (
    args:
        | { menu: number | { id: number } }
        | [menu: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { menu: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { menu: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            menu: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        menu: typeof args.menu === 'object' ? args.menu.id : args.menu,
    };

    return (
        updateMenu.definition.url
            .replace('{menu}', parsedArgs.menu.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::updateMenu
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
updateMenu.patch = (
    args:
        | { menu: number | { id: number } }
        | [menu: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateMenu.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeCategory
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
export const storeCategory = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeCategory.url(options),
    method: 'post',
});

storeCategory.definition = {
    methods: ['post'],
    url: '/master-data/menu/categories',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeCategory
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
storeCategory.url = (options?: RouteQueryOptions) => {
    return storeCategory.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storeCategory
 * @see app/Http/Controllers/MasterData/MenuController.php:213
 * @route '/master-data/menu/categories'
 */
storeCategory.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeCategory.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storePrice
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
export const storePrice = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storePrice.url(options),
    method: 'post',
});

storePrice.definition = {
    methods: ['post'],
    url: '/master-data/menu/prices',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storePrice
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
storePrice.url = (options?: RouteQueryOptions) => {
    return storePrice.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::storePrice
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
storePrice.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storePrice.url(options),
    method: 'post',
});
const MenuController = {
    index,
    storeMenu,
    updateMenu,
    storeCategory,
    storePrice,
};

export default MenuController;

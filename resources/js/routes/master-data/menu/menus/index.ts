import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/menu/menus',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:141
 * @route '/master-data/menu/menus'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::update
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
export const update = (
    args:
        | { menu: number | { id: number } }
        | [menu: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

update.definition = {
    methods: ['patch'],
    url: '/master-data/menu/menus/{menu}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::update
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
update.url = (
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
        update.definition.url
            .replace('{menu}', parsedArgs.menu.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::update
 * @see app/Http/Controllers/MasterData/MenuController.php:194
 * @route '/master-data/menu/menus/{menu}'
 */
update.patch = (
    args:
        | { menu: number | { id: number } }
        | [menu: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});
const menus = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
};

export default menus;

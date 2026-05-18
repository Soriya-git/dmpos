import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/master-data/menu/prices',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::store
 * @see app/Http/Controllers/MasterData/MenuController.php:234
 * @route '/master-data/menu/prices'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});
const prices = {
    store: Object.assign(store, store),
};

export default prices;

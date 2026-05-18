import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
export const remember = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: remember.url(options),
    method: 'get',
});

remember.definition = {
    methods: ['get', 'head'],
    url: '/features/state/remember',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.url = (options?: RouteQueryOptions) => {
    return remember.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: remember.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\StateController::remember
 * @see app/Http/Controllers/Feature/StateController.php:11
 * @route '/features/state/remember'
 */
remember.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: remember.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
export const flashData = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: flashData.url(options),
    method: 'get',
});

flashData.definition = {
    methods: ['get', 'head'],
    url: '/features/state/flash-data',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.url = (options?: RouteQueryOptions) => {
    return flashData.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: flashData.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\StateController::flashData
 * @see app/Http/Controllers/Feature/StateController.php:16
 * @route '/features/state/flash-data'
 */
flashData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: flashData.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashData
 * @see app/Http/Controllers/Feature/StateController.php:21
 * @route '/features/state/flash-data'
 */
export const storeFlashData = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashData.url(options),
    method: 'post',
});

storeFlashData.definition = {
    methods: ['post'],
    url: '/features/state/flash-data',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashData
 * @see app/Http/Controllers/Feature/StateController.php:21
 * @route '/features/state/flash-data'
 */
storeFlashData.url = (options?: RouteQueryOptions) => {
    return storeFlashData.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashData
 * @see app/Http/Controllers/Feature/StateController.php:21
 * @route '/features/state/flash-data'
 */
storeFlashData.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashData.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataError
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
export const storeFlashDataError = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashDataError.url(options),
    method: 'post',
});

storeFlashDataError.definition = {
    methods: ['post'],
    url: '/features/state/flash-data/error',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataError
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
storeFlashDataError.url = (options?: RouteQueryOptions) => {
    return storeFlashDataError.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataError
 * @see app/Http/Controllers/Feature/StateController.php:28
 * @route '/features/state/flash-data/error'
 */
storeFlashDataError.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashDataError.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataWarning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
export const storeFlashDataWarning = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashDataWarning.url(options),
    method: 'post',
});

storeFlashDataWarning.definition = {
    methods: ['post'],
    url: '/features/state/flash-data/warning',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataWarning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
storeFlashDataWarning.url = (options?: RouteQueryOptions) => {
    return storeFlashDataWarning.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::storeFlashDataWarning
 * @see app/Http/Controllers/Feature/StateController.php:35
 * @route '/features/state/flash-data/warning'
 */
storeFlashDataWarning.post = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: storeFlashDataWarning.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
export const sharedProps = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: sharedProps.url(options),
    method: 'get',
});

sharedProps.definition = {
    methods: ['get', 'head'],
    url: '/features/state/shared-props',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.url = (options?: RouteQueryOptions) => {
    return sharedProps.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sharedProps.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\StateController::sharedProps
 * @see app/Http/Controllers/Feature/StateController.php:42
 * @route '/features/state/shared-props'
 */
sharedProps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sharedProps.url(options),
    method: 'head',
});
const StateController = {
    remember,
    flashData,
    storeFlashData,
    storeFlashDataError,
    storeFlashDataWarning,
    sharedProps,
};

export default StateController;

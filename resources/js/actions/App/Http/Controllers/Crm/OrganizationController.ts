import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Crm\OrganizationController::index
 * @see app/Http/Controllers/Crm/OrganizationController.php:16
 * @route '/organizations'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/organizations',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::index
 * @see app/Http/Controllers/Crm/OrganizationController.php:16
 * @route '/organizations'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::index
 * @see app/Http/Controllers/Crm/OrganizationController.php:16
 * @route '/organizations'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Crm\OrganizationController::index
 * @see app/Http/Controllers/Crm/OrganizationController.php:16
 * @route '/organizations'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::show
 * @see app/Http/Controllers/Crm/OrganizationController.php:32
 * @route '/organizations/{organization}'
 */
export const show = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/organizations/{organization}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::show
 * @see app/Http/Controllers/Crm/OrganizationController.php:32
 * @route '/organizations/{organization}'
 */
show.url = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { organization: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            organization: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        organization:
            typeof args.organization === 'object'
                ? args.organization.id
                : args.organization,
    };

    return (
        show.definition.url
            .replace('{organization}', parsedArgs.organization.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::show
 * @see app/Http/Controllers/Crm/OrganizationController.php:32
 * @route '/organizations/{organization}'
 */
show.get = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Crm\OrganizationController::show
 * @see app/Http/Controllers/Crm/OrganizationController.php:32
 * @route '/organizations/{organization}'
 */
show.head = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::update
 * @see app/Http/Controllers/Crm/OrganizationController.php:48
 * @route '/organizations/{organization}'
 */
export const update = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

update.definition = {
    methods: ['put', 'patch'],
    url: '/organizations/{organization}',
} satisfies RouteDefinition<['put', 'patch']>;

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::update
 * @see app/Http/Controllers/Crm/OrganizationController.php:48
 * @route '/organizations/{organization}'
 */
update.url = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { organization: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { organization: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            organization: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        organization:
            typeof args.organization === 'object'
                ? args.organization.id
                : args.organization,
    };

    return (
        update.definition.url
            .replace('{organization}', parsedArgs.organization.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Crm\OrganizationController::update
 * @see app/Http/Controllers/Crm/OrganizationController.php:48
 * @route '/organizations/{organization}'
 */
update.put = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});
/**
 * @see \App\Http\Controllers\Crm\OrganizationController::update
 * @see app/Http/Controllers/Crm/OrganizationController.php:48
 * @route '/organizations/{organization}'
 */
update.patch = (
    args:
        | { organization: number | { id: number } }
        | [organization: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});
const OrganizationController = { index, show, update };

export default OrganizationController;

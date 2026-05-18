import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::index
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/master-data/company-branches',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::index
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::index
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::index
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::updateBranch
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
export const updateBranch = (
    args:
        | { branch: number | { id: number } }
        | [branch: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: updateBranch.url(args, options),
    method: 'post',
});

updateBranch.definition = {
    methods: ['post'],
    url: '/master-data/company-branches/branches/{branch}',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::updateBranch
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
updateBranch.url = (
    args:
        | { branch: number | { id: number } }
        | [branch: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { branch: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { branch: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            branch: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        branch: typeof args.branch === 'object' ? args.branch.id : args.branch,
    };

    return (
        updateBranch.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::updateBranch
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
updateBranch.post = (
    args:
        | { branch: number | { id: number } }
        | [branch: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: updateBranch.url(args, options),
    method: 'post',
});
const CompanyBranchController = { index, updateBranch };

export default CompanyBranchController;

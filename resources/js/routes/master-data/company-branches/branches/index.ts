import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::update
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
export const update = (
    args:
        | { branch: number | { id: number } }
        | [branch: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
});

update.definition = {
    methods: ['post'],
    url: '/master-data/company-branches/branches/{branch}',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::update
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
update.url = (
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
        update.definition.url
            .replace('{branch}', parsedArgs.branch.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::update
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:62
 * @route '/master-data/company-branches/branches/{branch}'
 */
update.post = (
    args:
        | { branch: number | { id: number } }
        | [branch: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
});
const branches = {
    update: Object.assign(update, update),
};

export default branches;

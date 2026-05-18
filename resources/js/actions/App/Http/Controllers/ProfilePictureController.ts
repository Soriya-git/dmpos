import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\ProfilePictureController::update
 * @see app/Http/Controllers/ProfilePictureController.php:11
 * @route '/profile/picture'
 */
export const update = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
});

update.definition = {
    methods: ['post'],
    url: '/profile/picture',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\ProfilePictureController::update
 * @see app/Http/Controllers/ProfilePictureController.php:11
 * @route '/profile/picture'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\ProfilePictureController::update
 * @see app/Http/Controllers/ProfilePictureController.php:11
 * @route '/profile/picture'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
});
const ProfilePictureController = { update };

export default ProfilePictureController;

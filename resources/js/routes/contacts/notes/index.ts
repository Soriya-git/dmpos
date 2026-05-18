import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
/**
 * @see \App\Http\Controllers\Crm\NoteController::store
 * @see app/Http/Controllers/Crm/NoteController.php:12
 * @route '/contacts/{contact}/notes'
 */
export const store = (
    args:
        | { contact: number | { id: number } }
        | [contact: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/contacts/{contact}/notes',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Crm\NoteController::store
 * @see app/Http/Controllers/Crm/NoteController.php:12
 * @route '/contacts/{contact}/notes'
 */
store.url = (
    args:
        | { contact: number | { id: number } }
        | [contact: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { contact: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { contact: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            contact: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        contact:
            typeof args.contact === 'object' ? args.contact.id : args.contact,
    };

    return (
        store.definition.url
            .replace('{contact}', parsedArgs.contact.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Crm\NoteController::store
 * @see app/Http/Controllers/Crm/NoteController.php:12
 * @route '/contacts/{contact}/notes'
 */
store.post = (
    args:
        | { contact: number | { id: number } }
        | [contact: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
});
const notes = {
    store: Object.assign(store, store),
};

export default notes;

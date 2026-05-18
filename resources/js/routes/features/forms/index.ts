import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
import validationFd8f7e from './validation';
/**
 * @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
export const useForm = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: useForm.url(options),
    method: 'get',
});

useForm.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/use-form',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.url = (options?: RouteQueryOptions) => {
    return useForm.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useForm.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: useForm.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
export const formComponent = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: formComponent.url(options),
    method: 'get',
});

formComponent.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/form-component',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.url = (options?: RouteQueryOptions) => {
    return formComponent.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: formComponent.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: formComponent.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
export const fileUploads = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: fileUploads.url(options),
    method: 'get',
});

fileUploads.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/file-uploads',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.url = (options?: RouteQueryOptions) => {
    return fileUploads.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fileUploads.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fileUploads.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
export const validation = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: validation.url(options),
    method: 'get',
});

validation.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/validation',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.url = (options?: RouteQueryOptions) => {
    return validation.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: validation.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: validation.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
export const precognition = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: precognition.url(options),
    method: 'get',
});

precognition.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/precognition',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.url = (options?: RouteQueryOptions) => {
    return precognition.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: precognition.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: precognition.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
export const optimisticUpdates = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: optimisticUpdates.url(options),
    method: 'get',
});

optimisticUpdates.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/optimistic-updates',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.url = (options?: RouteQueryOptions) => {
    return optimisticUpdates.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: optimisticUpdates.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: optimisticUpdates.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticToggle
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
export const optimisticToggle = (
    args:
        | { contact: number | { id: number } }
        | [contact: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: optimisticToggle.url(args, options),
    method: 'post',
});

optimisticToggle.definition = {
    methods: ['post'],
    url: '/features/forms/optimistic-toggle/{contact}',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticToggle
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
optimisticToggle.url = (
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
        optimisticToggle.definition.url
            .replace('{contact}', parsedArgs.contact.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Feature\FormController::optimisticToggle
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
optimisticToggle.post = (
    args:
        | { contact: number | { id: number } }
        | [contact: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: optimisticToggle.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
export const useFormContext = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: useFormContext.url(options),
    method: 'get',
});

useFormContext.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/use-form-context',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.url = (options?: RouteQueryOptions) => {
    return useFormContext.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useFormContext.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: useFormContext.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
export const dottedKeys = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: dottedKeys.url(options),
    method: 'get',
});

dottedKeys.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/dotted-keys',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.url = (options?: RouteQueryOptions) => {
    return dottedKeys.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dottedKeys.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dottedKeys.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
export const wayfinder = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: wayfinder.url(options),
    method: 'get',
});

wayfinder.definition = {
    methods: ['get', 'head'],
    url: '/features/forms/wayfinder',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.url = (options?: RouteQueryOptions) => {
    return wayfinder.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wayfinder.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wayfinder.url(options),
    method: 'head',
});
const forms = {
    useForm: Object.assign(useForm, useForm),
    formComponent: Object.assign(formComponent, formComponent),
    fileUploads: Object.assign(fileUploads, fileUploads),
    validation: Object.assign(validation, validationFd8f7e),
    precognition: Object.assign(precognition, precognition),
    optimisticUpdates: Object.assign(optimisticUpdates, optimisticUpdates),
    optimisticToggle: Object.assign(optimisticToggle, optimisticToggle),
    useFormContext: Object.assign(useFormContext, useFormContext),
    dottedKeys: Object.assign(dottedKeys, dottedKeys),
    wayfinder: Object.assign(wayfinder, wayfinder),
};

export default forms;

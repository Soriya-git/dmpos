import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
export const useForm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useForm.url(options),
    method: 'get',
})

useForm.definition = {
    methods: ["get","head"],
    url: '/features/forms/use-form',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.url = (options?: RouteQueryOptions) => {
    return useForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useForm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::useForm
 * @see app/Http/Controllers/Feature/FormController.php:22
 * @route '/features/forms/use-form'
 */
useForm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: useForm.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitUseForm
 * @see app/Http/Controllers/Feature/FormController.php:27
 * @route '/features/forms/use-form'
 */
export const submitUseForm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitUseForm.url(options),
    method: 'post',
})

submitUseForm.definition = {
    methods: ["post"],
    url: '/features/forms/use-form',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitUseForm
 * @see app/Http/Controllers/Feature/FormController.php:27
 * @route '/features/forms/use-form'
 */
submitUseForm.url = (options?: RouteQueryOptions) => {
    return submitUseForm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitUseForm
 * @see app/Http/Controllers/Feature/FormController.php:27
 * @route '/features/forms/use-form'
 */
submitUseForm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitUseForm.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
export const formComponent = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: formComponent.url(options),
    method: 'get',
})

formComponent.definition = {
    methods: ["get","head"],
    url: '/features/forms/form-component',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.url = (options?: RouteQueryOptions) => {
    return formComponent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: formComponent.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::formComponent
 * @see app/Http/Controllers/Feature/FormController.php:32
 * @route '/features/forms/form-component'
 */
formComponent.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: formComponent.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitFormComponent
 * @see app/Http/Controllers/Feature/FormController.php:37
 * @route '/features/forms/form-component'
 */
export const submitFormComponent = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitFormComponent.url(options),
    method: 'post',
})

submitFormComponent.definition = {
    methods: ["post"],
    url: '/features/forms/form-component',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitFormComponent
 * @see app/Http/Controllers/Feature/FormController.php:37
 * @route '/features/forms/form-component'
 */
submitFormComponent.url = (options?: RouteQueryOptions) => {
    return submitFormComponent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitFormComponent
 * @see app/Http/Controllers/Feature/FormController.php:37
 * @route '/features/forms/form-component'
 */
submitFormComponent.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitFormComponent.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
export const fileUploads = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fileUploads.url(options),
    method: 'get',
})

fileUploads.definition = {
    methods: ["get","head"],
    url: '/features/forms/file-uploads',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.url = (options?: RouteQueryOptions) => {
    return fileUploads.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fileUploads.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::fileUploads
 * @see app/Http/Controllers/Feature/FormController.php:42
 * @route '/features/forms/file-uploads'
 */
fileUploads.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fileUploads.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitFileUploads
 * @see app/Http/Controllers/Feature/FormController.php:47
 * @route '/features/forms/file-uploads'
 */
export const submitFileUploads = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitFileUploads.url(options),
    method: 'post',
})

submitFileUploads.definition = {
    methods: ["post"],
    url: '/features/forms/file-uploads',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitFileUploads
 * @see app/Http/Controllers/Feature/FormController.php:47
 * @route '/features/forms/file-uploads'
 */
submitFileUploads.url = (options?: RouteQueryOptions) => {
    return submitFileUploads.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitFileUploads
 * @see app/Http/Controllers/Feature/FormController.php:47
 * @route '/features/forms/file-uploads'
 */
submitFileUploads.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitFileUploads.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
export const validation = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: validation.url(options),
    method: 'get',
})

validation.definition = {
    methods: ["get","head"],
    url: '/features/forms/validation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.url = (options?: RouteQueryOptions) => {
    return validation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: validation.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::validation
 * @see app/Http/Controllers/Feature/FormController.php:56
 * @route '/features/forms/validation'
 */
validation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: validation.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidation
 * @see app/Http/Controllers/Feature/FormController.php:61
 * @route '/features/forms/validation'
 */
export const submitValidation = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitValidation.url(options),
    method: 'post',
})

submitValidation.definition = {
    methods: ["post"],
    url: '/features/forms/validation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidation
 * @see app/Http/Controllers/Feature/FormController.php:61
 * @route '/features/forms/validation'
 */
submitValidation.url = (options?: RouteQueryOptions) => {
    return submitValidation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidation
 * @see app/Http/Controllers/Feature/FormController.php:61
 * @route '/features/forms/validation'
 */
submitValidation.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitValidation.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidationSecondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
export const submitValidationSecondary = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitValidationSecondary.url(options),
    method: 'post',
})

submitValidationSecondary.definition = {
    methods: ["post"],
    url: '/features/forms/validation/secondary',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidationSecondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
submitValidationSecondary.url = (options?: RouteQueryOptions) => {
    return submitValidationSecondary.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitValidationSecondary
 * @see app/Http/Controllers/Feature/FormController.php:66
 * @route '/features/forms/validation/secondary'
 */
submitValidationSecondary.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitValidationSecondary.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
export const precognition = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: precognition.url(options),
    method: 'get',
})

precognition.definition = {
    methods: ["get","head"],
    url: '/features/forms/precognition',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.url = (options?: RouteQueryOptions) => {
    return precognition.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: precognition.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::precognition
 * @see app/Http/Controllers/Feature/FormController.php:71
 * @route '/features/forms/precognition'
 */
precognition.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: precognition.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::storeAccount
 * @see app/Http/Controllers/Feature/FormController.php:76
 * @route '/features/forms/precognition'
 */
export const storeAccount = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeAccount.url(options),
    method: 'post',
})

storeAccount.definition = {
    methods: ["post"],
    url: '/features/forms/precognition',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::storeAccount
 * @see app/Http/Controllers/Feature/FormController.php:76
 * @route '/features/forms/precognition'
 */
storeAccount.url = (options?: RouteQueryOptions) => {
    return storeAccount.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::storeAccount
 * @see app/Http/Controllers/Feature/FormController.php:76
 * @route '/features/forms/precognition'
 */
storeAccount.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeAccount.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
export const optimisticUpdates = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: optimisticUpdates.url(options),
    method: 'get',
})

optimisticUpdates.definition = {
    methods: ["get","head"],
    url: '/features/forms/optimistic-updates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.url = (options?: RouteQueryOptions) => {
    return optimisticUpdates.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: optimisticUpdates.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::optimisticUpdates
 * @see app/Http/Controllers/Feature/FormController.php:81
 * @route '/features/forms/optimistic-updates'
 */
optimisticUpdates.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: optimisticUpdates.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::toggleFavorite
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
export const toggleFavorite = (args: { contact: number | { id: number } } | [contact: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleFavorite.url(args, options),
    method: 'post',
})

toggleFavorite.definition = {
    methods: ["post"],
    url: '/features/forms/optimistic-toggle/{contact}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::toggleFavorite
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
toggleFavorite.url = (args: { contact: number | { id: number } } | [contact: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { contact: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { contact: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    contact: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        contact: typeof args.contact === 'object'
                ? args.contact.id
                : args.contact,
                }

    return toggleFavorite.definition.url
            .replace('{contact}', parsedArgs.contact.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::toggleFavorite
 * @see app/Http/Controllers/Feature/FormController.php:116
 * @route '/features/forms/optimistic-toggle/{contact}'
 */
toggleFavorite.post = (args: { contact: number | { id: number } } | [contact: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleFavorite.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
export const useFormContext = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useFormContext.url(options),
    method: 'get',
})

useFormContext.definition = {
    methods: ["get","head"],
    url: '/features/forms/use-form-context',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.url = (options?: RouteQueryOptions) => {
    return useFormContext.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: useFormContext.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::useFormContext
 * @see app/Http/Controllers/Feature/FormController.php:90
 * @route '/features/forms/use-form-context'
 */
useFormContext.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: useFormContext.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
export const dottedKeys = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dottedKeys.url(options),
    method: 'get',
})

dottedKeys.definition = {
    methods: ["get","head"],
    url: '/features/forms/dotted-keys',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.url = (options?: RouteQueryOptions) => {
    return dottedKeys.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dottedKeys.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::dottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:95
 * @route '/features/forms/dotted-keys'
 */
dottedKeys.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dottedKeys.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Feature\FormController::submitDottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:100
 * @route '/features/forms/dotted-keys'
 */
export const submitDottedKeys = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitDottedKeys.url(options),
    method: 'post',
})

submitDottedKeys.definition = {
    methods: ["post"],
    url: '/features/forms/dotted-keys',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Feature\FormController::submitDottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:100
 * @route '/features/forms/dotted-keys'
 */
submitDottedKeys.url = (options?: RouteQueryOptions) => {
    return submitDottedKeys.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::submitDottedKeys
 * @see app/Http/Controllers/Feature/FormController.php:100
 * @route '/features/forms/dotted-keys'
 */
submitDottedKeys.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submitDottedKeys.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
export const wayfinder = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wayfinder.url(options),
    method: 'get',
})

wayfinder.definition = {
    methods: ["get","head"],
    url: '/features/forms/wayfinder',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.url = (options?: RouteQueryOptions) => {
    return wayfinder.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wayfinder.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Feature\FormController::wayfinder
 * @see app/Http/Controllers/Feature/FormController.php:109
 * @route '/features/forms/wayfinder'
 */
wayfinder.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wayfinder.url(options),
    method: 'head',
})
const FormController = { useForm, submitUseForm, formComponent, submitFormComponent, fileUploads, submitFileUploads, validation, submitValidation, submitValidationSecondary, precognition, storeAccount, optimisticUpdates, toggleFavorite, useFormContext, dottedKeys, submitDottedKeys, wayfinder }

export default FormController
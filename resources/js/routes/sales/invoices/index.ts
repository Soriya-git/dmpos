import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::receive
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:72
 * @route '/sales/invoices/{invoice}/receive'
 */
export const receive = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(args, options),
    method: 'post',
})

receive.definition = {
    methods: ["post"],
    url: '/sales/invoices/{invoice}/receive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::receive
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:72
 * @route '/sales/invoices/{invoice}/receive'
 */
receive.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return receive.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::receive
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:72
 * @route '/sales/invoices/{invoice}/receive'
 */
receive.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(args, options),
    method: 'post',
})
const invoices = {
    receive: Object.assign(receive, receive),
}

export default invoices
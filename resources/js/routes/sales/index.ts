import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
import invoices from './invoices'
/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::index
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:17
 * @route '/sales'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/sales',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::index
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:17
 * @route '/sales'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::index
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:17
 * @route '/sales'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Sales\SaleInvoiceController::index
 * @see app/Http/Controllers/Sales/SaleInvoiceController.php:17
 * @route '/sales'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const sales = {
    index: Object.assign(index, index),
invoices: Object.assign(invoices, invoices),
}

export default sales
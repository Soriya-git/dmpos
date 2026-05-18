import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
export const preview = (
    args:
        | {
              diningSession: number | { id: number };
              invoice: number | { id: number };
              documentType: string | number;
          }
        | [
              diningSession: number | { id: number },
              invoice: number | { id: number },
              documentType: string | number,
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
});

preview.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/invoices/{invoice}/print/{documentType}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
preview.url = (
    args:
        | {
              diningSession: number | { id: number };
              invoice: number | { id: number };
              documentType: string | number;
          }
        | [
              diningSession: number | { id: number },
              invoice: number | { id: number },
              documentType: string | number,
          ],
    options?: RouteQueryOptions,
) => {
    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
            invoice: args[1],
            documentType: args[2],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
        invoice:
            typeof args.invoice === 'object' ? args.invoice.id : args.invoice,
        documentType: args.documentType,
    };

    return (
        preview.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace('{documentType}', parsedArgs.documentType.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
preview.get = (
    args:
        | {
              diningSession: number | { id: number };
              invoice: number | { id: number };
              documentType: string | number;
          }
        | [
              diningSession: number | { id: number },
              invoice: number | { id: number },
              documentType: string | number,
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::preview
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
preview.head = (
    args:
        | {
              diningSession: number | { id: number };
              invoice: number | { id: number };
              documentType: string | number;
          }
        | [
              diningSession: number | { id: number },
              invoice: number | { id: number },
              documentType: string | number,
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
});
const invoices = {
    preview: Object.assign(preview, preview),
};

export default invoices;

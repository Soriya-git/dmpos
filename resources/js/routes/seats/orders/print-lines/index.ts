import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancel
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
export const cancel = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

cancel.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/print-lines/{orderLine}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancel
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
cancel.url = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
) => {
    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
            orderLine: args[1],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
        orderLine:
            typeof args.orderLine === 'object'
                ? args.orderLine.id
                : args.orderLine,
    };

    return (
        cancel.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancel
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
cancel.patch = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: cancel.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnMethod
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
export const returnMethod = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: returnMethod.url(args, options),
    method: 'patch',
});

returnMethod.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/print-lines/{orderLine}/return',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnMethod
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
returnMethod.url = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
) => {
    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
            orderLine: args[1],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
        orderLine:
            typeof args.orderLine === 'object'
                ? args.orderLine.id
                : args.orderLine,
    };

    return (
        returnMethod.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnMethod
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
returnMethod.patch = (
    args:
        | {
              diningSession: number | { id: number };
              orderLine: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              orderLine: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: returnMethod.url(args, options),
    method: 'patch',
});
const printLines = {
    cancel: Object.assign(cancel, cancel),
    return: Object.assign(returnMethod, returnMethod),
};

export default printLines;

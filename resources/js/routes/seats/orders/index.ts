import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../wayfinder';
import currentInvoice from './current-invoice';
import customer from './customer';
import invoices from './invoices';
import items from './items';
import manageF1b31d from './manage';
import printJobs from './print-jobs';
import printLines from './print-lines';
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:33
 * @route '/orders/{diningSession}/menu'
 */
export const show = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/menu',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:33
 * @route '/orders/{diningSession}/menu'
 */
show.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        show.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:33
 * @route '/orders/{diningSession}/menu'
 */
show.get = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::show
 * @see app/Http/Controllers/Seats/SeatOrderController.php:33
 * @route '/orders/{diningSession}/menu'
 */
show.head = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::manage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:222
 * @route '/orders/{diningSession}/manage'
 */
export const manage = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: manage.url(args, options),
    method: 'get',
});

manage.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/manage',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::manage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:222
 * @route '/orders/{diningSession}/manage'
 */
manage.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        manage.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::manage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:222
 * @route '/orders/{diningSession}/manage'
 */
manage.get = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: manage.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::manage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:222
 * @route '/orders/{diningSession}/manage'
 */
manage.head = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: manage.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
export const sendKitchen = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: sendKitchen.url(args, options),
    method: 'post',
});

sendKitchen.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/send-kitchen',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendKitchen.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        sendKitchen.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendKitchen.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: sendKitchen.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:742
 * @route '/orders/{diningSession}/settle'
 */
export const settle = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: settle.url(args, options),
    method: 'post',
});

settle.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/settle',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:742
 * @route '/orders/{diningSession}/settle'
 */
settle.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        settle.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::settle
 * @see app/Http/Controllers/Seats/SeatOrderController.php:742
 * @route '/orders/{diningSession}/settle'
 */
settle.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: settle.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::close
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
export const close = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
});

close.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/close',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::close
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
close.url = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { diningSession: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { diningSession: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
    };

    return (
        close.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::close
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
close.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: close.url(args, options),
    method: 'post',
});
const orders = {
    show: Object.assign(show, show),
    manage: Object.assign(manage, manageF1b31d),
    items: Object.assign(items, items),
    sendKitchen: Object.assign(sendKitchen, sendKitchen),
    printJobs: Object.assign(printJobs, printJobs),
    printLines: Object.assign(printLines, printLines),
    currentInvoice: Object.assign(currentInvoice, currentInvoice),
    invoices: Object.assign(invoices, invoices),
    settle: Object.assign(settle, settle),
    customer: Object.assign(customer, customer),
    close: Object.assign(close, close),
};

export default orders;

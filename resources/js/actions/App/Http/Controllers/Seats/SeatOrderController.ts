import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
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
 * @see \App\Http\Controllers\Seats\SeatOrderController::saveManage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
export const saveManage = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: saveManage.url(args, options),
    method: 'post',
});

saveManage.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/manage',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::saveManage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
saveManage.url = (
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
        saveManage.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::saveManage
 * @see app/Http/Controllers/Seats/SeatOrderController.php:281
 * @route '/orders/{diningSession}/manage'
 */
saveManage.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: saveManage.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:167
 * @route '/orders/{diningSession}/items'
 */
export const addItem = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: addItem.url(args, options),
    method: 'post',
});

addItem.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:167
 * @route '/orders/{diningSession}/items'
 */
addItem.url = (
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
        addItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::addItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:167
 * @route '/orders/{diningSession}/items'
 */
addItem.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: addItem.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:347
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const updateItem = (
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
    url: updateItem.url(args, options),
    method: 'patch',
});

updateItem.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:347
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
updateItem.url = (
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
        updateItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:347
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
updateItem.patch = (
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
    url: updateItem.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:377
 * @route '/orders/{diningSession}/items'
 */
export const clearItems = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: clearItems.url(args, options),
    method: 'delete',
});

clearItems.definition = {
    methods: ['delete'],
    url: '/orders/{diningSession}/items',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:377
 * @route '/orders/{diningSession}/items'
 */
clearItems.url = (
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
        clearItems.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::clearItems
 * @see app/Http/Controllers/Seats/SeatOrderController.php:377
 * @route '/orders/{diningSession}/items'
 */
clearItems.delete = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: clearItems.url(args, options),
    method: 'delete',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:368
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
export const removeItem = (
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
): RouteDefinition<'delete'> => ({
    url: removeItem.url(args, options),
    method: 'delete',
});

removeItem.definition = {
    methods: ['delete'],
    url: '/orders/{diningSession}/items/{orderLine}',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:368
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
removeItem.url = (
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
        removeItem.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::removeItem
 * @see app/Http/Controllers/Seats/SeatOrderController.php:368
 * @route '/orders/{diningSession}/items/{orderLine}'
 */
removeItem.delete = (
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
): RouteDefinition<'delete'> => ({
    url: removeItem.url(args, options),
    method: 'delete',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
export const sendToKitchen = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: sendToKitchen.url(args, options),
    method: 'post',
});

sendToKitchen.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/send-kitchen',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendToKitchen.url = (
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
        sendToKitchen.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::sendToKitchen
 * @see app/Http/Controllers/Seats/SeatOrderController.php:386
 * @route '/orders/{diningSession}/send-kitchen'
 */
sendToKitchen.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: sendToKitchen.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::reprint
 * @see app/Http/Controllers/Seats/SeatOrderController.php:422
 * @route '/orders/{diningSession}/print-jobs/{printJob}/reprint'
 */
export const reprint = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: reprint.url(args, options),
    method: 'post',
});

reprint.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/print-jobs/{printJob}/reprint',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::reprint
 * @see app/Http/Controllers/Seats/SeatOrderController.php:422
 * @route '/orders/{diningSession}/print-jobs/{printJob}/reprint'
 */
reprint.url = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
) => {
    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
            printJob: args[1],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
        printJob:
            typeof args.printJob === 'object'
                ? args.printJob.id
                : args.printJob,
    };

    return (
        reprint.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{printJob}', parsedArgs.printJob.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::reprint
 * @see app/Http/Controllers/Seats/SeatOrderController.php:422
 * @route '/orders/{diningSession}/print-jobs/{printJob}/reprint'
 */
reprint.post = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: reprint.url(args, options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewPrintJob
 * @see app/Http/Controllers/Seats/SeatOrderController.php:455
 * @route '/orders/{diningSession}/print-jobs/{printJob}/preview'
 */
export const previewPrintJob = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: previewPrintJob.url(args, options),
    method: 'get',
});

previewPrintJob.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/print-jobs/{printJob}/preview',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewPrintJob
 * @see app/Http/Controllers/Seats/SeatOrderController.php:455
 * @route '/orders/{diningSession}/print-jobs/{printJob}/preview'
 */
previewPrintJob.url = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
) => {
    if (Array.isArray(args)) {
        args = {
            diningSession: args[0],
            printJob: args[1],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        diningSession:
            typeof args.diningSession === 'object'
                ? args.diningSession.id
                : args.diningSession,
        printJob:
            typeof args.printJob === 'object'
                ? args.printJob.id
                : args.printJob,
    };

    return (
        previewPrintJob.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{printJob}', parsedArgs.printJob.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewPrintJob
 * @see app/Http/Controllers/Seats/SeatOrderController.php:455
 * @route '/orders/{diningSession}/print-jobs/{printJob}/preview'
 */
previewPrintJob.get = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: previewPrintJob.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewPrintJob
 * @see app/Http/Controllers/Seats/SeatOrderController.php:455
 * @route '/orders/{diningSession}/print-jobs/{printJob}/preview'
 */
previewPrintJob.head = (
    args:
        | {
              diningSession: number | { id: number };
              printJob: number | { id: number };
          }
        | [
              diningSession: number | { id: number },
              printJob: number | { id: number },
          ],
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: previewPrintJob.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancelPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
export const cancelPrintedLine = (
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
    url: cancelPrintedLine.url(args, options),
    method: 'patch',
});

cancelPrintedLine.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/print-lines/{orderLine}/cancel',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancelPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
cancelPrintedLine.url = (
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
        cancelPrintedLine.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::cancelPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:620
 * @route '/orders/{diningSession}/print-lines/{orderLine}/cancel'
 */
cancelPrintedLine.patch = (
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
    url: cancelPrintedLine.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
export const returnPrintedLine = (
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
    url: returnPrintedLine.url(args, options),
    method: 'patch',
});

returnPrintedLine.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/print-lines/{orderLine}/return',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
returnPrintedLine.url = (
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
        returnPrintedLine.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{orderLine}', parsedArgs.orderLine.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::returnPrintedLine
 * @see app/Http/Controllers/Seats/SeatOrderController.php:667
 * @route '/orders/{diningSession}/print-lines/{orderLine}/return'
 */
returnPrintedLine.patch = (
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
    url: returnPrintedLine.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewCurrentInvoice
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
export const previewCurrentInvoice = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: previewCurrentInvoice.url(args, options),
    method: 'get',
});

previewCurrentInvoice.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/print/current-invoice',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewCurrentInvoice
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
previewCurrentInvoice.url = (
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
        previewCurrentInvoice.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewCurrentInvoice
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
previewCurrentInvoice.get = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: previewCurrentInvoice.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewCurrentInvoice
 * @see app/Http/Controllers/Seats/SeatOrderController.php:505
 * @route '/orders/{diningSession}/print/current-invoice'
 */
previewCurrentInvoice.head = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: previewCurrentInvoice.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewInvoiceDocument
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
export const previewInvoiceDocument = (
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
    url: previewInvoiceDocument.url(args, options),
    method: 'get',
});

previewInvoiceDocument.definition = {
    methods: ['get', 'head'],
    url: '/orders/{diningSession}/invoices/{invoice}/print/{documentType}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewInvoiceDocument
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
previewInvoiceDocument.url = (
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
        previewInvoiceDocument.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace('{documentType}', parsedArgs.documentType.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewInvoiceDocument
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
previewInvoiceDocument.get = (
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
    url: previewInvoiceDocument.url(args, options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::previewInvoiceDocument
 * @see app/Http/Controllers/Seats/SeatOrderController.php:566
 * @route '/orders/{diningSession}/invoices/{invoice}/print/{documentType}'
 */
previewInvoiceDocument.head = (
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
    url: previewInvoiceDocument.url(args, options),
    method: 'head',
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
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:927
 * @route '/orders/{diningSession}/customer'
 */
export const updateCustomer = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateCustomer.url(args, options),
    method: 'patch',
});

updateCustomer.definition = {
    methods: ['patch'],
    url: '/orders/{diningSession}/customer',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:927
 * @route '/orders/{diningSession}/customer'
 */
updateCustomer.url = (
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
        updateCustomer.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::updateCustomer
 * @see app/Http/Controllers/Seats/SeatOrderController.php:927
 * @route '/orders/{diningSession}/customer'
 */
updateCustomer.patch = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: updateCustomer.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
export const closeOrder = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: closeOrder.url(args, options),
    method: 'post',
});

closeOrder.definition = {
    methods: ['post'],
    url: '/orders/{diningSession}/close',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
closeOrder.url = (
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
        closeOrder.definition.url
            .replace('{diningSession}', parsedArgs.diningSession.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\Seats\SeatOrderController::closeOrder
 * @see app/Http/Controllers/Seats/SeatOrderController.php:973
 * @route '/orders/{diningSession}/close'
 */
closeOrder.post = (
    args:
        | { diningSession: number | { id: number } }
        | [diningSession: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: closeOrder.url(args, options),
    method: 'post',
});
const SeatOrderController = {
    show,
    manage,
    saveManage,
    addItem,
    updateItem,
    clearItems,
    removeItem,
    sendToKitchen,
    reprint,
    previewPrintJob,
    cancelPrintedLine,
    returnPrintedLine,
    previewCurrentInvoice,
    previewInvoiceDocument,
    settle,
    updateCustomer,
    closeOrder,
};

export default SeatOrderController;

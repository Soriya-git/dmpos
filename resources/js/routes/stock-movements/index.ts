import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../wayfinder';
import internalTransfer9293ce from './internal-transfer';
import stockAdjustmentsD02821 from './stock-adjustments';
import stockSettlements5fc36f from './stock-settlements';
import writeOffFe7a51 from './write-off';
/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::internalTransfer
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
export const internalTransfer = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: internalTransfer.url(options),
    method: 'get',
});

internalTransfer.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/internal-transfer',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::internalTransfer
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
internalTransfer.url = (options?: RouteQueryOptions) => {
    return internalTransfer.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::internalTransfer
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
internalTransfer.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: internalTransfer.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\InternalTransferController::internalTransfer
 * @see app/Http/Controllers/StockMovements/InternalTransferController.php:28
 * @route '/stock-movements/internal-transfer'
 */
internalTransfer.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: internalTransfer.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::stockAdjustments
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:26
 * @route '/stock-movements/stock-adjustments'
 */
export const stockAdjustments = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: stockAdjustments.url(options),
    method: 'get',
});

stockAdjustments.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/stock-adjustments',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::stockAdjustments
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:26
 * @route '/stock-movements/stock-adjustments'
 */
stockAdjustments.url = (options?: RouteQueryOptions) => {
    return stockAdjustments.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::stockAdjustments
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:26
 * @route '/stock-movements/stock-adjustments'
 */
stockAdjustments.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: stockAdjustments.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockAdjustmentController::stockAdjustments
 * @see app/Http/Controllers/StockMovements/StockAdjustmentController.php:26
 * @route '/stock-movements/stock-adjustments'
 */
stockAdjustments.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: stockAdjustments.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::stockSettlements
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
export const stockSettlements = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: stockSettlements.url(options),
    method: 'get',
});

stockSettlements.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/stock-settlements',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::stockSettlements
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
stockSettlements.url = (options?: RouteQueryOptions) => {
    return stockSettlements.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::stockSettlements
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
stockSettlements.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: stockSettlements.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockSettlementController::stockSettlements
 * @see app/Http/Controllers/StockMovements/StockSettlementController.php:24
 * @route '/stock-movements/stock-settlements'
 */
stockSettlements.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: stockSettlements.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::writeOff
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
export const writeOff = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: writeOff.url(options),
    method: 'get',
});

writeOff.definition = {
    methods: ['get', 'head'],
    url: '/stock-movements/write-off',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::writeOff
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
writeOff.url = (options?: RouteQueryOptions) => {
    return writeOff.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::writeOff
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
writeOff.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: writeOff.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\StockMovements\StockWriteOffController::writeOff
 * @see app/Http/Controllers/StockMovements/StockWriteOffController.php:25
 * @route '/stock-movements/write-off'
 */
writeOff.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: writeOff.url(options),
    method: 'head',
});
const stockMovements = {
    internalTransfer: Object.assign(internalTransfer, internalTransfer9293ce),
    stockAdjustments: Object.assign(stockAdjustments, stockAdjustmentsD02821),
    stockSettlements: Object.assign(stockSettlements, stockSettlements5fc36f),
    writeOff: Object.assign(writeOff, writeOffFe7a51),
};

export default stockMovements;

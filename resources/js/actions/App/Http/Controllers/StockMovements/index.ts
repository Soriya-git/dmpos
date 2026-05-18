import InternalTransferController from './InternalTransferController';
import StockAdjustmentController from './StockAdjustmentController';
import StockSettlementController from './StockSettlementController';
import StockWriteOffController from './StockWriteOffController';
const StockMovements = {
    InternalTransferController: Object.assign(
        InternalTransferController,
        InternalTransferController,
    ),
    StockAdjustmentController: Object.assign(
        StockAdjustmentController,
        StockAdjustmentController,
    ),
    StockSettlementController: Object.assign(
        StockSettlementController,
        StockSettlementController,
    ),
    StockWriteOffController: Object.assign(
        StockWriteOffController,
        StockWriteOffController,
    ),
};

export default StockMovements;

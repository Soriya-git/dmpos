import CompanyBranchController from './CompanyBranchController';
import CustomerController from './CustomerController';
import DiningResourceController from './DiningResourceController';
import ExchangeRateController from './ExchangeRateController';
import MenuController from './MenuController';
import MenuPriceListController from './MenuPriceListController';
import PosTerminalController from './PosTerminalController';
import ProductController from './ProductController';
import SupplierController from './SupplierController';
import TaxController from './TaxController';
import WarehouseLocationController from './WarehouseLocationController';
const MasterData = {
    ProductController: Object.assign(ProductController, ProductController),
    CompanyBranchController: Object.assign(
        CompanyBranchController,
        CompanyBranchController,
    ),
    CustomerController: Object.assign(CustomerController, CustomerController),
    ExchangeRateController: Object.assign(
        ExchangeRateController,
        ExchangeRateController,
    ),
    MenuController: Object.assign(MenuController, MenuController),
    MenuPriceListController: Object.assign(
        MenuPriceListController,
        MenuPriceListController,
    ),
    PosTerminalController: Object.assign(
        PosTerminalController,
        PosTerminalController,
    ),
    DiningResourceController: Object.assign(
        DiningResourceController,
        DiningResourceController,
    ),
    SupplierController: Object.assign(SupplierController, SupplierController),
    TaxController: Object.assign(TaxController, TaxController),
    WarehouseLocationController: Object.assign(
        WarehouseLocationController,
        WarehouseLocationController,
    ),
};

export default MasterData;

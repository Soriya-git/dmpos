import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
} from './../../wayfinder';
import companyBranches68109d from './company-branches';
import menu232ff9 from './menu';
import menuPriceListsAde053 from './menu-price-lists';
import products237d17 from './products';
/**
 * @see \App\Http\Controllers\MasterData\ProductController::products
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
export const products = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
});

products.definition = {
    methods: ['get', 'head'],
    url: '/master-data/products',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\ProductController::products
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
products.url = (options?: RouteQueryOptions) => {
    return products.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ProductController::products
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
products.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: products.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\ProductController::products
 * @see app/Http/Controllers/MasterData/ProductController.php:21
 * @route '/master-data/products'
 */
products.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: products.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::companyBranches
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
export const companyBranches = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: companyBranches.url(options),
    method: 'get',
});

companyBranches.definition = {
    methods: ['get', 'head'],
    url: '/master-data/company-branches',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::companyBranches
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
companyBranches.url = (options?: RouteQueryOptions) => {
    return companyBranches.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::companyBranches
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
companyBranches.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: companyBranches.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\CompanyBranchController::companyBranches
 * @see app/Http/Controllers/MasterData/CompanyBranchController.php:15
 * @route '/master-data/company-branches'
 */
companyBranches.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: companyBranches.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\CustomerController::customers
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
export const customers = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
});

customers.definition = {
    methods: ['get', 'head'],
    url: '/master-data/customers',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\CustomerController::customers
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
customers.url = (options?: RouteQueryOptions) => {
    return customers.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\CustomerController::customers
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
customers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\CustomerController::customers
 * @see app/Http/Controllers/MasterData/CustomerController.php:13
 * @route '/master-data/customers'
 */
customers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customers.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::exchangeRates
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
export const exchangeRates = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: exchangeRates.url(options),
    method: 'get',
});

exchangeRates.definition = {
    methods: ['get', 'head'],
    url: '/master-data/exchange-rates',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::exchangeRates
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
exchangeRates.url = (options?: RouteQueryOptions) => {
    return exchangeRates.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::exchangeRates
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
exchangeRates.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exchangeRates.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\ExchangeRateController::exchangeRates
 * @see app/Http/Controllers/MasterData/ExchangeRateController.php:12
 * @route '/master-data/exchange-rates'
 */
exchangeRates.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: exchangeRates.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuController::menu
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
export const menu = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: menu.url(options),
    method: 'get',
});

menu.definition = {
    methods: ['get', 'head'],
    url: '/master-data/menu',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuController::menu
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
menu.url = (options?: RouteQueryOptions) => {
    return menu.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuController::menu
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
menu.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: menu.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\MenuController::menu
 * @see app/Http/Controllers/MasterData/MenuController.php:21
 * @route '/master-data/menu'
 */
menu.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: menu.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::menuPriceLists
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
export const menuPriceLists = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: menuPriceLists.url(options),
    method: 'get',
});

menuPriceLists.definition = {
    methods: ['get', 'head'],
    url: '/master-data/menu-price-lists',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::menuPriceLists
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
menuPriceLists.url = (options?: RouteQueryOptions) => {
    return menuPriceLists.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::menuPriceLists
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
menuPriceLists.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: menuPriceLists.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\MenuPriceListController::menuPriceLists
 * @see app/Http/Controllers/MasterData/MenuPriceListController.php:18
 * @route '/master-data/menu-price-lists'
 */
menuPriceLists.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: menuPriceLists.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::posTerminals
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
export const posTerminals = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: posTerminals.url(options),
    method: 'get',
});

posTerminals.definition = {
    methods: ['get', 'head'],
    url: '/master-data/pos-terminals',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::posTerminals
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
posTerminals.url = (options?: RouteQueryOptions) => {
    return posTerminals.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::posTerminals
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
posTerminals.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: posTerminals.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\PosTerminalController::posTerminals
 * @see app/Http/Controllers/MasterData/PosTerminalController.php:13
 * @route '/master-data/pos-terminals'
 */
posTerminals.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: posTerminals.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::seats
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
export const seats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seats.url(options),
    method: 'get',
});

seats.definition = {
    methods: ['get', 'head'],
    url: '/master-data/seats',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::seats
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
seats.url = (options?: RouteQueryOptions) => {
    return seats.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::seats
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
seats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: seats.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\DiningResourceController::seats
 * @see app/Http/Controllers/MasterData/DiningResourceController.php:13
 * @route '/master-data/seats'
 */
seats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: seats.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\SupplierController::suppliers
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
export const suppliers = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: suppliers.url(options),
    method: 'get',
});

suppliers.definition = {
    methods: ['get', 'head'],
    url: '/master-data/suppliers',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\SupplierController::suppliers
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
suppliers.url = (options?: RouteQueryOptions) => {
    return suppliers.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\SupplierController::suppliers
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
suppliers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliers.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\SupplierController::suppliers
 * @see app/Http/Controllers/MasterData/SupplierController.php:12
 * @route '/master-data/suppliers'
 */
suppliers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: suppliers.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\TaxController::taxes
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
export const taxes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: taxes.url(options),
    method: 'get',
});

taxes.definition = {
    methods: ['get', 'head'],
    url: '/master-data/taxes',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\TaxController::taxes
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
taxes.url = (options?: RouteQueryOptions) => {
    return taxes.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\TaxController::taxes
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
taxes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: taxes.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\TaxController::taxes
 * @see app/Http/Controllers/MasterData/TaxController.php:12
 * @route '/master-data/taxes'
 */
taxes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: taxes.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\MasterData\WarehouseLocationController::warehouseLocations
 * @see app/Http/Controllers/MasterData/WarehouseLocationController.php:13
 * @route '/master-data/warehouse-locations'
 */
export const warehouseLocations = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: warehouseLocations.url(options),
    method: 'get',
});

warehouseLocations.definition = {
    methods: ['get', 'head'],
    url: '/master-data/warehouse-locations',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\MasterData\WarehouseLocationController::warehouseLocations
 * @see app/Http/Controllers/MasterData/WarehouseLocationController.php:13
 * @route '/master-data/warehouse-locations'
 */
warehouseLocations.url = (options?: RouteQueryOptions) => {
    return warehouseLocations.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\MasterData\WarehouseLocationController::warehouseLocations
 * @see app/Http/Controllers/MasterData/WarehouseLocationController.php:13
 * @route '/master-data/warehouse-locations'
 */
warehouseLocations.get = (
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: warehouseLocations.url(options),
    method: 'get',
});
/**
 * @see \App\Http\Controllers\MasterData\WarehouseLocationController::warehouseLocations
 * @see app/Http/Controllers/MasterData/WarehouseLocationController.php:13
 * @route '/master-data/warehouse-locations'
 */
warehouseLocations.head = (
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: warehouseLocations.url(options),
    method: 'head',
});
const masterData = {
    products: Object.assign(products, products237d17),
    companyBranches: Object.assign(companyBranches, companyBranches68109d),
    customers: Object.assign(customers, customers),
    exchangeRates: Object.assign(exchangeRates, exchangeRates),
    menu: Object.assign(menu, menu232ff9),
    menuPriceLists: Object.assign(menuPriceLists, menuPriceListsAde053),
    posTerminals: Object.assign(posTerminals, posTerminals),
    seats: Object.assign(seats, seats),
    suppliers: Object.assign(suppliers, suppliers),
    taxes: Object.assign(taxes, taxes),
    warehouseLocations: Object.assign(warehouseLocations, warehouseLocations),
};

export default masterData;

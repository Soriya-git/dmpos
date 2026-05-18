import categories from './categories';
import menus from './menus';
import prices from './prices';
const menu = {
    menus: Object.assign(menus, menus),
    categories: Object.assign(categories, categories),
    prices: Object.assign(prices, prices),
};

export default menu;

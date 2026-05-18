import bom from './bom';
import items from './items';
const products = {
    items: Object.assign(items, items),
    bom: Object.assign(bom, bom),
};

export default products;

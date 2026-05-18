import BalanceOnHand from './BalanceOnHand';
import Crm from './Crm';
import Feature from './Feature';
import GoodsReceipt from './GoodsReceipt';
import MasterData from './MasterData';
import POS from './POS';
import ProfilePictureController from './ProfilePictureController';
import Purchase from './Purchase';
import Putaway from './Putaway';
import Sales from './Sales';
import Seats from './Seats';
import StockMovements from './StockMovements';
const Controllers = {
    Crm: Object.assign(Crm, Crm),
    ProfilePictureController: Object.assign(
        ProfilePictureController,
        ProfilePictureController,
    ),
    POS: Object.assign(POS, POS),
    Sales: Object.assign(Sales, Sales),
    Purchase: Object.assign(Purchase, Purchase),
    GoodsReceipt: Object.assign(GoodsReceipt, GoodsReceipt),
    Putaway: Object.assign(Putaway, Putaway),
    BalanceOnHand: Object.assign(BalanceOnHand, BalanceOnHand),
    StockMovements: Object.assign(StockMovements, StockMovements),
    MasterData: Object.assign(MasterData, MasterData),
    Seats: Object.assign(Seats, Seats),
    Feature: Object.assign(Feature, Feature),
};

export default Controllers;

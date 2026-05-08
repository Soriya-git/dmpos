import Crm from './Crm'
import POS from './POS'
import Sales from './Sales'
import Seats from './Seats'
import Feature from './Feature'
const Controllers = {
    Crm: Object.assign(Crm, Crm),
POS: Object.assign(POS, POS),
Sales: Object.assign(Sales, Sales),
Seats: Object.assign(Seats, Seats),
Feature: Object.assign(Feature, Feature),
}

export default Controllers
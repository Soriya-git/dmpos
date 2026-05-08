import SeatController from './SeatController'
import SeatOrderController from './SeatOrderController'
const Seats = {
    SeatController: Object.assign(SeatController, SeatController),
SeatOrderController: Object.assign(SeatOrderController, SeatOrderController),
}

export default Seats
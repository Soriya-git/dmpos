import FormController from './FormController'
import NavigationController from './NavigationController'
import DataLoadingController from './DataLoadingController'
import PrefetchingController from './PrefetchingController'
import StateController from './StateController'
import LayoutController from './LayoutController'
import EventController from './EventController'
import NetworkErrorController from './NetworkErrorController'
import HttpController from './HttpController'
const Feature = {
    FormController: Object.assign(FormController, FormController),
NavigationController: Object.assign(NavigationController, NavigationController),
DataLoadingController: Object.assign(DataLoadingController, DataLoadingController),
PrefetchingController: Object.assign(PrefetchingController, PrefetchingController),
StateController: Object.assign(StateController, StateController),
LayoutController: Object.assign(LayoutController, LayoutController),
EventController: Object.assign(EventController, EventController),
NetworkErrorController: Object.assign(NetworkErrorController, NetworkErrorController),
HttpController: Object.assign(HttpController, HttpController),
}

export default Feature
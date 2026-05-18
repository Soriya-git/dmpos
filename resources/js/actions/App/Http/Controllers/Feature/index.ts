import DataLoadingController from './DataLoadingController';
import EventController from './EventController';
import FormController from './FormController';
import HttpController from './HttpController';
import LayoutController from './LayoutController';
import NavigationController from './NavigationController';
import NetworkErrorController from './NetworkErrorController';
import PrefetchingController from './PrefetchingController';
import StateController from './StateController';
const Feature = {
    FormController: Object.assign(FormController, FormController),
    NavigationController: Object.assign(
        NavigationController,
        NavigationController,
    ),
    DataLoadingController: Object.assign(
        DataLoadingController,
        DataLoadingController,
    ),
    PrefetchingController: Object.assign(
        PrefetchingController,
        PrefetchingController,
    ),
    StateController: Object.assign(StateController, StateController),
    LayoutController: Object.assign(LayoutController, LayoutController),
    EventController: Object.assign(EventController, EventController),
    NetworkErrorController: Object.assign(
        NetworkErrorController,
        NetworkErrorController,
    ),
    HttpController: Object.assign(HttpController, HttpController),
};

export default Feature;

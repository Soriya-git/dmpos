import dataLoading from './data-loading';
import errors from './errors';
import events from './events';
import forms from './forms';
import http from './http';
import layouts from './layouts';
import navigation from './navigation';
import prefetching from './prefetching';
import state from './state';
const features = {
    forms: Object.assign(forms, forms),
    navigation: Object.assign(navigation, navigation),
    dataLoading: Object.assign(dataLoading, dataLoading),
    prefetching: Object.assign(prefetching, prefetching),
    state: Object.assign(state, state),
    layouts: Object.assign(layouts, layouts),
    events: Object.assign(events, events),
    errors: Object.assign(errors, errors),
    http: Object.assign(http, http),
};

export default features;

import forms from './forms'
import navigation from './navigation'
import dataLoading from './data-loading'
import prefetching from './prefetching'
import state from './state'
import layouts from './layouts'
import events from './events'
import errors from './errors'
import http from './http'
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
}

export default features
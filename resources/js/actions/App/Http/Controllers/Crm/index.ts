import DashboardController from './DashboardController'
import ContactController from './ContactController'
import OrganizationController from './OrganizationController'
import NoteController from './NoteController'
const Crm = {
    DashboardController: Object.assign(DashboardController, DashboardController),
ContactController: Object.assign(ContactController, ContactController),
OrganizationController: Object.assign(OrganizationController, OrganizationController),
NoteController: Object.assign(NoteController, NoteController),
}

export default Crm
import ContactController from './ContactController';
import DashboardController from './DashboardController';
import NoteController from './NoteController';
import OrganizationController from './OrganizationController';
const Crm = {
    DashboardController: Object.assign(
        DashboardController,
        DashboardController,
    ),
    ContactController: Object.assign(ContactController, ContactController),
    OrganizationController: Object.assign(
        OrganizationController,
        OrganizationController,
    ),
    NoteController: Object.assign(NoteController, NoteController),
};

export default Crm;

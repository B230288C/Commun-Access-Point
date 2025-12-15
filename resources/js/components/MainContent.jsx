import AppointmentCalendar from './AppointmentCalendar';
import AppointmentsPage from './AppointmentsPage';

const MainContent = ({ activeView }) => {
    const renderContent = () => {
        switch (activeView) {
            case 'dashboard':
                return <AppointmentCalendar />;
            case 'appointments':
                return <AppointmentsPage />;
            default:
                return <AppointmentCalendar />;
        }
    };

    return (
        <div className="main-content-container w-full h-full">
            {/* Dynamic Content Area */}
            <div className="content-area">
                {renderContent()}
            </div>
        </div>
    );
};

export default MainContent;

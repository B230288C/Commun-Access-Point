import { createRoot } from 'react-dom/client';
import '@fortawesome/fontawesome-free/css/all.min.css';
import CustomerBookingPage from './components/CustomerBookingPage';
import '../css/app.css';
import '../css/booking.css';

function BookingApp() {
    const container = document.getElementById('booking-app');
    const staffId = container?.dataset?.staffId;

    if (!staffId) {
        return (
            <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100vh',
                fontSize: '16px',
                color: '#6D6D6D'
            }}>
                Invalid booking link
            </div>
        );
    }

    return <CustomerBookingPage staffId={staffId} />;
}

createRoot(document.getElementById('booking-app')).render(<BookingApp />);

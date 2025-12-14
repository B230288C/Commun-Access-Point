import { useState, useEffect } from 'react';
import BookingModal from './BookingModal';

export default function CustomerBookingPage({ staffId }) {
    const [staffData, setStaffData] = useState(null);
    const [slotsByDate, setSlotsByDate] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedSlot, setSelectedSlot] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        const fetchAvailability = async () => {
            try {
                setLoading(true);
                setError(null);

                const response = await fetch(`/api/public/staff/${staffId}/availability`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    if (response.status === 404) {
                        throw new Error('Staff member not found');
                    }
                    throw new Error('Failed to load availability');
                }

                const data = await response.json();
                setStaffData(data.staff);
                setSlotsByDate(data.slots_by_date);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        if (staffId) {
            fetchAvailability();
        }
    }, [staffId]);

    // Format time to 12-hour format (e.g., "9:00 AM")
    const formatTime = (timeString) => {
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    };

    // Format date for display (e.g., "Monday, December 15, 2025")
    const formatDate = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    // Format date for card header (e.g., "Mon, Dec 15")
    const formatDateShort = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
        });
    };

    // Handle slot selection - opens the booking modal
    const handleSlotClick = (slot, date) => {
        setSelectedSlot({ ...slot, date });
        setIsModalOpen(true);
    };

    // Handle modal close
    const handleModalClose = () => {
        setIsModalOpen(false);
        setSelectedSlot(null);
    };

    // Handle successful booking - remove the booked slot from the list
    const handleBookingSuccess = () => {
        if (selectedSlot) {
            const date = selectedSlot.date;
            setSlotsByDate(prev => {
                const updatedSlots = { ...prev };
                if (updatedSlots[date]) {
                    updatedSlots[date] = updatedSlots[date].filter(
                        slot => slot.id !== selectedSlot.id
                    );
                    // Remove the date if no slots left
                    if (updatedSlots[date].length === 0) {
                        delete updatedSlots[date];
                    }
                }
                return updatedSlots;
            });
        }
    };

    // Get sorted dates
    const sortedDates = Object.keys(slotsByDate).sort();

    if (loading) {
        return (
            <div className="booking-page">
                <div className="booking-container">
                    <div className="booking-loading">
                        <div className="loading-spinner"></div>
                        <p>Loading available times...</p>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="booking-page">
                <div className="booking-container">
                    <div className="booking-error">
                        <i className="fas fa-exclamation-circle"></i>
                        <h2>Unable to Load</h2>
                        <p>{error}</p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="booking-page">
            <div className="booking-container">
                {/* Header */}
                <header className="booking-header">
                    <h1 className="booking-title">Book an Appointment</h1>
                    {staffData && (
                        <div className="staff-info">
                            <div className="staff-avatar">
                                <i className="fas fa-user"></i>
                            </div>
                            <div className="staff-details">
                                <h2 className="staff-name">{staffData.name}</h2>
                                {staffData.position && (
                                    <p className="staff-position">{staffData.position}</p>
                                )}
                                {staffData.department && (
                                    <p className="staff-department">{staffData.department}</p>
                                )}
                            </div>
                        </div>
                    )}
                </header>

                {/* Availability Section */}
                <section className="booking-content">
                    <h3 className="section-title">
                        <i className="fas fa-calendar-alt"></i>
                        Available Times
                    </h3>

                    {sortedDates.length === 0 ? (
                        <div className="no-slots">
                            <i className="fas fa-calendar-times"></i>
                            <p>No available time slots at the moment.</p>
                            <p className="no-slots-hint">Please check back later.</p>
                        </div>
                    ) : (
                        <div className="date-cards">
                            {sortedDates.map((date) => (
                                <div key={date} className="date-card">
                                    <div className="date-card-header">
                                        <span className="date-day">{formatDateShort(date)}</span>
                                        <span className="date-full">{formatDate(date)}</span>
                                    </div>
                                    <div className="slots-grid">
                                        {slotsByDate[date].map((slot) => (
                                            <button
                                                key={slot.id}
                                                className={`slot-button ${selectedSlot?.id === slot.id ? 'slot-button-selected' : ''}`}
                                                onClick={() => handleSlotClick(slot, date)}
                                                title={`${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}`}
                                            >
                                                {formatTime(slot.start_time)}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                {/* Booking Modal */}
                <BookingModal
                    isOpen={isModalOpen}
                    onClose={handleModalClose}
                    slot={selectedSlot}
                    staffId={staffId}
                    staffName={staffData?.name}
                    onSuccess={handleBookingSuccess}
                />
            </div>
        </div>
    );
}

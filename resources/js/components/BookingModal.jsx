import { useState } from 'react';

export default function BookingModal({ isOpen, onClose, slot, staffId, staffName, onSuccess }) {
    const [formData, setFormData] = useState({
        visitor_name: '',
        student_name: '',
        phone_number: '',
        email: '',
        purpose: '',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    if (!isOpen || !slot) return null;

    // Format time to 12-hour format for display
    const formatTime = (timeString) => {
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    };

    // Format time to HH:mm for API (strips seconds if present)
    const formatTimeForApi = (timeString) => {
        const parts = timeString.split(':');
        return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
    };

    // Format date for display
    const formatDate = (dateString) => {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await fetch('/api/public/appointments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    ...formData,
                    availability_slot_id: slot.id,
                    staff_id: staffId,
                    date: slot.date,
                    start_time: formatTimeForApi(slot.start_time),
                    end_time: formatTimeForApi(slot.end_time),
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Failed to book appointment');
            }

            setSuccess(true);
            onSuccess?.(data.data);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        setFormData({
            visitor_name: '',
            student_name: '',
            phone_number: '',
            email: '',
            purpose: '',
        });
        setError('');
        setSuccess(false);
        onClose();
    };

    return (
        <>
            {/* Backdrop */}
            <div className="booking-modal-backdrop" onClick={handleClose} />

            {/* Modal */}
            <div className="booking-modal">
                <div className="booking-modal-header">
                    <h2 className="booking-modal-title">Book Appointment</h2>
                    <button
                        className="booking-modal-close"
                        onClick={handleClose}
                        aria-label="Close"
                        type="button"
                    >
                        <i className="fas fa-times"></i>
                    </button>
                </div>

                {success ? (
                    <div className="booking-modal-success">
                        <div className="success-icon">
                            <i className="fas fa-check-circle"></i>
                        </div>
                        <h3>Booked!</h3>
                        <p>Your appointment has been successfully scheduled.</p>
                        <div className="success-details">
                            <p><i className="fas fa-calendar"></i> {formatDate(slot.date)}</p>
                            <p><i className="fas fa-clock"></i> {formatTime(slot.start_time)} - {formatTime(slot.end_time)}</p>
                            <p><i className="fas fa-user"></i> {staffName}</p>
                        </div>
                        <button
                            type="button"
                            className="btn-primary btn-full"
                            onClick={handleClose}
                        >
                            Done
                        </button>
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="booking-modal-body">
                        {/* Date and Time Display */}
                        <div className="booking-slot-info">
                            <div className="slot-info-item">
                                <i className="fas fa-calendar"></i>
                                <span>{formatDate(slot.date)}</span>
                            </div>
                            <div className="slot-info-item">
                                <i className="fas fa-clock"></i>
                                <span>{formatTime(slot.start_time)} - {formatTime(slot.end_time)}</span>
                            </div>
                        </div>

                        {error && (
                            <div className="booking-modal-error">
                                <i className="fas fa-exclamation-circle"></i>
                                {error}
                            </div>
                        )}

                        {/* Visitor Name */}
                        <div className="form-group">
                            <label htmlFor="visitor_name" className="form-label">
                                Your Name <span className="text-required">*</span>
                            </label>
                            <input
                                id="visitor_name"
                                name="visitor_name"
                                type="text"
                                value={formData.visitor_name}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Enter your full name"
                                required
                                disabled={loading}
                            />
                        </div>

                        {/* Student Name */}
                        <div className="form-group">
                            <label htmlFor="student_name" className="form-label">
                                Student Name
                            </label>
                            <input
                                id="student_name"
                                name="student_name"
                                type="text"
                                value={formData.student_name}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Enter student's name (if applicable)"
                                disabled={loading}
                            />
                        </div>

                        {/* Phone Number */}
                        <div className="form-group">
                            <label htmlFor="phone_number" className="form-label">
                                Phone Number <span className="text-required">*</span>
                            </label>
                            <input
                                id="phone_number"
                                name="phone_number"
                                type="tel"
                                value={formData.phone_number}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Enter your phone number"
                                required
                                disabled={loading}
                            />
                        </div>

                        {/* Email */}
                        <div className="form-group">
                            <label htmlFor="email" className="form-label">
                                Email <span className="text-required">*</span>
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value={formData.email}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Enter your email address"
                                required
                                disabled={loading}
                            />
                        </div>

                        {/* Purpose */}
                        <div className="form-group">
                            <label htmlFor="purpose" className="form-label">
                                Purpose of Visit <span className="text-required">*</span>
                            </label>
                            <textarea
                                id="purpose"
                                name="purpose"
                                value={formData.purpose}
                                onChange={handleChange}
                                className="form-input form-textarea"
                                placeholder="Briefly describe the purpose of your visit"
                                rows="3"
                                required
                                disabled={loading}
                            />
                        </div>

                        {/* Actions */}
                        <div className="booking-modal-actions">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="btn-secondary"
                                disabled={loading}
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                className="btn-primary"
                                disabled={loading}
                            >
                                {loading ? 'Booking...' : 'Confirm Booking'}
                            </button>
                        </div>
                    </form>
                )}
            </div>
        </>
    );
}

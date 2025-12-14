import { useState, useEffect } from 'react';

export default function UpdateAppointmentModal({ isOpen, onClose, appointmentData, onSuccess, position = { x: 0, y: 0 } }) {
    const [formData, setFormData] = useState({
        visitor_name: '',
        student_name: '',
        phone_number: '',
        email: '',
        purpose: '',
        status: 'pending',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Update formData when appointmentData changes
    useEffect(() => {
        if (appointmentData?.appointment) {
            const apt = appointmentData.appointment;
            setFormData({
                visitor_name: apt.visitor_name || '',
                student_name: apt.student_name || '',
                phone_number: apt.phone_number || '',
                email: apt.email || '',
                purpose: apt.purpose || '',
                status: apt.status || 'pending',
            });
            setError('');
        }
    }, [appointmentData]);

    if (!isOpen || !appointmentData) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await fetch(`/api/appointments/${appointmentData.appointment.id}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Failed to update appointment');
            }

            onSuccess?.(data.data || data);
            onClose();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    // Get status color for visual indicator
    const getStatusColor = (status) => {
        switch (status) {
            case 'approved':
                return '#D1FAE5';
            case 'cancelled':
                return '#FEE2E2';
            case 'completed':
                return '#E0E7FF';
            case 'pending':
            default:
                return '#FEF3C7';
        }
    };

    // Get status label
    const getStatusLabel = (status) => {
        switch (status) {
            case 'approved':
                return 'Approved - Appointment confirmed';
            case 'cancelled':
                return 'Cancelled - Appointment cancelled';
            case 'completed':
                return 'Completed - Appointment finished';
            case 'pending':
            default:
                return 'Pending - Awaiting approval';
        }
    };

    return (
        <>
            {/* Backdrop */}
            <div
                className={`modal-backdrop ${isOpen ? 'modal-backdrop-open' : ''}`}
                onClick={onClose}
            />

            {/* Floating Panel */}
            <div
                className={`floating-panel ${isOpen ? 'floating-panel-open' : ''}`}
                style={{
                    left: `${position.x}px`,
                    top: `${position.y}px`,
                    width: '420px',
                }}
            >
                <div className="floating-panel-header">
                    <h2 className="floating-panel-title">Update Appointment</h2>
                    <button
                        className="floating-panel-close"
                        onClick={onClose}
                        aria-label="Close"
                        type="button"
                    >
                        <i className="fas fa-times"></i>
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="floating-panel-body">
                    {error && (
                        <div className="alert-error">
                            {error}
                        </div>
                    )}

                    {/* Visitor Name */}
                    <div className="form-group">
                        <label htmlFor="visitor_name" className="form-label">
                            Visitor Name <span className="text-required">*</span>
                        </label>
                        <input
                            id="visitor_name"
                            name="visitor_name"
                            type="text"
                            value={formData.visitor_name}
                            onChange={handleChange}
                            className="form-input"
                            placeholder="Enter visitor name"
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
                            placeholder="Enter student name (if applicable)"
                            disabled={loading}
                        />
                    </div>

                    {/* Phone and Email in row */}
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="phone_number" className="form-label">
                                Phone <span className="text-required">*</span>
                            </label>
                            <input
                                id="phone_number"
                                name="phone_number"
                                type="tel"
                                value={formData.phone_number}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Phone number"
                                required
                                disabled={loading}
                            />
                        </div>

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
                                placeholder="Email address"
                                required
                                disabled={loading}
                            />
                        </div>
                    </div>

                    {/* Purpose */}
                    <div className="form-group">
                        <label htmlFor="purpose" className="form-label">
                            Purpose <span className="text-required">*</span>
                        </label>
                        <textarea
                            id="purpose"
                            name="purpose"
                            value={formData.purpose}
                            onChange={handleChange}
                            className="form-input form-textarea"
                            placeholder="Purpose of visit"
                            rows="3"
                            required
                            disabled={loading}
                        />
                    </div>

                    {/* Status Dropdown */}
                    <div className="form-group">
                        <label htmlFor="status" className="form-label">
                            Status <span className="text-required">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            value={formData.status}
                            onChange={handleChange}
                            className="form-input"
                            disabled={loading}
                        >
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>

                        {/* Status indicator */}
                        <div
                            className="mt-2 p-2 rounded-lg border border-[#E0E0E0] flex items-center gap-2"
                            style={{ backgroundColor: getStatusColor(formData.status) }}
                        >
                            <div
                                className="w-3 h-3 rounded-full border border-[#1F1F1F]/20"
                                style={{ backgroundColor: getStatusColor(formData.status) }}
                            />
                            <span className="text-sm text-[#1F1F1F]">
                                {getStatusLabel(formData.status)}
                            </span>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="floating-panel-actions">
                        <button
                            type="button"
                            onClick={onClose}
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
                            {loading ? 'Updating...' : 'Update Appointment'}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

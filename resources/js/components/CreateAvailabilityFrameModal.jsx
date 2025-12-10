import { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';

export default function CreateAvailabilityFrameModal({ isOpen, onClose, initialData, onSuccess, position = { x: 0, y: 0 } }) {
    const { user } = useAuth();
    const [formData, setFormData] = useState({
        staff_id: user?.id || '',
        title: '',
        duration: 20,
        interval: 10,
        is_recurring: false,
        day: '',
        status: 'active',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Update formData when initialData changes
    useEffect(() => {
        if (initialData) {
            setFormData(prev => ({
                ...prev,
                ...initialData,
                staff_id: user?.id || '',
            }));
        }
    }, [initialData, user?.id]);

    if (!isOpen) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await fetch('/api/availability-frames', {
                method: 'POST',
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
                throw new Error(data.message || 'Failed to create availability frame');
            }

            onSuccess?.(data.data);
            onClose();
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));
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
                }}
            >
                <div className="floating-panel-header">
                    <h2 className="floating-panel-title">Create Availability Frame</h2>
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

                    {/* Date and Time Display (Read-only) */}
                    <div className="form-section">
                        <h3 className="form-section-title">Selected Time Slot</h3>
                        <div className="time-slot-display">
                            <div className="time-slot-item">
                                <i className="fas fa-calendar"></i>
                                <span>{formData.date} ({formData.day})</span>
                            </div>
                            <div className="time-slot-item">
                                <i className="fas fa-clock"></i>
                                <span>{formData.start_time} - {formData.end_time}</span>
                            </div>
                        </div>
                    </div>

                    {/* Title */}
                    <div className="form-group">
                        <label htmlFor="title" className="form-label">
                            Title <span className="text-required">*</span>
                        </label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            value={formData.title}
                            onChange={handleChange}
                            className="form-input"
                            placeholder="e.g., Morning Consultations"
                            required
                            disabled={loading}
                        />
                    </div>

                    {/* Duration and Interval */}
                    <div className="form-row">
                        <div className="form-group">
                            <label htmlFor="duration" className="form-label">
                                Duration (minutes) <span className="text-required">*</span>
                            </label>
                            <input
                                id="duration"
                                name="duration"
                                type="number"
                                value={formData.duration}
                                onChange={handleChange}
                                className="form-input"
                                min="5"
                                required
                                disabled={loading}
                            />
                            <p className="helper-text">Length of each appointment slot</p>
                        </div>

                        <div className="form-group">
                            <label htmlFor="interval" className="form-label">
                                Interval (minutes)
                            </label>
                            <input
                                id="interval"
                                name="interval"
                                type="number"
                                value={formData.interval}
                                onChange={handleChange}
                                className="form-input"
                                min="0"
                                disabled={loading}
                            />
                            <p className="helper-text">Gap between slots</p>
                        </div>
                    </div>

                    {/* Recurring Option */}
                    <div className="form-group">
                        <label className="form-checkbox">
                            <input
                                name="is_recurring"
                                type="checkbox"
                                checked={formData.is_recurring}
                                onChange={handleChange}
                                disabled={loading}
                            />
                            <span>Repeat weekly</span>
                        </label>
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
                            {loading ? 'Creating...' : 'Create Frame'}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

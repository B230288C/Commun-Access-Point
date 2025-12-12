import { useState, useEffect } from 'react';

export default function UpdateAvailabilitySlotModal({ isOpen, onClose, slotData, onSuccess, position = { x: 0, y: 0 } }) {
    const [formData, setFormData] = useState({
        status: 'available',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Update formData when slotData changes
    useEffect(() => {
        if (slotData) {
            setFormData({
                status: slotData.status || 'available',
            });
            setError('');
        }
    }, [slotData]);

    if (!isOpen || !slotData) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await fetch(`/api/availability-slots/${slotData.slotId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    status: formData.status,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Failed to update slot');
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
            case 'booked':
                return '#D1FAE5';
            case 'unavailable':
                return '#E5E7EB';
            case 'available':
            default:
                return '#FFEBB7';
        }
    };

    // Get status label
    const getStatusLabel = (status) => {
        switch (status) {
            case 'booked':
                return 'Booked - This slot has an appointment';
            case 'unavailable':
                return 'Unavailable - This slot cannot be booked';
            case 'available':
            default:
                return 'Available - Open for booking';
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
                    width: '360px',
                }}
            >
                <div className="floating-panel-header">
                    <h2 className="floating-panel-title">Update Slot</h2>
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

                    {/* Slot Info Display */}
                    <div className="form-section">
                        <div className="time-slot-display">
                            <div className="time-slot-item">
                                <i className="fas fa-clock"></i>
                                <span>{slotData.start_time} - {slotData.end_time}</span>
                            </div>
                            {slotData.frameTitle && (
                                <div className="time-slot-item">
                                    <i className="fas fa-calendar"></i>
                                    <span>{slotData.frameTitle}</span>
                                </div>
                            )}
                        </div>
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
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="unavailable">Unavailable</option>
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
                            {loading ? 'Updating...' : 'Update Slot'}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

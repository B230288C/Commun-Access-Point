import { useState, useEffect } from 'react';

export default function UpdateAvailabilityFrameModal({ isOpen, onClose, frameData, onSuccess, position = { x: 0, y: 0 } }) {
    const [formData, setFormData] = useState({
        title: '',
        is_recurring: false,
        status: 'active',
    });
    const [loading, setLoading] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const [error, setError] = useState('');

    // Update formData when frameData changes
    useEffect(() => {
        if (frameData) {
            setFormData({
                title: frameData.title || '',
                is_recurring: frameData.is_recurring || false,
                status: frameData.status || 'active',
            });
            setShowDeleteConfirm(false);
            setError('');
        }
    }, [frameData]);

    if (!isOpen || !frameData) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const response = await fetch(`/api/availability-frames/${frameData.id}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    ...frameData,
                    title: formData.title,
                    is_recurring: formData.is_recurring,
                    status: formData.status,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(errorMessages);
                }
                throw new Error(data.message || 'Failed to update availability frame');
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
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));
    };

    const handleDelete = async () => {
        setError('');
        setDeleting(true);

        try {
            const response = await fetch(`/api/availability-frames/${frameData.id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete availability frame');
            }

            onSuccess?.();
            onClose();
        } catch (err) {
            setError(err.message);
            setShowDeleteConfirm(false);
        } finally {
            setDeleting(false);
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
                }}
            >
                <div className="floating-panel-header">
                    <h2 className="floating-panel-title">Update Frame</h2>
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
                        <div className="time-slot-display">
                            <div className="time-slot-item">
                                <i className="fas fa-calendar"></i>
                                <span>{frameData.date} ({frameData.day})</span>
                            </div>
                            <div className="time-slot-item">
                                <i className="fas fa-clock"></i>
                                <span>{frameData.start_time} - {frameData.end_time}</span>
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
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <p className="helper-text">
                            {formData.status === 'inactive'
                                ? 'Inactive frames and their slots will appear dimmed and cannot be booked.'
                                : 'Active frames are available for booking.'
                            }
                        </p>
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
                        <p className="helper-text">
                            {frameData.is_recurring && !formData.is_recurring
                                ? 'Unchecking will cancel all future occurrences from this date. Past events will be kept.'
                                : formData.is_recurring
                                    ? `This frame repeats every week on ${frameData.day}`
                                    : `Enable to repeat this frame every week on ${frameData.day}`
                            }
                        </p>
                    </div>

                    {/* Delete Confirmation */}
                    {showDeleteConfirm && (
                        <div className="alert-warning" style={{ marginBottom: '1rem' }}>
                            <p style={{ marginBottom: '0.5rem', fontWeight: 500 }}>
                                Are you sure you want to delete this event?
                            </p>
                            <p style={{ fontSize: '0.875rem', color: '#6D6D6D', marginBottom: '0.75rem' }}>
                                This will only delete this single occurrence. Other recurring events will not be affected.
                            </p>
                            <div style={{ display: 'flex', gap: '0.5rem' }}>
                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    className="btn-danger"
                                    disabled={deleting}
                                    style={{ fontSize: '0.875rem', padding: '0.375rem 0.75rem' }}
                                >
                                    {deleting ? 'Deleting...' : 'Yes, Delete'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setShowDeleteConfirm(false)}
                                    className="btn-secondary"
                                    disabled={deleting}
                                    style={{ fontSize: '0.875rem', padding: '0.375rem 0.75rem' }}
                                >
                                    No, Keep It
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Actions */}
                    <div className="floating-panel-actions">
                        <button
                            type="button"
                            onClick={() => setShowDeleteConfirm(true)}
                            className="btn-danger-outline"
                            disabled={loading || deleting || showDeleteConfirm}
                        >
                            <i className="fas fa-trash-alt" style={{ marginRight: '0.375rem' }}></i>
                            Delete
                        </button>
                        <div style={{ display: 'flex', gap: '0.5rem', marginLeft: 'auto' }}>
                            <button
                                type="button"
                                onClick={onClose}
                                className="btn-secondary"
                                disabled={loading || deleting}
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                className="btn-primary"
                                disabled={loading || deleting}
                            >
                                {loading ? 'Updating...' : 'Update Frame'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
}

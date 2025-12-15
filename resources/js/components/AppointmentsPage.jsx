import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import AppointmentList from './AppointmentList';
import AppointmentDetail from './AppointmentDetail';
import UpdateAppointmentModal from './UpdateAppointmentModal';

const AppointmentsPage = () => {
    const { user } = useAuth();
    
    // --- State ---
    // Removed: appointments, loading (handled by list now)
    const [selectedAppointment, setSelectedAppointment] = useState(null);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [editAppointmentData, setEditAppointmentData] = useState(null);
    const [deleteConfirmation, setDeleteConfirmation] = useState({ show: false, appointment: null });
    
    // New: Trigger to force list reload after update/delete
    const [refreshTrigger, setRefreshTrigger] = useState(0);

    // Helper to refresh the list
    const triggerRefresh = () => {
        setRefreshTrigger(prev => prev + 1);
    };

    // Handle appointment selection
    const handleSelectAppointment = (appointment) => {
        setSelectedAppointment(appointment);
    };

    // Handle edit appointment
    const handleEditAppointment = (appointment) => {
        setEditAppointmentData({
            slotId: appointment.slot?.id,
            appointment: appointment,
        });
        setIsEditModalOpen(true);
    };

    // Handle delete appointment request
    const handleDeleteRequest = (appointment) => {
        setDeleteConfirmation({ show: true, appointment });
    };

    // Confirm delete
    const handleConfirmDelete = async () => {
        const appointment = deleteConfirmation.appointment;
        if (!appointment) return;

        try {
            const response = await fetch(`/api/appointments/${appointment.id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to delete appointment');
            }

            // Clear selection if deleted appointment was selected
            if (selectedAppointment?.id === appointment.id) {
                setSelectedAppointment(null);
            }

            // Refresh the list
            triggerRefresh();

        } catch (err) {
            console.error('Error deleting appointment:', err);
        } finally {
            setDeleteConfirmation({ show: false, appointment: null });
        }
    };

    // Handle successful appointment update
    const handleAppointmentUpdated = () => {
        triggerRefresh(); // Reload list
        // Optionally refetch selected appointment details if needed, 
        // but typically the list reload is enough.
        // If you need to update the detail view instantly without re-selecting:
        // You might want to fetch the single appointment details here.
    };

    // Close detail view
    const handleCloseDetail = () => {
        setSelectedAppointment(null);
    };

    return (
        <div className="appointments-page h-full flex flex-col p-6">
            {/* Page Header */}
            <div className="mb-6 flex-shrink-0">
                <h1 className="text-2xl font-bold text-[#1F1F1F]">Appointments</h1>
                <p className="text-sm text-[#6D6D6D] mt-1">
                    Manage and view all your appointments
                </p>
            </div>

            {/* Main Content */}
            <div className="flex gap-6 flex-1 min-h-0">
                {/* Left Panel - Appointments List */}
                <div className="w-96 flex-shrink-0 flex flex-col">
                    <div className="bg-white rounded-xl border border-[#E0E0E0] shadow-sm overflow-hidden flex flex-col h-full">
                        {/* List Header */}
                        <div className="p-4 border-b border-[#E0E0E0] bg-[#FAFAFA] flex-shrink-0">
                            <h2 className="text-lg font-semibold text-[#1F1F1F]">All Appointments</h2>
                            {/* Note: We removed the count badge because parent doesn't know total count anymore */}
                        </div>

                        {/* List Content */}
                        <div className="flex-1 overflow-hidden relative">
                            <AppointmentList
                                // Pass the refresh trigger
                                refreshTrigger={refreshTrigger} 
                                selectedAppointmentId={selectedAppointment?.id}
                                onSelectAppointment={handleSelectAppointment}
                                onEditAppointment={handleEditAppointment}
                                onDeleteAppointment={handleDeleteRequest}
                            />
                        </div>
                    </div>
                </div>

                {/* Right Panel - Appointment Detail */}
                <div className="flex-1 min-w-0">
                    <div className="bg-white rounded-xl border border-[#E0E0E0] shadow-sm overflow-hidden h-full">
                        <AppointmentDetail
                            appointment={selectedAppointment}
                            onEdit={handleEditAppointment}
                            onDelete={handleDeleteRequest}
                            onClose={handleCloseDetail}
                        />
                    </div>
                </div>
            </div>

            {/* Edit Appointment Modal */}
            <UpdateAppointmentModal
                isOpen={isEditModalOpen}
                onClose={() => setIsEditModalOpen(false)}
                appointmentData={editAppointmentData}
                onSuccess={handleAppointmentUpdated}
                position={{ x: window.innerWidth / 2 - 210, y: 100 }}
            />

            {/* Delete Confirmation Modal */}
            {deleteConfirmation.show && (
                <>
                    <div
                        className="fixed inset-0 bg-black/50 z-50"
                        onClick={() => setDeleteConfirmation({ show: false, appointment: null })}
                    />
                    <div className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg z-50 w-96 p-6">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-10 h-10 flex items-center justify-center rounded-full bg-[#FEE2E2]">
                                <svg className="w-5 h-5 text-[#991B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-[#1F1F1F]">Delete Appointment</h3>
                                <p className="text-sm text-[#6D6D6D]">This action cannot be undone.</p>
                            </div>
                        </div>
                        <p className="text-sm text-[#1F1F1F] mb-6">
                            Are you sure you want to delete the appointment for <strong>{deleteConfirmation.appointment?.visitor_name}</strong>?
                        </p>
                        <div className="flex items-center gap-3">
                            <button
                                onClick={() => setDeleteConfirmation({ show: false, appointment: null })}
                                className="flex-1 h-10 text-sm font-medium text-[#1F1F1F] bg-white border border-[#E0E0E0] rounded-lg hover:bg-[#F5F5F5] transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handleConfirmDelete}
                                className="flex-1 h-10 text-sm font-medium text-white bg-[#991B1B] rounded-lg hover:bg-[#7F1D1D] transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default AppointmentsPage;
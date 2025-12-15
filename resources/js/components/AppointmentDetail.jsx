const AppointmentDetail = ({ appointment, onEdit, onDelete, onClose }) => {
    if (!appointment) {
        return (
            <div className="flex flex-col items-center justify-center h-full py-12 text-center">
                <svg
                    className="w-16 h-16 text-[#E0E0E0] mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={1.5}
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                </svg>
                <p className="text-[#6D6D6D]">Select an appointment to view details</p>
            </div>
        );
    }

    // Format date for display
    const formatDate = (dateStr) => {
        if (!dateStr) return 'No date';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
    };

    // Format time for display
    const formatTime = (timeStr) => {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    };

    // Get status config
    const getStatusConfig = (status) => {
        const statusConfig = {
            pending: {
                bg: '#FEF3C7',
                text: '#92400E',
                label: 'Pending',
                description: 'Awaiting approval',
            },
            approved: {
                bg: '#D1FAE5',
                text: '#065F46',
                label: 'Approved',
                description: 'Appointment confirmed',
            },
            cancelled: {
                bg: '#FEE2E2',
                text: '#991B1B',
                label: 'Cancelled',
                description: 'Appointment cancelled',
            },
            completed: {
                bg: '#E0E7FF',
                text: '#3730A3',
                label: 'Completed',
                description: 'Appointment finished',
            },
        };
        return statusConfig[status] || statusConfig.pending;
    };

    const statusConfig = getStatusConfig(appointment.status);

    // Info section component
    const InfoSection = ({ title, icon, children }) => (
        <div className="mb-6">
            <div className="flex items-center gap-2 mb-3">
                <div className="w-8 h-8 flex items-center justify-center rounded-lg bg-[#EEF2FF]">
                    {icon}
                </div>
                <h3 className="text-sm font-semibold text-[#1F1F1F]">{title}</h3>
            </div>
            <div className="pl-10">{children}</div>
        </div>
    );

    // Info row component
    const InfoRow = ({ label, value, isLink, href }) => (
        <div className="flex items-start py-1.5">
            <span className="text-xs text-[#6D6D6D] w-24 flex-shrink-0">{label}</span>
            {isLink ? (
                <a
                    href={href}
                    className="text-sm text-[#2563EB] hover:underline break-all"
                >
                    {value}
                </a>
            ) : (
                <span className="text-sm text-[#1F1F1F] break-all">{value || '-'}</span>
            )}
        </div>
    );

    return (
        <div className="appointment-detail h-full flex flex-col bg-white">
            {/* Header Section */}
            <div className="flex items-center justify-between p-4 border-b border-[#E0E0E0]">
                <h2 className="text-lg font-semibold text-[#1F1F1F]">Appointment Details</h2>
                <button
                    onClick={onClose}
                    className="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-[#F5F5F5] transition-colors"
                    title="Close"
                >
                    <svg className="w-5 h-5 text-[#6D6D6D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Main Content Area */}
            <div className="flex-1 overflow-y-auto p-6">
                
                {/* 1. Status Banner - Full Width */}
                <div
                    className="rounded-lg p-3 mb-6 flex items-center gap-3 w-full"
                    style={{ backgroundColor: statusConfig.bg }}
                >
                    <div
                        className="w-3 h-3 rounded-full"
                        style={{ backgroundColor: statusConfig.text }}
                    />
                    <div>
                        <p className="text-sm font-medium" style={{ color: statusConfig.text }}>
                            {statusConfig.label}
                        </p>
                        <p className="text-xs opacity-80" style={{ color: statusConfig.text }}>
                            {statusConfig.description}
                        </p>
                    </div>
                </div>

                {/* 2. Grid Layout System 
                   - Mobile: 1 column (stack)
                   - Desktop (lg): 2 columns
                   - Gap: 24px (gap-6) between columns and rows
                */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    {/* Position: Top Left - Date & Time */}
                    <div className="col-span-1">
                        <InfoSection
                            title="Date & Time"
                            icon={
                                <svg className="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            }
                        >
                            <div className="bg-[#FAFAFA] rounded-lg p-3">
                                <p className="text-sm font-medium text-[#1F1F1F] mb-1">
                                    {formatDate(appointment.frame?.date)}
                                </p>
                                {appointment.slot && (
                                    <p className="text-sm text-[#6D6D6D]">
                                        {formatTime(appointment.slot.start_time)} - {formatTime(appointment.slot.end_time)}
                                    </p>
                                )}
                                {appointment.frame?.title && (
                                    <p className="text-xs text-[#6D6D6D] mt-1">
                                        Frame: {appointment.frame.title}
                                    </p>
                                )}
                            </div>
                        </InfoSection>
                    </div>

                    {/* Position: Top Right - Visitor Information */}
                    <div className="col-span-1">
                        <InfoSection
                            title="Visitor Information"
                            icon={
                                <svg className="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            }
                        >
                            <div className="space-y-1">
                                <InfoRow label="Name" value={appointment.visitor_name} />
                                <InfoRow label="Student" value={appointment.student_name} />
                                <InfoRow
                                    label="Email"
                                    value={appointment.email}
                                    isLink
                                    href={`mailto:${appointment.email}`}
                                />
                                <InfoRow
                                    label="Phone"
                                    value={appointment.phone_number}
                                    isLink
                                    href={`tel:${appointment.phone_number}`}
                                />
                            </div>
                        </InfoSection>
                    </div>

                    {/* Position: Bottom Left - Staff Information */}
                    <div className="col-span-1">
                        <InfoSection
                            title="Staff Information"
                            icon={
                                <svg className="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            }
                        >
                            <div className="space-y-1">
                                <InfoRow label="Name" value={appointment.staff.name} />
                                <InfoRow label="Department" value={appointment.staff.department} />
                                <InfoRow label="Position" value={appointment.staff.position} />
                                <InfoRow
                                    label="Email"
                                    value={appointment.staff.email}
                                    isLink
                                    href={`mailto:${appointment.staff.email}`}
                                />
                                <InfoRow
                                    label="Phone"
                                    value={appointment.staff.phone}
                                    isLink
                                    href={`tel:${appointment.staff.phone}`}
                                />
                            </div>
                        </InfoSection>
                    </div>

                    {/* Position: Bottom Right - Additional Information */}
                    <div className="col-span-1">
                        <InfoSection
                            title="Additional Information"
                            icon={
                                <svg className="w-4 h-4 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            }
                        >
                            <div className="space-y-3">
                                <div>
                                    <p className="text-xs text-[#6D6D6D] mb-1">Purpose of Visit</p>
                                    <p className="text-sm text-[#1F1F1F] bg-[#FAFAFA] rounded-lg p-3 min-h-[60px]">
                                        {appointment.purpose || 'No purpose specified'}
                                    </p>
                                </div>
                                {appointment.created_at && (
                                    <div className="text-xs text-[#6D6D6D]">
                                        Created: {new Date(appointment.created_at).toLocaleString()}
                                    </div>
                                )}
                            </div>
                        </InfoSection>
                    </div>

                </div> {/* End Grid Container */}
            </div>

            {/* Footer Actions */}
            <div className="p-4 border-t border-[#E0E0E0] flex items-center gap-3 bg-white mt-auto">
                <button
                    onClick={() => onEdit(appointment)}
                    className="flex-1 h-10 flex items-center justify-center gap-2 text-sm font-medium text-white bg-[#2563EB] rounded-lg hover:bg-[#1E4FCC] active:bg-[#1B49B2] transition-colors"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </button>
                <button
                    onClick={() => onDelete(appointment)}
                    className="flex-1 h-10 flex items-center justify-center gap-2 text-sm font-medium text-[#991B1B] bg-[#FEE2E2] rounded-lg hover:bg-[#FECACA] active:bg-[#FCA5A5] transition-colors"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    );
};

export default AppointmentDetail;

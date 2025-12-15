import { useState, useMemo } from 'react';

const AppointmentList = ({
    appointments,
    loading,
    selectedAppointmentId,
    onSelectAppointment,
    onEditAppointment,
    onDeleteAppointment,
}) => {
    // Local state for filters
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedStatus, setSelectedStatus] = useState('all');

    // Get status badge styles
    const getStatusBadge = (status) => {
        const statusConfig = {
            pending: { bg: '#FEF3C7', text: '#92400E', label: 'Pending' },
            approved: { bg: '#D1FAE5', text: '#065F46', label: 'Approved' },
            booked:   { bg: '#D1FAE5', text: '#065F46', label: 'Booked' },
            cancelled:{ bg: '#FEE2E2', text: '#991B1B', label: 'Cancelled' },
            completed:{ bg: '#E0E7FF', text: '#3730A3', label: 'Completed' },
        };

        const config = statusConfig[status] || statusConfig.pending;
        return (
            <span
                className="px-2 py-0.5 text-xs font-medium rounded-full"
                style={{ backgroundColor: config.bg, color: config.text }}
            >
                {config.label}
            </span>
        );
    };

    // Format date for display
    const formatDate = (dateStr) => {
        if (!dateStr) return 'No date';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
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

    // 1. Filter Logic (Updated: Removed Purpose search)
    const filteredAppointments = useMemo(() => {
        return appointments.filter(apt => {
            // Status Check
            const statusMatch = selectedStatus === 'all' || apt.status === selectedStatus;
            
            // Search Check (Visitor Name OR Student Name ONLY)
            const term = searchTerm.toLowerCase();
            const searchMatch = !term || 
                (apt.visitor_name || '').toLowerCase().includes(term) ||
                (apt.student_name || '').toLowerCase().includes(term);

            return statusMatch && searchMatch;
        });
    }, [appointments, searchTerm, selectedStatus]);

    // 2. Group filtered appointments by date
    const groupedAppointments = useMemo(() => {
        const groups = {};
        filteredAppointments.forEach((apt) => {
            const date = apt.frame?.date || 'No Date';
            if (!groups[date]) {
                groups[date] = [];
            }
            groups[date].push(apt);
        });

        const sortedDates = Object.keys(groups).sort((a, b) => {
            if (a === 'No Date') return 1;
            if (b === 'No Date') return -1;
            return new Date(a) - new Date(b);
        });

        return sortedDates.map((date) => ({
            date,
            appointments: groups[date],
        }));
    }, [filteredAppointments]);

    return (
        <div className="appointment-list h-full flex flex-col">
            
            {/* --- Filter Controls --- */}
            <div className="p-3 border-b border-[#E0E0E0] bg-white sticky top-0 z-10 gap-2 flex flex-col">
                {/* Search Bar */}
                <div className="relative">
                    <input
                        type="text"
                        placeholder="Search visitor or student name..." 
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full h-9 pl-9 pr-3 text-sm border border-[#E0E0E0] rounded-lg focus:outline-none focus:border-[#2563EB]"
                    />
                    <svg className="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                {/* Status Dropdown */}
                <select
                    value={selectedStatus}
                    onChange={(e) => setSelectedStatus(e.target.value)}
                    className="w-full h-9 px-3 text-sm border border-[#E0E0E0] rounded-lg bg-white focus:outline-none focus:border-[#2563EB]"
                >
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved/Booked</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {/* --- Loading State --- */}
            {loading && (
                <div className="flex items-center justify-center py-8">
                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#2563EB]"></div>
                    <span className="ml-2 text-sm text-[#6D6D6D]">Loading...</span>
                </div>
            )}

            {/* --- Empty State --- */}
            {!loading && filteredAppointments.length === 0 && (
                <div className="flex flex-col items-center justify-center py-8 text-center flex-1">
                    <svg className="w-12 h-12 text-[#E0E0E0] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p className="text-sm text-[#6D6D6D]">
                        {appointments.length === 0 ? "No appointments yet" : "No results found"}
                    </p>
                </div>
            )}

            {/* --- List Content --- */}
            {!loading && (
                <div className="flex-1 overflow-y-auto">
                    {groupedAppointments.map(({ date, appointments: dateAppointments }) => (
                        <div key={date} className="mb-0">
                            {/* Date Header */}
                            <div className="sticky top-0 bg-[#F5F5F5] px-3 py-2 text-xs font-semibold text-[#6D6D6D] border-y border-[#E0E0E0]">
                                {formatDate(date)}
                            </div>

                            {/* Appointments for this date */}
                            <div className="divide-y divide-[#E0E0E0]">
                                {dateAppointments.map((apt) => (
                                    <div
                                        key={apt.id}
                                        className={`
                                            appointment-list-item p-3 cursor-pointer
                                            transition-all duration-150 ease-in-out
                                            ${selectedAppointmentId === apt.id
                                                ? 'bg-[#EEF2FF] border-l-2 border-l-[#2563EB]'
                                                : 'hover:bg-[#FAFAFA] border-l-2 border-l-transparent'
                                            }
                                        `}
                                        onClick={() => onSelectAppointment(apt)}
                                    >
                                        {/* Top row: Visitor name and status */}
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-sm font-medium text-[#1F1F1F] truncate flex-1 mr-2">
                                                {apt.visitor_name}
                                            </span>
                                            {getStatusBadge(apt.status)}
                                        </div>

                                        {/* Student Name (New Line) */}
                                        {apt.student_name && (
                                            <div className="text-xs text-[#6D6D6D] mb-1 flex items-center gap-1">
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>Student: {apt.student_name}</span>
                                            </div>
                                        )}

                                        {/* Time */}
                                        <div className="flex items-center text-xs text-[#6D6D6D] mb-2">
                                            <svg className="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {apt.slot ? (
                                                <span>{formatTime(apt.slot.start_time)} - {formatTime(apt.slot.end_time)}</span>
                                            ) : (
                                                <span>No time set</span>
                                            )}
                                        </div>

                                        {/* Purpose preview */}
                                        {apt.purpose && (
                                            <p className="text-xs text-[#6D6D6D] line-clamp-1 mb-2">
                                                {apt.purpose}
                                            </p>
                                        )}

                                        {/* Action buttons */}
                                        <div className="flex items-center gap-2 mt-2">
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    onEditAppointment(apt);
                                                }}
                                                className="flex items-center gap-1 px-2 py-1 text-xs font-medium text-[#2563EB] bg-[#EEF2FF] rounded hover:bg-[#E0E7FF] transition-colors"
                                                title="Edit appointment"
                                            >
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    onDeleteAppointment(apt);
                                                }}
                                                className="flex items-center gap-1 px-2 py-1 text-xs font-medium text-[#991B1B] bg-[#FEE2E2] rounded hover:bg-[#FECACA] transition-colors"
                                                title="Delete appointment"
                                            >
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default AppointmentList;
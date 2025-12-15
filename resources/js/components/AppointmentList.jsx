import { useState, useEffect, useMemo, useRef } from 'react';
import { useAuth } from '../contexts/AuthContext';

const AppointmentList = ({
    // Removed 'appointments' and 'loading' from props as they are handled internally now
    refreshTrigger, // New prop to trigger re-fetch
    selectedAppointmentId,
    onSelectAppointment,
    onEditAppointment,
    onDeleteAppointment,
}) => {
    const { user } = useAuth();
    
    // --- Local State ---
    const [appointments, setAppointments] = useState([]);
    const [loading, setLoading] = useState(false);
    
    // Filter & Pagination State
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedStatus, setSelectedStatus] = useState('all');
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    // Ref for debounce timer
    const debounceTimeout = useRef(null);

    // --- API Fetch Function ---
    const fetchAppointments = async (page, status, search) => {
        if (!user?.id) return;
        
        setLoading(true);
        try {
            // Construct query parameters
            const params = new URLSearchParams({
                page: page,
                status: status,
                search: search,
                user_id: user.id
            });

            const response = await fetch(`/api/appointments?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    // Use optional chaining to prevent errors if meta tag is missing
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            
            if (!response.ok) throw new Error("Failed to fetch");

            const data = await response.json();

            // Handle Laravel Pagination Response Structure
            if (data.data) {
                // 🛑 KEY FIX: Map backend data to match frontend naming conventions
                const mappedData = data.data.map(apt => ({
                    ...apt,
                    // Map 'availability_slot' (backend) to 'slot' (frontend expectation)
                    slot: apt.availability_slot, 
                    // Map the nested frame directly to 'frame' for easier access
                    frame: apt.availability_slot?.availability_frame 
                }));

                setAppointments(mappedData);
                setTotalPages(data.last_page);
                setCurrentPage(data.current_page);
            } else {
                setAppointments([]);
            }
        } catch (error) {
            console.error("Failed to fetch appointments", error);
        } finally {
            setLoading(false);
        }
    };

    // --- Effect 1: Handle Search & Filter Changes (Debounced) ---
    useEffect(() => {
        if (debounceTimeout.current) {
            clearTimeout(debounceTimeout.current);
        }

        // Wait 500ms after typing stops before fetching
        debounceTimeout.current = setTimeout(() => {
            // Reset to page 1 when filter changes
            fetchAppointments(1, selectedStatus, searchTerm);
            setCurrentPage(1); 
        }, 500);

        return () => clearTimeout(debounceTimeout.current);
    }, [searchTerm, selectedStatus]);

    // --- Effect 2: Handle Page Changes & Refresh Trigger ---
    useEffect(() => {
        // Skip initial fetch if handled by Effect 1 (to avoid double fetch on mount)
        // But re-fetch if page > 1 or if refreshTrigger changed
        if (currentPage > 1 || refreshTrigger > 0) {
            fetchAppointments(currentPage, selectedStatus, searchTerm);
        }
    }, [currentPage, refreshTrigger]);


    // --- Helper Functions ---

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
                className="px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap"
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

    // Group appointments by date (Client-side grouping of the fetched page)
    const groupedAppointments = useMemo(() => {
        const groups = {};
        appointments.forEach((apt) => {
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
    }, [appointments]);

    // Pagination Handlers
    const handlePrevPage = () => {
        if (currentPage > 1) setCurrentPage(prev => prev - 1);
    };

    const handleNextPage = () => {
        if (currentPage < totalPages) setCurrentPage(prev => prev + 1);
    };

    return (
        <div className="appointment-list h-full flex flex-col bg-white">
            
            {/* --- Filter Controls (Row Layout) --- */}
            <div className="p-3 border-b border-[#E0E0E0] bg-white sticky top-0 z-10 flex items-center gap-2">
                {/* Search Bar */}
                <div className="relative flex-1">
                    <input
                        type="text"
                        placeholder="Search visitor/student..." 
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
                    className="w-32 h-9 px-2 text-sm border border-[#E0E0E0] rounded-lg bg-white focus:outline-none focus:border-[#2563EB]"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Booked</option>
                    <option value="completed">Done</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            {/* --- Loading State --- */}
            {loading && (
                <div className="flex items-center justify-center py-8">
                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#2563EB]"></div>
                </div>
            )}

            {/* --- Empty State --- */}
            {!loading && appointments.length === 0 && (
                <div className="flex flex-col items-center justify-center py-8 text-center flex-1">
                    <svg className="w-12 h-12 text-[#E0E0E0] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p className="text-sm text-[#6D6D6D]">
                        No appointments found
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

                                        {/* Student Name */}
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
                                                title="Edit"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    onDeleteAppointment(apt);
                                                }}
                                                className="flex items-center gap-1 px-2 py-1 text-xs font-medium text-[#991B1B] bg-[#FEE2E2] rounded hover:bg-[#FECACA] transition-colors"
                                                title="Delete"
                                            >
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

            {/* --- Pagination Controls --- */}
            <div className="p-3 border-t border-[#E0E0E0] bg-[#F9FAFB] flex items-center justify-between">
                <button
                    onClick={handlePrevPage}
                    disabled={currentPage === 1 || loading}
                    className={`px-3 py-1.5 text-xs font-medium rounded-md border ${currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
                >
                    Previous
                </button>
                <span className="text-xs text-gray-500">
                    Page {currentPage} of {totalPages}
                </span>
                <button
                    onClick={handleNextPage}
                    disabled={currentPage === totalPages || loading}
                    className={`px-3 py-1.5 text-xs font-medium rounded-md border ${currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
                >
                    Next
                </button>
            </div>
        </div>
    );
};

export default AppointmentList;
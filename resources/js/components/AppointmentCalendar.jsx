import { useState, useRef, useMemo, useEffect, useCallback } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import multiMonthPlugin from '@fullcalendar/multimonth';
import CreateAvailabilityFrameModal from './CreateAvailabilityFrameModal';
import UpdateAvailabilityFrameModal from './UpdateAvailabilityFrameModal';
import { useAuth } from '../contexts/AuthContext';

const AppointmentCalendar = () => {
    const { user } = useAuth();
    const calendarRef = useRef(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedStatus, setSelectedStatus] = useState('all');
    const [viewMode, setViewMode] = useState('month');
    const [currentDate, setCurrentDate] = useState(new Date());

    // Frames from API (recurring instances are stored in DB)
    const [frames, setFrames] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Create modal state
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [createModalData, setCreateModalData] = useState(null);
    const [modalPosition, setModalPosition] = useState({ x: 0, y: 0 });

    // Update modal state
    const [isUpdateModalOpen, setIsUpdateModalOpen] = useState(false);
    const [updateFrameData, setUpdateFrameData] = useState(null);

    // Format date in local timezone (YYYY-MM-DD)
    const formatLocalDate = useCallback((dateObj) => {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }, []);

    // Transform API frame data to FullCalendar event format
    const transformFrameToEvent = useCallback((frame) => ({
        id: `frame-${frame.id}`,
        title: frame.title || 'Availability',
        start: `${frame.date}T${frame.start_time}`,
        end: `${frame.date}T${frame.end_time}`,
        backgroundColor: frame.status === 'active' ? '#2563EB' : '#6D6D6D',
        borderColor: frame.status === 'active' ? '#1E4FCC' : '#4A4A4A',
        classNames: ['event-frame'],
        displayEventTime: false, // Hide time display for frame events
        extendedProps: {
            type: 'frame',
            frameId: frame.id,
            status: frame.status,
            duration: frame.duration,
            interval: frame.interval,
            is_recurring: frame.is_recurring,
            repeat_group_id: frame.repeat_group_id,
            day: frame.day,
            start_time: frame.start_time,
            end_time: frame.end_time,
            staff_id: frame.staff_id,
        },
    }), []);

    // Transform slot data to FullCalendar event format
    const transformSlotToEvent = useCallback((slot, frame) => ({
        id: `slot-${slot.id}`,
        title: `${slot.start_time?.substring(0, 5)} - ${slot.end_time?.substring(0, 5)}`,
        start: `${frame.date}T${slot.start_time}`,
        end: `${frame.date}T${slot.end_time}`,
        backgroundColor: '#FFEBB7',
        borderColor: '#E0E0E0',
        classNames: ['event-slot'],
        extendedProps: {
            type: 'slot',
            slotId: slot.id,
            frameId: frame.id,
            status: slot.status,
            frameTitle: frame.title,
            start_time: slot.start_time,
            end_time: slot.end_time,
        },
    }), []);

    // Transform all frames and slots to calendar events
    const allEvents = useMemo(() => {
        const events = [];

        frames.forEach((frame) => {
            // Add frame event
            events.push(transformFrameToEvent(frame));

            // Add slot events if available
            if (frame.slots && Array.isArray(frame.slots)) {
                frame.slots.forEach((slot) => {
                    events.push(transformSlotToEvent(slot, frame));
                });
            }
        });

        return events;
    }, [frames, transformFrameToEvent, transformSlotToEvent]);

    // Fetch frames on component mount
    useEffect(() => {
        const fetchFrames = async () => {
            if (!user?.id) return;

            setLoading(true);
            setError(null);

            try {
                const response = await fetch(`/api/availability-frames/staff/${user.id}`, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch availability frames');
                }

                const data = await response.json();
                setFrames(data);
            } catch (err) {
                setError(err.message);
                console.error('Error fetching frames:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchFrames();
    }, [user?.id]);

    // Filter events based on search term and status
    const filteredEvents = useMemo(() => {
        return allEvents.filter((event) => {
            // Filter by search term
            const matchesSearch = event.title
                .toLowerCase()
                .includes(searchTerm.toLowerCase());

            // Filter by status
            const matchesStatus =
                selectedStatus === 'all' ||
                event.extendedProps.status === selectedStatus;

            return matchesSearch && matchesStatus;
        });
    }, [allEvents, searchTerm, selectedStatus]);

    // Handler functions
    const onSearchChange = (value) => {
        setSearchTerm(value);
    };

    const onStatusChange = (status) => {
        setSelectedStatus(status);
    };

    const onViewModeChange = (mode) => {
        setViewMode(mode);
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            // Map view modes to FullCalendar view names
            const viewMap = {
                year: 'multiMonthYear',
                month: 'dayGridMonth',
                week: 'timeGridWeek',
                day: 'timeGridDay',
            };
            calendarApi.changeView(viewMap[mode]);
        }
    };

    const onNavigate = (direction) => {
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            if (direction === 'previous') {
                calendarApi.prev();
            } else if (direction === 'next') {
                calendarApi.next();
            } else if (direction === 'today') {
                calendarApi.today();
            }
            setCurrentDate(calendarApi.getDate());
        }
    };

    // Handle date clicks for drill-down navigation
    const handleDateClick = (info) => {
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            // Navigate to the clicked date
            calendarApi.gotoDate(info.date);

            // Drill down based on current view
            if (viewMode === 'year' || viewMode === 'month') {
                // Switch to day view when clicking from year or month view
                setViewMode('day');
                calendarApi.changeView('timeGridDay');
            }
        }
    };

    // Get formatted date range for display
    const getDateRangeDisplay = () => {
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            const view = calendarApi.view;
            const start = view.currentStart;
            const end = view.currentEnd;

            if (viewMode === 'day') {
                return start.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });
            } else if (viewMode === 'week') {
                return `${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
            } else if (viewMode === 'month') {
                return start.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                });
            } else {
                return start.toLocaleDateString('en-US', { year: 'numeric' });
            }
        }
        return currentDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
        });
    };

    // Calculate modal position near the clicked element
    const calculateModalPosition = (jsEvent) => {
        const clickX = jsEvent.pageX;
        const clickY = jsEvent.pageY;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        const modalWidth = 480;
        const modalHeight = 600; // Approximate height
        const offset = 20;

        let x = clickX + offset;
        let y = clickY;

        // Adjust if modal would go off right edge
        if (x + modalWidth > windowWidth) {
            x = clickX - modalWidth - offset;
        }

        // Adjust if modal would go off bottom edge
        if (y + modalHeight > windowHeight) {
            y = windowHeight - modalHeight - offset;
        }

        // Ensure it doesn't go off top edge
        if (y < offset) {
            y = offset;
        }

        return { x, y };
    };

    // Handle calendar selection (drag to create new frame)
    const handleSelect = (selectInfo) => {
        const { start, end, jsEvent } = selectInfo;

        // Format date and times
        const date = formatLocalDate(start);
        const startTime = start.toTimeString().split(' ')[0].substring(0, 5);
        const endTime = end.toTimeString().split(' ')[0].substring(0, 5);
        const day = start.toLocaleDateString('en-US', { weekday: 'long' });

        // Calculate position
        if (jsEvent) {
            setModalPosition(calculateModalPosition(jsEvent));
        }

        // Open create modal with pre-filled data
        setCreateModalData({
            date,
            start_time: startTime,
            end_time: endTime,
            day,
        });
        setIsCreateModalOpen(true);

        // Unselect the time range
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            calendarApi.unselect();
        }
    };

    // Extract frame data from calendar event for update modal
    const extractFrameDataFromEvent = (event) => {
        const props = event.extendedProps;
        const frameId = props.frameId || event.id.replace('frame-', '').split('-')[0];

        // Find the original frame from our frames array
        const originalFrame = frames.find(f => String(f.id) === String(frameId));

        return {
            id: frameId,
            title: event.title,
            date: originalFrame?.date || props.originalDate || formatLocalDate(event.start),
            day: props.day || event.start.toLocaleDateString('en-US', { weekday: 'long' }),
            start_time: props.start_time || event.start.toTimeString().split(' ')[0].substring(0, 5),
            end_time: props.end_time || event.end?.toTimeString().split(' ')[0].substring(0, 5),
            duration: props.duration,
            interval: props.interval,
            is_recurring: props.is_recurring || false,
            status: props.status,
            staff_id: props.staff_id,
        };
    };

    // Handle event click (open update modal)
    const handleEventClick = (clickInfo) => {
        const { event, jsEvent } = clickInfo;

        // Calculate position
        if (jsEvent) {
            setModalPosition(calculateModalPosition(jsEvent));
        }

        // Extract frame data and open update modal
        const frameData = extractFrameDataFromEvent(event);
        setUpdateFrameData(frameData);
        setIsUpdateModalOpen(true);
    };

    // Handle event resize - revert and open update modal
    const handleEventResize = (resizeInfo) => {
        const { event, jsEvent, revert } = resizeInfo;

        // Revert the resize - we don't allow resizing, just opening update modal
        revert();

        // Calculate position
        if (jsEvent) {
            setModalPosition(calculateModalPosition(jsEvent));
        }

        // Extract frame data and open update modal
        const frameData = extractFrameDataFromEvent(event);
        setUpdateFrameData(frameData);
        setIsUpdateModalOpen(true);
    };

    // Handle event drop - revert and open update modal
    const handleEventDrop = (dropInfo) => {
        const { event, jsEvent, revert } = dropInfo;

        // Revert the drop - we don't allow dragging, just opening update modal
        revert();

        // Calculate position
        if (jsEvent) {
            setModalPosition(calculateModalPosition(jsEvent));
        }

        // Extract frame data and open update modal
        const frameData = extractFrameDataFromEvent(event);
        setUpdateFrameData(frameData);
        setIsUpdateModalOpen(true);
    };

    // Refetch all frames from API
    const refetchFrames = async () => {
        if (!user?.id) return;

        try {
            const response = await fetch(`/api/availability-frames/staff/${user.id}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setFrames(data);
            }
        } catch (err) {
            console.error('Error refetching frames:', err);
        }
    };

    // Handle successful frame creation - refetch to get all recurring instances
    const handleFrameCreated = () => {
        refetchFrames();
    };

    // Handle successful frame update - refetch to get updated recurring instances
    const handleFrameUpdated = () => {
        refetchFrames();
    };

    return (
        <div className="appointment-calendar-container">
            <div className="appointment-calendar">
                {/* Page Title */}
                <h1 className="text-2xl font-bold text-[#1F1F1F] mb-6">
                    Appointment Calendar
                </h1>

            {/* Top Controls Container */}
            <div className="controls-container bg-white rounded-xl border border-[#E0E0E0] p-4 mb-4 shadow-[0px_1px_2px_rgba(0,0,0,0.05)]">
                <div className="flex items-center justify-between gap-4 flex-wrap">
                    {/* Left Section: Search & Status */}
                    <div className="flex items-center gap-3 flex-1 min-w-[320px]">
                        {/* Search Bar */}
                        <div className="relative flex-1 max-w-xs">
                            <input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => onSearchChange(e.target.value)}
                                placeholder="Search events..."
                                className="w-full h-10 px-3 pr-10 text-sm text-[#1F1F1F] bg-white border border-[#E0E0E0] rounded-lg
                                    placeholder:text-[#6D6D6D]
                                    focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]
                                    hover:border-[#C0C0C0]
                                    transition-all duration-150 ease-in-out"
                            />
                            <svg
                                className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#6D6D6D] pointer-events-none"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>

                        {/* Status Dropdown */}
                        <div className="relative">
                            <select
                                value={selectedStatus}
                                onChange={(e) => onStatusChange(e.target.value)}
                                className="h-10 px-3 pr-8 text-sm font-medium text-[#1F1F1F] bg-white border border-[#E0E0E0] rounded-lg
                                    appearance-none cursor-pointer
                                    focus:outline-none focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]
                                    hover:border-[#C0C0C0]
                                    transition-all duration-150 ease-in-out"
                            >
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <svg
                                className="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-[#6D6D6D] pointer-events-none"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                    </div>

                    {/* Right Section: View Mode Buttons */}
                    <div className="flex items-center gap-2">
                        {['year', 'month', 'week', 'day'].map((mode) => (
                            <button
                                key={mode}
                                onClick={() => onViewModeChange(mode)}
                                className={`
                                    h-10 px-4 text-sm font-medium rounded-lg
                                    transition-all duration-150 ease-in-out
                                    ${
                                        viewMode === mode
                                            ? 'bg-[#2563EB] text-white hover:bg-[#1E4FCC] active:bg-[#1B49B2]'
                                            : 'bg-white text-[#1F1F1F] border border-[#E0E0E0] hover:bg-[#F5F5F5] active:bg-[#EBEBEB]'
                                    }
                                `}
                            >
                                {mode.charAt(0).toUpperCase() + mode.slice(1)}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Navigation Controls */}
            <div className="navigation-controls bg-white rounded-xl border border-[#E0E0E0] p-4 mb-4 shadow-[0px_1px_2px_rgba(0,0,0,0.05)]">
                <div className="flex items-center justify-between">
                    {/* Left: Previous/Next Navigation */}
                    <div className="flex items-center gap-2">
                        <button
                            onClick={() => onNavigate('previous')}
                            className="h-10 w-10 flex items-center justify-center bg-white border border-[#E0E0E0] rounded-lg
                                hover:bg-[#F5F5F5] active:bg-[#EBEBEB]
                                transition-all duration-150 ease-in-out"
                            aria-label="Previous"
                        >
                            <svg
                                className="w-5 h-5 text-[#1F1F1F]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </button>

                        <button
                            onClick={() => onNavigate('next')}
                            className="h-10 w-10 flex items-center justify-center bg-white border border-[#E0E0E0] rounded-lg
                                hover:bg-[#F5F5F5] active:bg-[#EBEBEB]
                                transition-all duration-150 ease-in-out"
                            aria-label="Next"
                        >
                            <svg
                                className="w-5 h-5 text-[#1F1F1F]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>

                    {/* Center: Current Date/Period Display */}
                    <div className="text-lg font-semibold text-[#1F1F1F]">
                        {getDateRangeDisplay()}
                    </div>

                    {/* Right: Today Button */}
                    <button
                        onClick={() => onNavigate('today')}
                        className="h-10 px-4 text-sm font-medium text-[#2563EB] bg-white border border-[#2563EB] rounded-lg
                            hover:bg-[#EEF2FF] active:bg-[#E0E7FF]
                            transition-all duration-150 ease-in-out"
                    >
                        Today
                    </button>
                </div>
            </div>

            {/* Calendar Content */}
            <div className="calendar-content bg-white rounded-xl border border-[#E0E0E0] shadow-[0px_1px_2px_rgba(0,0,0,0.05)] overflow-hidden p-4">
                {/* Loading State */}
                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#2563EB]"></div>
                        <span className="ml-3 text-[#6D6D6D]">Loading availability...</span>
                    </div>
                )}

                {/* Error State */}
                {error && !loading && (
                    <div className="flex items-center justify-center py-12 text-red-600">
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{error}</span>
                    </div>
                )}

                {/* Calendar */}
                {!loading && <FullCalendar
                    ref={calendarRef}
                    plugins={[
                        dayGridPlugin,
                        timeGridPlugin,
                        interactionPlugin,
                        multiMonthPlugin,
                    ]}
                    initialView="dayGridMonth"
                    headerToolbar={false} // We're using custom controls
                    events={filteredEvents}
                    height="auto"
                    aspectRatio={1.8}
                    editable={true}
                    selectable={true}
                    selectMirror={true}
                    dayMaxEvents={true}
                    weekends={true}
                    eventOverlap={true}
                    // Drag configuration
                    dragScroll={true}
                    dragRevertDuration={500}
                    eventLongPressDelay={0}
                    // Time grid configuration
                    slotDuration="00:15:00" // 15-minute slots
                    slotLabelInterval="01:00:00" // Show hour labels
                    snapDuration="00:15:00" // Snap to 15-minute intervals when dragging
                    slotMinTime="00:00:00"
                    slotMaxTime="24:00:00"
                    scrollTime="08:00:00" // Default scroll to 8 AM
                    allDaySlot={true}
                    eventDurationEditable={true}
                    eventStartEditable={true}
                    eventResourceEditable={false}
                    // Event handlers
                    dateClick={handleDateClick}
                    select={handleSelect}
                    eventResize={handleEventResize}
                    eventDrop={handleEventDrop}
                    eventClick={handleEventClick}
                    datesSet={(dateInfo) => {
                        setCurrentDate(dateInfo.start);
                    }}
                />}
            </div>
            </div>

            {/* Create Availability Frame Floating Panel */}
            <CreateAvailabilityFrameModal
                isOpen={isCreateModalOpen}
                onClose={() => setIsCreateModalOpen(false)}
                initialData={createModalData}
                onSuccess={handleFrameCreated}
                position={modalPosition}
            />

            {/* Update Availability Frame Floating Panel */}
            <UpdateAvailabilityFrameModal
                isOpen={isUpdateModalOpen}
                onClose={() => setIsUpdateModalOpen(false)}
                frameData={updateFrameData}
                onSuccess={handleFrameUpdated}
                position={modalPosition}
            />
        </div>
    );
};

export default AppointmentCalendar;

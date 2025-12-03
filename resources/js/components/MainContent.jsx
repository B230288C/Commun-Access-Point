import { useState } from 'react';
import AppointmentCalendar from './AppointmentCalendar';

const MainContent = () => {
    const [activeView, setActiveView] = useState('calendar');

    const renderContent = () => {
        switch (activeView) {
            case 'calendar':
                return <AppointmentCalendar />;
            case 'dashboard':
                return (
                    <div className="p-6">
                        <h1 className="text-2xl font-bold text-[#1F1F1F]">
                            Dashboard Overview
                        </h1>
                        <p className="text-sm text-[#6D6D6D] mt-2">
                            Dashboard content will go here...
                        </p>
                    </div>
                );
            case 'settings':
                return (
                    <div className="p-6">
                        <h1 className="text-2xl font-bold text-[#1F1F1F]">
                            Settings
                        </h1>
                        <p className="text-sm text-[#6D6D6D] mt-2">
                            Settings content will go here...
                        </p>
                    </div>
                );
            default:
                return <AppointmentCalendar />;
        }
    };

    return (
        <div className="main-content-container w-full h-full">
            {/* Optional: View Switcher (for testing) - Remove when you integrate with Sidebar */}
            <div className="view-switcher bg-white border-b border-[#E0E0E0] p-4 flex gap-2">
                <button
                    onClick={() => setActiveView('calendar')}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 ${
                        activeView === 'calendar'
                            ? 'bg-[#2563EB] text-white'
                            : 'bg-[#F5F5F5] text-[#1F1F1F] hover:bg-[#EBEBEB]'
                    }`}
                >
                    Calendar
                </button>
                <button
                    onClick={() => setActiveView('dashboard')}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 ${
                        activeView === 'dashboard'
                            ? 'bg-[#2563EB] text-white'
                            : 'bg-[#F5F5F5] text-[#1F1F1F] hover:bg-[#EBEBEB]'
                    }`}
                >
                    Dashboard
                </button>
                <button
                    onClick={() => setActiveView('settings')}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150 ${
                        activeView === 'settings'
                            ? 'bg-[#2563EB] text-white'
                            : 'bg-[#F5F5F5] text-[#1F1F1F] hover:bg-[#EBEBEB]'
                    }`}
                >
                    Settings
                </button>
            </div>

            {/* Dynamic Content Area */}
            <div className="content-area">
                {renderContent()}
            </div>
        </div>
    );
};

export default MainContent;

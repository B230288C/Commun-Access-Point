import React from 'react';

const Header = () => {
    return (
        <header className="h-16 bg-white border-b border-[#E0E0E0] flex items-center justify-between px-6 shadow-sm">
            {/* Left: Page Title */}
            <div>
                <h1 className="text-2xl font-bold text-[#1F1F1F]">Appointment</h1>
            </div>

            {/* Right: Icons */}
            <div className="flex items-center gap-4">
                {/* Notification Bell Icon */}
                <button
                    className="flex items-center justify-center w-10 h-10 text-[#6D6D6D] transition-all duration-150 ease-in-out hover:text-[#2563EB] active:text-[#1B49B2] hover:scale-110"
                    aria-label="Notifications"
                    title="Notifications"
                >
                    <i className="fas fa-bell" style={{ fontSize: '20px' }}></i>
                </button>

                {/* User Account Icon */}
                <button
                    className="flex items-center justify-center w-10 h-10 text-[#6D6D6D] transition-all duration-150 ease-in-out hover:text-[#2563EB] active:text-[#1B49B2] hover:scale-110"
                    aria-label="User Account"
                    title="User Account"
                >
                    <i className="fas fa-user-circle" style={{ fontSize: '20px' }}></i>
                </button>
            </div>
        </header>
    );
};

export default Header;

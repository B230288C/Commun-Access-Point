import React, { useState } from 'react';

const Sidebar = () => {
    const [isExpanded, setIsExpanded] = useState(false);

    const menuItems = [
        {
            id: 'dashboard',
            label: 'Dashboard',
            icon: 'fas fa-chart-line',
            href: '/dashboard',
        },
        {
            id: 'appointment',
            label: 'Appointment',
            icon: 'fas fa-calendar',
            href: '/appointment',
            active: true,
        },
    ];

    return (
        <aside
            className={`fixed left-0 top-16 h-[calc(100vh-64px)] bg-white border-r border-[#E0E0E0] transition-all duration-300 ease-in-out shadow-sm z-40 ${
                isExpanded ? 'w-48' : 'w-16'
            }`}
            onMouseEnter={() => setIsExpanded(true)}
            onMouseLeave={() => setIsExpanded(false)}
        >
            <nav className="flex flex-col gap-2 p-3">
                {menuItems.map((item) => (
                    <a
                        key={item.id}
                        href={item.href}
                        className={`flex items-center gap-4 px-3 py-3 rounded-lg transition-all duration-150 ease-in-out ${
                            item.active
                                ? 'bg-[#2563EB] text-white'
                                : 'text-[#6D6D6D] hover:bg-[#F5F5F5] active:bg-[#EFEFEF]'
                        }`}
                        title={!isExpanded ? item.label : ''}
                    >
                        {/* Icon */}
                        <i
                            className={item.icon}
                            style={{
                                fontSize: '20px',
                                flexShrink: 0,
                            }}
                        ></i>

                        {/* Label - Only show when expanded */}
                        {isExpanded && (
                            <span className="text-sm font-medium whitespace-nowrap">
                                {item.label}
                            </span>
                        )}
                    </a>
                ))}
            </nav>
        </aside>
    );
};

export default Sidebar;

const Sidebar = ({ isExpanded, onExpandChange }) => {
    const menuItems = [
        {
            id: 'dashboard',
            label: 'Dashboard',
            icon: 'fas fa-chart-line',
            href: '/dashboard',
            active: true,
        },
        {
            id: 'appointment',
            label: 'Appointment',
            icon: 'fas fa-calendar',
            href: '/appointment',
        },
    ];

    return (
        <aside
            className={`sidebar ${isExpanded ? 'w-48' : 'w-16'}`}
            onMouseEnter={() => onExpandChange(true)}
            onMouseLeave={() => onExpandChange(false)}
        >
            <nav className="sidebar-nav">
                {menuItems.map((item) => (
                    <a
                        key={item.id}
                        href={item.href}
                        className={`sidebar-item ${
                            item.active ? 'sidebar-item-active' : 'sidebar-item-default'
                        }`}
                        title={!isExpanded ? item.label : ''}
                    >
                        <i
                            className={`${item.icon} sidebar-item-icon`}
                            style={{ fontSize: '20px' }}
                        ></i>

                        <span className={`sidebar-item-label ${
                            isExpanded ? 'sidebar-item-label-expanded' : 'sidebar-item-label-collapsed'
                        }`}>
                            {item.label}
                        </span>
                    </a>
                ))}
            </nav>
        </aside>
    );
};

export default Sidebar;

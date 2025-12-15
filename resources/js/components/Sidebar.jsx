const Sidebar = ({ isExpanded, onExpandChange, activeView, onNavigate }) => {
    const menuItems = [
        {
            id: 'dashboard',
            label: 'Dashboard',
            icon: 'fas fa-chart-line',
        },
        {
            id: 'appointments',
            label: 'Appointments',
            icon: 'fas fa-calendar-check',
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
                    <button
                        key={item.id}
                        onClick={() => onNavigate(item.id)}
                        className={`sidebar-item w-full text-left ${
                            activeView === item.id ? 'sidebar-item-active' : 'sidebar-item-default'
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
                    </button>
                ))}
            </nav>
        </aside>
    );
};

export default Sidebar;

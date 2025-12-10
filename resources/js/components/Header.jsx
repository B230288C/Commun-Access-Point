import { useState, useRef, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';

const Header = () => {
    const { user, logout } = useAuth();
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const dropdownRef = useRef(null);

    const handleLogout = async () => {
        if (confirm('Are you sure you want to logout?')) {
            await logout();
        }
    };

    const toggleDropdown = () => {
        setIsDropdownOpen(!isDropdownOpen);
    };

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsDropdownOpen(false);
            }
        };

        if (isDropdownOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isDropdownOpen]);

    return (
        <header className="header">
            <div>
                <h1 className="header-title">Appointment</h1>
            </div>

            <div className="header-actions">
                <button
                    className="btn-icon"
                    aria-label="Notifications"
                    title="Notifications"
                >
                    <i className="fas fa-bell" style={{ fontSize: '20px' }}></i>
                </button>

                <div className="account-dropdown" ref={dropdownRef}>
                    <button
                        className="btn-icon"
                        aria-label="User Account"
                        title={user?.name || 'User'}
                        onClick={toggleDropdown}
                    >
                        <i className="fas fa-user-circle" style={{ fontSize: '20px' }}></i>
                    </button>

                    {isDropdownOpen && (
                        <div className="dropdown-menu">
                            <div className="dropdown-user-info">
                                <div className="dropdown-user-name">{user?.name}</div>
                                <div className="dropdown-user-email">{user?.email}</div>
                            </div>
                            <div className="dropdown-divider"></div>
                            <button
                                className="dropdown-item"
                                onClick={handleLogout}
                            >
                                <i className="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
};

export default Header;

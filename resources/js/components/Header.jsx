import { useState, useRef, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';

const Header = () => {
    const { user, logout } = useAuth();
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const dropdownRef = useRef(null);
    const [isCopied, setIsCopied] = useState(false);

    // Robust "Copy" function that works on HTTP and HTTPS
    const handleCopyLink = (e) => {
        // 1. Prevent dropdown from closing
        e.preventDefault();
        e.stopPropagation();

        if (!user?.id) return;

        const url = `${window.location.origin}/book/${user.id}`;

        // 2. The "Old School" method - Works everywhere (HTTP & HTTPS)
        // It creates a hidden text box, selects it, and commands browser to copy
        const textArea = document.createElement("textarea");
        textArea.value = url;
        
        // Hide it so user doesn't see it
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            // The magic command
            document.execCommand('copy');
            
            // Success feedback
            setIsCopied(true);
            setTimeout(() => setIsCopied(false), 2000);
        } catch (err) {
            console.error('Copy failed', err);
            alert('Failed to copy');
        }

        // Clean up
        document.body.removeChild(textArea);
    };
    
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
                            {/* User Info Section */}
                            <div className="dropdown-user-info">
                                <div className="dropdown-user-name">{user?.name}</div>
                                <div className="dropdown-user-email">{user?.email}</div>
                            </div>
                            
                            <div className="dropdown-divider"></div>

                            {/* --- Copy Link Button --- */}
                            <button 
                                className="dropdown-item" 
                                onClick={handleCopyLink}
                            >
                                {/* Icon changes on success */}
                                <i className={`fas ${isCopied ? 'fa-check text-green-500' : 'fa-link'}`}></i>
                                <span>{isCopied ? 'Link Copied!' : 'Copy Booking Link'}</span>
                            </button>

                            <div className="dropdown-divider"></div>

                            {/* Logout Button */}
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

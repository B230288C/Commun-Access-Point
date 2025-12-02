const Header = () => {
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

                <button
                    className="btn-icon"
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

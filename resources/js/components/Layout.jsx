import { useState } from 'react';
import Header from './Header';
import Sidebar from './Sidebar';

const Layout = ({ children }) => {
    const [sidebarExpanded, setSidebarExpanded] = useState(false);

    return (
        <div className="layout-container">
            <Header />
            <Sidebar
                isExpanded={sidebarExpanded}
                onExpandChange={setSidebarExpanded}
            />
            <main className={`main-content ${sidebarExpanded ? 'ml-48' : 'ml-16'}`}>
                {children}
            </main>
        </div>
    );
};

export default Layout;

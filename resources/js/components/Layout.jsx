import { useState } from 'react';
import Header from './Header';
import Sidebar from './Sidebar';
import MainContent from './MainContent';

const Layout = () => {
    const [sidebarExpanded, setSidebarExpanded] = useState(false);
    const [activeView, setActiveView] = useState('dashboard');

    const handleNavigate = (viewId) => {
        setActiveView(viewId);
    };

    return (
        <div className="layout-container">
            <Header />
            <Sidebar
                isExpanded={sidebarExpanded}
                onExpandChange={setSidebarExpanded}
                activeView={activeView}
                onNavigate={handleNavigate}
            />
            <main className={`main-content ${sidebarExpanded ? 'ml-48' : 'ml-16'}`}>
                <MainContent activeView={activeView} />
            </main>
        </div>
    );
};

export default Layout;

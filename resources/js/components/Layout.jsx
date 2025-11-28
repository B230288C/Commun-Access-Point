import React from 'react';
import Header from './Header';
import Sidebar from './Sidebar';

const Layout = ({ children }) => {
    return (
        <div className="min-h-screen bg-[#FAFAFA]">
            {/* Header */}
            <Header />

            <div className="flex">
                {/* Sidebar */}
                <Sidebar />

                {/* Main Content Area */}
                <main className="flex-1 ml-16 p-6">
                    {children}
                </main>
            </div>
        </div>
    );
};

export default Layout;

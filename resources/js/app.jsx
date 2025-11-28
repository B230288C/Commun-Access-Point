import React from 'react';
import { createRoot } from 'react-dom/client';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Layout from './components/Layout';

function App() {
    return (
        <Layout>
            <div className="flex items-center justify-center h-96">
                <div className="text-center">
                    <h2 className="text-xl font-semibold text-[#1F1F1F] mb-2">
                        Calendar Content Area
                    </h2>
                    <p className="text-[#6D6D6D]">
                        React Big Calendar will be integrated here
                    </p>
                </div>
            </div>
        </Layout>
    );
}

createRoot(document.getElementById('app')).render(<App />);

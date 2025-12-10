import { createRoot } from 'react-dom/client';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Layout from './components/Layout';
import MainContent from './components/MainContent';
import Login from './components/Login';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import '../css/app.css';
import '../css/calendar.css';
import '../css/login.css';

function AppContent() {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100vh',
                fontSize: '16px',
                color: '#6D6D6D'
            }}>
                Loading...
            </div>
        );
    }

    if (!user) {
        return <Login />;
    }

    return (
        <Layout>
            <MainContent />
        </Layout>
    );
}

function App() {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
}

createRoot(document.getElementById('display')).render(<App />);

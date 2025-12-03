import { createRoot } from 'react-dom/client';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Layout from './components/Layout';
import MainContent from './components/MainContent';
import '../css/app.css';
import '../css/calendar.css';

function App() {
    return (
        <Layout>
            <MainContent />
        </Layout>
    );
}

createRoot(document.getElementById('display')).render(<App />);

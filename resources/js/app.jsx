import { createRoot } from 'react-dom/client';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Layout from './components/Layout';
import '../css/app.css';

function App() {
    return (
        <Layout>
        </Layout>
    );
}

createRoot(document.getElementById('display')).render(<App />);

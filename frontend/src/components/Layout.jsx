import { Outlet, Navigate, Link, useNavigate } from 'react-router-dom';
import './Layout.css';

export default function Layout() {
    const navigate = useNavigate();
    const token = localStorage.getItem('token');
    
    // Kalau nggak ada token, tendang ke login
    if (!token) return <Navigate to="/login" replace />;

    const user = JSON.parse(localStorage.getItem('user'));
    const roles = JSON.parse(localStorage.getItem('roles'));

    const handleLogout = () => {
        localStorage.clear();
        navigate('/login');
    };

    return (
        <div className="layout-app">
            <aside className="sidebar">
                <div className="brand">SMK BOPKRI 2</div>
                <nav>
                    <Link to="/dashboard">Dashboard</Link>
                    <Link to="/keuangan/penerimaan">Catat Penerimaan</Link>
                    {/* Tambah menu lain di sini */}
                </nav>
            </aside>
            <div className="main-content">
                <header className="topbar">
                    <div>Halo, <strong>{user?.NAMA_KARYAWAN}</strong> ({roles?.join(', ')})</div>
                    <button onClick={handleLogout} className="btn-logout">Logout</button>
                </header>
                <div className="page-content">
                    <Outlet /> {/* Isi halaman bakal gonta-ganti di sini */}
                </div>
            </div>
        </div>
    );
}
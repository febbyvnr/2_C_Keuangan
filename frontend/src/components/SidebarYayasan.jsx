import "../styles/yayasan/SidebarYayasan.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink, useLocation, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";

export default function SidebarYayasan() {
    const location = useLocation();
    const navigate = useNavigate();

    const [isOpen, setIsOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);
    const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);

    const [userData, setUserData] = useState({
        nama: "Loading...",
        email: "Loading..."
    });

    useEffect(() => {
        const handleResize = () => {
            setIsMobile(window.innerWidth <= 768);
            if (window.innerWidth > 768) setIsOpen(false);
        };
        window.addEventListener("resize", handleResize);

        const fetchUserData = async () => {
            try {
                const storedUser = JSON.parse(localStorage.getItem('user'));
                const token = localStorage.getItem('token');

                if (storedUser && storedUser.NIP_KARYAWAN) {
                    const response = await fetch(`http://localhost:8000/api/karyawan/${storedUser.NIP_KARYAWAN}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        setUserData({
                            nama: result.data.NAMA_KARYAWAN,
                            email: result.data.EMAIL_KARYAWAN
                        });
                    }
                }
            } catch (error) {
                console.error("Gagal mengambil data profil:", error);
                setUserData({ nama: "User", email: "user@mail.com" });
            }
        };

        fetchUserData();
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    const confirmLogout = async () => {
        try {
            const token = localStorage.getItem('token'); 
            await fetch('http://localhost:8000/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        } catch (error) {
            console.error("Gagal lapor logout ke backend:", error);
        } finally {
            localStorage.clear(); 
            setShowLogoutConfirm(false);
            navigate("/login");   
        }
    };

    return (
        <>
            {showLogoutConfirm && (
                <div style={styles.modalOverlay}>
                    <div style={styles.modalBox}>
                        <h4 style={{ margin: "0 0 10px 0", color: "#333" }}>Konfirmasi Keluar</h4>
                        <p style={{ margin: "0 0 20px 0", color: "#666" }}>Apakah Anda yakin ingin logout dari sistem?</p>
                        <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px" }}>
                            <button onClick={() => setShowLogoutConfirm(false)} style={styles.btnTidak}>Tidak</button>
                            <button onClick={confirmLogout} style={styles.btnIya}>Iya</button>
                        </div>
                    </div>
                </div>
            )}
            {isMobile && isOpen && (
                <div className="sidebar-overlay" onClick={() => setIsOpen(false)}></div>
            )}
            <div className={`sidebar-container ${isMobile && isOpen ? "active" : ""} ${isCollapsed && !isMobile ? "collapsed" : ""}`}>
                {(isMobile || !isCollapsed) && (
                    <>
                        <div className="atas">
                            <div className="sidebar-header mb-4">
                                <div className="sidebar-logo">
                                    <img src={logo} alt="logo"/> 
                                </div>
                                <div className="header-text">
                                    <div className="sidebar-title">SIBOKU</div>
                                    <div className="sidebar-subtitle">Ruang Yayasan</div>
                                </div>
                                <button className="hamburger-btn inside" onClick={() => isMobile ? setIsOpen(false) : setIsCollapsed(true)}>
                                    <i className="bi bi-list"></i>
                                </button>
                            </div>
                            <div className="user-account">
                                <div className="user-profile">
                                    <img src={profile} alt="profile" />
                                </div>
                                <div className="user-info">
                                    <div className="user-role" style={{ fontWeight: 'bold' }}>{userData.nama}</div>
                                    <div className="user-email" style={{ fontSize: '12px' }}>{userData.email}</div>
                                </div>
                            </div>
                        </div>
                        <ul className="nav flex-column sidebar-menu">
                            <li className="nav-item">
                                <NavLink to="/yayasan/dashboard" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-columns-gap"></i>Dashboard
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/yayasan/approval" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-journal-check"></i>Approval Center
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/yayasan/monitoring" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-bar-chart-line"></i>Monitoring
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/yayasan/laporan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-file-earmark"></i>Laporan
                                </NavLink>
                            </li>
                        </ul>
                        <div className="logout">
                            <div className="logout-button">
                                <button className="btn-logout" onClick={() => setShowLogoutConfirm(true)}>
                                    <i className="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </button>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </>
    );
}
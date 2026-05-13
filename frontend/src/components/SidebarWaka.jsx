import "../styles/bendahara/SidebarBendahara.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink, useLocation, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";

export default function SidebarWaka() {
    const location = useLocation();
    const navigate = useNavigate();

    const [openMaster, setOpenMaster] = useState(false);
    const isMasterActive = location.pathname.startsWith("/bendahara/master");
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

        const fetchUserProfile = async () => {
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
                console.error("Gagal mengambil profil:", error);
                setUserData({ nama: "Waka", email: "waka@gmail.com" });
            }
        };

        fetchUserProfile();
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    useEffect(() => {
        if (isMasterActive) setOpenMaster(true);
    }, [isMasterActive]);

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
            console.error("Logout gagal:", error);
        } finally {
            localStorage.clear(); 
            setShowLogoutConfirm(false);
            navigate("/login");   
        }
    };

    return (
        <>
            {/* --- MODAL LOGOUT --- */}
            {showLogoutConfirm && (
                <div style={styles.modalOverlay}>
                    <div style={styles.modalBox}>
                        <h4 style={{ margin: "0 0 10px 0", color: "#333" }}>Konfirmasi Keluar</h4>
                        <p style={{ margin: "0 0 20px 0", color: "#666" }}>Apakah Anda yakin ingin logout?</p>
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

            {!isMobile && isCollapsed && (
                <button
                    className="hamburger-btn outside"
                    onClick={() => setIsCollapsed(false)}
                >
                    <i className="bi bi-list"></i>
                </button>
            )}

            <div className={`sidebar-container ${isMobile && isOpen ? "active" : ""} ${isCollapsed && !isMobile ? "collapsed" : ""}`}>
                {(isMobile || !isCollapsed) && (
                    <>
                        <div className="atas">
                            <div className="sidebar-header mb-4">
                                <div className="sidebar-logo"><img src={logo} alt="logo"/></div>
                                <div className="header-text">
                                    <div className="sidebar-title">SIBOKU</div>
                                    <div className="sidebar-subtitle">Ruang Waka</div>
                                </div>
                                <button className="hamburger-btn inside" onClick={() => isMobile ? setIsOpen(false) : setIsCollapsed(true)}>
                                    <i className="bi bi-list"></i>
                                </button>
                            </div>

                            {/* --- USER ACCOUNT DINAMIS --- */}
                            <div className="user-account">
                                <div className="user-profile">
                                    <img src={profile} alt="profile" />
                                </div>
                                <div className="user-info">
                                    <div className="user-role" style={{ fontWeight: 'bold' }}>{userData.nama}</div>
                                    <div className="user-email" style={{ fontSize: '11px' }}>{userData.email}</div>
                                </div>
                            </div>
                        </div>

                        <ul className="nav flex-column sidebar-menu">
                            <li className="nav-item">
                                <NavLink to="/waka/dashboard" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-columns-gap"></i>Dashboard
                                </NavLink>
                            </li>
                            {/* <li className="nav-item">
                                <NavLink to="/waka/rka" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-cash-coin"></i>Verifikasi RKA
                                </NavLink>
                            </li> */}
                            {/* <li className="nav-item">
                                <NavLink to="/waka/fpd" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-cash-stack"></i>FPD
                                </NavLink>
                            </li> */}
                            {/* <li className="nav-item">
                                <NavLink to="/waka/evaluasi-rkt" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-clipboard-data"></i>Evaluasi RKT
                                </NavLink>
                            </li> */}
                            <li className="nav-item">
                                <NavLink to="/waka/approval-center" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-check2-square"></i>Verifikasi FPD
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

const styles = {
    modalOverlay: {
        position: "fixed", top: 0, left: 0, right: 0, bottom: 0,
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        display: "flex", justifyContent: "center", alignItems: "center",
        zIndex: 9999 
    },
    modalBox: {
        backgroundColor: "white", padding: "20px 25px",
        borderRadius: "8px", boxShadow: "0 4px 10px rgba(0,0,0,0.2)",
        maxWidth: "400px", width: "90%", textAlign: "left",
    },
    btnTidak: {
        backgroundColor: "#dc2626", color: "white",
        border: "none", padding: "8px 16px", borderRadius: "4px",
        cursor: "pointer", fontWeight: "bold"
    },
    btnIya: {
        backgroundColor: "#0d6efd", color: "white",
        border: "none", padding: "8px 16px", borderRadius: "4px",
        cursor: "pointer", fontWeight: "bold"
    }
};

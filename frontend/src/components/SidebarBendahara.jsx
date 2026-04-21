import "../styles/bendahara/SidebarBendahara.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink, useLocation, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";

export default function SidebarBendahara() {
    const location = useLocation();
    const navigate = useNavigate();

    const [openMaster, setOpenMaster] = useState(false);
    const isMasterActive = location.pathname.startsWith("/bendahara/master");
    const [isOpen, setIsOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);
    
    // TAMBAHAN: State untuk nampilin pop-up konfirmasi logout
    const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            setIsMobile(window.innerWidth <= 768);
            if (window.innerWidth > 768) {
                setIsOpen(false);
            }
        };
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, []);

    useEffect(() => {
        if (isMasterActive) {
            setOpenMaster(true);
        }
    }, [isMasterActive]);

    const confirmLogout = async () => {
        try {
            // 1. Ambil token dari localStorage (sesuaikan jika nama key-mu beda, misal 'access_token')
            const token = localStorage.getItem('token'); 
            
            // 2. Tembak API logout ke Laravel supaya waktu logout dicatat
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
            // 3. Apapun hasilnya (sukses/gagal tembak API), bersihkan sesi dan redirect
            localStorage.clear(); 
            setShowLogoutConfirm(false);
            navigate("/login");   
        }
    };

    return (
        <>
            {/* --- POP-UP KONFIRMASI LOGOUT --- */}
            {showLogoutConfirm && (
                <div style={styles.modalOverlay}>
                    <div style={styles.modalBox}>
                        <h4 style={{ margin: "0 0 10px 0", color: "#333" }}>Konfirmasi Keluar</h4>
                        <p style={{ margin: "0 0 20px 0", color: "#666" }}>Apakah Anda yakin ingin logout dari sistem?</p>
                        <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px" }}>
                            {/* Tombol TIDAK (Warna Merah) */}
                            <button 
                                onClick={() => setShowLogoutConfirm(false)} 
                                style={styles.btnTidak}
                            >
                                Tidak
                            </button>
                            {/* Tombol IYA */}
                            <button 
                                onClick={confirmLogout} 
                                style={styles.btnIya}
                            >
                                Iya
                            </button>
                        </div>
                    </div>
                </div>
            )}
            {/* -------------------------------- */}

            {isMobile && isOpen && (
                <div 
                    className="sidebar-overlay" 
                    onClick={() => setIsOpen(false)}
                ></div>
            )}
            {(isCollapsed || isMobile) && !isOpen && (
                <button 
                    className="hamburger-btn floating-global"
                    onClick={() => {
                        if (isMobile) {
                            setIsOpen(true);
                        } else {
                            setIsCollapsed(false);
                        }
                    }}
                >
                    <i className="bi bi-list"></i>
                </button>
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
                                    <div className="sidebar-subtitle">Ruang Bendahara</div>
                                </div>
                                <button 
                                    className="hamburger-btn inside"
                                    onClick={() => {
                                        if (isMobile) {
                                            setIsOpen(false);
                                        } else {
                                            setIsCollapsed(true);
                                        }
                                    }}
                                >
                                    <i className="bi bi-list"></i>
                                </button>
                            </div>
                            <div className="user-account">
                                <div className="user-profile">
                                    <img src={profile} alt="profile" />
                                </div>
                                <div className="user-info">
                                    <div className="user-role">Bendahara</div>
                                    <div className="user-email">bendahara@gmail.com</div>
                                </div>
                            </div>
                        </div>
                        <ul className="nav flex-column sidebar-menu">
                            <li className="nav-item">
                                <NavLink to="/bendahara/dashboard" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-columns-gap"></i>Dashboard
                                </NavLink>
                            </li>
                            <li 
                                className="nav-item"
                                onMouseEnter={() => setOpenMaster(true)}
                                onMouseLeave={() => !isMasterActive && setOpenMaster(false)}
                            >
                                <div className={`nav-link text-dark master-menu ${isMasterActive ? "sidebar-active" : ""}`}>
                                    <i className="bi bi-database"></i>
                                    Master Data
                                    <i className={`bi bi-chevron-right ms-auto transition-transform ${openMaster ? 'rotate-90' : ''}`} style={{ fontSize: '10px' }}></i>
                                </div>
                                {openMaster && (
                                    <ul className="submenu">
                                        <li><NavLink to="/bendahara/master/coa" className="nav-link">Master COA</NavLink></li>
                                        <li><NavLink to="/bendahara/master/kegiatan" className="nav-link">Master Kegiatan</NavLink></li>
                                        <li><NavLink to="/bendahara/master/tahun-anggaran" className="nav-link">Master Tahun Anggaran</NavLink></li>
                                        <li><NavLink to="/bendahara/master/tahun-akademik" className="nav-link">Master Tahun Akademik</NavLink></li>
                                        <li><NavLink to="/bendahara/master/sumber-dana" className="nav-link">Master Sumber Dana</NavLink></li>
                                        <li><NavLink to="/bendahara/master/ref-penerimaan" className="nav-link">Master Ref Penerimaan</NavLink></li>
                                        <li><NavLink to="/bendahara/master/tarif" className="nav-link">Master Tarif</NavLink></li>
                                        <li><NavLink to="/bendahara/master/jenis-tarif" className="nav-link">Master Jenis Tarif</NavLink></li>
                                        <li><NavLink to="/bendahara/master/jenis-pembayaran" className="nav-link">Master Jenis Pembayaran</NavLink></li>
                                    </ul>
                                )}
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bendahara/penerimaan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-wallet2"></i>Penerimaan
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bendahara/tagihan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-receipt"></i>Tagihan Siswa
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bendahara/verifikasi" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-file-check"></i>Verifikasi Pembayaran
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bendahara/laporan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-file-earmark"></i>Laporan Keuangan
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bendahara/log" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    <i className="bi bi-clock-history"></i>Log Aktivitas
                                </NavLink>
                            </li>
                        </ul>
                        <div className="logout">
                            <div className="logout-button">
                                {/* UBAHAN: Tombol ini sekarang cuma nampilin pop-up, bukan langsung logout */}
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
    )
}

// Objek gaya khusus buat Pop-up biar rapi tanpa perlu edit file CSS lagi
const styles = {
    modalOverlay: {
        position: "fixed", top: 0, left: 0, right: 0, bottom: 0,
        backgroundColor: "rgba(0, 0, 0, 0.5)",
        display: "flex", justifyContent: "center", alignItems: "center",
        zIndex: 9999 // Pastikan muncul paling depan
    },
    modalBox: {
        backgroundColor: "white", padding: "20px 25px",
        borderRadius: "8px", boxShadow: "0 4px 10px rgba(0,0,0,0.2)",
        maxWidth: "400px", width: "90%", textAlign: "left",
        fontFamily: "sans-serif"
    },
    btnTidak: {
        backgroundColor: "#dc2626", color: "white", // Merah
        border: "none", padding: "8px 16px", borderRadius: "4px",
        cursor: "pointer", fontWeight: "bold"
    },
    btnIya: {
        backgroundColor: "#0d6efd", color: "white", // Biru
        border: "none", padding: "8px 16px", borderRadius: "4px",
        cursor: "pointer", fontWeight: "bold"
    }
};
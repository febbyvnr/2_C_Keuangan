import "../styles/bendahara/SidebarBendahara.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink } from "react-router-dom";
import { useState, useEffect } from "react";

export default function SidebarBendahara() {
    const [openMaster, setOpenMaster] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);

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

    return (
        <>
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
                                onMouseLeave={() => setOpenMaster(false)}
                            >
                                <div className="nav-link text-dark master-menu">
                                    <i className="bi bi-database"></i>Master Data
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
                                <button className="btn-logout">
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
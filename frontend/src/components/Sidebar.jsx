import "../styles/Sidebar.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink } from "react-router-dom";
import { useState, useEffect } from "react";

export default function Sidebar() {
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
                                <NavLink to="/dashboard" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Dashboard
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/persetujuan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Persetujuan & Verifikasi
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/dana" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Pencairan Dana
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/rka" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Page RKA
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bku" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Page BKU
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bkm" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Page BKM
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/bkk" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Page BKK
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/tagihan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Tagihan Siswa
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/tarif" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Master Tarif
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink to="/laporan" className={({isActive}) => isActive ? "nav-link sidebar-active" : "nav-link text-dark"}>
                                    Laporan Keuangan
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
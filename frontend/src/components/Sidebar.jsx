import "../styles/Sidebar.css";
import logo from "../assets/logo.png";
import { NavLink } from "react-router-dom";

export default function Sidebar() {
    return (
        <div className="sidebar-container vh-100 p-3" style={{ width: "220px" }}>
            <div className="sidebar-header mb-4">
                {/* ini maksudnya tu tulisan "SMK" atau kasi logo ya?? */}
                <div className="sidebar-logo">
                    <img src={logo} alt="logo"/> 
                </div>
                <div>
                <div className="sidebar-title">Ruang Bendahara</div>
                    <div className="sidebar-subtitle">
                        SMK BOPKRI 2
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
        </div>
    )
}
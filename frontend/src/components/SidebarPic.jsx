import { NavLink } from "react-router-dom";
import "../styles/pic/SidebarPic.css";

const MENU_ITEMS = [
    { label: "Dashboard" },
    { label: "Page RKT" },
    { label: "Page Realisasi RKT" },
    { label: "Page Bridging RKT" },
    { label: "Page Pengajuan Dana", to: "/pic/guru/fpd" },
    { label: "Page LPJ" },
    { label: "Page Evaluasi RKT" },
];

export default function SidebarPic() {
    return (
        <aside className="pic-sidebar">
            <div className="pic-sidebar-brand">
                <div className="pic-sidebar-badge">SMK</div>
                <div>
                    <strong>Portal Guru</strong>
                    <span>SMK BOPKRI 2</span>
                </div>
            </div>

            <nav className="pic-sidebar-nav" aria-label="Navigasi portal guru">
                {MENU_ITEMS.map((item) =>
                    item.to ? (
                        <NavLink
                            key={item.label}
                            to={item.to}
                            className={({ isActive }) =>
                                `pic-sidebar-item ${isActive ? "active" : ""}`
                            }
                        >
                            {item.label}
                        </NavLink>
                    ) : (
                        <button key={item.label} type="button" className="pic-sidebar-item muted">
                            {item.label}
                        </button>
                    )
                )}
            </nav>
        </aside>
    );
}

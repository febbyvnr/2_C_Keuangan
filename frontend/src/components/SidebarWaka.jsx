import { NavLink } from "react-router-dom";
import "../styles/waka/SidebarWaka.css";

const MENU_ITEMS = [
    { label: "Dashboard" },
    { label: "RKT", to: "/waka/rkt" },
    { label: "Realisasi RKT" },
    { label: "Bridging RKT" },
    { label: "Evaluasi RKT", to: "/waka/evaluasi-rkt" },
];

export default function SidebarWaka() {
    return (
        <aside className="waka-sidebar">
            <div className="waka-sidebar-brand">
                <div className="waka-sidebar-badge">WK</div>
                <div>
                    <strong>Portal Waka</strong>
                    <span>Rencana Kerja</span>
                </div>
            </div>

            <nav className="waka-sidebar-nav" aria-label="Navigasi waka">
                {MENU_ITEMS.map((item) =>
                    item.to ? (
                        <NavLink
                            key={item.label}
                            to={item.to}
                            className={({ isActive }) =>
                                `waka-sidebar-item ${isActive ? "active" : ""}`
                            }
                        >
                            {item.label}
                        </NavLink>
                    ) : (
                        <button key={item.label} type="button" className="waka-sidebar-item muted">
                            {item.label}
                        </button>
                    )
                )}
            </nav>
        </aside>
    );
}

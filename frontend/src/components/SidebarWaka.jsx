import { NavLink } from "react-router-dom";

const MENU_ITEMS = [
    { label: "Dashboard" },
    { label: "RKT", to: "/waka/rkt" },
    { label: "Realisasi RKT" },
    { label: "Bridging RKT" },
    { label: "Evaluasi RKT", to: "/waka/evaluasi-rkt" },
];

const styles = {
    sidebar: {
        padding: "24px 14px",
        background: "#ffffff",
        borderRight: "1px solid #e5edf5",
    },
    brand: {
        display: "flex",
        gap: "10px",
        alignItems: "center",
        padding: "0 6px 18px",
        marginBottom: "22px",
        borderBottom: "1px solid #e8edf4",
    },
    badge: {
        display: "grid",
        placeItems: "center",
        width: "38px",
        height: "38px",
        borderRadius: "10px",
        background: "#1d4ed8",
        color: "#fff",
        fontWeight: 700,
    },
    brandText: {
        display: "block",
    },
    brandSubtext: {
        display: "block",
        color: "#6b7280",
        fontSize: "0.88rem",
    },
    nav: {
        display: "grid",
        gap: "8px",
    },
    item: {
        width: "100%",
        padding: "12px 14px",
        border: 0,
        borderRadius: "12px",
        background: "transparent",
        color: "#334155",
        textAlign: "left",
        textDecoration: "none",
    },
    activeItem: {
        background: "#dbeafe",
        color: "#1d4ed8",
        fontWeight: 700,
        boxShadow: "inset -4px 0 0 #1d4ed8",
    },
    mutedItem: {
        cursor: "default",
        color: "#94a3b8",
    },
};

export default function SidebarWaka() {
    return (
        <aside style={styles.sidebar}>
            <div style={styles.brand}>
                <div style={styles.badge}>WK</div>
                <div>
                    <strong style={styles.brandText}>Portal Waka</strong>
                    <span style={styles.brandSubtext}>Rencana Kerja</span>
                </div>
            </div>

            <nav style={styles.nav} aria-label="Navigasi waka">
                {MENU_ITEMS.map((item) =>
                    item.to ? (
                        <NavLink
                            key={item.label}
                            to={item.to}
                            style={({ isActive }) => ({
                                ...styles.item,
                                ...(isActive ? styles.activeItem : {}),
                            })}
                        >
                            {item.label}
                        </NavLink>
                    ) : (
                        <button key={item.label} type="button" style={{ ...styles.item, ...styles.mutedItem }}>
                            {item.label}
                        </button>
                    )
                )}
            </nav>
        </aside>
    );
}

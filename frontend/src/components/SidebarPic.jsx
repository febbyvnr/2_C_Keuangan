import { NavLink } from "react-router-dom";

const MENU_ITEMS = [
    { label: "Dashboard" },
    { label: "Page RKT" },
    { label: "Page Realisasi RKT" },
    { label: "Page Bridging RKT" },
    { label: "Page Pengajuan Dana", to: "/pic/guru/fpd" },
    { label: "Page LPJ" },
    { label: "Page Evaluasi RKT" },
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
        width: "36px",
        height: "36px",
        borderRadius: "8px",
        background: "#2f67ad",
        color: "#fff",
        fontSize: "0.9rem",
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
        color: "#374151",
        textAlign: "left",
        fontSize: "0.98rem",
        textDecoration: "none",
    },
    activeItem: {
        background: "#e8f0fb",
        color: "#245b9c",
        fontWeight: 700,
        boxShadow: "inset -4px 0 0 #2f67ad",
    },
    mutedItem: {
        cursor: "default",
        color: "#94a3b8",
    },
};

export default function SidebarPic() {
    return (
        <aside style={styles.sidebar}>
            <div style={styles.brand}>
                <div style={styles.badge}>SMK</div>
                <div>
                    <strong style={styles.brandText}>Portal Guru</strong>
                    <span style={styles.brandSubtext}>SMK BOPKRI 2</span>
                </div>
            </div>

            <nav style={styles.nav} aria-label="Navigasi portal guru">
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

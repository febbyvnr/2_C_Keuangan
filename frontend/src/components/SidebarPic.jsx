import "../styles/pic/guru/SidebarPic.css";
import logo from "../assets/logo.png";
import profile from "../assets/user-profile.jpg";
import { NavLink, useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";

const MOBILE_BREAKPOINT = 640;

const MENU_ITEMS = [
  { label: "Dashboard", to: "/pic/guru", icon: "bi bi-columns-gap", end: true },
  { label: "Page RKT", to: "/pic/guru/rkt", icon: "bi bi-journal-check" },
  { label: "Page Realisasi RKT", icon: "bi bi-bar-chart-steps" },
  { label: "Page Bridging RKT", icon: "bi bi-diagram-2" },
  { label: "Page Pengajuan Dana", to: "/pic/guru/fpd", icon: "bi bi-cash-coin" },
  { label: "Page LPJ", icon: "bi bi-file-earmark-text" },
  { label: "Page Evaluasi RKT", icon: "bi bi-clipboard2-pulse" },
];

export default function SidebarPic() {
  const navigate = useNavigate();
  const [isOpen, setIsOpen] = useState(false);
  const [isCollapsed, setIsCollapsed] = useState(false);
  const [isMobile, setIsMobile] = useState(window.innerWidth <= MOBILE_BREAKPOINT);
  const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);
  const user = JSON.parse(localStorage.getItem("user") || "{}");
const userName = user.NAMA_KARYAWAN || "PIC Guru";
const userEmail = user.EMAIL_KARYAWAN || user.email || "-";
  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth <= MOBILE_BREAKPOINT);
      if (window.innerWidth > MOBILE_BREAKPOINT) {
        setIsOpen(false);
      }
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const confirmLogout = async () => {
    try {
      const token = localStorage.getItem("token");

      await fetch("http://localhost:8000/api/logout", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
        },
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
            <p style={{ margin: "0 0 20px 0", color: "#666" }}>
              Apakah Anda yakin ingin logout dari sistem?
            </p>
            <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px" }}>
              <button onClick={() => setShowLogoutConfirm(false)} style={styles.btnTidak}>
                Tidak
              </button>
              <button onClick={confirmLogout} style={styles.btnIya}>
                Iya
              </button>
            </div>
          </div>
        </div>
      )}

      {isMobile && isOpen && (
        <div className="sidebar-overlay" onClick={() => setIsOpen(false)}></div>
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

      <div
        className={`pic-sidebar-container ${isMobile && isOpen ? "active" : ""} ${
          isCollapsed && !isMobile ? "collapsed" : ""
        }`}
      >
        {(isMobile || !isCollapsed) && (
          <>
            <div className="atas">
              <div className="sidebar-header mb-4">
                <div className="sidebar-logo">
                  <img src={logo} alt="logo" />
                </div>
                <div className="header-text">
                  <div className="sidebar-title">SIBOKU</div>
                  <div className="sidebar-subtitle">Ruang PIC Guru</div>
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
                  <div className="user-role">{userName}</div>
                  <div className="user-email">{userEmail}</div>
                </div>
              </div>
            </div>

            <ul className="nav flex-column sidebar-menu">
              {MENU_ITEMS.map((item) => (
                <li key={item.label} className="nav-item">
                  {item.to ? (
                    <NavLink
                      to={item.to}
                      end={item.end}
                      className={({ isActive }) =>
                        isActive ? "nav-link sidebar-active" : "nav-link text-dark"
                      }
                    >
                      <i className={item.icon}></i>
                      {item.label}
                    </NavLink>
                  ) : (
                    <button
                      type="button"
                      className="nav-link text-dark"
                      style={{
                        width: "100%",
                        opacity: 0.55,
                        cursor: "default",
                        background: "transparent",
                        border: "none",
                      }}
                    >
                      <i className={item.icon}></i>
                      {item.label}
                    </button>
                  )}
                </li>
              ))}
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
    position: "fixed",
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: "rgba(0, 0, 0, 0.5)",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    zIndex: 9999,
  },
  modalBox: {
    backgroundColor: "white",
    padding: "20px 25px",
    borderRadius: "8px",
    boxShadow: "0 4px 10px rgba(0,0,0,0.2)",
    maxWidth: "400px",
    width: "90%",
    textAlign: "left",
  },
  btnTidak: {
    backgroundColor: "#dc2626",
    color: "white",
    border: "none",
    padding: "8px 16px",
    borderRadius: "4px",
    cursor: "pointer",
    fontWeight: "bold",
  },
  btnIya: {
    backgroundColor: "#0d6efd",
    color: "white",
    border: "none",
    padding: "8px 16px",
    borderRadius: "4px",
    cursor: "pointer",
    fontWeight: "bold",
  },
};
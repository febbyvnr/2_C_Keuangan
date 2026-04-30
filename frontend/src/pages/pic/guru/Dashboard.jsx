import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/Dashboard.css";

export default function DashboardPIC() {
  return (
    <div className="dashboard-wrapper">
      <SidebarPic />

      <main className="waka-container">
        {/* ===== HEADER ===== */}
        <div className="header-card welcome-card">
          <div className="welcome-left">
            <h2 className="waka-title">Selamat Datang</h2>

            <p className="welcome-sub">PIC / Guru</p>

            <p className="welcome-date">
              {new Date().toLocaleDateString("id-ID", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </p>
          </div>
        </div>

        {/* ===== KPI STATUS (4) ===== */}
        <div className="waka-grid">
          <div className="card">
            <div className="card-top">
              <p>Total Program</p>
            </div>
            <h3>5</h3>
          </div>

          <div className="card">
            <div className="card-top">
              <p>Program Aktif</p>
            </div>
            <h3>3</h3>
          </div>

          <div className="card success">
            <div className="card-top">
              <p>Program Selesai</p>
            </div>
            <h3>2</h3>
          </div>

          <div className="card warning">
            <div className="card-top">
              <p>Progress</p>
            </div>
            <h3>40%</h3>
          </div>
        </div>

        {/* ===== AKTIVITAS TERBARU ===== */}
        <div className="main-grid">
          <div className="chart-card">
            <h4>Aktivitas Terbaru</h4>

            <ul style={{ paddingLeft: "16px" }}>
              <li>✔ Update progress Program Ujian</li>
              <li>✔ Input dana Program Lomba</li>
              <li>✔ Upload laporan Program Seminar</li>
            </ul>
          </div>
        </div>

        {/* ===== PROGRAM SAYA (WAJIB) ===== */}
        <div className="chart-card">
          <h4>Program Saya</h4>

          <div style={{ marginTop: "10px" }}>
            <div className="card" style={{ marginBottom: "10px" }}>
              <p>
                <strong>Program Ujian</strong>
              </p>
              <p>Status: Sedang Berjalan</p>
              <p>Progress: 70%</p>
            </div>

            <div className="card" style={{ marginBottom: "10px" }}>
              <p>
                <strong>Program Lomba</strong>
              </p>
              <p>Status: Belum Mulai</p>
              <p>Progress: 0%</p>
            </div>
          </div>
        </div>

        {/* ===== QUICK ACTION ===== */}
        <div className="summary-card">
          <h4>Quick Action</h4>

          <div style={{ display: "flex", gap: "10px", marginTop: "10px" }}>
            <button className="btn-primary">+ Tambah Laporan</button>
            <button className="btn-primary">💰 Input Dana</button>
            <button className="btn-primary">✏ Update Progress</button>
          </div>
        </div>
      </main>
    </div>
  );
}

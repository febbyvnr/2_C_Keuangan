import { useEffect, useState } from "react";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/Dashboard.css";

export default function DashboardPIC() {
  const [program, setProgram] = useState([]);
  const [fpd, setFpd] = useState([]);

  useEffect(() => {
    fetch("http://localhost:8000/api/rkt")
      .then((res) => res.json())
      .then((data) => setProgram(data.data || data || []));

    fetch("http://localhost:8000/api/fpd-anggaran")
      .then((res) => res.json())
      .then((data) => setFpd(data.data || data || []));
  }, []);

  const totalProgram = program.length;
  const programSelesai = program.filter((p) => p.STATUS === "SELESAI").length;

  const programAktif = totalProgram - programSelesai;

  const progress = totalProgram > 0 ? (programSelesai / totalProgram) * 100 : 0;

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
            <h3>{totalProgram}</h3>
          </div>

          <div className="card">
            <div className="card-top">
              <p>Program Aktif</p>
            </div>
            <h3>{programAktif}</h3>
          </div>

          <div className="card success">
            <div className="card-top">
              <p>Program Selesai</p>
            </div>
            <h3>{programSelesai}</h3>
          </div>

          <div className="card warning">
            <div className="card-top">
              <p>Progress</p>
            </div>
            <h3>{progress.toFixed(0)}%</h3>
          </div>
        </div>

        {/* ===== AKTIVITAS TERBARU ===== */}
        <div className="main-grid">
          <div className="chart-card">
            <h4>Aktivitas Terbaru</h4>

            <ul style={{ paddingLeft: "16px" }}>
              {!fpd || fpd.length === 0 ? (
                <li>Belum ada aktivitas</li>
              ) : (
                fpd
                  .slice(0, 5)
                  .map((d, i) => (
                    <li key={i}>✔ Input dana {d.NAMA_PROGRAM || "Program"}</li>
                  ))
              )}
            </ul>
          </div>
        </div>

        {/* ===== PROGRAM SAYA ===== */}
        <div className="chart-card">
          <h4>Program Saya</h4>

          <div style={{ marginTop: "10px" }}>
            {program.length === 0 ? (
              <p>Tidak ada program</p>
            ) : (
              program.map((p) => (
                <div key={p.ID_PROGRAM_KERJA} className="card program-card">
                  <div className="program-header">
                    <strong>{p.PROGRAM_KERJA}</strong>
                    <span className="status">{p.STATUS || "Belum Mulai"}</span>
                  </div>

                  <p>
                    Progress:{" "}
                    <span className="progress-text">{p.PROGRESS || 0}%</span>
                  </p>

                  <div className="progress-bar">
                    <div
                      className="progress-fill"
                      style={{ width: `${p.PROGRESS || 0}%` }}
                    ></div>
                  </div>
                </div>
              ))
            )}
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

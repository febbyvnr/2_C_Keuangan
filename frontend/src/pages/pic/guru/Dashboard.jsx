import { useEffect, useState } from "react";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/Dashboard.css";

import { BsCalendarEvent } from "react-icons/bs";

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

  const getNamaProgram = (id) => {
    const found = program.find((p) => p.ID_PROGRAM_KERJA === id);
    return found ? found.PROGRAM_KERJA : "Program";
  };

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

            <div className="activity-list">
              {!fpd || fpd.length === 0 ? (
                <p className="no-activity">Belum ada aktivitas</p>
              ) : (
                fpd.slice(0, 5).map((d, i) => (
                  <div key={i} className="activity-item">
                    <div className="activity-icon">
                      <BsCalendarEvent />
                    </div>

                    <div className="activity-content">
                      <p className="activity-title">
                        Input dana {getNamaProgram(d.ID_PROGRAM_KERJA)}
                      </p>

                      <span className="activity-date">
                        {d.TGL_FPD
                          ? new Date(d.TGL_FPD).toLocaleDateString("id-ID", {
                              day: "numeric",
                              month: "short",
                              year: "numeric",
                            })
                          : "-"}{" "}
                        •{" "}
                        {d.TGL_FPD
                          ? new Date(d.TGL_FPD).toLocaleTimeString("id-ID", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })
                          : "-"}
                      </span>
                    </div>
                  </div>
                ))
              )}
            </div>
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

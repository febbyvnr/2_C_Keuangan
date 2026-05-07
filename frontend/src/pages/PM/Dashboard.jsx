import { useEffect, useState } from "react";
import "../../styles/PM/Dashboard.css";

export default function DashboardPM() {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("http://localhost:8000/api/dashboardtimpenjaminanmutu")
      .then((res) => res.json())
      .then((data) => {
        setDashboard(data.data);
      })
      .catch((err) => {
        console.error("Gagal mengambil dashboard:", err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <div className="pm-container">
        {/* HEADER */}
        <div className="pm-header-card">
          <div className="pm-header-left">
            <h2 className="pm-title">Selamat Datang</h2>

            <p className="pm-subtitle">Tim Penjaminan Mutu</p>

            <p className="pm-date">
              {new Date().toLocaleDateString("id-ID", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </p>
          </div>
        </div>

        <div className="loading-box">Loading dashboard...</div>
      </div>
    );
  }

  if (!dashboard) {
    return (
      <div className="pm-container">
        {/* HEADER */}
        <div className="pm-header-card">
          <div className="pm-header-left">
            <h2 className="pm-title">Selamat Datang</h2>

            <p className="pm-subtitle">Tim Penjaminan Mutu</p>

            <p className="pm-date">
              {new Date().toLocaleDateString("id-ID", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </p>
          </div>
        </div>

        <div className="loading-box error">Gagal memuat data dashboard</div>
      </div>
    );
  }

  return (
    <div className="pm-container">
      {/* HEADER */}
      <div className="pm-header-card">
        <div className="pm-header-left">
          <h2 className="pm-title">Selamat Datang</h2>

          <p className="pm-subtitle">Tim Penjaminan Mutu</p>

          <p className="pm-date">
            {new Date().toLocaleDateString("id-ID", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
            })}
          </p>
        </div>
      </div>

      {/* KPI SECTION */}
      <div className="pm-kpi">
        <div className="card primary">
          <p>Total Program RKT</p>
          <h3>{dashboard.total_rkt}</h3>
        </div>

        <div className="card success">
          <p>Realisasi Evaluasi</p>
          <h3>{dashboard.realisasi}</h3>
        </div>

        <div className="card warning">
          <p>Total Deviasi</p>
          <h3>{dashboard.deviasi}</h3>
        </div>

        <div className="card info">
          <p>Persentase Capaian</p>
          <h3>{dashboard.persentase_capaian}%</h3>
        </div>
      </div>

      {/* PROGRESS PROGRAM RKT */}
      <div className="pm-progress-section">
        <div className="table-header">
          <h3>Progress RKT</h3>
        </div>

        <div className="pm-progress-wrapper">
          {dashboard.program_rkt && dashboard.program_rkt.length > 0 ? (
            dashboard.program_rkt.map((item, index) => {
              const percentage =
                dashboard.total_rkt > 0
                  ? Math.round((item.realisasi / dashboard.total_rkt) * 100)
                  : 0;

              return (
                <div className="progress-card" key={index}>
                  <div className="progress-header">
                    <h4>{item.PROGRAM_KERJA}</h4>

                    <span className="progress-status">
                      {percentage >= 100 ? "Selesai" : "Monitoring"}
                    </span>
                  </div>

                  <p className="progress-text">
                    Progress:
                    <span className="progress-percent"> {percentage}%</span>
                  </p>

                  <div className="progress-bar">
                    <div
                      className="progress-fill"
                      style={{
                        width: `${percentage}%`,
                        backgroundColor: "#EDA60F",
                      }}
                    ></div>
                  </div>
                </div>
              );
            })
          ) : (
            <div className="empty-data">Tidak ada data RKT</div>
          )}
        </div>
      </div>

      {/* SPACER */}
      <div
        style={{
          minHeight: "40px",
          width: "100%",
        }}
      ></div>
    </div>
  );
}

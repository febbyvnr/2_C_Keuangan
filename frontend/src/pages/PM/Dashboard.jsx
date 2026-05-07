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

      {/* KPI Section */}
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

      {/* PROGRESS MONITORING MUTU */}
      <div className="pm-progress-section">
        <div className="table-header">
          <h3>Progress Monitoring Mutu</h3>
        </div>

        <div className="pm-progress-wrapper">
          {dashboard.rincian_mutu && dashboard.rincian_mutu.length > 0 ? (
            dashboard.rincian_mutu.map((item, index) => {
              const percentage =
                dashboard.total_rkt > 0
                  ? Math.round((item.jumlah / dashboard.total_rkt) * 100)
                  : 0;

              return (
                <div className="progress-card" key={index}>
                  <div className="progress-header">
                    <h4>
                      {item.kategori
                        .toLowerCase()
                        .replace(/\b\w/g, (c) => c.toUpperCase())}
                    </h4>

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
            <div className="empty-data">Tidak ada data monitoring mutu</div>
          )}
        </div>
      </div>

      {/* ANALISIS MONITORING */}
      <div className="pm-summary">
        <h3>Analisis Monitoring Mutu</h3>

        <div className="summary-grid">
          <div className="summary-box">
            <h4>Realisasi Program</h4>

            <p>
              Sebanyak
              <strong> {dashboard.realisasi}</strong>
              program telah terealisasi dari total
              <strong> {dashboard.total_rkt}</strong>
              program kerja RKT.
            </p>
          </div>

          <div className="summary-box">
            <h4>Deviasi Kegiatan</h4>

            <p>
              Terdapat
              <strong> {dashboard.deviasi}</strong>
              deviasi yang memerlukan monitoring lanjutan.
            </p>
          </div>

          <div className="summary-box">
            <h4>Persentase Capaian</h4>

            <p>
              Tingkat capaian program saat ini mencapai
              <strong> {dashboard.persentase_capaian}%</strong>.
            </p>
          </div>
        </div>
      </div>

      {/* Spacer bawah agar tidak terpotong saat scroll mentok */}
      <div style={{ minHeight: "40px", width: "100%" }}></div>
    </div>
  );
}

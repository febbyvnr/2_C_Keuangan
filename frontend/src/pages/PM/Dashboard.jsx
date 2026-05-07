import { useEffect, useState } from "react";
import "../../styles/PM/Dashboard.css";

export default function DashboardPM() {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("http://localhost:8000/api/dashboard-penjaminan-mutu")
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
        <h2 className="pm-title">Dashboard Penjaminan Mutu</h2>

        <div className="loading-box">Loading dashboard...</div>
      </div>
    );
  }

  if (!dashboard) {
    return (
      <div className="pm-container">
        <h2 className="pm-title">Dashboard Penjaminan Mutu</h2>

        <div className="loading-box error">Gagal memuat data dashboard</div>
      </div>
    );
  }

  return (
    <div className="pm-container">
      <h2 className="pm-title">Dashboard Penjaminan Mutu</h2>

      {/* KPI */}
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

      {/* TABLE RINCIAN MUTU */}
      <div className="pm-table">
        <div className="table-header">
          <h3>Rincian Monitoring Mutu</h3>
        </div>

        <table>
          <thead>
            <tr>
              <th>Kategori Mutu</th>
              <th>Jumlah Program</th>
            </tr>
          </thead>

          <tbody>
            {dashboard.rincian_mutu && dashboard.rincian_mutu.length > 0 ? (
              dashboard.rincian_mutu.map((item, index) => (
                <tr key={index}>
                  <td>{item.kategori}</td>
                  <td>{item.jumlah}</td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="2" className="empty-data">
                  Tidak ada data monitoring mutu
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* SUMMARY */}
      <div className="pm-summary">
        <h3>Ringkasan Penjaminan Mutu</h3>

        <div className="summary-content">
          <p>
            Dashboard ini digunakan untuk monitoring mutu sekolah berdasarkan
            pelaksanaan Program Kerja RKT.
          </p>

          <ul>
            <li>
              Total Program Kerja :<strong> {dashboard.total_rkt}</strong>
            </li>

            <li>
              Realisasi Evaluasi :<strong> {dashboard.realisasi}</strong>
            </li>

            <li>
              Total Deviasi :<strong> {dashboard.deviasi}</strong>
            </li>

            <li>
              Persentase Capaian :
              <strong> {dashboard.persentase_capaian}%</strong>
            </li>
          </ul>
        </div>
      </div>
    </div>
  );
}

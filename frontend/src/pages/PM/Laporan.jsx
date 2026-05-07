import { useState, useEffect } from "react";
import "../../styles/PM/Laporan.css";
import "bootstrap-icons/font/bootstrap-icons.css";

export default function LaporanPM() {
  const tabs = ["Monitoring Mutu", "Evaluasi RKT", "Rekapitulasi RKT"];
  const [active, setActive] = useState("Monitoring Mutu");
  const [data, setData] = useState([]);
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(false);

  const [page, setPage] = useState(1);
  const perPage = 10;

  const loadData = () => {
    setLoading(true);
    let url = "";
    if (active === "Monitoring Mutu") {
      url = "http://localhost:8000/api/rkt";
    } else if (active === "Evaluasi RKT") {
      url = "http://localhost:8000/api/evaluasi-rkt";
    } else if (active === "Rekapitulasi RKT") {
      url = "http://localhost:8000/api/dashboardtimpenjaminanmutu";
    }

    fetch(url, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    })
      .then((res) => res.json())
      .then((res) => {
        if (active === "Rekapitulasi RKT") {
          setDashboard(res.summary);
          setData(res.data || []);
        } else {
          setData(res.data || []);
        }
      })
      .catch((err) => console.error("Error loading data:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
    setPage(1);
  }, [active]);

  const handleExportExcel = () => {
    let baseUrl = "";
    if (active === "Monitoring Mutu") {
      baseUrl = "http://localhost:8000/api/rkt/export/excel";
    } else if (active === "Evaluasi RKT") {
      baseUrl = "http://localhost:8000/api/evaluasi-rkt/export/excel";
    } else if (active === "Rekapitulasi RKT") {
      baseUrl = "http://localhost:8000/api/ref-pm/export/excel";
    }
    if (baseUrl) window.open(baseUrl, "_blank");
  };

  const handleExportPDF = () => {
    let baseUrl = "";
    if (active === "Monitoring Mutu") {
      baseUrl = "http://localhost:8000/api/rkt/export/pdf";
    } else if (active === "Evaluasi RKT") {
      baseUrl = "http://localhost:8000/api/evaluasi-rkt/export/pdf";
    } else if (active === "Rekapitulasi RKT") {
      baseUrl = "http://localhost:8000/api/ref-pm/export/pdf";
    }
    if (baseUrl) window.open(baseUrl, "_blank");
  };

  // Pagination logic
  const startIndex = (page - 1) * perPage;
  const currentData = data.slice(startIndex, startIndex + perPage);
  const totalPage = Math.ceil(data.length / perPage);

  return (
    <div className="pm-laporan-container">
      <h2>Laporan Tim PM</h2>

      <div className="laporan-tabs">
        {tabs.map((tab) => (
          <div
            key={tab}
            className={`laporan-tab ${active === tab ? "active" : ""}`}
            onClick={() => setActive(tab)}
          >
            {tab}
          </div>
        ))}
      </div>

      <div className="laporan-header">
        <div className="laporan-actions">
          <button className="btn-outline excel" onClick={handleExportExcel}>
            <i className="bi bi-file-earmark-excel"></i> Export Excel
          </button>
          <button className="btn-outline pdf" onClick={handleExportPDF}>
            <i className="bi bi-file-earmark-pdf"></i> Export PDF
          </button>
        </div>
      </div>


      <div className="laporan-table">
        <table>
          <thead>
            {active === "Monitoring Mutu" && (
              <tr>
                <th>No</th>
                <th>Program Kerja</th>
                <th>Penanggung Jawab</th>
                <th>Target</th>
                <th>Status</th>
              </tr>
            )}
            {active === "Evaluasi RKT" && (
              <tr>
                <th>No</th>
                <th>Program Kerja</th>
                <th>Kategori Mutu</th>
                <th>Hasil Evaluasi</th>
                <th>Tanggal</th>
              </tr>
            )}
            {active === "Rekapitulasi RKT" && (
              <tr>
                <th>No</th>
                <th>Program Kerja</th>
                <th>Target Indikator</th>
                <th>Sasaran</th>
                <th>Pagu Anggaran</th>
                <th>Realisasi</th>
                <th>Evaluasi</th>
              </tr>
            )}
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan="5" style={{ textAlign: "center" }}>Loading...</td>
              </tr>
            ) : currentData.length > 0 ? (
              currentData.map((item, index) => (
                <tr key={index}>
                  <td>{startIndex + index + 1}</td>
                  {active === "Monitoring Mutu" && (
                    <>
                      <td>{item.PROGRAM_KERJA}</td>
                      <td>{item.NAMA_VALIDATOR || "-"}</td>
                      <td>{item.TARGET || "-"}</td>
                      <td>
                        <span className={`status-badge ${(item.tr_pm?.[item.tr_pm.length - 1]?.DESKRIPSI_TR_PM || "Pending").toLowerCase().includes("setuju") ? "completed" : "process"}`}>
                          {item.tr_pm?.[item.tr_pm.length - 1]?.DESKRIPSI_TR_PM || "Pending"}
                        </span>
                      </td>
                    </>
                  )}
                  {active === "Evaluasi RKT" && (
                    <>
                      <td>{item.program_kerja?.PROGRAM_KERJA || "-"}</td>
                      <td>{item.ref_pm?.NAMA_PM || "-"}</td>
                      <td>{item.DESKRIPSI_TR_PM || "-"}</td>
                      <td>{item.TGL_PM ? new Date(item.TGL_PM).toLocaleDateString("id-ID") : "-"}</td>
                    </>
                  )}
                  {active === "Rekapitulasi RKT" && (
                    <>
                      <td>{item.program_kerja}</td>
                      <td>{item.target_indikator}</td>
                      <td>{item.sasaran}</td>
                      <td className="nominal">Rp {Number(item.pagu_anggaran || 0).toLocaleString("id-ID")}</td>
                      <td>{item.realisasi_teks}</td>
                      <td>{item.evaluasi_teks}</td>
                    </>
                  )}
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="5" style={{ textAlign: "center" }}>Tidak ada data</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="laporan-footer">
        <div className="laporan-info">
          Menampilkan {data.length === 0 ? 0 : startIndex + 1} - {Math.min(startIndex + perPage, data.length)} dari {data.length} data
        </div>
        <div className="laporan-pagination">
          <button className="page-btn" onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}>&lt;</button>
          {[...Array(totalPage)].map((_, i) => (
            <button key={i} className={`page-btn ${page === i + 1 ? "active" : ""}`} onClick={() => setPage(i + 1)}>{i + 1}</button>
          ))}
          <button className="page-btn" onClick={() => setPage(p => Math.min(totalPage, p + 1))} disabled={page === totalPage}>&gt;</button>
        </div>
      </div>
    </div>
  );
}

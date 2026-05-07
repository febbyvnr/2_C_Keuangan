import { useState, useEffect } from "react";
import "../../styles/kepsek/Laporan.css"; 
import "bootstrap-icons/font/bootstrap-icons.css";

export default function LaporanKepsek() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU"];
  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);
  const [total, setTotal] = useState(0);
  
  // Filter States
  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const [tahun, setTahun] = useState("2026");
  const [page, setPage] = useState(1);
  const perPage = 10;

  const loadData = () => {
    let baseUrl = "";
    // Menyesuaikan endpoint API (Asumsi endpoint sama dengan bendahara namun bisa dibedakan jika perlu)
    if (active === "Penerimaan") baseUrl = "http://localhost:8000/api/laporan/penerimaan";
    else if (active === "Pengeluaran") baseUrl = "http://localhost:8000/api/laporan/pengeluaran";
    else if (active === "BKU") baseUrl = "http://localhost:8000/api/laporan/bku";
    else if (active === "RKAS") baseUrl = "http://localhost:8000/api/laporan/rkas";

    const params = new URLSearchParams();
    params.append("tahun", tahun);
    if (start) params.append("start", start);
    if (end) params.append("end", end);

    fetch(`${baseUrl}?${params.toString()}`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    })
      .then((res) => res.json())
      .then((res) => {
        // Logika penarikan data sesuai struktur respons API bendahara
        let result = res.data || res.bku || [];
        setData(result);
        setTotal(res.total || 0);
        setPage(1);
      })
      .catch((err) => {
        console.error("Fetch Error:", err);
        setData([]);
      });
  };

  useEffect(() => {
    loadData();
  }, [active, tahun]);

  // Pagination Logic
  const startIndex = (page - 1) * perPage;
  const currentData = data.slice(startIndex, startIndex + perPage);
  const totalPage = Math.ceil(data.length / perPage);

  return (
    <div className="laporan-kepsek-container" style={{ padding: "30px" }}>
      <div className="header-box">
        <h2>Laporan Kepala Sekolah</h2>
        <p className="subtitle">Monitoring Keuangan & RKT SMK BOPKRI 2 YOGYAKARTA</p>
      </div>

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
          <button className="btn-outline pdf">
            <i className="bi bi-printer"></i> Cetak Laporan
          </button>
        </div>

        <div className="laporan-filter">
          <input type="date" value={start} onChange={(e) => setStart(e.target.value)} />
          <input type="date" value={end} onChange={(e) => setEnd(e.target.value)} />
          <select value={tahun} onChange={(e) => setTahun(e.target.value)}>
            <option value="2026">Tahun 2026</option>
            <option value="2025">Tahun 2025</option>
          </select>
          <button className="btn btn-primary" onClick={loadData}>Filter</button>
        </div>
      </div>

      <div className="laporan-table">
        <table>
          <thead>
            {active === "Penerimaan" || active === "Pengeluaran" ? (
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>{active === "Penerimaan" ? "Sumber" : "Program (PM)"}</th>
                <th>Uraian</th>
                <th className="nominal">Nominal</th>
              </tr>
            ) : active === "RKAS" ? (
              <tr>
                <th>No</th>
                <th>Program Kerja</th>
                <th>Sumber Dana</th>
                <th className="nominal">Anggaran</th>
              </tr>
            ) : (
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Uraian</th>
                <th className="nominal">Debit</th>
                <th className="nominal">Kredit</th>
                <th className="nominal">Saldo</th>
              </tr>
            )}
          </thead>
          <tbody>
            {currentData.length > 0 ? (
              currentData.map((item, i) => (
                <tr key={i}>
                  <td>{startIndex + i + 1}</td>
                  {/* Kondisi kolom sesuai Tab aktif */}
                  {(active === "Penerimaan" || active === "Pengeluaran") && (
                    <>
                      <td>{new Date(item.tanggal).toLocaleDateString("id-ID")}</td>
                      <td>{item.NAMA_PM || item.jenis}</td>
                      <td>{item.uraian}</td>
                      <td className="nominal">Rp {Number(item.jumlah || item.nominal).toLocaleString("id-ID")}</td>
                    </>
                  )}
                  {active === "RKAS" && (
                    <>
                      <td>{item.program_kerja}</td>
                      <td>{item.sumber_dana}</td>
                      <td className="nominal">Rp {Number(item.anggaran_disetujui).toLocaleString("id-ID")}</td>
                    </>
                  )}
                  {active === "BKU" && (
                    <>
                      <td>{new Date(item.tanggal).toLocaleDateString("id-ID")}</td>
                      <td>{item.uraian}</td>
                      <td className="nominal">Rp {Number(item.debit || 0).toLocaleString("id-ID")}</td>
                      <td className="nominal">Rp {Number(item.kredit || 0).toLocaleString("id-ID")}</td>
                      <td className="nominal">Rp {Number(item.saldo || 0).toLocaleString("id-ID")}</td>
                    </>
                  )}
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="6" style={{ textAlign: "center" }}>Data tidak ditemukan</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="laporan-footer">
        <div className="laporan-info">
          Menampilkan {startIndex + 1} - {Math.min(startIndex + perPage, data.length)} dari {data.length} data
        </div>
        
        <div className="laporan-pagination">
          <button className="page-btn arrow" onClick={() => setPage(page - 1)} disabled={page === 1}>‹</button>
          {[...Array(totalPage)].map((_, i) => (
            <button key={i} className={`page-btn ${page === i + 1 ? "active" : ""}`} onClick={() => setPage(i + 1)}>
              {i + 1}
            </button>
          ))}
          <button className="page-btn arrow" onClick={() => setPage(page + 1)} disabled={page === totalPage}>›</button>
        </div>

        <div className="laporan-total-card">
          <span>Total Keseluruhan</span>
          <strong>Rp {total.toLocaleString("id-ID")}</strong>
        </div>
      </div>
    </div>
  );
}
import { useState, useEffect } from "react";
import "../../styles/bendahara/Laporan.css";
import "bootstrap-icons/font/bootstrap-icons.css";

export default function Laporan() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU", "Yayasan"];

  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);
  const [total, setTotal] = useState(0);

  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const [sumberDana, setSumberDana] = useState("");

  // 🔥 PAGINATION STATE
  const [page, setPage] = useState(1);
  const perPage = 10;

  const loadData = () => {
    let baseUrl = "";

    if (active === "Penerimaan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "Pengeluaran") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/pengeluaran";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    } else if (active === "Yayasan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/yayasan";
    } else {
      setData([]);
      setTotal(0);
      return;
    }

    const params = new URLSearchParams();
    if (start) params.append("start", start);
    if (end) params.append("end", end);
    if (sumberDana) params.append("sumber_dana", sumberDana);

    fetch(`${baseUrl}?${params.toString()}`)
      .then((res) => res.json())
      .then((res) => {
        setData(res.data || []);
        setTotal(res.total || 0);
        setPage(1); // reset page
      })
      .catch(() => {
        setData([]);
        setTotal(0);
      });
  };

  useEffect(() => {
    loadData();
  }, [active]);

  // =========================
  // PAGINATION LOGIC
  // =========================
  const totalData = data.length;
  const totalPage = Math.ceil(totalData / perPage);

  const startIndex = (page - 1) * perPage;
  const endIndex = startIndex + perPage;

  const currentData = data.slice(startIndex, endIndex);

  return (
    <div style={{ padding: "30px" }}>
      <h2>Laporan</h2>

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

      {active === "Penerimaan" && (
        <>
          <div className="laporan-header">
            <div className="laporan-actions">
              <button className="btn-outline excel">
                <i className="bi bi-file-earmark-excel"></i>
                Export Excel
              </button>

              <button className="btn-outline pdf">
                <i className="bi bi-file-earmark-pdf"></i>
                Export PDF
              </button>
            </div>

            <div className="laporan-filter">
              <input
                type="date"
                value={start}
                onChange={(e) => setStart(e.target.value)}
              />
              <input
                type="date"
                value={end}
                onChange={(e) => setEnd(e.target.value)}
              />

              <select
                value={sumberDana}
                onChange={(e) => setSumberDana(e.target.value)}
              >
                <option value="">Semua Dana</option>
                <option value="1">Pemerintah</option>
                <option value="2">Komite</option>
              </select>

              <button className="btn btn-primary" onClick={loadData}>
                Filter
              </button>
            </div>
          </div>

          {/* ================= TABLE ================= */}
          <div className="laporan-table">
            <table>
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Kategori</th>
                  <th>Keterangan</th>
                  <th>Nominal</th>
                </tr>
              </thead>

              <tbody>
                {currentData.length > 0 ? (
                  currentData.map((item, i) => (
                    <tr key={i}>
                      <td>{startIndex + i + 1}</td>
                      <td>
                        {new Date(item.tanggal).toLocaleDateString("id-ID")}
                      </td>
                      <td>{item.jenis}</td>
                      <td>{item.uraian}</td>
                      <td className="nominal">
                        Rp {Number(item.jumlah ?? 0).toLocaleString("id-ID")}
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="5" style={{ textAlign: "center" }}>
                      Tidak ada data
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {/* ================= FOOT AREA ================= */}
          <div className="laporan-footer">
            {/* kiri */}
            <div className="laporan-info">
              Menampilkan {totalData === 0 ? 0 : startIndex + 1} -{" "}
              {Math.min(endIndex, totalData)} dari {totalData} data
            </div>

            {/* tengah */}
            <div className="laporan-pagination">
              {/* kiri */}
              <button
                className="page-btn arrow"
                onClick={() => setPage(page - 1)}
                disabled={page === 1}
              >
                ‹
              </button>

              {/* angka */}
              {[...Array(totalPage)].map((_, i) => (
                <button
                  key={i}
                  className={`page-btn ${page === i + 1 ? "active" : ""}`}
                  onClick={() => setPage(i + 1)}
                >
                  {i + 1}
                </button>
              ))}

              {/* kanan */}
              <button
                className="page-btn arrow"
                onClick={() => setPage(page + 1)}
                disabled={page === totalPage}
              >
                ›
              </button>
            </div>

            {/* kanan */}
            <div className="laporan-total-card">
              <span>Total</span>
              <strong>Rp {total.toLocaleString("id-ID")}</strong>
            </div>
          </div>
        </>
      )}

      {active !== "Penerimaan" && (
        <div className="laporan-content">
          <div style={{ flex: 1 }}>
            <div className="laporan-table">
              <table>
                <thead>
                  <tr>
                    <th>{active}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style={{ textAlign: "center" }}>
                      Fitur {active} belum dibuat
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

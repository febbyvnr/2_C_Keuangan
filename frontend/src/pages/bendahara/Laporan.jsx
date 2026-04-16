import { useState, useEffect } from "react";
import "../../styles/bendahara/LaporanPenerimaan.css";

export default function Laporan() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU", "Yayasan"];

  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);

  // 🔥 TAMBAHAN
  const [total, setTotal] = useState(0);

  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const [sumberDana, setSumberDana] = useState("");

  const loadData = () => {
    let baseUrl = "";

    if (active === "Penerimaan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    } else {
      setData([]);
      setTotal(0); // 🔥 TAMBAHAN
      return;
    }

    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
    });

    fetch(`${baseUrl}?${params.toString()}`)
      .then((res) => res.json())
      .then((res) => {
        setData(res.data || []);
        setTotal(res.total || 0); // 🔥 TAMBAHAN
      })
      .catch(() => {
        setData([]);
        setTotal(0); // 🔥 TAMBAHAN
      });
  };

  const handleExportExcel = () => {
    let baseUrl = "";

    if (active === "Penerimaan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    }

    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
      type: "excel",
    });

    if (baseUrl) window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  const handleExportPDF = () => {
    let baseUrl = "";

    if (active === "Penerimaan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    }

    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
      type: "pdf",
    });

    if (baseUrl) window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  useEffect(() => {
    loadData();
  }, [active]);

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

      <div className="laporan-content">
        {/* ========================= */}
        {/* TABEL */}
        {/* ========================= */}
        <div style={{ flex: 1 }}>
          <div className="laporan-table">
            <table>
              <thead>
                <tr>
                  {active === "Penerimaan" || active === "BKU" ? (
                    <>
                      <th>No</th>
                      <th>Tanggal</th>
                      <th>Jenis</th>
                      <th>Uraian</th>
                      <th>Jumlah</th>
                    </>
                  ) : (
                    <>
                      <th>-</th>
                      <th>-</th>
                      <th>-</th>
                      <th>-</th>
                      <th>-</th>
                    </>
                  )}
                </tr>
              </thead>

              <tbody>
                {active === "Penerimaan" || active === "BKU" ? (
                  data.length > 0 ? (
                    data.map((item, i) => (
                      <tr key={i}>
                        <td>{i + 1}</td>
                        <td>
                          {item.tanggal
                            ? new Date(item.tanggal).toLocaleString("sv-SE")
                            : "-"}
                        </td>
                        <td>{item.jenis || "-"}</td>
                        <td>{item.uraian || item.keterangan}</td>
                        <td>
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
                  )
                ) : (
                  <tr>
                    <td colSpan="5" style={{ textAlign: "center" }}>
                      Data belum tersedia
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* ========================= */}
        {/* KANAN */}
        {/* ========================= */}
        <div className="laporan-side">
          {/* FILTER */}
          {active === "Penerimaan" && (
            <div className="laporan-filter">
              <div className="filter-range">
                <label>Periode</label>

                <div className="range-input">
                  <input
                    type="date"
                    value={start}
                    onChange={(e) => setStart(e.target.value)}
                  />

                  <span className="range-separator">—</span>

                  <input
                    type="date"
                    value={end}
                    onChange={(e) => setEnd(e.target.value)}
                  />
                </div>
              </div>

              <div className="filter-sumber">
                <label>Sumber Dana</label>

                <select
                  value={sumberDana}
                  onChange={(e) => setSumberDana(e.target.value)}
                >
                  <option value="">Semua Dana</option>
                  <option value="1">Dana Pemerintah</option>
                  <option value="2">Dana Komite Sekolah</option>
                  <option value="3">Dana Pemerintah Daerah</option>
                </select>
              </div>

              <button className="btn btn-primary" onClick={loadData}>
                Filter
              </button>
            </div>
          )}

          {/* 🔥 TAMBAHAN: TOTAL CARD */}
          {active === "Penerimaan" && (
            <div className="laporan-total-card">
              <div className="laporan-total-title">Total Penerimaan</div>

              <div className="laporan-total-value">
                Rp {Number(total).toLocaleString("id-ID")}
              </div>
            </div>
          )}

          {/* EXPORT */}
          <div className="laporan-actions">
            <button className="btn-export excel" onClick={handleExportExcel}>
              Excel
            </button>

            <button className="btn-export pdf" onClick={handleExportPDF}>
              PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

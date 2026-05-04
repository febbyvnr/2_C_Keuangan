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
      })
      .catch(() => {
        setData([]);
        setTotal(0);
      });
  };

  // =========================
  // EXPORT
  // =========================
  const handleExportExcel = () => {
    let baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";

    if (active === "Pengeluaran") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/pengeluaran";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    } else if (active === "Yayasan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/yayasan";
    }

    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
      type: "excel",
    });

    window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  const handleExportPDF = () => {
    let baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";

    if (active === "Pengeluaran") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/pengeluaran";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    } else if (active === "Yayasan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/yayasan";
    }

    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
      type: "pdf",
    });

    window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  useEffect(() => {
    loadData();
  }, [active]);

  return (
    <div style={{ padding: "30px" }}>
      <h2>Laporan</h2>

      {/* ================= TAB ================= */}
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

      {/* ================= PENERIMAAN ================= */}
      {active === "Penerimaan" && (
        <>
          <div className="laporan-header">
            <div className="laporan-actions">
              <button className="btn-outline excel" onClick={handleExportExcel}>
                <i className="bi bi-file-earmark-excel"></i>
                Export Excel
              </button>

              <button className="btn-outline pdf" onClick={handleExportPDF}>
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
                {data.length > 0 ? (
                  data.map((item, i) => (
                    <tr key={i}>
                      <td>{i + 1}</td>
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
              <tfoot>
                <tr>
                  <td colSpan="4" style={{ textAlign: "right" }}>
                    TOTAL
                  </td>
                  <td style={{ textAlign: "right", fontWeight: "600" }}>
                    Rp {total.toLocaleString("id-ID")}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </>
      )}

      {/* ================= TAB LAIN ================= */}
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

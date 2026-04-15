import { useState, useEffect } from "react";
import "../../styles/bendahara/laporan.css";

export default function Laporan() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU", "Yayasan"];

  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);

  // 🔥 TAMBAHAN STATE FILTER
  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const [sumberDana, setSumberDana] = useState("");

  // 🔥 PERBAIKAN LOAD DATA (SUDAH SUPPORT FILTER)
  const loadData = () => {
    let baseUrl = "";

    if (active === "Penerimaan") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      baseUrl = "http://127.0.0.1:8000/api/laporan/bku";
    } else {
      setData([]); // 🔥 FIX biar ga putih kosong
      return;
    }

    // 🔥 QUERY PARAM FILTER
    const params = new URLSearchParams({
      start,
      end,
      sumber_dana: sumberDana,
    });

    fetch(`${baseUrl}?${params.toString()}`)
      .then((res) => res.json())
      .then((res) => {
        setData(res.data || []);
      })
      .catch((err) => {
        console.error("ERROR:", err);
        setData([]);
      });
  };

  // 🔥 EXPORT EXCEL SUDAH IKUT FILTER
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

  // 🔥 EXPORT PDF SUDAH IKUT FILTER
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
    // eslint-disable-next-line
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

      <div className="laporan-filter">
        {/* 🔥 UBAH JADI 1 GROUP */}
        <div className="filter-range">
          <label>Tanggal</label>

          <div className="range-input">
            <input
              type="date"
              value={start}
              onChange={(e) => setStart(e.target.value)}
            />

            {/* 🔥 TANDA STRIP */}
            <span className="range-separator">—</span>

            <input
              type="date"
              value={end}
              onChange={(e) => setEnd(e.target.value)}
            />
          </div>
        </div>

        {/* 🔥 BUTTON FILTER */}
        <button className="btn btn-primary" onClick={loadData}>
          Filter
        </button>
      </div>

      <div className="laporan-table">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Jenis Penerimaan</th>
              <th>Uraian</th>
              <th>Jumlah</th>
            </tr>
          </thead>

          <tbody>
            {data.length > 0 ? (
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
                  <td>Rp {Number(item.jumlah ?? 0).toLocaleString("id-ID")}</td>
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

      <div className="laporan-actions">
        <button className="btn-export excel" onClick={handleExportExcel}>
          Excel
        </button>

        <button className="btn-export pdf" onClick={handleExportPDF}>
          PDF
        </button>
      </div>
    </div>
  );
}

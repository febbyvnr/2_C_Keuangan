import { useState, useEffect } from "react";
import "../../styles/bendahara/laporan.css";

export default function Laporan() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU", "Yayasan"];

  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);

  const loadData = () => {
    let url = "";

    if (active === "Penerimaan") {
      url = "http://127.0.0.1:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      url = "http://127.0.0.1:8000/api/laporan/bku";
    } else {
      // 🔥 JANGAN LANGSUNG KOSONGIN
      console.warn("Belum ada endpoint untuk:", active);
      setData([]);
      return;
    }

    fetch(url)
      .then((res) => res.json())
      .then((res) => {
        console.log("DATA:", res); // 🔥 DEBUG
        setData(res.data || []);
      })
      .catch((err) => {
        console.error("ERROR:", err);
        setData([]);
      });
  };

  // 🔥 TAMBAHAN EXPORT EXCEL
  const handleExportExcel = () => {
    let url = "";

    if (active === "Penerimaan") {
      url = "http://127.0.0.1:8000/api/laporan/penerimaan?type=excel";
    } else if (active === "BKU") {
      url = "http://127.0.0.1:8000/api/laporan/bku?type=excel";
    }

    if (url) window.open(url, "_blank");
  };

  // 🔥 TAMBAHAN EXPORT PDF
  const handleExportPDF = () => {
    let url = "";

    if (active === "Penerimaan") {
      url = "http://127.0.0.1:8000/api/laporan/penerimaan?type=pdf";
    } else if (active === "BKU") {
      url = "http://127.0.0.1:8000/api/laporan/bku?type=pdf";
    }

    if (url) window.open(url, "_blank");
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

      {/* 🔥 BUTTON SUDAH TERHUBUNG */}
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

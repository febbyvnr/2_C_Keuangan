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
      setData([]);
      return;
    }

    fetch(url)
      .then((res) => res.json())
      .then((res) => {
        setData(res.data); // ✅ FIX
      })
      .catch((err) => console.error(err));
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
                      ? new Date(item.tanggal).toLocaleDateString("id-ID")
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
        <button className="btn-export excel">Excel</button>
        <button className="btn-export pdf">PDF</button>
      </div>
    </div>
  );
}

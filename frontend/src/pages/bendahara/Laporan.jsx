import { useState, useEffect } from "react";
import "../../styles/laporan.css";

export default function Laporan() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU", "Yayasan"];

  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);

  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");

  // LOAD DATA AWAL
  useEffect(() => {
    loadData();
  }, [active]);

  const loadData = () => {
    let url = "";

    if (active === "Penerimaan") {
      url = "http://localhost:8000/api/laporan/penerimaan";
    } else if (active === "BKU") {
      url = "http://localhost:8000/api/laporan/bku";
    } else {
      setData([]);
      return;
    }

    fetch(url)
      .then((res) => res.json())
      .then((res) => setData(res))
      .catch((err) => console.error(err));
  };

  // FILTER
  const handleFilter = () => {
    let url = "";

    if (active === "Penerimaan") {
      url = `http://localhost:8000/api/laporan/penerimaan?from=${from}&to=${to}`;
    } else {
      return;
    }

    fetch(url)
      .then((res) => res.json())
      .then((res) => setData(res))
      .catch((err) => console.error(err));
  };

  return (
    <div className="laporan-container">
      <h2 className="laporan-title">Laporan</h2>

      {/* TAB */}
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

      {/* FILTER */}
      <div className="laporan-filter">
        <div>
          <label>Dari</label>
          <input
            type="date"
            value={from}
            onChange={(e) => setFrom(e.target.value)}
          />
        </div>

        <div>
          <label>Sampai</label>
          <input
            type="date"
            value={to}
            onChange={(e) => setTo(e.target.value)}
          />
        </div>

        <button className="btn btn-primary" onClick={handleFilter}>
          Filter
        </button>

        <button className="btn btn-success">Export Excel</button>
      </div>

      {/* TABLE */}
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
                  <td>{item.tanggal}</td>
                  <td>{item.jenis || "-"}</td>
                  <td>{item.uraian || item.keterangan}</td>
                  <td>Rp {Number(item.jumlah || 0).toLocaleString("id-ID")}</td>
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

      {/* TOTAL */}
      <div className="laporan-total">
        TOTAL: Rp{" "}
        {data
          .reduce((acc, item) => acc + Number(item.jumlah || 0), 0)
          .toLocaleString("id-ID")}
      </div>
    </div>
  );
}

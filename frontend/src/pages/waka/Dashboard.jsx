import { useEffect, useState } from "react";
import SidebarWaka from "../../components/SidebarWaka";
import "../../styles/waka/Dashboard.css";

export default function Dashboard() {
  const [rkt, setRkt] = useState([]);
  const [rka, setRka] = useState([]);
  const [fpd, setFpd] = useState([]);

  useEffect(() => {
    fetch("http://localhost:8000/api/mst-program-kerja")
      .then((res) => res.json())
      .then((data) => setRkt(data.data || []));

    fetch("http://localhost:8000/api/rka")
      .then((res) => res.json())
      .then((data) => setRka(data.data || []));

    fetch("http://localhost:8000/api/fpd-anggaran")
      .then((res) => res.json())
      .then((data) => setFpd(data.data || []));
  }, []);

  // KPI
  const totalRKT = rkt.length;
  const totalRKA = rka.length;
  const totalFPD = fpd.length;

  const totalAnggaran = rkt.reduce((sum, d) => sum + (d?.NOMINAL || 0), 0);
  const totalTerpakai = fpd.reduce((sum, d) => sum + (d?.NOMINAL_FPD || 0), 0);
  const totalSisa = fpd.reduce((sum, d) => sum + (d?.NOMINAL_SISA || 0), 0);

  return (
    <div style={{ display: "flex" }}>
      {/* SIDEBAR */}
      <SidebarWaka />

      {/* CONTENT */}
      <div className="waka-container">
        <h2 className="waka-title">Dashboard Waka</h2>

        {/* KPI */}
        <div className="waka-grid">
          <div className="card">
            <p>Total RKT</p>
            <h3>{totalRKT}</h3>
          </div>

          <div className="card">
            <p>Total RKA</p>
            <h3>{totalRKA}</h3>
          </div>

          <div className="card">
            <p>Total FPD</p>
            <h3>{totalFPD}</h3>
          </div>

          <div className="card primary">
            <p>Total Anggaran</p>
            <h3>Rp {totalAnggaran.toLocaleString()}</h3>
          </div>
        </div>

        {/* KEUANGAN */}
        <div className="waka-grid">
          <div className="card success">
            <p>Terpakai</p>
            <h3>Rp {totalTerpakai.toLocaleString()}</h3>
          </div>

          <div className="card warning">
            <p>Sisa</p>
            <h3>Rp {totalSisa.toLocaleString()}</h3>
          </div>
        </div>

        {/* TABLE RKT */}
        <div className="waka-table">
          <h3>Program Kerja (RKT)</h3>
          <table>
            <thead>
              <tr>
                <th>Program</th>
                <th>Indikator</th>
                <th>Nominal</th>
              </tr>
            </thead>
            <tbody>
              {rkt.map((item, i) => (
                <tr key={i}>
                  <td>{item.PROGRAM_KERJA}</td>
                  <td>{item.INDIKATOR}</td>
                  <td>Rp {item.NOMINAL?.toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

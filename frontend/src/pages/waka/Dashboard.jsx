import { useEffect, useState } from "react";
import SidebarWaka from "../../components/SidebarWaka";
import "../../styles/waka/Dashboard.css";

import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
  CartesianGrid,
  BarChart,
  Bar,
  Legend,
} from "recharts";

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

  const totalRKT = rkt.length;
  const totalRKA = rka.length;

  const totalTerpakai = fpd.reduce((sum, d) => sum + (d?.NOMINAL_FPD || 0), 0);

  const totalSisa = fpd.reduce((sum, d) => sum + (d?.NOMINAL_SISA || 0), 0);

  // === LINE CHART (trend per bulan)
  const trendData = fpd.map((d) => ({
    bulan: d.BULAN || "Data",
    nominal: d.NOMINAL_FPD || 0,
  }));

  // === BAR CHART
  const barData = [
    {
      name: "Keuangan",
      terpakai: totalTerpakai,
      sisa: totalSisa,
    },
  ];

  return (
    <div style={{ display: "flex" }}>
      <SidebarWaka />

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

          <div className="card success">
            <p>Terpakai</p>
            <h3>Rp {totalTerpakai.toLocaleString()}</h3>
          </div>

          <div className="card warning">
            <p>Sisa</p>
            <h3>Rp {totalSisa.toLocaleString()}</h3>
          </div>
        </div>

        {/* GRID UTAMA */}
        <div className="main-grid">
          {/* LEFT SIDE */}
          <div className="chart-section">
            {/* LINE */}
            <div className="chart-card">
              <h4>Trend Penggunaan Dana</h4>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={trendData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="bulan" />
                  <YAxis />
                  <Tooltip />
                  <Line
                    type="monotone"
                    dataKey="nominal"
                    stroke="#1e3a8a"
                    strokeWidth={3}
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>

            {/* BAR */}
            <div className="chart-card">
              <h4>Anggaran vs Realisasi</h4>
              <ResponsiveContainer width="100%" height={250}>
                <BarChart data={barData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Bar dataKey="terpakai" fill="#1e3a8a" />
                  <Bar dataKey="sisa" fill="#f59e0b" />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* RIGHT SIDE TABLE */}
          <div className="table-section">
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
    </div>
  );
}

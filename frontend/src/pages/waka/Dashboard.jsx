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
    fetch("http://localhost:8000/api/rkt")
      .then((res) => res.json())
      .then((data) => setRkt(data.data || []));

    fetch("http://localhost:8000/api/rka")
      .then((res) => res.json())
      .then((data) => setRka(data.data || []));

    fetch("http://localhost:8000/api/fpd-anggaran")
      .then((res) => res.json())
      .then((data) => setFpd(data.data || []));
  }, []);

  // helper fleksibel
  const getNominal = (d) => d.NOMINAL_FPD || d.nominal_fpd || d.nominal || 0;
  const getSisa = (d) => d.NOMINAL_SISA || d.nominal_sisa || d.sisa || 0;

  // KPI
  const totalRKT = rkt.length;
  const totalRKA = rka.length;

  const totalTerpakai = fpd.reduce((sum, d) => sum + getNominal(d), 0);
  const totalSisa = fpd.reduce((sum, d) => sum + getSisa(d), 0);

  const formatJt = (value) =>
    value >= 1000000 ? `${(value / 1000000).toFixed(1)} jt` : value;

  // trend
  const trendData = fpd.map((d) => ({
    bulan: d.BULAN || d.bulan || "Data",
    nominal: getNominal(d),
  }));

  // bar
  const barData = [
    {
      name: "Keuangan",
      terpakai: totalTerpakai,
      sisa: totalSisa,
    },
  ];

  return (
    <div className="dashboard-wrapper">
      <SidebarWaka />

      <main className="waka-container">
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

        {/* MAIN */}
        <div className="main-grid">
          {/* LEFT - CHART */}
          <div className="chart-section">
            {/* LINE */}
            <div className="chart-card">
              <h4>Trend Penggunaan Dana</h4>

              <div className="chart-inner">
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart data={trendData}>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} />

                    <XAxis dataKey="bulan" tick={{ fontSize: 10 }} />

                    <YAxis tick={{ fontSize: 10 }} tickFormatter={formatJt} />

                    <Tooltip
                      contentStyle={{
                        fontSize: "11px",
                        borderRadius: "8px",
                      }}
                    />

                    <Line
                      type="monotone" // 🔥 smooth
                      dataKey="nominal"
                      stroke="#1e3a8a"
                      strokeWidth={2}
                      dot={{ r: 3 }}
                      activeDot={{ r: 5 }}
                    />
                  </LineChart>
                </ResponsiveContainer>
              </div>
            </div>

            {/* BAR */}
            <div className="chart-card">
              <h4>Anggaran vs Realisasi</h4>

              <div className="chart-inner">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={barData}>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} />

                    <XAxis dataKey="name" tick={{ fontSize: 10 }} />

                    <YAxis tick={{ fontSize: 10 }} tickFormatter={formatJt} />

                    <Tooltip
                      contentStyle={{
                        fontSize: "11px",
                        borderRadius: "8px",
                      }}
                    />

                    <Legend wrapperStyle={{ fontSize: "11px" }} />

                    <Bar
                      dataKey="terpakai"
                      fill="#1e3a8a"
                      radius={[6, 6, 0, 0]}
                      barSize={40}
                    />

                    <Bar
                      dataKey="sisa"
                      fill="#f59e0b"
                      radius={[6, 6, 0, 0]}
                      barSize={40}
                    />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>

          {/* RIGHT - TABLE */}
          <div className="table-section">
            <h3>Program Kerja (RKT)</h3>

            <div className="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Program</th>
                    <th>Indikator</th>
                    <th>Nominal</th>
                  </tr>
                </thead>

                <tbody>
                  {rkt.length > 0 ? (
                    rkt.map((item, i) => (
                      <tr key={i}>
                        <td>{item.PROGRAM_KERJA || item.program_kerja}</td>
                        <td>{item.INDIKATOR || item.indikator}</td>
                        <td>
                          Rp{" "}
                          {(item.NOMINAL || item.nominal || 0).toLocaleString()}
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="3" style={{ textAlign: "center" }}>
                        Tidak ada data
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}

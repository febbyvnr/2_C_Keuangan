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

  const [selectedYear, setSelectedYear] = useState("all");
  const [selectedMonth, setSelectedMonth] = useState("all");

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

  // FILTER
  const filteredFpd = fpd.filter((d) => {
    if (!d.TGL_FPD) return true;

    const date = new Date(d.TGL_FPD);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();

    return (
      (selectedYear === "all" || year === Number(selectedYear)) &&
      (selectedMonth === "all" || month === Number(selectedMonth))
    );
  });

  // SORT
  const sortedFpd = [...filteredFpd].sort(
    (a, b) => new Date(a.TGL_FPD) - new Date(b.TGL_FPD),
  );

  // HELPER
  const getNominal = (d) => d.NOMINAL_FPD || d.nominal_fpd || d.nominal || 0;
  const getSisa = (d) => d.NOMINAL_SISA || d.nominal_sisa || d.sisa || 0;

  const monthName = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleString("id-ID", { month: "short" });
  };

  // KPI
  const totalRKT = rkt.length;
  const totalRKA = rka.length;

  const totalTerpakai = sortedFpd.reduce((sum, d) => sum + getNominal(d), 0);
  const totalSisa = sortedFpd.reduce((sum, d) => sum + getSisa(d), 0);

  const formatJt = (value) =>
    value >= 1000000 ? `${(value / 1000000).toFixed(1)} jt` : value;

  const grouped = {};

  sortedFpd.forEach((d) => {
    if (!d.TGL_FPD) return;

    const bulan = monthName(d.TGL_FPD);

    if (!grouped[bulan]) {
      grouped[bulan] = 0;
    }

    grouped[bulan] += getNominal(d);
  });

  const trendData = Object.entries(grouped)
    .map(([bulan, nominal]) => ({ bulan, nominal }))
    .sort((a, b) => {
      const monthOrder = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
      ];
      return monthOrder.indexOf(a.bulan) - monthOrder.indexOf(b.bulan);
    });

  // BAR
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
        <div className="header-card">
          <div className="header-top">
            <h2 className="waka-title">Dashboard</h2>
          </div>

          <div className="header-filter">
            <div className="filter-item">
              <span>Tahun</span>
              <select onChange={(e) => setSelectedYear(e.target.value)}>
                <option value="all">Semua</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
              </select>
            </div>

            <div className="filter-item">
              <span>Bulan</span>
              <select onChange={(e) => setSelectedMonth(e.target.value)}>
                <option value="all">Semua</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
              </select>
            </div>
          </div>
        </div>

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
          {/* LEFT */}
          <div className="chart-section">
            <div className="chart-card">
              <h4>Trend Penggunaan Dana</h4>

              <div className="chart-inner">
                {trendData.length === 0 ? (
                  <p className="no-data">Tidak ada data</p>
                ) : (
                  <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={trendData}>
                      <CartesianGrid strokeDasharray="3 3" vertical={false} />
                      <XAxis dataKey="bulan" tick={{ fontSize: 10 }} />
                      <YAxis tick={{ fontSize: 10 }} tickFormatter={formatJt} />
                      <Tooltip
                        formatter={(value) => `Rp ${value.toLocaleString()}`}
                      />
                      <Line
                        type="monotone"
                        dataKey="nominal"
                        stroke="#1e3a8a"
                        strokeWidth={2}
                        dot={{ r: 3 }}
                        activeDot={{ r: 5 }}
                      />
                    </LineChart>
                  </ResponsiveContainer>
                )}
              </div>
            </div>

            <div className="chart-card">
              <h4>Anggaran vs Realisasi</h4>

              <div className="chart-inner">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={barData}>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} />
                    <XAxis dataKey="name" tick={{ fontSize: 10 }} />
                    <YAxis tick={{ fontSize: 10 }} tickFormatter={formatJt} />
                    <Tooltip
                      formatter={(value) => `Rp ${value.toLocaleString()}`}
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

          {/* RIGHT */}
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

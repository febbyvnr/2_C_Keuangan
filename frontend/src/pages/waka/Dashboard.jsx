import { useEffect, useState } from "react";
import SidebarWaka from "../../components/SidebarWaka";
import "../../styles/waka/Dashboard.css";
import {
  AreaChart,
  Area,
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

  const filteredFpd = fpd.filter((d) => {
    if (!d.TGL_FPD) return false;

    const date = new Date(d.TGL_FPD);
    if (isNaN(date.getTime())) return false;

    const month = date.getMonth() + 1;
    const year = date.getFullYear();

    return (
      (selectedYear === "all" || year === Number(selectedYear)) &&
      (selectedMonth === "all" || month === Number(selectedMonth))
    );
  });

  const sortedFpd = [...filteredFpd].sort(
    (a, b) => new Date(a.TGL_FPD) - new Date(b.TGL_FPD),
  );

  const getNominal = (d) => d.NOMINAL_FPD || d.nominal_fpd || d.nominal || 0;
  const getSisa = (d) => d.NOMINAL_SISA || d.nominal_sisa || d.sisa || 0;

  const monthName = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleString("id-ID", { month: "short" });
  };

  const totalRKT = rkt.length;
  const totalRKA = rka.length;

  const totalTerpakai = sortedFpd.reduce((sum, d) => sum + getNominal(d), 0);
  const totalSisa = sortedFpd.reduce((sum, d) => sum + getSisa(d), 0);

  const totalAnggaranRKT = rkt.reduce(
    (sum, d) => sum + (d.NOMINAL || d.nominal || 0),
    0,
  );

  const maxProgram = rkt.reduce(
    (max, d) => ((d.NOMINAL || 0) > (max?.NOMINAL || 0) ? d : max),
    null,
  );

  const minProgram = rkt.reduce(
    (min, d) => ((d.NOMINAL || 0) < (min?.NOMINAL || Infinity) ? d : min),
    null,
  );

  const formatJt = (value) =>
    value >= 1000000 ? `${(value / 1000000).toFixed(1)} jt` : value;

  const grouped = {};

  sortedFpd.forEach((d) => {
    if (!d.TGL_FPD || isNaN(new Date(d.TGL_FPD))) return;

    const bulan = monthName(d.TGL_FPD);

    if (!grouped[bulan]) grouped[bulan] = 0;

    grouped[bulan] += getNominal(d);
  });

  const trendData = Object.entries(grouped)
    .map(([bulan, nominal]) => ({ bulan, nominal }))
    .sort((a, b) => {
      const order = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
      ];
      return order.indexOf(a.bulan) - order.indexOf(b.bulan);
    });

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
        {/* HEADER */}
        {/* 🔥 HEADER (DIGANTI JADI WELCOME) */}
        <div className="header-card welcome-card">
          <div className="welcome-left">
            <h2 className="welcome-title">Selamat Datang</h2>

            <p className="welcome-sub">Wakil Kepala Sekolah</p>

            <p className="welcome-date">
              {new Date().toLocaleDateString("id-ID", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </p>
          </div>

          {/* 🔥 FILTER TETAP ADA */}
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
                {[...Array(12)].map((_, i) => (
                  <option key={i + 1} value={i + 1}>
                    {new Date(0, i).toLocaleString("id-ID", {
                      month: "long",
                    })}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        <div className="waka-grid">
          <div className="card">
            <div className="card-top">
              <p>Total RKT</p>
              <span className={`kpi-badge ${totalRKT > 0 ? "up" : "down"}`}>
                {totalRKT > 0 ? "↑" : "↓"} {totalRKT > 0 ? "100%" : "0%"}
              </span>
            </div>
            <h3>{totalRKT}</h3>
          </div>

          <div className="card">
            <div className="card-top">
              <p>Total RKA</p>
              <span className={`kpi-badge ${totalRKA > 0 ? "up" : "down"}`}>
                {totalRKA > 0 ? "↑" : "↓"} {totalRKA > 0 ? "100%" : "0%"}
              </span>
            </div>
            <h3>{totalRKA}</h3>
          </div>

          <div className="card success">
            <div className="card-top">
              <p>Terpakai</p>
              <span
                className={`kpi-badge ${totalTerpakai > 0 ? "up" : "down"}`}
              >
                {totalTerpakai > 0 ? "↑" : "↓"}{" "}
                {totalAnggaranRKT > 0
                  ? Math.min(
                      (totalTerpakai / totalAnggaranRKT) * 100,
                      100,
                    ).toFixed(0)
                  : 0}
                %
              </span>
            </div>
            <h3>Rp {totalTerpakai.toLocaleString()}</h3>
          </div>

          <div className="card warning">
            <div className="card-top">
              <p>Sisa</p>
              <span className={`kpi-badge ${totalSisa > 0 ? "up" : "down"}`}>
                {totalSisa > 0 ? "↑" : "↓"}{" "}
                {totalAnggaranRKT > 0
                  ? Math.min((totalSisa / totalAnggaranRKT) * 100, 100).toFixed(
                      0,
                    )
                  : 0}
                %
              </span>
            </div>
            <h3>Rp {totalSisa.toLocaleString()}</h3>
          </div>
        </div>

        {/* CHART */}
        <div className="main-grid">
          <div className="chart-section">
            <div className="chart-card">
              <h4>Penggunaan Dana (FPD)</h4>

              <div className="chart-inner">
                {trendData.length === 0 ? (
                  <p className="no-data">Belum ada data untuk periode ini</p>
                ) : (
                  <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={trendData}>
                      <CartesianGrid strokeDasharray="3 3" vertical={false} />
                      <XAxis dataKey="bulan" tick={{ fontSize: 11 }} />
                      <YAxis tick={{ fontSize: 11 }} tickFormatter={formatJt} />
                      <Tooltip
                        contentStyle={{ fontSize: "12px", textAlign: "center" }}
                        itemStyle={{ textAlign: "center" }}
                        formatter={(v) => `Rp ${v.toLocaleString()}`}
                      />

                      <Area
                        type="monotone"
                        dataKey="nominal"
                        stroke="#265f9c"
                        fill="#265f9c"
                        fillOpacity={0.12}
                      />
                    </AreaChart>
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
                    <XAxis dataKey="name" tick={{ fontSize: 13 }} />
                    <YAxis tick={{ fontSize: 11 }} tickFormatter={formatJt} />
                    <Tooltip
                      shared={false}
                      contentStyle={{ fontSize: "12px" }}
                      formatter={(v) => `Rp ${v.toLocaleString()}`}
                    />
                    <Legend wrapperStyle={{ fontSize: "11px" }} />

                    <Bar dataKey="terpakai" fill="#265f9c" />
                    <Bar dataKey="sisa" fill="#EDA60F" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        </div>

        {/* 🔥 2 CARD BAWAH (UPDATED) */}
        <div className="bottom-grid">
          {/* CARD 1 */}
          <div className="summary-card">
            <h4>Ringkasan Anggaran</h4>

            <p className="summary-sub">
              Total Anggaran:{" "}
              {totalAnggaranRKT > 0
                ? `${Math.min(
                    (totalTerpakai / totalAnggaranRKT) * 100,
                    100,
                  ).toFixed(0)}%`
                : "0%"}
            </p>

            <div className="progress-bar">
              <div
                className="progress-fill"
                style={{
                  width: `${
                    totalAnggaranRKT > 0
                      ? Math.min((totalTerpakai / totalAnggaranRKT) * 100, 100)
                      : 0
                  }%`,
                }}
              ></div>
            </div>

            <div className="summary-footer">
              <span>Total Program: {totalRKT}</span>
            </div>
          </div>

          {/* CARD 2 */}
          <div className="summary-card">
            <h4 className="insight-main-title">Insight Program</h4>

            <div className="insight-grid">
              <div className="insight-item">
                <p className="insight-label">Program Tertinggi</p>

                <h3 className="insight-title">
                  <span className="insight-icon up">↑</span>{" "}
                  {maxProgram?.PROGRAM_KERJA || "-"}
                </h3>

                <p className="insight-value">
                  Rp {(maxProgram?.NOMINAL || 0).toLocaleString()}
                </p>
              </div>

              <div className="insight-item">
                <p className="insight-label">Program Terendah</p>

                <h3 className="insight-title">
                  <span className="insight-icon down">↓</span>{" "}
                  {minProgram?.PROGRAM_KERJA || "-"}
                </h3>

                <p className="insight-value">
                  Rp {(minProgram?.NOMINAL || 0).toLocaleString()}
                </p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}

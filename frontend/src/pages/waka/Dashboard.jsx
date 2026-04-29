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

import {
  BsCheckCircleFill,
  BsExclamationTriangleFill,
  BsXCircleFill,
} from "react-icons/bs";

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
    (sum, d) => sum + (d.TOTAL_PROGKER || 0),
    0,
  );

  const maxProgram = rkt.reduce(
    (max, d) => ((d.TOTAL_PROGKER || 0) > (max?.TOTAL_PROGKER || 0) ? d : max),
    null,
  );

  const minProgram = rkt.reduce(
    (min, d) =>
      (d.TOTAL_PROGKER || 0) < (min?.TOTAL_PROGKER || Infinity) ? d : min,
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

  const filteredRkt =
    selectedYear === "all" ? rkt : rkt.filter((d) => d.TAHUN == selectedYear);

  const totalProgram = filteredRkt.length;

  const programAktif = new Set(filteredFpd.map((d) => d.ID_PROGRAM_KERJA)).size;

  const progress = totalProgram > 0 ? (programAktif / totalProgram) * 100 : 0;

  let progressStatus = "Belum Mulai";
  if (progress >= 80) progressStatus = "Hampir Selesai";
  else if (progress >= 40) progressStatus = "Sedang Berjalan";

  return (
    <div className="dashboard-wrapper">
      <SidebarWaka />

      <main className="waka-container">
        <div className="header-card welcome-card">
          <div className="welcome-left">
            <h2 className="waka-title">Selamat Datang</h2>

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

          <div className="header-filter">
            <div className="filter-item">
              <span>Tahun</span>
              <select onChange={(e) => setSelectedYear(e.target.value)}>
                <option value="all">Semua</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
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
                      <YAxis
                        tick={{ fontSize: 11, fill: "#585858" }}
                        tickFormatter={formatJt}
                      />
                      <Tooltip
                        contentStyle={{ fontSize: "12px", textAlign: "center" }}
                        itemStyle={{ textAlign: "center", color: "#585858" }}
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
                    <XAxis
                      dataKey="name"
                      tick={false}
                      axisLine={false}
                      tickLine={false}
                    />
                    <YAxis
                      tick={{ fontSize: 11, fill: "#585858" }}
                      tickFormatter={formatJt}
                    />
                    <Tooltip
                      shared={false}
                      contentStyle={{ fontSize: "12px" }}
                      itemStyle={{ color: "#585858" }}
                      formatter={(v) => `Rp ${v.toLocaleString()}`}
                    />
                    <Legend
                      verticalAlign="top"
                      align="right"
                      wrapperStyle={{
                        fontSize: "11px",
                        top: -35,
                        right: 0,
                      }}
                      formatter={(value) => (
                        <span style={{ color: "#585858" }}>{value}</span>
                      )}
                    />
                    <Bar dataKey="terpakai" fill="#265f9c" />
                    <Bar dataKey="sisa" fill="#EDA60F" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        </div>
        <div className="bottom-grid">
          <div className="summary-card ringkasan-card">
            <h4 className="summary-title">Status Pengajuan</h4>

            <div className="progress-wrapper">
              <p className="progress-text">
                <strong>{programAktif}</strong> / {totalProgram} Program sudah
                diajukan
              </p>

              <div className="progress-bar-large">
                <div
                  className="progress-fill-large"
                  style={{ width: `${progress}%` }}
                ></div>
              </div>

              <p className="progress-percent">{progress.toFixed(0)}%</p>

              <p className="progress-status">
                Status: <span>{progressStatus}</span>
              </p>
            </div>
          </div>
          <div className="summary-card">
            <h4 className="insight-main-title insight-title">
              Insight Program
            </h4>

            <div className="insight-compare">
              <div className="insight-col">
                <p className="insight-heading">PROGRAM PENYERAPAN TERBESAR</p>

                <div className="insight-percent">
                  ↑{" "}
                  {totalAnggaranRKT > 0
                    ? (
                        ((maxProgram?.TOTAL_PROGKER || 0) / totalAnggaranRKT) *
                        100
                      ).toFixed(1)
                    : 0}
                  %
                </div>

                <p className="insight-program">
                  {maxProgram?.PROGRAM_KERJA || "-"}
                </p>

                <p className="insight-value">
                  Rp {(maxProgram?.TOTAL_PROGKER || 0).toLocaleString()}
                </p>
              </div>

              <div className="insight-col">
                <p className="insight-heading">PROGRAM PENYERAPAN TERKECIL</p>

                <div className="insight-percent">
                  ↓{" "}
                  {totalAnggaranRKT > 0
                    ? (
                        ((minProgram?.TOTAL_PROGKER || 0) / totalAnggaranRKT) *
                        100
                      ).toFixed(1)
                    : 0}
                  %
                </div>

                <p className="insight-program">
                  {minProgram?.PROGRAM_KERJA || "-"}
                </p>

                <p className="insight-value">
                  Rp {(minProgram?.TOTAL_PROGKER || 0).toLocaleString()}
                </p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}

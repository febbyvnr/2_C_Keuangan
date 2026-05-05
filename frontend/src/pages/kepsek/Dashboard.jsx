import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "../../api/api";
import "../../styles/kepsek/Dashboard.css";

const money = (value) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(Number(value || 0));

const percent = (part, total) => {
  if (!total) return 0;
  return Math.min((Number(part || 0) / Number(total)) * 100, 100);
};

export default function KepsekDashboard() {
  const [fpdList, setFpdList] = useState([]);
  const [evaluasiList, setEvaluasiList] = useState([]);
  const [bkuList, setBkuList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedYear, setSelectedYear] = useState(0);
  const [selectedMonth, setSelectedMonth] = useState(0);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        const [fpdRes, evaluasiRes, bkuRes] = await Promise.all([
          apiFetch("/fpd-anggaran"),
          apiFetch("/evaluasi-rkt"),
          apiFetch("/laporan/bku"),
        ]);

        setFpdList(fpdRes.data || []);
        setEvaluasiList(evaluasiRes.data || []);
        setBkuList(bkuRes.bku || bkuRes.data || []);
      } catch (error) {
        console.error("Gagal memuat dashboard kepsek:", error);
      } finally {
        setLoading(false);
      }
    };

    load();
  }, []);

  const filteredFpdList = useMemo(() => {
    return fpdList.filter((item) => {
      const tanggal = item.TGL_FPD ? new Date(item.TGL_FPD) : null;
      if (!tanggal || Number.isNaN(tanggal.getTime())) return true;

      const yearMatch = selectedYear === 0 || tanggal.getFullYear() === selectedYear;
      const monthMatch = selectedMonth === 0 || tanggal.getMonth() + 1 === selectedMonth;

      return yearMatch && monthMatch;
    });
  }, [fpdList, selectedYear, selectedMonth]);

  const summary = useMemo(() => {
    const totalFpd = filteredFpdList.length;
    const totalEvaluasi = evaluasiList.length;
    const totalBku = bkuList.length;

    const totalAnggaran = filteredFpdList.reduce((sum, item) => sum + Number(item.NOMINAL_ANGGARAN || 0), 0);
    const totalTerpakai = filteredFpdList.reduce((sum, item) => sum + Number(item.TERPAKAI || item.NOMINAL_FPD || 0), 0);
    const totalSisa = filteredFpdList.reduce((sum, item) => sum + Number(item.SISA_ANGGARAN || item.NOMINAL_SISA || 0), 0);

    const statusCount = {
      disetujui: filteredFpdList.filter((item) => item.NIP_VALIDATOR_FPD && item.NIP_VALIDATOR_FPD !== "Ditolak").length,
      menunggu: filteredFpdList.filter((item) => !item.NIP_VALIDATOR_FPD).length,
      ditolak: filteredFpdList.filter((item) => item.NIP_VALIDATOR_FPD === "Ditolak").length,
    };

    return {
      totalFpd,
      totalEvaluasi,
      totalBku,
      totalAnggaran,
      totalTerpakai,
      totalSisa,
      statusCount,
      progressDana: percent(totalTerpakai, totalAnggaran),
    };
  }, [filteredFpdList, evaluasiList, bkuList]);

  const recentFpd = [...filteredFpdList]
    .sort((a, b) => new Date(b.TGL_FPD || 0) - new Date(a.TGL_FPD || 0))
    .slice(0, 4);

  return (
    <main className="kepsek-dashboard-main">
      <section className="kepsek-hero">
        <div>
          <h1>Selamat Datang</h1>
          <p className="kepsek-eyebrow">Kepala Sekolah</p>
          <p className="kepsek-hero-text">
            {new Date().toLocaleDateString("id-ID", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}
          </p>
        </div>

        <div className="kepsek-hero-controls">
          <div className="filter-group">
            <label className="filter-label">Tahun</label>
            <div className="control">
              <select value={selectedYear} onChange={(e) => setSelectedYear(Number(e.target.value))}>
                <option value={0}>Semua</option>
                {Array.from({ length: 6 }).map((_, i) => {
                  const y = new Date().getFullYear() - i;
                  return (
                    <option key={y} value={y}>{y}</option>
                  );
                })}
              </select>
            </div>
          </div>

          <div className="filter-group">
            <label className="filter-label">Bulan</label>
            <div className="control">
              <select value={selectedMonth} onChange={(e) => setSelectedMonth(Number(e.target.value))}>
                <option value={0}>Semua</option>
                {[
                  "Januari","Februari","Maret","April","Mei","Juni",
                  "Juli","Agustus","September","Oktober","November","Desember",
                ].map((m, idx) => (
                  <option key={m} value={idx + 1}>{m}</option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </section>

      {loading ? (
        <div className="kepsek-loading-panel">Memuat ringkasan dashboard...</div>
      ) : (
        <>
          <section className="kepsek-kpi-grid">
            <article className="kepsek-kpi-card accent-blue">
              <span>Total FPD</span>
              <strong>{summary.totalFpd}</strong>
            </article>
            <article className="kepsek-kpi-card accent-green">
              <span>Total Evaluasi RKT</span>
              <strong>{summary.totalEvaluasi}</strong>
            </article>
            <article className="kepsek-kpi-card accent-gold">
              <span>Total BKU</span>
              <strong>{summary.totalBku}</strong>
            </article>
            <article className="kepsek-kpi-card accent-secondary">
              <span>Anggaran Tersisa</span>
              <strong>{money(summary.totalSisa)}</strong>
            </article>
          </section>

          <section className="kepsek-insight-grid">
            <div className="kepsek-panel">
              <div className="kepsek-panel-head">
                <div>
                  <p>Progress Dana</p>
                  <h3>{summary.progressDana.toFixed(0)}%</h3>
                </div>
                <span>{money(summary.totalTerpakai)} terpakai</span>
              </div>
              <div className="kepsek-progress-track">
                <div className="kepsek-progress-fill" style={{ width: `${summary.progressDana}%` }} />
              </div>
              <p className="kepsek-panel-note">
                Menampilkan proporsi anggaran yang sudah dipakai dibanding total anggaran yang tercatat.
              </p>
            </div>

            <div className="kepsek-panel">
              <div className="kepsek-panel-head">
                <div>
                  <p>Distribusi Status</p>
                  <h3>{summary.statusCount.disetujui} disetujui</h3>
                </div>
                <span>{summary.statusCount.menunggu} menunggu</span>
              </div>
              <div className="kepsek-status-stack">
                <div>
                  <label>Disetujui</label>
                  <strong>{summary.statusCount.disetujui}</strong>
                </div>
                <div>
                  <label>Menunggu</label>
                  <strong>{summary.statusCount.menunggu}</strong>
                </div>
                <div>
                  <label>Ditolak</label>
                  <strong>{summary.statusCount.ditolak}</strong>
                </div>
              </div>
            </div>
          </section>

          <section className="kepsek-bottom-grid single-column">
            <div className="kepsek-panel">
              <div className="kepsek-panel-head compact">
                <div>
                  <p>Pengajuan Terbaru</p>
                  <h3>FPD terbaru</h3>
                </div>
              </div>

              <div className="kepsek-list">
                {recentFpd.length === 0 ? (
                  <div className="kepsek-empty-state">Belum ada data pengajuan.</div>
                ) : (
                  recentFpd.map((item, index) => (
                    <article key={item.ID_FPD || index} className="kepsek-list-item">
                      <div>
                        <strong>{item.program_kerja?.PROGRAM_KERJA || "Program kerja"}</strong>
                        <p>{item.TGL_FPD || "-"}</p>
                      </div>
                      <span>{money(item.NOMINAL_ANGGARAN)}</span>
                    </article>
                  ))
                )}
              </div>
            </div>
          </section>
        </>
      )}
    </main>
  );
}

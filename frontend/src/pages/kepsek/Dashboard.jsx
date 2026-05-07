import { useEffect, useMemo, useState } from "react";
import { apiFetch } from "../../api/api";
import "../../styles/kepsek/Dashboard.css";

const DASHBOARD_VIEWS = [
  {
    value: "keuangan",
    label: "Kondisi Keuangan",
    subtitle: "Ringkasan FPD, BKU, dan arus anggaran sekolah.",
  },
  {
    value: "rkt",
    label: "RKT",
    subtitle: "Ringkasan program kerja dan evaluasi RKT.",
  },
];

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

const formatDate = (value) => {
  if (!value) return "-";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

const readNumber = (item, ...keys) => {
  for (const key of keys) {
    const raw = item?.[key];
    if (raw !== undefined && raw !== null && raw !== "") {
      return Number(raw) || 0;
    }
  }

  return 0;
};

const readText = (item, ...keys) => {
  for (const key of keys) {
    const raw = item?.[key];
    if (raw !== undefined && raw !== null && String(raw).trim() !== "") {
      return String(raw);
    }
  }

  return "-";
};

const normalizeStatus = (description) => {
  const text = String(description || "").toLowerCase();

  if (!text) return "draft";
  if (text.includes("disetujui")) return "disetujui";
  if (text.includes("ditolak")) return "ditolak";
  if (text.includes("revisi")) return "revisi";
  if (text.includes("diajukan")) return "menunggu";

  return "draft";
};

const formatDashboardError = (error) => error?.message || error?.error || "Gagal memuat sebagian data dashboard";

export default function KepsekDashboard() {
  const [activeView, setActiveView] = useState("keuangan");
  const [fpdList, setFpdList] = useState([]);
  const [bkuList, setBkuList] = useState([]);
  const [programKerjaList, setProgramKerjaList] = useState([]);
  const [evaluasiList, setEvaluasiList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [warning, setWarning] = useState("");
  const [selectedYear, setSelectedYear] = useState(0);
  const [selectedMonth, setSelectedMonth] = useState(0);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      setWarning("");

      try {
        if (activeView === "keuangan") {
          const [fpdResult, bkuResult] = await Promise.allSettled([
            apiFetch("/fpd-anggaran"),
            apiFetch("/laporan/bku"),
          ]);

          const nextWarnings = [];

          if (fpdResult.status === "fulfilled") {
            setFpdList(fpdResult.value.data || []);
          } else {
            setFpdList([]);
            nextWarnings.push(`FPD: ${formatDashboardError(fpdResult.reason)}`);
          }

          if (bkuResult.status === "fulfilled") {
            setBkuList(bkuResult.value.bku || bkuResult.value.data || []);
          } else {
            setBkuList([]);
            nextWarnings.push(`BKU: ${formatDashboardError(bkuResult.reason)}`);
          }

          setWarning(nextWarnings.join(" | "));
        } else {
          const [programResult, evaluasiResult] = await Promise.allSettled([
            apiFetch("/rkt"),
            apiFetch("/evaluasi-rkt"),
          ]);

          const nextWarnings = [];

          if (programResult.status === "fulfilled") {
            setProgramKerjaList(programResult.value.data || []);
          } else {
            setProgramKerjaList([]);
            nextWarnings.push(`RKT: ${formatDashboardError(programResult.reason)}`);
          }

          if (evaluasiResult.status === "fulfilled") {
            setEvaluasiList(evaluasiResult.value.data || []);
          } else {
            setEvaluasiList([]);
            nextWarnings.push(`Evaluasi: ${formatDashboardError(evaluasiResult.reason)}`);
          }

          setWarning(nextWarnings.join(" | "));
        }
      } catch (error) {
        setWarning(formatDashboardError(error));
      } finally {
        setLoading(false);
      }
    };

    load();
  }, [activeView]);

  const filteredFpdList = useMemo(() => {
    return fpdList.filter((item) => {
      const tanggal = item.TGL_FPD ? new Date(item.TGL_FPD) : null;
      if (!tanggal || Number.isNaN(tanggal.getTime())) return true;

      const yearMatch = selectedYear === 0 || tanggal.getFullYear() === selectedYear;
      const monthMatch = selectedMonth === 0 || tanggal.getMonth() + 1 === selectedMonth;

      return yearMatch && monthMatch;
    });
  }, [fpdList, selectedYear, selectedMonth]);

  const filteredProgramKerja = useMemo(() => {
    return programKerjaList.filter((item) => {
      if (selectedYear === 0 && selectedMonth === 0) return true;

      const tahunStr =
        item.tahunAnggaran?.DESKRIPSI_TAHUN_ANGGARAN || item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "";
      const yearMatch = (tahunStr.match(/(\d{4})/) || [])[0];
      const year = yearMatch ? Number(yearMatch) : null;

      if (selectedYear !== 0 && year && year !== selectedYear) return false;

      const rawDate = item.TGL_PM || item.tgl || item.TANGGAL || item.tanggal;
      if (selectedMonth !== 0 && rawDate) {
        const d = new Date(rawDate);
        if (!Number.isNaN(d.getTime()) && d.getMonth() + 1 !== selectedMonth) return false;
      }

      return true;
    });
  }, [programKerjaList, selectedYear, selectedMonth]);

  const filteredEvaluasi = useMemo(() => {
    return evaluasiList.filter((item) => {
      const rawDate = item.TGL_PM || item.tgl || item.TGL || item.tanggal;
      if (!rawDate || Number.isNaN(new Date(rawDate).getTime())) return true;

      const d = new Date(rawDate);
      const yearMatch = selectedYear === 0 || d.getFullYear() === selectedYear;
      const monthMatch = selectedMonth === 0 || d.getMonth() + 1 === selectedMonth;
      return yearMatch && monthMatch;
    });
  }, [evaluasiList, selectedYear, selectedMonth]);

  const filteredBkuList = useMemo(() => {
    return bkuList.filter((item) => {
      const rawDate = item.TANGGAL || item.tanggal || item.TGL || item.tgl;
      const tanggal = rawDate ? new Date(rawDate) : null;
      if (!tanggal || Number.isNaN(tanggal.getTime())) return true;

      const yearMatch = selectedYear === 0 || tanggal.getFullYear() === selectedYear;
      const monthMatch = selectedMonth === 0 || tanggal.getMonth() + 1 === selectedMonth;

      return yearMatch && monthMatch;
    });
  }, [bkuList, selectedYear, selectedMonth]);

  const summary = useMemo(() => {
    if (activeView === "keuangan") {
      const totalFpd = filteredFpdList.length;
      const totalBku = filteredBkuList.length;
      const totalAnggaran = filteredFpdList.reduce(
        (sum, item) => sum + readNumber(item, "NOMINAL_ANGGARAN", "nominal_anggaran"),
        0
      );
      const totalTerpakai = filteredFpdList.reduce(
        (sum, item) => sum + readNumber(item, "TERPAKAI", "NOMINAL_FPD", "nominal_fpd"),
        0
      );
      const totalSisa = filteredFpdList.reduce(
        (sum, item) => sum + readNumber(item, "SISA_ANGGARAN", "NOMINAL_SISA", "nominal_sisa"),
        0
      );

      const statusCount = {
        disetujui: filteredFpdList.filter((item) => item.NIP_VALIDATOR_FPD && item.NIP_VALIDATOR_FPD !== "Ditolak").length,
        menunggu: filteredFpdList.filter((item) => !item.NIP_VALIDATOR_FPD).length,
        ditolak: filteredFpdList.filter((item) => item.NIP_VALIDATOR_FPD === "Ditolak").length,
      };

      return {
        totalFpd,
        totalBku,
        totalAnggaran,
        totalTerpakai,
        totalSisa,
        statusCount,
        progressDana: percent(totalTerpakai, totalAnggaran),
      };
    }

    const totalProgramKerja = filteredProgramKerja.length;
    const totalEvaluasi = filteredEvaluasi.length;
    const approvedCount = filteredProgramKerja.filter(
      (item) => normalizeStatus(item.trPm?.DESKRIPSI_TR_PM || item.trPm?.deskripsi_tr_pm) === "disetujui"
    ).length;
    const waitingCount = filteredProgramKerja.filter((item) => {
      const status = normalizeStatus(item.trPm?.DESKRIPSI_TR_PM || item.trPm?.deskripsi_tr_pm);
      return status === "menunggu" || status === "draft";
    }).length;
    const revisionCount = filteredProgramKerja.filter(
      (item) => normalizeStatus(item.trPm?.DESKRIPSI_TR_PM || item.trPm?.deskripsi_tr_pm) === "revisi"
    ).length;

    return {
      totalProgramKerja,
      totalEvaluasi,
      approvedCount,
      waitingCount,
      revisionCount,
      progressRkt: percent(approvedCount, totalProgramKerja),
    };
  }, [activeView, filteredBkuList, filteredFpdList, evaluasiList, programKerjaList]);

  const recentFpd = [...filteredFpdList]
    .sort((a, b) => new Date(b.TGL_FPD || 0) - new Date(a.TGL_FPD || 0))
    .slice(0, 4);

  const recentBku = [...filteredBkuList]
    .sort((a, b) => new Date(b.TANGGAL || b.tanggal || 0) - new Date(a.TANGGAL || a.tanggal || 0))
    .slice(0, 4);

  const recentProgramKerja = [...filteredProgramKerja]
    .sort((a, b) => Number(b.ID_PROGRAM_KERJA || 0) - Number(a.ID_PROGRAM_KERJA || 0))
    .slice(0, 4);

  const recentEvaluasi = [...filteredEvaluasi]
    .sort((a, b) => new Date(b.TGL_PM || 0) - new Date(a.TGL_PM || 0))
    .slice(0, 4);

  return (
    <main className="kepsek-dashboard-main">
      <section className="kepsek-hero">
        <div>
          <h1>Selamat Datang</h1>
          <p className="kepsek-eyebrow">Kepala Sekolah</p>
          <p className="kepsek-hero-text">
            {new Date().toLocaleDateString("id-ID", {
              weekday: "long",
              year: "numeric",
              month: "long",
              day: "numeric",
            })}
          </p>
        </div>

        <div className="kepsek-hero-controls">
          <div className="filter-group">
            <label className="filter-label">Tampilan</label>
            <div className="control">
              <select value={activeView} onChange={(e) => setActiveView(e.target.value)}>
                {DASHBOARD_VIEWS.map((view) => (
                  <option key={view.value} value={view.value}>
                    {view.label}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {(activeView === "keuangan" || activeView === "rkt") && (
            <>
              <div className="filter-group">
                <label className="filter-label">Tahun</label>
                <div className="control">
                  <select value={selectedYear} onChange={(e) => setSelectedYear(Number(e.target.value))}>
                    <option value={0}>Semua</option>
                    {Array.from({ length: 6 }).map((_, index) => {
                      const year = new Date().getFullYear() - index;
                      return (
                        <option key={year} value={year}>
                          {year}
                        </option>
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
                    ].map((monthName, index) => (
                      <option key={monthName} value={index + 1}>
                        {monthName}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            </>
          )}
        </div>
      </section>

      {loading ? (
        <div className="kepsek-loading-panel">Memuat ringkasan dashboard...</div>
      ) : (
        <>
          {warning && <div className="kepsek-alert">{warning}</div>}

          {activeView === "keuangan" ? (
            <>
              <section className="kepsek-kpi-grid">
                <article className="kepsek-kpi-card accent-blue">
                  <span>Total FPD</span>
                  <strong>{summary.totalFpd}</strong>
                </article>
                <article className="kepsek-kpi-card accent-green">
                  <span>Total BKU</span>
                  <strong>{summary.totalBku}</strong>
                </article>
                <article className="kepsek-kpi-card accent-gold">
                  <span>Anggaran Terpakai</span>
                  <strong>{money(summary.totalTerpakai)}</strong>
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
                      <h3>
                        <span style={{ color: "#EDA60F", WebkitTextFillColor: "#EDA60F" }}>
                          {summary.progressDana.toFixed(0)}%
                        </span>
                      </h3>
                    </div>
                    <span>{money(summary.totalAnggaran)} total anggaran</span>
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

              <section className="kepsek-bottom-grid">
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
                            <strong>{item.program_kerja?.PROGRAM_KERJA || item.PROGRAM_KERJA || "Program kerja"}</strong>
                            <p>{formatDate(item.TGL_FPD)}</p>
                          </div>
                          <span>{money(item.NOMINAL_ANGGARAN)}</span>
                        </article>
                      ))
                    )}
                  </div>
                </div>

                <div className="kepsek-panel">
                  <div className="kepsek-panel-head compact">
                    <div>
                      <p>Buku Kas Umum</p>
                      <h3>Transaksi terbaru</h3>
                    </div>
                  </div>

                  <div className="kepsek-list">
                    {recentBku.length === 0 ? (
                      <div className="kepsek-empty-state">Belum ada data BKU.</div>
                    ) : (
                      recentBku.map((item, index) => (
                        <article key={item.ID_BKU || index} className="kepsek-list-item">
                          <div>
                            <strong>{readText(item, "URAIAN", "uraian")}</strong>
                            <p>{formatDate(item.TANGGAL || item.tanggal)}</p>
                          </div>
                          <span>{money(item.SALDO || item.saldo)}</span>
                        </article>
                      ))
                    )}
                  </div>
                </div>
              </section>
            </>
          ) : (
            <>
              <section className="kepsek-kpi-grid">
                <article className="kepsek-kpi-card accent-blue">
                  <span>Total Program Kerja</span>
                  <strong>{summary.totalProgramKerja}</strong>
                </article>
                <article className="kepsek-kpi-card accent-green">
                  <span>Total Evaluasi</span>
                  <strong>{summary.totalEvaluasi}</strong>
                </article>
                <article className="kepsek-kpi-card accent-gold">
                  <span>Disetujui</span>
                  <strong>{summary.approvedCount}</strong>
                </article>
                <article className="kepsek-kpi-card accent-secondary">
                  <span>Menunggu / Draft</span>
                  <strong>{summary.waitingCount}</strong>
                </article>
              </section>

              <section className="kepsek-insight-grid">
                <div className="kepsek-panel">
                  <div className="kepsek-panel-head">
                    <div>
                      <p>Progress Persetujuan</p>
                      <h3>
                        <span style={{ color: "#EDA60F", WebkitTextFillColor: "#EDA60F" }}>
                          {summary.progressRkt.toFixed(0)}%
                        </span>
                      </h3>
                    </div>
                    <span>{summary.revisionCount} revisi</span>
                  </div>
                  <div className="kepsek-progress-track">
                    <div className="kepsek-progress-fill" style={{ width: `${summary.progressRkt}%` }} />
                  </div>
                  <p className="kepsek-panel-note">
                    Persentase program kerja yang sudah masuk status disetujui kepala sekolah.
                  </p>
                </div>

                <div className="kepsek-panel">
                  <div className="kepsek-panel-head">
                    <div>
                      <p>Status Ringkas</p>
                      <h3>Distribusi RKT</h3>
                    </div>
                    <span>Data terbaru</span>
                  </div>
                  <div className="kepsek-status-stack">
                    <div>
                      <label>Disetujui</label>
                      <strong>{summary.approvedCount}</strong>
                    </div>
                    <div>
                      <label>Menunggu</label>
                      <strong>{summary.waitingCount}</strong>
                    </div>
                    <div>
                      <label>Revisi</label>
                      <strong>{summary.revisionCount}</strong>
                    </div>
                  </div>
                </div>
              </section>

              <section className="kepsek-bottom-grid">
                <div className="kepsek-panel">
                  <div className="kepsek-panel-head compact">
                    <div>
                      <p>Program Kerja Terbaru</p>
                      <h3>RKT terbaru</h3>
                    </div>
                  </div>

                  <div className="kepsek-list">
                    {recentProgramKerja.length === 0 ? (
                      <div className="kepsek-empty-state">Belum ada program kerja.</div>
                    ) : (
                      recentProgramKerja.map((item, index) => {
                        const statusLabel = item.trPm?.DESKRIPSI_TR_PM || item.trPm?.deskripsi_tr_pm || "Belum ada status";

                        return (
                          <article key={item.ID_PROGRAM_KERJA || index} className="kepsek-list-item">
                            <div>
                              <strong>{item.PROGRAM_KERJA || "Program kerja"}</strong>
                              <p>{item.tahunAnggaran?.DESKRIPSI_TAHUN_ANGGARAN || item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "-"}</p>
                            </div>
                            <div className="kepsek-list-meta">
                              <span>{money(item.NOMINAL)}</span>
                              <small>{statusLabel}</small>
                            </div>
                          </article>
                        );
                      })
                    )}
                  </div>
                </div>

                <div className="kepsek-panel">
                  <div className="kepsek-panel-head compact">
                    <div>
                      <p>Evaluasi Terbaru</p>
                      <h3>Jejak penilaian RKT</h3>
                    </div>
                  </div>

                  <div className="kepsek-list">
                    {recentEvaluasi.length === 0 ? (
                      <div className="kepsek-empty-state">Belum ada evaluasi RKT.</div>
                    ) : (
                      recentEvaluasi.map((item, index) => (
                        <article key={item.ID_EVALUASI || index} className="kepsek-list-item">
                          <div>
                            <strong>{item.program_kerja?.PROGRAM_KERJA || "Program kerja"}</strong>
                            <p>{formatDate(item.TGL_PM)}</p>
                          </div>
                          <div className="kepsek-list-meta">
                            <span>{item.ref_pm?.NAMA_PM || "-"}</span>
                            <small>{readText(item, "DESKRIPSI_TR_PM", "deskripsi_tr_pm")}</small>
                          </div>
                        </article>
                      ))
                    )}
                  </div>
                </div>
              </section>
            </>
          )}
        </>
      )}
    </main>
  );
}

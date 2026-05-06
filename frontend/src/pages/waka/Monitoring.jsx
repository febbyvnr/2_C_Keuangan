import { useEffect, useState, useMemo } from "react";
import SidebarWaka from "../../components/SidebarWaka";
import "../../styles/waka/Monitoring.css";

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";
const fmt = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });

function getStatusInfo(item) {
    const trPmList = item?.trPm || item?.tr_pm || [];
    const lastNote = (trPmList[trPmList.length - 1]?.DESKRIPSI_TR_PM || "").toLowerCase().trim();
    const validator = item?.NIP_VALIDATOR_PROGKER;

    if (lastNote.startsWith("draft")) return { value: "draft", label: "Draft", color: "#6b7280", bg: "#f3f4f6" };
    if (lastNote.startsWith("ditolak")) return { value: "ditolak", label: "Ditolak", color: "#dc2626", bg: "#fef2f2" };
    if (lastNote.startsWith("revisi")) return { value: "revisi", label: "Revisi", color: "#d97706", bg: "#fffbeb" };
    if (validator) return { value: "disetujui", label: "Disetujui", color: "#16a34a", bg: "#f0fdf4" };
    return { value: "diajukan", label: "Menunggu Approval", color: "#2563eb", bg: "#eff6ff" };
}

function ProgressBar({ value, max, color = "#265f9c" }) {
    const pct = max > 0 ? Math.min((value / max) * 100, 100) : 0;
    return (
        <div className="mon-progress-track">
            <div className="mon-progress-fill" style={{ width: `${pct}%`, background: color }} />
        </div>
    );
}

export default function MonitoringWaka() {
    const [rkt, setRkt] = useState([]);
    const [fpd, setFpd] = useState([]);
    const [rka, setRka] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [filterStatus, setFilterStatus] = useState("all");
    const [filterUnit, setFilterUnit] = useState("all");
    const [selectedYear, setSelectedYear] = useState("all");
    const [activeTab, setActiveTab] = useState("overview");

    useEffect(() => {
        Promise.all([
            fetch(`${API_BASE}/rkt`).then(r => r.json()),
            fetch(`${API_BASE}/fpd-anggaran`).then(r => r.json()),
            fetch(`${API_BASE}/rka`).then(r => r.json()),
        ]).then(([r1, r2, r3]) => {
            setRkt(r1.data || r1 || []);
            setFpd(r2.data || r2 || []);
            setRka(r3.data || r3 || []);
        }).catch(console.error)
          .finally(() => setLoading(false));
    }, []);

    const units = useMemo(() => {
        const s = new Set();
        rkt.forEach(i => { if (i.unit?.NAMA_UNIT || i.nama_unit) s.add(i.unit?.NAMA_UNIT || i.nama_unit); });
        return [...s];
    }, [rkt]);

    const filteredRkt = useMemo(() => {
        return rkt.filter(item => {
            const st = getStatusInfo(item).value;
            const matchStatus = filterStatus === "all" || st === filterStatus;
            const unitName = item.unit?.NAMA_UNIT || item.nama_unit || "";
            const matchUnit = filterUnit === "all" || unitName === filterUnit;
            const kw = search.toLowerCase();
            const matchSearch = !kw ||
                (item.PROGRAM_KERJA || "").toLowerCase().includes(kw) ||
                (item.NIP_PENANGGUNG_JAWAB || "").toLowerCase().includes(kw);
            const year = item.WAKTU_AWAL ? new Date(item.WAKTU_AWAL).getFullYear().toString() : "";
            const matchYear = selectedYear === "all" || year === selectedYear;
            return matchStatus && matchUnit && matchSearch && matchYear;
        });
    }, [rkt, filterStatus, filterUnit, search, selectedYear]);

    const stats = useMemo(() => {
        const total = rkt.length;
        const byStatus = {};
        rkt.forEach(i => {
            const s = getStatusInfo(i).value;
            byStatus[s] = (byStatus[s] || 0) + 1;
        });
        const totalBudget = rkt.reduce((s, i) => s + (i.TOTAL_PROGKER || 0), 0);
        const totalTerpakai = fpd.reduce((s, d) => s + (d.NOMINAL_FPD || d.nominal_fpd || 0), 0);
        const totalRka = rka.length;
        return { total, byStatus, totalBudget, totalTerpakai, totalRka };
    }, [rkt, fpd, rka]);

    const unitBreakdown = useMemo(() => {
        const map = {};
        rkt.forEach(item => {
            const unit = item.unit?.NAMA_UNIT || item.nama_unit || "Tidak Diketahui";
            if (!map[unit]) map[unit] = { total: 0, disetujui: 0, budget: 0 };
            map[unit].total++;
            if (getStatusInfo(item).value === "disetujui") map[unit].disetujui++;
            map[unit].budget += item.TOTAL_PROGKER || 0;
        });
        return Object.entries(map).sort((a, b) => b[1].total - a[1].total);
    }, [rkt]);

    const pctTerpakai = stats.totalBudget > 0
    ? Math.min((stats.totalTerpakai / stats.totalBudget) * 100, 100)
    : 0;

    const pctLabel = `${pctTerpakai.toFixed(1)}%`;
    const pctMarkerLeft = Math.min(Math.max(pctTerpakai, 6), 94);


    return (
        <div className="mon-shell">
            <SidebarWaka />
            <main className="mon-main">
                {/* Header */}
                <div className="mon-topbar">
                    <div>
                        <h1 className="mon-page-title">Monitoring</h1>
                        <p className="mon-page-sub">Pantau progres seluruh program kerja dan realisasi anggaran</p>
                    </div>
                    <div className="mon-year-filter">
                        <label>Tahun</label>
                        <select value={selectedYear} onChange={e => setSelectedYear(e.target.value)}>
                            <option value="all">Semua</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                </div>

                {/* KPI - CLEAN (border kiri, no icon) */}
                <div className="mon-kpi-grid">
                    <div className="mon-kpi-card primary">
                        <div className="mon-kpi-label">Total Program Kerja</div>
                        <div className="mon-kpi-val">{stats.total}</div>
                        <div className="mon-kpi-pct">{stats.total > 0 ? "100%" : "0%"}</div>
                    </div>
                    <div className="mon-kpi-card green">
                        <div className="mon-kpi-label">Disetujui</div>
                        <div className="mon-kpi-val">{stats.byStatus.disetujui || 0}</div>
                        <div className="mon-kpi-pct">{stats.total > 0 ? (((stats.byStatus.disetujui || 0)/stats.total)*100).toFixed(0) : 0}%</div>
                    </div>
                    <div className="mon-kpi-card yellow">
                        <div className="mon-kpi-label">Menunggu</div>
                        <div className="mon-kpi-val">{stats.byStatus.diajukan || 0}</div>
                        <div className="mon-kpi-pct">Perlu review</div>
                    </div>
                    <div className="mon-kpi-card red">
                        <div className="mon-kpi-label">Perlu Perbaikan</div>
                        <div className="mon-kpi-val">{(stats.byStatus.revisi || 0) + (stats.byStatus.ditolak || 0)}</div>
                        <div className="mon-kpi-pct">Revisi / ditolak</div>
                    </div>
                </div>

                {/* Budget Overview */}
                <div className="mon-budget-bar">
                    <div className="mon-budget-info">
                        <div>
                            <div className="mon-budget-label">Total Anggaran RKT</div>
                            <div className="mon-budget-val">{fmt.format(stats.totalBudget)}</div>
                        </div>
                        <div>
                            <div className="mon-budget-label">Terpakai (FPD)</div>
                            <div className="mon-budget-val terpakai">{fmt.format(stats.totalTerpakai)}</div>
                        </div>
                        <div>
                            <div className="mon-budget-label">Sisa Anggaran</div>
                            <div className="mon-budget-val sisa">{fmt.format(Math.max(0, stats.totalBudget - stats.totalTerpakai))}</div>
                        </div>
                        <div>
                            <div className="mon-budget-label">Serapan</div>
                            <div className="mon-budget-val">{pctTerpakai}%</div>
                        </div>
                    </div>
                    <ProgressBar value={stats.totalTerpakai} max={stats.totalBudget} color="#265f9c" />
                    <div className="mon-budget-pct-row">
                      <span>0%</span>
                        {pctTerpakai > 0 && (
                         <span className="mon-budget-marker" style={{ left: `${pctMarkerLeft}%` }}>
                            {pctLabel}
                                </span>
                        )}
                            <span>100%</span>
                        </div>
                         </div>

                {/* Tabs (no icon) */}
                <div className="mon-tabs">
                    {["overview","program","unit"].map(tab => (
                        <button key={tab} className={`mon-tab ${activeTab === tab ? "active" : ""}`} onClick={() => setActiveTab(tab)}>
                            {tab === "overview" && "Ringkasan"}
                            {tab === "program"  && "Daftar Program"}
                            {tab === "unit"     && "Per Unit"}
                        </button>
                    ))}
                </div>

                {loading ? (
                    <div className="mon-loading"><div className="mon-spinner" /><p>Memuat data...</p></div>
                ) : activeTab === "overview" ? (
                    <div className="mon-overview-grid">
                        <div className="mon-card">
                            <div className="mon-card-title">Distribusi Status</div>
                            <div className="mon-status-list">
                                {[
                                    { key: "disetujui", label: "Disetujui", color: "#16a34a" },
                                    { key: "diajukan",  label: "Menunggu Approval", color: "#2563eb" },
                                    { key: "revisi",    label: "Revisi", color: "#d97706" },
                                    { key: "ditolak",   label: "Ditolak", color: "#dc2626" },
                                    { key: "draft",     label: "Draft", color: "#6b7280" },
                                ].map(({ key, label, color }) => {
                                    const count = stats.byStatus[key] || 0;
                                    return (
                                        <div key={key} className="mon-status-row">
                                            <div className="mon-status-info">
                                                <span className="mon-status-dot" style={{ background: color }} />
                                                <span className="mon-status-label">{label}</span>
                                                <span className="mon-status-count">{count}</span>
                                            </div>
                                            <ProgressBar value={count} max={stats.total} color={color} />
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="mon-card">
                            <div className="mon-card-title">Top 5 Program Anggaran Terbesar</div>
                            <div className="mon-top-list">
                                {[...rkt].sort((a, b) => (b.TOTAL_PROGKER||0) - (a.TOTAL_PROGKER||0)).slice(0, 5).map((item, i) => (
                                    <div key={item.ID_PROGRAM_KERJA} className="mon-top-item">
                                        <div className="mon-top-rank">#{i+1}</div>
                                        <div className="mon-top-info">
                                            <div className="mon-top-name">{item.PROGRAM_KERJA || "-"}</div>
                                            <div className="mon-top-unit">{item.unit?.NAMA_UNIT || item.nama_unit || "-"}</div>
                                        </div>
                                        <div className="mon-top-budget">{fmt.format(item.TOTAL_PROGKER||0)}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                ) : activeTab === "program" ? (
                    <div className="mon-card">
                        <div className="mon-filter-row">
                            <input
                                className="mon-search"
                                placeholder="Cari program kerja atau PIC..."
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                            />
                            <select className="mon-select" value={filterStatus} onChange={e => setFilterStatus(e.target.value)}>
                                <option value="all">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="revisi">Revisi</option>
                                <option value="ditolak">Ditolak</option>
                                <option value="disetujui">Disetujui</option>
                            </select>
                            <select className="mon-select" value={filterUnit} onChange={e => setFilterUnit(e.target.value)}>
                                <option value="all">Semua Unit</option>
                                {units.map(u => <option key={u} value={u}>{u}</option>)}
                            </select>
                        </div>
                        <div className="mon-table-wrap">
                            <table className="mon-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Program Kerja</th>
                                        <th>Unit</th>
                                        <th>PIC</th>
                                        <th>Anggaran</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredRkt.length === 0 ? (
                                        <tr><td colSpan="7" className="mon-empty-cell">Tidak ada data</td></tr>
                                    ) : filteredRkt.map((item, i) => {
                                        const st = getStatusInfo(item);
                                        return (
                                            <tr key={item.ID_PROGRAM_KERJA}>
                                                <td>{i+1}</td>
                                                <td className="mon-td-prog">{item.PROGRAM_KERJA || "-"}</td>
                                                <td>{item.unit?.NAMA_UNIT || item.nama_unit || "-"}</td>
                                                <td>{item.penanggung_jawab?.NAMA_KARYAWAN || item.NIP_PENANGGUNG_JAWAB || "-"}</td>
                                                <td className="mon-td-num">{fmt.format(item.TOTAL_PROGKER||0)}</td>
                                                <td className="mon-td-date">
                                                    {item.WAKTU_AWAL ? new Date(item.WAKTU_AWAL).toLocaleDateString("id-ID") : "-"}
                                                </td>
                                                <td>
                                                    <span className="mon-badge" style={{ color: st.color, background: st.bg }}>{st.label}</span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                        <div className="mon-table-footer">
                            Menampilkan {filteredRkt.length} dari {rkt.length} program
                        </div>
                    </div>
                ) : (
                    <div className="mon-card">
                        <div className="mon-card-title">Monitoring Per Unit</div>
                        <div className="mon-unit-grid">
                            {unitBreakdown.map(([unit, data]) => (
                                <div key={unit} className="mon-unit-card">
                                    <div className="mon-unit-name">{unit}</div>
                                    <div className="mon-unit-stats">
                                        <div className="mon-unit-stat">
                                            <span className="mon-unit-stat-val">{data.total}</span>
                                            <span className="mon-unit-stat-label">Program</span>
                                        </div>
                                        <div className="mon-unit-stat">
                                            <span className="mon-unit-stat-val" style={{ color: "#16a34a" }}>{data.disetujui}</span>
                                            <span className="mon-unit-stat-label">Disetujui</span>
                                        </div>
                                        <div className="mon-unit-stat">
                                            <span className="mon-unit-stat-val">{fmt.format(data.budget)}</span>
                                            <span className="mon-unit-stat-label">Anggaran</span>
                                        </div>
                                    </div>
                                    <div className="mon-unit-progress-wrap">
                                        <div className="mon-unit-progress-label">
                                            <span>Progress Persetujuan</span>
                                            <span>{data.total > 0 ? ((data.disetujui/data.total)*100).toFixed(0) : 0}%</span>
                                        </div>
                                        <ProgressBar value={data.disetujui} max={data.total} color="#265f9c" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
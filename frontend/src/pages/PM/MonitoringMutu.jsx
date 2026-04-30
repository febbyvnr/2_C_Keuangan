import { useEffect, useState, useMemo } from "react";
import "../../styles/pm/MonitoringMutu.css";

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
    return { value: "diajukan", label: "Menunggu Review", color: "#2563eb", bg: "#eff6ff" };
}

function ScoreMeter({ value, max = 100 }) {
    const pct = Math.min((value / max) * 100, 100);
    const color = pct >= 80 ? "#16a34a" : pct >= 50 ? "#d97706" : "#dc2626";
    return (
        <div className="pm-meter">
            <div className="pm-meter-track">
                <div className="pm-meter-fill" style={{ width: `${pct}%`, background: color }} />
            </div>
            <span className="pm-meter-label" style={{ color }}>{pct.toFixed(0)}%</span>
        </div>
    );
}

function MutuCard({ title, value, icon, color, sub }) {
    return (
        <div className="pm-kpi-card" style={{ borderTopColor: color }}>
            <div className="pm-kpi-icon" style={{ color, background: color + "18" }}>
                <i className={icon} />
            </div>
            <div className="pm-kpi-val">{value}</div>
            <div className="pm-kpi-title">{title}</div>
            {sub && <div className="pm-kpi-sub">{sub}</div>}
        </div>
    );
}

export default function MonitoringMutu() {
    const [rkt, setRkt] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [filterUnit, setFilterUnit] = useState("all");
    const [selected, setSelected] = useState(null);
    const [activeTab, setActiveTab] = useState("semua");

    useEffect(() => {
        fetch(`${API_BASE}/rkt`)
            .then(r => r.json())
            .then(res => {
                const data = res.data || res || [];
                setRkt(data);
                if (data.length > 0) setSelected(data[0]);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    const units = useMemo(() => {
        const s = new Set();
        rkt.forEach(i => { const u = i.unit?.NAMA_UNIT || i.nama_unit; if (u) s.add(u); });
        return [...s];
    }, [rkt]);

    const stats = useMemo(() => {
        const total = rkt.length;
        const disetujui = rkt.filter(i => getStatusInfo(i).value === "disetujui").length;
        const butuhRevisi = rkt.filter(i => ["revisi","ditolak"].includes(getStatusInfo(i).value)).length;
        const menunggu = rkt.filter(i => getStatusInfo(i).value === "diajukan").length;
        const totalBudget = rkt.reduce((s, i) => s + (i.TOTAL_PROGKER || 0), 0);
        const conformanceRate = total > 0 ? (disetujui / total) * 100 : 0;
        return { total, disetujui, butuhRevisi, menunggu, totalBudget, conformanceRate };
    }, [rkt]);

    const filtered = useMemo(() => {
        const byTab = activeTab === "semua" ? rkt
            : rkt.filter(i => getStatusInfo(i).value === activeTab);

        return byTab.filter(item => {
            const unitName = item.unit?.NAMA_UNIT || item.nama_unit || "";
            const matchUnit = filterUnit === "all" || unitName === filterUnit;
            const kw = search.toLowerCase();
            const matchSearch = !kw || (item.PROGRAM_KERJA || "").toLowerCase().includes(kw) ||
                (item.NIP_PENANGGUNG_JAWAB || "").toLowerCase().includes(kw);
            return matchUnit && matchSearch;
        });
    }, [rkt, activeTab, filterUnit, search]);

    const unitMutuData = useMemo(() => {
        const map = {};
        rkt.forEach(item => {
            const unit = item.unit?.NAMA_UNIT || item.nama_unit || "Tidak Diketahui";
            if (!map[unit]) map[unit] = { total: 0, disetujui: 0, revisi: 0, menunggu: 0 };
            const st = getStatusInfo(item).value;
            map[unit].total++;
            if (st === "disetujui") map[unit].disetujui++;
            if (["revisi","ditolak"].includes(st)) map[unit].revisi++;
            if (st === "diajukan") map[unit].menunggu++;
        });
        return Object.entries(map).sort((a, b) => {
            const rateA = a[1].total > 0 ? a[1].disetujui / a[1].total : 0;
            const rateB = b[1].total > 0 ? b[1].disetujui / b[1].total : 0;
            return rateB - rateA;
        });
    }, [rkt]);

    return (
        <div className="pm-shell">
            {/* Top Nav (placeholder for sidebar-less layout) */}
            <div className="pm-topnav">
                <div className="pm-topnav-brand">
                    <div className="pm-topnav-logo">
                        <i className="bi bi-shield-check" />
                    </div>
                    <div>
                        <div className="pm-topnav-title">SIBOKU</div>
                        <div className="pm-topnav-sub">Tim Penjaminan Mutu</div>
                    </div>
                </div>
                <div className="pm-topnav-user">
                    <div className="pm-topnav-avatar">PM</div>
                    <div className="pm-topnav-info">
                        <div className="pm-topnav-name">Tim PM</div>
                        <div className="pm-topnav-role">Penjaminan Mutu</div>
                    </div>
                </div>
            </div>

            <div className="pm-body">
                {/* Sidebar mini */}
                <aside className="pm-sidebar">
                    <nav className="pm-sidenav">
                        <a href="#" className="pm-sidenav-item active">
                            <i className="bi bi-bar-chart-line" />
                            <span>Monitoring Mutu</span>
                        </a>
                        <a href="#" className="pm-sidenav-item">
                            <i className="bi bi-clipboard-data" />
                            <span>Evaluasi RKT</span>
                        </a>
                        <a href="#" className="pm-sidenav-item">
                            <i className="bi bi-file-earmark-check" />
                            <span>Review Program</span>
                        </a>
                        <a href="#" className="pm-sidenav-item">
                            <i className="bi bi-graph-up-arrow" />
                            <span>Laporan Mutu</span>
                        </a>
                    </nav>
                </aside>

                <main className="pm-main">
                    <div className="pm-header">
                        <div>
                            <h1 className="pm-page-title">Monitoring Mutu</h1>
                            <p className="pm-page-sub">Pantau standar mutu dan kesesuaian program kerja seluruh unit</p>
                        </div>
                        <div className="pm-header-badge">
                            <i className="bi bi-patch-check-fill" />
                            Tim Penjaminan Mutu
                        </div>
                    </div>

                    {/* Conformance Rate — highlight metric */}
                    <div className="pm-conformance-card">
                        <div className="pm-conformance-left">
                            <div className="pm-conformance-label">Tingkat Kesesuaian Mutu (Conformance Rate)</div>
                            <div className="pm-conformance-val">
                                {stats.conformanceRate.toFixed(1)}
                                <span>%</span>
                            </div>
                            <div className="pm-conformance-sub">
                                {stats.disetujui} dari {stats.total} program telah memenuhi standar mutu
                            </div>
                        </div>
                        <div className="pm-conformance-right">
                            <div className="pm-big-meter">
                                <svg viewBox="0 0 120 60" className="pm-gauge-svg">
                                    <path d="M10,60 A50,50 0 0,1 110,60" fill="none" stroke="#f0f0f0" strokeWidth="10" strokeLinecap="round" />
                                    <path d="M10,60 A50,50 0 0,1 110,60" fill="none" stroke={
                                        stats.conformanceRate >= 80 ? "#16a34a" : stats.conformanceRate >= 50 ? "#d97706" : "#dc2626"
                                    } strokeWidth="10" strokeLinecap="round"
                                    strokeDasharray={`${(stats.conformanceRate / 100) * 157} 157`} />
                                </svg>
                                <div className="pm-gauge-center">
                                    <span>{stats.conformanceRate >= 80 ? "Baik" : stats.conformanceRate >= 50 ? "Cukup" : "Perlu Perhatian"}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* KPI Cards */}
                    <div className="pm-kpi-row">
                        <MutuCard title="Total Program" value={stats.total} icon="bi bi-journals" color="#265f9c" sub="Seluruh unit" />
                        <MutuCard title="Memenuhi Mutu" value={stats.disetujui} icon="bi bi-check-circle-fill" color="#16a34a" sub="Sudah disetujui" />
                        <MutuCard title="Perlu Review" value={stats.menunggu} icon="bi bi-hourglass-split" color="#2563eb" sub="Menunggu review PM" />
                        <MutuCard title="Perlu Perbaikan" value={stats.butuhRevisi} icon="bi bi-exclamation-triangle-fill" color="#dc2626" sub="Revisi / ditolak" />
                    </div>

                    {/* Content Grid */}
                    <div className="pm-content-grid">
                        {/* Left: Program List */}
                        <div className="pm-list-section">
                            <div className="pm-section-head">
                                <div className="pm-section-title">Daftar Program Kerja</div>
                                <div className="pm-section-controls">
                                    <input
                                        className="pm-search"
                                        placeholder="Cari program..."
                                        value={search}
                                        onChange={e => setSearch(e.target.value)}
                                    />
                                    <select className="pm-select" value={filterUnit} onChange={e => setFilterUnit(e.target.value)}>
                                        <option value="all">Semua Unit</option>
                                        {units.map(u => <option key={u} value={u}>{u}</option>)}
                                    </select>
                                </div>
                            </div>

                            <div className="pm-tab-row">
                                {[
                                    { key: "semua", label: "Semua" },
                                    { key: "diajukan", label: "Perlu Review" },
                                    { key: "revisi", label: "Revisi" },
                                    { key: "disetujui", label: "Approved" },
                                    { key: "ditolak", label: "Ditolak" },
                                ].map(t => (
                                    <button key={t.key} className={`pm-tab-btn ${activeTab === t.key ? "active" : ""}`} onClick={() => setActiveTab(t.key)}>
                                        {t.label}
                                    </button>
                                ))}
                            </div>

                            {loading ? (
                                <div className="pm-loading">
                                    <div className="pm-spinner" />
                                    <p>Memuat data...</p>
                                </div>
                            ) : (
                                <div className="pm-prog-list">
                                    {filtered.length === 0 ? (
                                        <div className="pm-empty">
                                            <i className="bi bi-inbox" />
                                            <p>Tidak ada data</p>
                                        </div>
                                    ) : filtered.map(item => {
                                        const st = getStatusInfo(item);
                                        const trPm = item?.tr_pm || item?.trPm || [];
                                        return (
                                            <div
                                                key={item.ID_PROGRAM_KERJA}
                                                className={`pm-prog-card ${selected?.ID_PROGRAM_KERJA === item.ID_PROGRAM_KERJA ? "selected" : ""}`}
                                                onClick={() => setSelected(item)}
                                            >
                                                <div className="pm-prog-top">
                                                    <span className="pm-prog-name">{item.PROGRAM_KERJA || "-"}</span>
                                                    <span className="pm-prog-badge" style={{ color: st.color, background: st.bg }}>{st.label}</span>
                                                </div>
                                                <div className="pm-prog-meta">
                                                    <span><i className="bi bi-building" /> {item.unit?.NAMA_UNIT || item.nama_unit || "-"}</span>
                                                    <span><i className="bi bi-cash" /> {fmt.format(item.TOTAL_PROGKER || 0)}</span>
                                                </div>
                                                {trPm.length > 0 && (
                                                    <div className="pm-prog-review-count">
                                                        <i className="bi bi-chat-left-dots" /> {trPm.length} catatan review
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>

                        {/* Right: Detail + Unit Mutu */}
                        <div className="pm-right-col">
                            {/* Review Detail */}
                            <div className="pm-detail-card">
                                {!selected ? (
                                    <div className="pm-detail-empty">
                                        <i className="bi bi-shield-check" />
                                        <p>Pilih program untuk melihat detail mutu</p>
                                    </div>
                                ) : (() => {
                                    const st = getStatusInfo(selected);
                                    const trPm = selected?.tr_pm || selected?.trPm || [];
                                    const lastNote = trPm[trPm.length - 1]?.DESKRIPSI_TR_PM || "";
                                    return (
                                        <>
                                            <div className="pm-detail-title">{selected.PROGRAM_KERJA}</div>
                                            <div className="pm-detail-badge-row">
                                                <span className="pm-prog-badge lg" style={{ color: st.color, background: st.bg }}>{st.label}</span>
                                                <span className="pm-detail-unit">{selected.unit?.NAMA_UNIT || selected.nama_unit || "-"}</span>
                                            </div>

                                            <div className="pm-detail-metrics">
                                                <div className="pm-dm-item">
                                                    <span className="pm-dm-label">Anggaran</span>
                                                    <span className="pm-dm-val blue">{fmt.format(selected.TOTAL_PROGKER || 0)}</span>
                                                </div>
                                                <div className="pm-dm-item">
                                                    <span className="pm-dm-label">Jumlah Review</span>
                                                    <span className="pm-dm-val">{trPm.length}</span>
                                                </div>
                                                <div className="pm-dm-item">
                                                    <span className="pm-dm-label">Validator</span>
                                                    <span className="pm-dm-val">{selected.NIP_VALIDATOR_PROGKER || "-"}</span>
                                                </div>
                                                <div className="pm-dm-item">
                                                    <span className="pm-dm-label">PIC</span>
                                                    <span className="pm-dm-val">{selected.penanggung_jawab?.NAMA_KARYAWAN || selected.NIP_PENANGGUNG_JAWAB || "-"}</span>
                                                </div>
                                            </div>

                                            {lastNote && (
                                                <div className={`pm-note ${st.value === "ditolak" ? "danger" : st.value === "revisi" ? "warning" : "info"}`}>
                                                    <div className="pm-note-head">Catatan Terakhir</div>
                                                    <p>{lastNote}</p>
                                                </div>
                                            )}

                                            {trPm.length > 0 && (
                                                <div className="pm-review-history">
                                                    <div className="pm-rh-title">Riwayat Review PM</div>
                                                    {[...trPm].reverse().map((t, i) => (
                                                        <div key={i} className="pm-rh-item">
                                                            <div className="pm-rh-dot" />
                                                            <div>
                                                                <div className="pm-rh-desc">{t.DESKRIPSI_TR_PM}</div>
                                                                {t.NIP_VALIDATOR_PM && <div className="pm-rh-by">oleh {t.NIP_VALIDATOR_PM}</div>}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </>
                                    );
                                })()}
                            </div>

                            {/* Unit Mutu Summary */}
                            <div className="pm-unit-mutu-card">
                                <div className="pm-unit-mutu-title">Mutu Per Unit</div>
                                {unitMutuData.map(([unit, data]) => {
                                    const rate = data.total > 0 ? (data.disetujui / data.total) * 100 : 0;
                                    const color = rate >= 80 ? "#16a34a" : rate >= 50 ? "#d97706" : "#dc2626";
                                    return (
                                        <div key={unit} className="pm-um-row">
                                            <div className="pm-um-head">
                                                <span className="pm-um-name">{unit}</span>
                                                <span className="pm-um-rate" style={{ color }}>{rate.toFixed(0)}%</span>
                                            </div>
                                            <div className="pm-um-stats">
                                                <span>{data.total} program</span>
                                                <span style={{ color: "#16a34a" }}>{data.disetujui} approved</span>
                                                {data.revisi > 0 && <span style={{ color: "#dc2626" }}>{data.revisi} perlu perbaikan</span>}
                                            </div>
                                            <ScoreMeter value={rate} />
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    );
}
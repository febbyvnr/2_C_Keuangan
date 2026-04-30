import { useEffect, useState, useMemo } from "react";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/StatusPengajuan.css";

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";
const fmt = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });

function getStatusInfo(item) {
    const trPmList = item?.trPm || item?.tr_pm || [];
    const lastNote = (trPmList[trPmList.length - 1]?.DESKRIPSI_TR_PM || "").toLowerCase().trim();
    const validator = item?.NIP_VALIDATOR_PROGKER;

    if (lastNote.startsWith("draft")) return { value: "draft", label: "Draft", color: "#6b7280", bg: "#f3f4f6" };
    if (lastNote.startsWith("ditolak")) return { value: "ditolak", label: "Ditolak", color: "#dc2626", bg: "#fef2f2" };
    if (lastNote.startsWith("revisi")) return { value: "revisi", label: "Perlu Revisi", color: "#d97706", bg: "#fffbeb" };
    if (validator) return { value: "disetujui", label: "Disetujui", color: "#16a34a", bg: "#f0fdf4" };
    return { value: "diajukan", label: "Menunggu Approval", color: "#2563eb", bg: "#eff6ff" };
}

function TimelineStep({ label, active, done, last }) {
    return (
        <div className="sp-timeline-step">
            <div className={`sp-timeline-dot ${done ? "done" : active ? "active" : ""}`}>
                {done ? "✓" : null}
            </div>
            {!last && <div className={`sp-timeline-line ${done ? "done" : ""}`} />}
            <span className={`sp-timeline-label ${active ? "active" : done ? "done" : ""}`}>{label}</span>
        </div>
    );
}

function StatusTimeline({ status }) {
    const steps = ["Draft", "Diajukan", "Review", "Disetujui"];
    const stepMap = { draft: 0, diajukan: 1, revisi: 2, ditolak: 2, disetujui: 3 };
    const current = stepMap[status] ?? 0;
    return (
        <div className="sp-timeline">
            {steps.map((s, i) => (
                <TimelineStep key={s} label={s} done={i < current} active={i === current} last={i === steps.length - 1} />
            ))}
        </div>
    );
}

export default function StatusPengajuan() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [filterStatus, setFilterStatus] = useState("all");
    const [selected, setSelected] = useState(null);

    useEffect(() => {
        fetch(`${API_BASE}/rkt`)
            .then(r => r.json())
            .then(res => {
                const rktData = res.data || res || [];
                setData(rktData);
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    const user = JSON.parse(localStorage.getItem("user") || "{}");
    const nipLogin = String(user.NIP_KARYAWAN || "");

    const myData = useMemo(() => {
        return data.filter(item => String(item.NIP_PENANGGUNG_JAWAB || "") === nipLogin || !nipLogin);
    }, [data, nipLogin]);

    const filtered = useMemo(() => {
        return myData.filter(item => {
            const s = getStatusInfo(item).value;
            const matchStatus = filterStatus === "all" || s === filterStatus;
            const kw = search.toLowerCase();
            const matchSearch = !kw || (item.PROGRAM_KERJA || "").toLowerCase().includes(kw);
            return matchStatus && matchSearch;
        });
    }, [myData, filterStatus, search]);

    const stats = useMemo(() => {
        const total = myData.length;
        const disetujui = myData.filter(i => getStatusInfo(i).value === "disetujui").length;
        const pending = myData.filter(i => ["diajukan"].includes(getStatusInfo(i).value)).length;
        const revisi = myData.filter(i => ["revisi", "ditolak"].includes(getStatusInfo(i).value)).length;
        const totalBudget = myData.reduce((s, i) => s + (i.TOTAL_PROGKER || 0), 0);
        return { total, disetujui, pending, revisi, totalBudget };
    }, [myData]);

    return (
        <div className="sp-shell">
            <SidebarPic />
            <main className="sp-main">
                <div className="sp-topbar">
                    <div>
                        <h1 className="sp-page-title">Status Pengajuan</h1>
                        <p className="sp-page-sub">Pantau perkembangan seluruh program kerja yang kamu ajukan</p>
                    </div>
                </div>

                {/* KPI Cards - Minimalis */}
                <div className="sp-kpi-row">
                    <div className="sp-kpi-card">
                        <div className="sp-kpi-val">{stats.total}</div>
                        <div className="sp-kpi-label">Total Program</div>
                    </div>
                    <div className="sp-kpi-card green">
                        <div className="sp-kpi-val">{stats.disetujui}</div>
                        <div className="sp-kpi-label">Disetujui</div>
                    </div>
                    <div className="sp-kpi-card yellow">
                        <div className="sp-kpi-val">{stats.pending}</div>
                        <div className="sp-kpi-label">Menunggu</div>
                    </div>
                    <div className="sp-kpi-card red">
                        <div className="sp-kpi-val">{stats.revisi}</div>
                        <div className="sp-kpi-label">Perlu Perhatian</div>
                    </div>
                    <div className="sp-kpi-card wide">
                        <div className="sp-kpi-val">{fmt.format(stats.totalBudget)}</div>
                        <div className="sp-kpi-label">Total Anggaran</div>
                    </div>
                </div>

                <div className="sp-content-grid">
                    {/* Left: List */}
                    <div className="sp-list-panel">
                        <div className="sp-filter-bar">
                            <input
                                className="sp-search"
                                placeholder="Cari program kerja..."
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                            />
                            <div className="sp-filter-pills">
                                {["all","draft","diajukan","revisi","ditolak","disetujui"].map(s => (
                                    <button
                                        key={s}
                                        className={`sp-pill ${filterStatus === s ? "active" : ""}`}
                                        onClick={() => setFilterStatus(s)}
                                    >
                                        {s === "all" ? "Semua" : s.charAt(0).toUpperCase() + s.slice(1)}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="sp-list">
                            {loading ? (
                                <div className="sp-empty">
                                    <div className="sp-spinner" />
                                    <p>Memuat data...</p>
                                </div>
                            ) : filtered.length === 0 ? (
                                <div className="sp-empty">
                                    <p>Tidak ada program kerja ditemukan</p>
                                </div>
                            ) : filtered.map(item => {
                                const st = getStatusInfo(item);
                                const isSelected = selected?.ID_PROGRAM_KERJA === item.ID_PROGRAM_KERJA;
                                return (
                                    <div
                                        key={item.ID_PROGRAM_KERJA}
                                        className={`sp-list-item ${isSelected ? "selected" : ""}`}
                                        onClick={() => setSelected(item)}
                                    >
                                        <div className="sp-list-item-top">
                                            <span className="sp-prog-name">{item.PROGRAM_KERJA || "-"}</span>
                                            <span className="sp-badge" style={{ color: st.color, background: st.bg }}>{st.label}</span>
                                        </div>
                                        <div className="sp-list-item-meta">
                                            <span>{item.WAKTU_AWAL ? new Date(item.WAKTU_AWAL).toLocaleDateString("id-ID") : "-"}</span>
                                            <span>{fmt.format(item.TOTAL_PROGKER || 0)}</span>
                                        </div>
                                        {st.value === "revisi" || st.value === "ditolak" ? (
                                            <div className="sp-list-alert">
                                                Ada catatan yang perlu ditindaklanjuti
                                            </div>
                                        ) : null}
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Right: Detail */}
                    <div className="sp-detail-panel">
                        {!selected ? (
                            <div className="sp-detail-empty">
                                <p>Pilih program kerja untuk melihat detail</p>
                            </div>
                        ) : (() => {
                            const st = getStatusInfo(selected);
                            const trPm = selected?.tr_pm || selected?.trPm || [];
                            const lastNote = trPm[trPm.length - 1]?.DESKRIPSI_TR_PM || "";
                            return (
                                <div className="sp-detail-body">
                                    <div className="sp-detail-head">
                                        <div>
                                            <h2 className="sp-detail-title">{selected.PROGRAM_KERJA}</h2>
                                            <span className="sp-badge lg" style={{ color: st.color, background: st.bg }}>{st.label}</span>
                                        </div>
                                    </div>

                                    <StatusTimeline status={st.value} />

                                    <div className="sp-detail-section">
                                        <div className="sp-detail-label-title">Informasi Program</div>
                                        <div className="sp-detail-grid">
                                            <div className="sp-detail-field">
                                                <span className="sp-field-label">Indikator</span>
                                                <span className="sp-field-val">{selected.INDIKATOR || "-"}</span>
                                            </div>
                                            <div className="sp-detail-field">
                                                <span className="sp-field-label">Sasaran</span>
                                                <span className="sp-field-val">{selected.SASARAN || "-"}</span>
                                            </div>
                                            <div className="sp-detail-field">
                                                <span className="sp-field-label">Keluaran</span>
                                                <span className="sp-field-val">{selected.KELUARAN_PROGKER || "-"}</span>
                                            </div>
                                            <div className="sp-detail-field">
                                                <span className="sp-field-label">Periode</span>
                                                <span className="sp-field-val">
                                                    {selected.WAKTU_AWAL ? new Date(selected.WAKTU_AWAL).toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" }) : "-"}
                                                    {" – "}
                                                    {selected.WAKTU_AKHIR ? new Date(selected.WAKTU_AKHIR).toLocaleDateString("id-ID", { day: "2-digit", month: "long", year: "numeric" }) : "-"}
                                                </span>
                                            </div>
                                            <div className="sp-detail-field highlight">
                                                <span className="sp-field-label">Total Anggaran</span>
                                                <span className="sp-field-val strong">{fmt.format(selected.TOTAL_PROGKER || 0)}</span>
                                            </div>
                                            <div className="sp-detail-field">
                                                <span className="sp-field-label">Validator</span>
                                                <span className="sp-field-val">
                                                    {selected.NIP_VALIDATOR_PROGKER || <span style={{ color: "#9ca3af" }}>Belum ada</span>}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {lastNote && (
                                        <div className={`sp-note-box ${st.value === "ditolak" ? "danger" : st.value === "revisi" ? "warning" : "info"}`}>
                                            <div className="sp-note-head">Catatan dari Reviewer</div>
                                            <p className="sp-note-content">{lastNote}</p>
                                        </div>
                                    )}

                                    {trPm.length > 0 && (
                                        <div className="sp-detail-section">
                                            <div className="sp-detail-label-title">Riwayat Review</div>
                                            <div className="sp-history-list">
                                                {[...trPm].reverse().map((t, i) => (
                                                    <div key={i} className="sp-history-item">
                                                        <div className="sp-history-dot" />
                                                        <div className="sp-history-content">
                                                            <div className="sp-history-desc">{t.DESKRIPSI_TR_PM}</div>
                                                            {t.NIP_VALIDATOR_PM && (
                                                                <div className="sp-history-meta">oleh {t.NIP_VALIDATOR_PM}</div>
                                                            )}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            );
                        })()}
                    </div>
                </div>
            </main>
        </div>
    );
}
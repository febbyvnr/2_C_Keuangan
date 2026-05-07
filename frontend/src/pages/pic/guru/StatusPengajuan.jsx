import { useEffect, useState, useMemo } from "react";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/StatusPengajuan.css";

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

const fmt = new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
});

function getStatusInfo(item) {
    const trPmList = item?.trPm || item?.tr_pm || [];
    const lastNote = (trPmList[trPmList.length - 1]?.DESKRIPSI_TR_PM || "")
        .toLowerCase()
        .trim();

    const validator = item?.NIP_VALIDATOR_PROGKER;

    if (lastNote.startsWith("draft")) {
        return {
            value: "draft",
            label: "Draft",
            color: "#585858",
            bg: "#f6f7f9",
        };
    }

    if (lastNote.startsWith("ditolak")) {
        return {
            value: "ditolak",
            label: "Ditolak",
            color: "#c62828",
            bg: "#f6f7f9",
        };
    }

    if (lastNote.startsWith("revisi")) {
        return {
            value: "revisi",
            label: "Perlu Revisi",
            color: "#eda60f",
            bg: "#f6f7f9",
        };
    }

    if (validator) {
        return {
            value: "disetujui",
            label: "Disetujui",
            color: "#2e7d32",
            bg: "#f6f7f9",
        };
    }

    return {
        value: "diajukan",
        label: "Menunggu Approval",
        color: "#265f9c",
        bg: "#f6f7f9",
    };
}

function TimelineStep({ label, active, done, last }) {
    return (
        <div className="sp-timeline-step">
            <div className={`sp-timeline-dot ${done ? "done" : active ? "active" : ""}`}>
                {done ? "✓" : null}
            </div>

            {!last && <div className={`sp-timeline-line ${done ? "done" : ""}`} />}

            <span className={`sp-timeline-label ${active ? "active" : done ? "done" : ""}`}>
                {label}
            </span>
        </div>
    );
}

function StatusTimeline({ status }) {
    const steps = ["Draft", "Diajukan", "Review", "Disetujui"];

    const stepMap = {
        draft: 0,
        diajukan: 1,
        revisi: 2,
        ditolak: 2,
        disetujui: 3,
    };

    const current = stepMap[status] ?? 0;

    return (
        <div className="sp-timeline">
            {steps.map((step, index) => (
                <TimelineStep
                    key={step}
                    label={step}
                    done={index < current}
                    active={index === current}
                    last={index === steps.length - 1}
                />
            ))}
        </div>
    );
}

function safeJsonParse(value, fallback = {}) {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch (error) {
        console.error("Gagal membaca data user dari localStorage:", error);
        return fallback;
    }
}

function getLoginNip(user) {
    return String(
        user?.NIP_KARYAWAN ||
        user?.nip_karyawan ||
        user?.nip ||
        user?.user?.NIP_KARYAWAN ||
        user?.user?.nip_karyawan ||
        ""
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
            .then(response => response.json())
            .then(responseData => {
                const rktData = responseData.data || responseData || [];
                setData(rktData);
            })
            .catch(error => {
                console.error("Gagal mengambil data RKT:", error);
            })
            .finally(() => {
                setLoading(false);
            });
    }, []);

    const user = safeJsonParse(localStorage.getItem("user"), {});
    const nipLogin = getLoginNip(user);

    const myData = useMemo(() => {
        return data.filter(item => {
            const nipPenanggungJawab = String(item.NIP_PENANGGUNG_JAWAB || "");
            return nipPenanggungJawab === nipLogin || !nipLogin;
        });
    }, [data, nipLogin]);

    const filtered = useMemo(() => {
        return myData.filter(item => {
            const status = getStatusInfo(item).value;
            const matchStatus = filterStatus === "all" || status === filterStatus;

            const keyword = search.toLowerCase();
            const programKerja = (item.PROGRAM_KERJA || "").toLowerCase();
            const matchSearch = !keyword || programKerja.includes(keyword);

            return matchStatus && matchSearch;
        });
    }, [myData, filterStatus, search]);

    const selectedItem = useMemo(() => {
        const fallback = filtered[0] ?? null;

        if (!selected) return fallback;

        const selectedStillVisible = filtered.some(
            item => item.ID_PROGRAM_KERJA === selected.ID_PROGRAM_KERJA
        );

        return selectedStillVisible ? selected : fallback;
    }, [filtered, selected]);

    const stats = useMemo(() => {
        const total = myData.length;
        const disetujui = myData.filter(item => getStatusInfo(item).value === "disetujui").length;
        const pending = myData.filter(item => getStatusInfo(item).value === "diajukan").length;
        const revisi = myData.filter(item =>
            ["revisi", "ditolak"].includes(getStatusInfo(item).value)
        ).length;

        const totalBudget = myData.reduce((sum, item) => {
            return sum + (item.TOTAL_PROGKER || 0);
        }, 0);

        return {
            total,
            disetujui,
            pending,
            revisi,
            totalBudget,
        };
    }, [myData]);

    return (
        <div className="sp-shell">
            <SidebarPic />

            <main className="sp-main">
                <div className="sp-topbar">
                    <div>
                        <h1 className="sp-page-title">Status Pengajuan</h1>
                        <p className="sp-page-sub">
                            Pantau perkembangan seluruh program kerja yang kamu ajukan
                        </p>
                    </div>
                </div>

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
                    <div className="sp-list-panel">
                        <div className="sp-filter-bar">
                            <input
                                className="sp-search"
                                placeholder="Cari program kerja..."
                                value={search}
                                onChange={event => setSearch(event.target.value)}
                            />

                            <div className="sp-filter-pills">
                                {["all", "draft", "diajukan", "revisi", "ditolak", "disetujui"].map(status => (
                                    <button
                                        key={status}
                                        type="button"
                                        className={`sp-pill ${filterStatus === status ? "active" : ""}`}
                                        onClick={() => setFilterStatus(status)}
                                    >
                                        {status === "all"
                                            ? "Semua"
                                            : status.charAt(0).toUpperCase() + status.slice(1)}
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
                            ) : (
                                filtered.map(item => {
                                    const status = getStatusInfo(item);
                                    const isSelected =
                                        selectedItem?.ID_PROGRAM_KERJA === item.ID_PROGRAM_KERJA;

                                    return (
                                        <div
                                            key={item.ID_PROGRAM_KERJA}
                                            className={`sp-list-item ${isSelected ? "selected" : ""}`}
                                            onClick={() => setSelected(item)}
                                        >
                                            <div className="sp-list-item-top">
                                                <span className="sp-prog-name">
                                                    {item.PROGRAM_KERJA || "-"}
                                                </span>

                                                <span
                                                    className="sp-badge"
                                                    style={{
                                                        color: status.color,
                                                        background: status.bg,
                                                    }}
                                                >
                                                    {status.label}
                                                </span>
                                            </div>

                                            <div className="sp-list-item-meta">
                                                <span>
                                                    {item.WAKTU_AWAL
                                                        ? new Date(item.WAKTU_AWAL).toLocaleDateString("id-ID")
                                                        : "-"}
                                                </span>

                                                <span>{fmt.format(item.TOTAL_PROGKER || 0)}</span>
                                            </div>

                                            {status.value === "revisi" || status.value === "ditolak" ? (
                                                <div className="sp-list-alert">
                                                    Ada catatan yang perlu ditindaklanjuti
                                                </div>
                                            ) : null}
                                        </div>
                                    );
                                })
                            )}
                        </div>
                    </div>

                    <div className="sp-detail-panel">
                        {!selectedItem ? (
                            <div className="sp-detail-empty">
                                <p>Pilih program kerja untuk melihat detail</p>
                            </div>
                        ) : (
                            (() => {
                                const status = getStatusInfo(selectedItem);
                                const trPm = selectedItem?.tr_pm || selectedItem?.trPm || [];
                                const lastNote = trPm[trPm.length - 1]?.DESKRIPSI_TR_PM || "";

                                return (
                                    <div className="sp-detail-body">
                                        <div className="sp-detail-head">
                                            <div>
                                                <h2 className="sp-detail-title">
                                                    {selectedItem.PROGRAM_KERJA || "-"}
                                                </h2>

                                                <span
                                                    className="sp-badge lg"
                                                    style={{
                                                        color: status.color,
                                                        background: status.bg,
                                                    }}
                                                >
                                                    {status.label}
                                                </span>
                                            </div>
                                        </div>

                                        <StatusTimeline status={status.value} />

                                        <div className="sp-detail-section">
                                            <div className="sp-detail-label-title">
                                                Informasi Program
                                            </div>

                                            <div className="sp-detail-grid">
                                                <div className="sp-detail-field">
                                                    <span className="sp-field-label">Indikator</span>
                                                    <span className="sp-field-val">
                                                        {selectedItem.INDIKATOR || "-"}
                                                    </span>
                                                </div>

                                                <div className="sp-detail-field">
                                                    <span className="sp-field-label">Sasaran</span>
                                                    <span className="sp-field-val">
                                                        {selectedItem.SASARAN || "-"}
                                                    </span>
                                                </div>

                                                <div className="sp-detail-field">
                                                    <span className="sp-field-label">Keluaran</span>
                                                    <span className="sp-field-val">
                                                        {selectedItem.KELUARAN_PROGKER || "-"}
                                                    </span>
                                                </div>

                                                <div className="sp-detail-field">
                                                    <span className="sp-field-label">Periode</span>
                                                    <span className="sp-field-val">
                                                        {selectedItem.WAKTU_AWAL
                                                            ? new Date(selectedItem.WAKTU_AWAL).toLocaleDateString(
                                                                  "id-ID",
                                                                  {
                                                                      day: "2-digit",
                                                                      month: "long",
                                                                      year: "numeric",
                                                                  }
                                                              )
                                                            : "-"}
                                                        {" – "}
                                                        {selectedItem.WAKTU_AKHIR
                                                            ? new Date(selectedItem.WAKTU_AKHIR).toLocaleDateString(
                                                                  "id-ID",
                                                                  {
                                                                      day: "2-digit",
                                                                      month: "long",
                                                                      year: "numeric",
                                                                  }
                                                              )
                                                            : "-"}
                                                    </span>
                                                </div>

                                                <div className="sp-detail-field highlight">
                                                    <span className="sp-field-label">Total Anggaran</span>
                                                    <span className="sp-field-val strong">
                                                        {fmt.format(selectedItem.TOTAL_PROGKER || 0)}
                                                    </span>
                                                </div>

                                                <div className="sp-detail-field">
                                                    <span className="sp-field-label">Validator</span>
                                                    <span className="sp-field-val">
                                                        {selectedItem.NIP_VALIDATOR_PROGKER || (
                                                            <span style={{ color: "#7d7d7e" }}>
                                                                Belum ada
                                                            </span>
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {lastNote && (
                                            <div
                                                className={`sp-note-box ${
                                                    status.value === "ditolak"
                                                        ? "danger"
                                                        : status.value === "revisi"
                                                        ? "warning"
                                                        : "info"
                                                }`}
                                            >
                                                <div className="sp-note-head">Catatan dari Reviewer</div>
                                                <p className="sp-note-content">{lastNote}</p>
                                            </div>
                                        )}

                                        {trPm.length > 0 && (
                                            <div className="sp-detail-section">
                                                <div className="sp-detail-label-title">
                                                    Riwayat Review
                                                </div>

                                                <div className="sp-history-list">
                                                    {[...trPm].reverse().map((historyItem, index) => (
                                                        <div key={index} className="sp-history-item">
                                                            <div className="sp-history-dot" />

                                                            <div className="sp-history-content">
                                                                <div className="sp-history-desc">
                                                                    {historyItem.DESKRIPSI_TR_PM}
                                                                </div>

                                                                {historyItem.NIP_VALIDATOR_PM && (
                                                                    <div className="sp-history-meta">
                                                                        oleh {historyItem.NIP_VALIDATOR_PM}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })()
                        )}
                    </div>
                </div>
            </main>
        </div>
    );
}
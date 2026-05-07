import { useState, useEffect } from "react";
// import SidebarKepsek from "../../components/SidebarKepsek";
import { apiFetch } from "../../api/api";
import "../../styles/kepsek/Monitoring.css";

const BASE_URL = "http://localhost:8000/api";

const formatRupiah = (n) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(n || 0);

const formatDate = (d) => {
    if (!d) return "-";
    const date = new Date(d);
    if (isNaN(date.getTime())) return d;
    return date.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
};

function BadgeFPD({ nip }) {
    if (!nip) return <span className="kepsek-badge pending">Menunggu</span>;
    if (nip === "Ditolak") return <span className="kepsek-badge ditolak">Ditolak</span>;
    return <span className="kepsek-badge disetujui">Disetujui</span>;
}


async function downloadFile(endpoint, filename) {
    const token = localStorage.getItem("token");
    const res = await fetch(`${BASE_URL}${endpoint}`, {
        headers: { Authorization: `Bearer ${token}`, Accept: "*/*" },
    });
    if (!res.ok) throw new Error(`Server error (${res.status})`);
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

export default function Monitoring() {
    const [tab, setTab] = useState("fpd");
    const [fpdList, setFpdList] = useState([]);
    const [evaluasiList, setEvaluasiList] = useState([]);
    const [bkuList, setBkuList] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [keyword, setKeyword] = useState("");
    const [exportingEval, setExportingEval] = useState(false);
    const [exportingBku, setExportingBku] = useState(false);
    const [toast, setToast] = useState(null);

    useEffect(() => {
        fetchData();
    }, [tab]);

    const showToast = (msg, type = "success") => {
        setToast({ msg, type });
        setTimeout(() => setToast(null), 3500);
    };

    const fetchData = async () => {
        setLoading(true);
        setError("");
        try {
            if (tab === "fpd") {
                const res = await apiFetch("/fpd-anggaran");
                setFpdList(res.data || []);
            } else if (tab === "evaluasi") {
                const res = await apiFetch("/evaluasi-rkt");
                setEvaluasiList(res.data || []);
            } else if (tab === "bku") {
                const res = await apiFetch("/laporan/bku");
                setBkuList(res.bku || res.data || []);
            }
        } catch (err) {
            setError(err.message || "Gagal mengambil data");
        } finally {
            setLoading(false);
        }
    };

    const handleExportEval = async () => {
        setExportingEval(true);
        try {
            await downloadFile("/evaluasi-rkt/export/excel", "EvaluasiRKT.xlsx");
            showToast("Berhasil mengunduh file Excel");
        } catch {
            showToast("Terjadi kesalahan pada server, silakan coba lagi.", "error");
        } finally {
            setExportingEval(false);
        }
    };

    const handleExportBku = async (type) => {
        setExportingBku(true);
        try {
            const ext = type === "pdf" ? "pdf" : "xlsx";
            await downloadFile(`/laporan/bku?type=${type}`, `BKU.${ext}`);
            showToast(`Berhasil mengunduh file ${type.toUpperCase()}`);
        } catch {
            showToast("Terjadi kesalahan pada server, silakan coba lagi.", "error");
        } finally {
            setExportingBku(false);
        }
    };

    const filteredFpd = fpdList.filter((f) => {
        const kw = keyword.toLowerCase();
        return (
            (f.program_kerja?.PROGRAM_KERJA || "").toLowerCase().includes(kw) ||
            (f.TGL_FPD || "").includes(kw) ||
            (f.NIP_VALIDATOR_FPD || "").toLowerCase().includes(kw)
        );
    });

    const filteredEvaluasi = evaluasiList.filter((e) => {
        const kw = keyword.toLowerCase();
        return (
            (e.program_kerja?.PROGRAM_KERJA || "").toLowerCase().includes(kw) ||
            (e.DESKRIPSI_TR_PM || "").toLowerCase().includes(kw) ||
            (e.ref_pm?.NAMA_PM || "").toLowerCase().includes(kw)
        );
    });

    const filteredBku = bkuList.filter((b) => {
        const kw = keyword.toLowerCase();
        return (
            (b.URAIAN || "").toLowerCase().includes(kw) ||
            (b.METODE || "").toLowerCase().includes(kw) ||
            (b.TANGGAL || "").includes(kw)
        );
    });

    const switchTab = (t) => {
        setTab(t);
        setKeyword("");
        setError("");
    };

    const dataCount =
        tab === "fpd"
            ? filteredFpd.length
            : tab === "evaluasi"
            ? filteredEvaluasi.length
            : filteredBku.length;

    return (
        <div className="kepsek-monitoring-shell">
            <main className="kepsek-monitoring-main">
                <header className="kepsek-monitoring-header">
                    <h1>Monitoring</h1>
                    <p>Pantau pengajuan dana, evaluasi rencana kerja, dan buku kas umum</p>
                </header>

                <div className="kepsek-tabs">
                    <button
                        className={`kepsek-tab-btn ${tab === "fpd" ? "active" : ""}`}
                        onClick={() => switchTab("fpd")}
                    >
                        <i className="bi bi-cash-stack"></i> Pengajuan Dana (FPD)
                    </button>
                    <button
                        className={`kepsek-tab-btn ${tab === "evaluasi" ? "active" : ""}`}
                        onClick={() => switchTab("evaluasi")}
                    >
                        <i className="bi bi-clipboard-data"></i> Evaluasi RKT
                    </button>
                    <button
                        className={`kepsek-tab-btn ${tab === "bku" ? "active" : ""}`}
                        onClick={() => switchTab("bku")}
                    >
                        <i className="bi bi-journal-text"></i> BKU
                    </button>
                </div>

                <div className="kepsek-card">
                    <div className="kepsek-card-head">
                        <span>{dataCount} data ditemukan</span>
                        <div className="kepsek-search-row">
                            <input
                                type="text"
                                placeholder="Cari..."
                                value={keyword}
                                onChange={(e) => setKeyword(e.target.value)}
                                className="kepsek-search-input"
                            />
                            <button className="kepsek-btn ghost" onClick={fetchData}>
                                <i className="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            {tab === "evaluasi" && (
                                <button
                                    className="kepsek-btn success"
                                    onClick={handleExportEval}
                                    disabled={exportingEval}
                                >
                                    <i className="bi bi-file-earmark-excel"></i>
                                    {exportingEval ? "Mengunduh..." : "Export Excel"}
                                </button>
                            )}
                            {tab === "bku" && (
                                <>
                                    <button
                                        className="kepsek-btn success"
                                        onClick={() => handleExportBku("excel")}
                                        disabled={exportingBku}
                                    >
                                        <i className="bi bi-file-earmark-excel"></i>
                                        {exportingBku ? "Mengunduh..." : "Excel"}
                                    </button>
                                    <button
                                        className="kepsek-btn danger"
                                        onClick={() => handleExportBku("pdf")}
                                        disabled={exportingBku}
                                    >
                                        <i className="bi bi-file-earmark-pdf"></i>
                                        {exportingBku ? "Mengunduh..." : "PDF"}
                                    </button>
                                </>
                            )}
                        </div>
                    </div>

                    {error && <div className="kepsek-error">{error}</div>}

                    {loading ? (
                        <div className="kepsek-loading">Memuat data...</div>
                    ) : tab === "fpd" ? (
                        <div className="kepsek-table-wrap">
                            <table className="kepsek-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Program Kerja</th>
                                        <th>Tanggal FPD</th>
                                        <th>Nominal Anggaran</th>
                                        <th>Terpakai</th>
                                        <th>Sisa</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredFpd.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="kepsek-empty">
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredFpd.map((f, i) => (
                                            <tr key={f.ID_FPD}>
                                                <td>{i + 1}</td>
                                                <td>{f.program_kerja?.PROGRAM_KERJA || "-"}</td>
                                                <td>{formatDate(f.TGL_FPD)}</td>
                                                <td>{formatRupiah(f.NOMINAL_ANGGARAN)}</td>
                                                <td>{formatRupiah(f.NOMINAL_FPD)}</td>
                                                <td>{formatRupiah(f.NOMINAL_SISA)}</td>
                                                <td>
                                                    <BadgeFPD nip={f.NIP_VALIDATOR_FPD} />
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    ) : tab === "evaluasi" ? (
                        <div className="kepsek-table-wrap">
                            <table className="kepsek-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Program Kerja</th>
                                        <th>Jenis Pencapaian</th>
                                        <th>Tanggal</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredEvaluasi.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="kepsek-empty">
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredEvaluasi.map((e, i) => (
                                            <tr key={e.ID_EVALUASI || i}>
                                                <td>{i + 1}</td>
                                                <td>{e.program_kerja?.PROGRAM_KERJA || "-"}</td>
                                                <td>{e.ref_pm?.NAMA_PM || "-"}</td>
                                                <td>{formatDate(e.TGL_PM)}</td>
                                                <td>{e.DESKRIPSI_TR_PM || "-"}</td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="kepsek-table-wrap">
                            <table className="kepsek-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Uraian</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                        <th>Metode</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredBku.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="kepsek-empty">
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredBku.map((b, i) => (
                                            <tr key={b.ID_BKU || i}>
                                                <td>{i + 1}</td>
                                                <td>{formatDate(b.TANGGAL)}</td>
                                                <td>{b.URAIAN || "-"}</td>
                                                <td>{formatRupiah(b.DEBIT)}</td>
                                                <td>{formatRupiah(b.KREDIT)}</td>
                                                <td>{b.METODE || "-"}</td>
                                                <td>{formatRupiah(b.SALDO)}</td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </main>

            {toast && (
                <div className={`kepsek-toast ${toast.type}`}>{toast.msg}</div>
            )}
        </div>
    );
}

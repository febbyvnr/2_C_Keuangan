import { useState, useEffect, useCallback, useMemo } from "react";
import { apiFetch } from "../../api/api";
import "../../styles/kepsek/Monitoring.css";

const BASE_URL = "http://localhost:8000/api";
const PER_PAGE = 10;

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

async function downloadFile(endpoint, filename, params = {}) {
    const token = localStorage.getItem("token");
    const qs = new URLSearchParams(
        Object.fromEntries(Object.entries(params).filter(([, v]) => v !== ""))
    ).toString();
    const url = `${BASE_URL}${endpoint}${qs ? "?" + qs : ""}`;
    const res = await fetch(url, {
        headers: { Authorization: `Bearer ${token}`, Accept: "*/*" },
    });
    if (!res.ok) throw new Error(`Server error (${res.status})`);
    const blob = await res.blob();
    const objUrl = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = objUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(objUrl);
}

const FPD_FILTERS_INIT  = { PROGRAM_KERJA: "", TGL_FPD: "", NIP_VALIDATOR_FPD: "" };
const EVAL_FILTERS_INIT = { keyword: "", TGL_PM: "" };

function Pagination({ page, lastPage, total, perPage, onPageChange }) {
    if (!lastPage || lastPage <= 1) return null;
    const from = total === 0 ? 0 : (page - 1) * perPage + 1;
    const to   = Math.min(page * perPage, total);
    return (
        <div className="pagination-wrapper">
            <div className="pagination-info">
                Menampilkan {from} - {to} dari {total} data
            </div>
            <div className="pagination">
                <button
                    className="page-btn"
                    onClick={() => onPageChange(page - 1)}
                    disabled={page <= 1}
                >
                    <i className="bi bi-chevron-left"></i>
                </button>
                {Array.from({ length: lastPage }, (_, i) => (
                    <button
                        key={i + 1}
                        className={`page-btn ${page === i + 1 ? "active" : ""}`}
                        onClick={() => onPageChange(i + 1)}
                    >
                        {i + 1}
                    </button>
                ))}
                <button
                    className="page-btn"
                    onClick={() => onPageChange(page + 1)}
                    disabled={page >= lastPage}
                >
                    <i className="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    );
}

function useSort() {
    const [sort, setSort] = useState({ col: null, dir: "asc" });
    const toggle = (col) =>
        setSort((s) =>
            s.col === col
                ? { col, dir: s.dir === "asc" ? "desc" : "asc" }
                : { col, dir: "asc" }
        );
    return [sort, toggle];
}

function SortTh({ col, sort, onSort, children, className = "" }) {
    const active = sort.col === col;
    return (
        <th
            className={`kepsek-th-sortable ${className}`}
            onClick={() => onSort(col)}
        >
            {children}
            <span className={`kepsek-sort-icon ${active ? "active" : "neutral"}`}>
                {active ? (sort.dir === "asc" ? " ↑" : " ↓") : " ⇅"}
            </span>
        </th>
    );
}

function applySort(list, sort, getValue) {
    if (!sort.col) return list;
    return [...list].sort((a, b) => {
        const va = getValue(a, sort.col) ?? "";
        const vb = getValue(b, sort.col) ?? "";
        const cmp =
            typeof va === "number" && typeof vb === "number"
                ? va - vb
                : String(va).localeCompare(String(vb), "id");
        return sort.dir === "asc" ? cmp : -cmp;
    });
}

export default function Monitoring() {
    const [tab, setTab] = useState("fpd");

    // FPD
    const [fpdList,       setFpdList]       = useState([]);
    const [fpdFilters,    setFpdFilters]    = useState(FPD_FILTERS_INIT);
    const [fpdPage,       setFpdPage]       = useState(1);
    const [fpdPagination, setFpdPagination] = useState(null);
    const [fpdSort,       handleFpdSort]    = useSort();

    // Evaluasi
    const [evaluasiList,   setEvaluasiList]   = useState([]);
    const [evalFilters,    setEvalFilters]    = useState(EVAL_FILTERS_INIT);
    const [evalPage,       setEvalPage]       = useState(1);
    const [evalPagination, setEvalPagination] = useState(null);
    const [evalSort,       handleEvalSort]    = useSort();

    // Global search (toolbar)
    const [search, setSearch] = useState("");

    // UI
    const [loading,   setLoading]   = useState(false);
    const [error,     setError]     = useState("");
    const [exporting, setExporting] = useState("");
    const [toast,     setToast]     = useState(null);

    const showToast = (msg, type = "success") => {
        setToast({ msg, type });
        setTimeout(() => setToast(null), 3500);
    };

    const fetchFpd = useCallback(async (filters, page) => {
        setLoading(true);
        setError("");
        try {
            const params = new URLSearchParams({
                per_page: PER_PAGE,
                page,
                ...Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== "")),
            });
            const res = await apiFetch(`/fpd-anggaran?${params}`);
            setFpdList(res.data ?? []);
            setFpdPagination(res.pagination ?? null);
        } catch (err) {
            setError(err.message || "Gagal mengambil data FPD");
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchEvaluasi = useCallback(async (filters, page) => {
        setLoading(true);
        setError("");
        try {
            const params = new URLSearchParams({
                per_page: PER_PAGE,
                page,
                ...Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== "")),
            });
            const res = await apiFetch(`/evaluasi-rkt?${params}`);
            setEvaluasiList(res.data ?? []);
            setEvalPagination(res.pagination ?? null);
        } catch (err) {
            setError(err.message || "Gagal mengambil data Evaluasi RKT");
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        setError("");
        setSearch("");
        if (tab === "fpd")      fetchFpd(fpdFilters, fpdPage);
        if (tab === "evaluasi") fetchEvaluasi(evalFilters, evalPage);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [tab]);

    // Column filter handlers (server-side)
    const handleFpdFilter = (key, val) => {
        const next = { ...fpdFilters, [key]: val };
        setFpdFilters(next);
        setFpdPage(1);
        fetchFpd(next, 1);
    };

    const handleEvalFilter = (key, val) => {
        const next = { ...evalFilters, [key]: val };
        setEvalFilters(next);
        setEvalPage(1);
        fetchEvaluasi(next, 1);
    };

    // Global search (server-side keyword)
    const handleSearch = () => {
        if (tab === "fpd") {
            const next = { ...fpdFilters, PROGRAM_KERJA: search };
            setFpdFilters(next);
            setFpdPage(1);
            fetchFpd(next, 1);
        } else {
            const next = { ...evalFilters, keyword: search };
            setEvalFilters(next);
            setEvalPage(1);
            fetchEvaluasi(next, 1);
        }
    };

    const resetFpd = () => {
        setFpdFilters(FPD_FILTERS_INIT);
        setFpdPage(1);
        setSearch("");
        fetchFpd(FPD_FILTERS_INIT, 1);
    };

    const resetEval = () => {
        setEvalFilters(EVAL_FILTERS_INIT);
        setEvalPage(1);
        setSearch("");
        fetchEvaluasi(EVAL_FILTERS_INIT, 1);
    };

    const isFiltered =
        tab === "fpd"
            ? Object.values(fpdFilters).some(Boolean)
            : Object.values(evalFilters).some(Boolean);


    // Export
    const handleExport = async (type) => {
        setExporting(type);
        try {
            if (tab === "fpd") {
                await downloadFile(
                    `/fpd-anggaran/export/${type === "pdf" ? "pdf" : "excel"}`,
                    `FPD_Anggaran.${type === "pdf" ? "pdf" : "xlsx"}`,
                    fpdFilters
                );
            } else {
                await downloadFile(
                    `/evaluasi-rkt/export/${type === "pdf" ? "pdf" : "excel"}`,
                    `EvaluasiRKT.${type === "pdf" ? "pdf" : "xlsx"}`,
                    evalFilters
                );
            }
            showToast(`Berhasil mengunduh file ${type.toUpperCase()}`);
        } catch {
            showToast("Gagal mengunduh file, silakan coba lagi.", "error");
        } finally {
            setExporting("");
        }
    };

    const switchTab = (t) => {
        setTab(t);
        setError("");
    };

    // Client-side sort
    const sortedFpd = useMemo(() =>
        applySort(fpdList, fpdSort, (f, col) => {
            if (col === "program_kerja") return f.program_kerja?.PROGRAM_KERJA;
            if (col === "tgl")          return f.TGL_FPD;
            if (col === "anggaran")     return Number(f.NOMINAL_ANGGARAN);
            if (col === "terpakai")     return Number(f.NOMINAL_FPD);
            if (col === "sisa")         return Number(f.NOMINAL_SISA);
            if (col === "status") {
                if (!f.NIP_VALIDATOR_FPD) return "Menunggu";
                if (f.NIP_VALIDATOR_FPD === "Ditolak") return "Ditolak";
                return "Disetujui";
            }
        }), [fpdList, fpdSort]);

    const sortedEval = useMemo(() =>
        applySort(evaluasiList, evalSort, (e, col) => {
            if (col === "program_kerja") return e.program_kerja?.PROGRAM_KERJA;
            if (col === "jenis")         return e.ref_pm?.NAMA_PM;
            if (col === "tgl")           return e.TGL_PM;
            if (col === "deskripsi")     return e.DESKRIPSI_TR_PM;
        }), [evaluasiList, evalSort]);

    const dataCount =
        tab === "fpd"
            ? (fpdPagination?.total ?? fpdList.length)
            : (evalPagination?.total ?? evaluasiList.length);

    return (
        <div className="kepsek-monitoring-shell">
            <main className="kepsek-monitoring-main">
                <header className="kepsek-monitoring-header">
                    <h1>Monitoring</h1>
                    <p>Pantau pengajuan dana dan evaluasi rencana kerja tahunan</p>
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
                </div>

                <div className="kepsek-card">
                    <div className="kepsek-card-head">
                        <span>{dataCount} data ditemukan</span>
                        <div className="kepsek-search-row">
                            <input
                                type="text"
                                className="kepsek-search-input"
                                placeholder="Cari..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === "Enter" && handleSearch()}
                            />
                            <button className="kepsek-btn ghost" onClick={handleSearch}>
                                <i className="bi bi-search"></i> Cari
                            </button>
                            {isFiltered && (
                                <button
                                    className="kepsek-btn ghost"
                                    onClick={tab === "fpd" ? resetFpd : resetEval}
                                >
                                    <i className="bi bi-x-circle"></i> Reset
                                </button>
                            )}
                            <button
                                className="kepsek-btn ghost"
                                onClick={() =>
                                    tab === "fpd"
                                        ? fetchFpd(fpdFilters, fpdPage)
                                        : fetchEvaluasi(evalFilters, evalPage)
                                }
                            >
                                <i className="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            <button
                                className="kepsek-btn export-excel"
                                onClick={() => handleExport("excel")}
                                disabled={!!exporting}
                            >
                                <i className="bi bi-filetype-xlsx"></i>
                                {exporting === "excel" ? "Mengunduh..." : "Export Excel"}
                            </button>
                            <button
                                className="kepsek-btn export-pdf"
                                onClick={() => handleExport("pdf")}
                                disabled={!!exporting}
                            >
                                <i className="bi bi-filetype-pdf"></i>
                                {exporting === "pdf" ? "Mengunduh..." : "Export PDF"}
                            </button>
                        </div>
                    </div>

                    {error && <div className="kepsek-error">{error}</div>}

                    {loading ? (
                        <div className="kepsek-loading">Memuat data...</div>
                    ) : tab === "fpd" ? (
                        <>
                            <div className="kepsek-table-wrap">
                                <table className="kepsek-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <SortTh col="program_kerja" sort={fpdSort} onSort={handleFpdSort}>Program Kerja</SortTh>
                                            <SortTh col="tgl" sort={fpdSort} onSort={handleFpdSort}>Tanggal FPD</SortTh>
                                            <SortTh col="anggaran" sort={fpdSort} onSort={handleFpdSort}>Nominal Anggaran</SortTh>
                                            <SortTh col="terpakai" sort={fpdSort} onSort={handleFpdSort}>Nominal FPD</SortTh>
                                            <SortTh col="sisa" sort={fpdSort} onSort={handleFpdSort}>Sisa</SortTh>
                                            <SortTh col="status" sort={fpdSort} onSort={handleFpdSort}>Status</SortTh>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {sortedFpd.length === 0 ? (
                                            <tr>
                                                <td colSpan={7} className="kepsek-empty">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        ) : (
                                            sortedFpd.map((f, i) => (
                                                <tr key={f.ID_FPD}>
                                                    <td>{(fpdPage - 1) * PER_PAGE + i + 1}</td>
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
                            <Pagination
                                page={fpdPage}
                                lastPage={fpdPagination?.last_page}
                                total={fpdPagination?.total ?? fpdList.length}
                                perPage={PER_PAGE}
                                onPageChange={(p) => { setFpdPage(p); fetchFpd(fpdFilters, p); }}
                            />
                        </>
                    ) : (
                        <>
                            <div className="kepsek-table-wrap">
                                <table className="kepsek-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <SortTh col="program_kerja" sort={evalSort} onSort={handleEvalSort}>Program Kerja</SortTh>
                                            <SortTh col="jenis" sort={evalSort} onSort={handleEvalSort}>Jenis Pencapaian</SortTh>
                                            <SortTh col="tgl" sort={evalSort} onSort={handleEvalSort}>Tanggal</SortTh>
                                            <SortTh col="deskripsi" sort={evalSort} onSort={handleEvalSort}>Deskripsi</SortTh>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {sortedEval.length === 0 ? (
                                            <tr>
                                                <td colSpan={5} className="kepsek-empty">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        ) : (
                                            sortedEval.map((e, i) => (
                                                <tr key={e.ID_PM || i}>
                                                    <td>{(evalPage - 1) * PER_PAGE + i + 1}</td>
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
                            <Pagination
                                page={evalPage}
                                lastPage={evalPagination?.last_page}
                                total={evalPagination?.total ?? evaluasiList.length}
                                perPage={PER_PAGE}
                                onPageChange={(p) => { setEvalPage(p); fetchEvaluasi(evalFilters, p); }}
                            />
                        </>
                    )}
                </div>
            </main>

            {toast && (
                <div className={`kepsek-toast ${toast.type}`}>{toast.msg}</div>
            )}
        </div>
    );
}

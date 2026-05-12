import { useEffect, useMemo, useRef, useState } from "react";
import "../../../styles/pic/guru/EvaluasiRKT.css";
import SidebarPic from "../../../components/SidebarPic";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";
const PAGE_SIZE = 10;

const createEmptyForm = () => ({
    ID_PROGRAM_KERJA: "",
    ID_REF_PM: "",
    TGL_PM: "",
    DESKRIPSI_TR_PM: "",
});

const createEmptyFilters = () => ({
    keyword: "",
    ID_PROGRAM_KERJA: "",
    ID_REF_PM: "",
    TGL_PM: "",
});

const extractCollection = (payload) => {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    return [];
};

const normalizeProgramKerjaLabel = (item) =>
    item?.PROGRAM_KERJA ??
    item?.program_kerja ??
    item?.DESKRIPSI ??
    `Program Kerja ${item?.ID_PROGRAM_KERJA ?? ""}`.trim();

const normalizePmLabel = (item) =>
    item?.NAMA_PM ??
    item?.DESKRIPSI_PM ??
    `PM ${item?.ID_REF_PM ?? ""}`.trim();

const formatDateLabel = (value) => {
    if (!value) {
        return "-";
    }

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(parsed);
};

const escapeHtml = (value) =>
    String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");

const escapeXml = (value) =>
    String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&apos;");

const downloadBlob = (blob, filename) => {
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const json = await response.json();

    if (!response.ok || json?.success === false) {
        throw new Error(
            json?.message ||
                json?.error ||
                Object.values(json?.errors || {})?.flat?.()?.[0] ||
                "Terjadi kesalahan pada server"
        );
    }

    return json;
}

const toQueryString = (filters) => {
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
        if (String(value || "").trim() !== "") {
            params.set(key, value);
        }
    });

    return params.toString();
};

export default function PicEvaluasiRKT() {
    const [rows, setRows] = useState([]);
    const [programKerjaOptions, setProgramKerjaOptions] = useState([]);
    const [refPmOptions, setRefPmOptions] = useState([]);
    const [form, setForm] = useState(createEmptyForm());
    const [filters, setFilters] = useState(createEmptyFilters());
    const [editId, setEditId] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");
    const tanggalEvaluasiRef = useRef(null);
    const tanggalFilterRef = useRef(null);

    const stats = useMemo(() => {
        const total = rows.length;
        const selesai = rows.filter((item) =>
            String(item?.ref_pm?.NAMA_PM ?? item?.refPm?.NAMA_PM ?? "").toLowerCase().includes("selesai")
        ).length;
        const terbaru = rows[0]?.TGL_PM ?? "";

        return { total, selesai, terbaru };
    }, [rows]);

    const totalPages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
    const paginatedRows = useMemo(() => {
        const startIndex = (currentPage - 1) * PAGE_SIZE;
        return rows.slice(startIndex, startIndex + PAGE_SIZE);
    }, [currentPage, rows]);

    const startData = rows.length === 0 ? 0 : (currentPage - 1) * PAGE_SIZE + 1;
    const endData = rows.length === 0 ? 0 : Math.min(currentPage * PAGE_SIZE, rows.length);

    useEffect(() => {
        setCurrentPage((page) => Math.min(page, totalPages));
    }, [totalPages]);

    const loadReferences = async () => {
        const [rktJson, refPmJson] = await Promise.all([
            fetchJson(`${API_BASE_URL}/rkt`),
            fetchJson(`${API_BASE_URL}/ref-pm`),
        ]);

        const nextRkt = extractCollection(rktJson);
        const nextRefPm = extractCollection(refPmJson);

        setProgramKerjaOptions(nextRkt);
        setRefPmOptions(nextRefPm);
        setForm((current) => ({
            ...current,
            ID_PROGRAM_KERJA: current.ID_PROGRAM_KERJA || String(nextRkt[0]?.ID_PROGRAM_KERJA ?? ""),
            ID_REF_PM: current.ID_REF_PM || String(nextRefPm[0]?.ID_REF_PM ?? ""),
        }));
    };

    const loadRows = async (activeFilters = filters) => {
        setLoading(true);
        setError("");

        try {
            const queryString = toQueryString(activeFilters);
            const endpoint = queryString ? `/evaluasi-rkt/search?${queryString}` : "/evaluasi-rkt";
            const json = await fetchJson(`${API_BASE_URL}${endpoint}`);
            setRows(extractCollection(json));
            setCurrentPage(1);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const initialize = async () => {
            setLoading(true);
            setError("");

            try {
                await loadReferences();
                await loadRows(createEmptyFilters());
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        initialize();
    }, []);

    const resetForm = () => {
        setEditId(null);
        setMessage("");
        setError("");
        setForm((current) => ({
            ...createEmptyForm(),
            ID_PROGRAM_KERJA: current.ID_PROGRAM_KERJA,
            ID_REF_PM: current.ID_REF_PM,
        }));
    };

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
    };

    const handleFilterChange = (event) => {
        const { name, value } = event.target;
        setFilters((current) => ({ ...current, [name]: value }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setMessage("");
        setError("");

        const payload = {
            ID_PROGRAM_KERJA: Number(form.ID_PROGRAM_KERJA),
            ID_REF_PM: Number(form.ID_REF_PM),
            TGL_PM: form.TGL_PM,
            DESKRIPSI_TR_PM: form.DESKRIPSI_TR_PM.trim(),
        };

        const url = editId ? `${API_BASE_URL}/evaluasi-rkt/update/${editId}` : `${API_BASE_URL}/evaluasi-rkt/store`;
        const method = editId ? "PUT" : "POST";

        try {
            await fetchJson(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            setMessage(editId ? "Evaluasi RKT berhasil diperbarui." : "Evaluasi RKT berhasil ditambahkan.");
            await loadRows();
            resetForm();
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.ID_PM);
        setMessage("");
        setError("");
        setForm({
            ID_PROGRAM_KERJA: String(item.ID_PROGRAM_KERJA ?? ""),
            ID_REF_PM: String(item.ID_REF_PM ?? ""),
            TGL_PM: item.TGL_PM ?? "",
            DESKRIPSI_TR_PM: item.DESKRIPSI_TR_PM ?? "",
        });
    };

    const handleDelete = async (id) => {
        if (!window.confirm("Yakin ingin menghapus evaluasi RKT ini?")) {
            return;
        }

        setMessage("");
        setError("");

        try {
            await fetchJson(`${API_BASE_URL}/evaluasi-rkt/delete/${id}`, { method: "DELETE" });
            setMessage("Evaluasi RKT berhasil dihapus.");
            await loadRows();

            if (editId === id) {
                resetForm();
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const applyFilters = async (event) => {
        event.preventDefault();
        await loadRows(filters);
    };

    const resetFilters = async () => {
        const nextFilters = createEmptyFilters();
        setFilters(nextFilters);
        await loadRows(nextFilters);
    };

    const openExport = (type) => {
        const exportRows = rows.map((item) => ({
            id: item.ID_PM ?? "-",
            programKerja: item?.program_kerja?.PROGRAM_KERJA ?? item?.programKerja?.PROGRAM_KERJA ?? "-",
            indikator: item?.program_kerja?.INDIKATOR ?? item?.programKerja?.INDIKATOR ?? "-",
            statusPm: item?.ref_pm?.NAMA_PM ?? item?.refPm?.NAMA_PM ?? "-",
            tanggal: formatDateLabel(item.TGL_PM),
            deskripsi: item.DESKRIPSI_TR_PM || "-",
        }));

        if (exportRows.length === 0) {
            setError("Tidak ada data evaluasi RKT untuk diexport.");
            return;
        }

        setError("");

        if (type === "csv") {
            const headers = ["ID Evaluasi", "Program Kerja", "Indikator", "Status PM", "Tanggal Evaluasi", "Deskripsi"];
            const lines = exportRows.map((row) =>
                [row.id, row.programKerja, row.indikator, row.statusPm, row.tanggal, row.deskripsi]
                    .map((cell) => `"${String(cell ?? "").replace(/"/g, '""')}"`)
                    .join(",")
            );

            const csvContent = [headers.join(","), ...lines].join("\n");
            downloadBlob(new Blob(["\uFEFF" + csvContent], { type: "text/csv;charset=utf-8;" }), "evaluasi_rkt.csv");
            return;
        }

        if (type === "excel") {
            const activeFilters = [
                filters.keyword ? `Keyword: ${filters.keyword}` : null,
                filters.ID_PROGRAM_KERJA
                    ? `Program Kerja: ${
                          programKerjaOptions.find((item) => String(item.ID_PROGRAM_KERJA) === String(filters.ID_PROGRAM_KERJA))
                              ?.PROGRAM_KERJA ?? filters.ID_PROGRAM_KERJA
                      }`
                    : null,
                filters.ID_REF_PM
                    ? `Status PM: ${
                          refPmOptions.find((item) => String(item.ID_REF_PM) === String(filters.ID_REF_PM))?.NAMA_PM ?? filters.ID_REF_PM
                      }`
                    : null,
                filters.TGL_PM ? `Tanggal: ${formatDateLabel(filters.TGL_PM)}` : null,
            ].filter(Boolean);

            const filterText = activeFilters.length > 0 ? activeFilters.join(" | ") : "Semua data evaluasi";

            const xmlRows = exportRows
                .map(
                    (row, index) => `
                        <Row>
                            <Cell ss:StyleID="cellCenter"><Data ss:Type="Number">${index + 1}</Data></Cell>
                            <Cell ss:StyleID="cellCenter"><Data ss:Type="String">${escapeXml(row.id)}</Data></Cell>
                            <Cell ss:StyleID="cellText"><Data ss:Type="String">${escapeXml(row.programKerja)}</Data></Cell>
                            <Cell ss:StyleID="cellText"><Data ss:Type="String">${escapeXml(row.indikator)}</Data></Cell>
                            <Cell ss:StyleID="cellText"><Data ss:Type="String">${escapeXml(row.statusPm)}</Data></Cell>
                            <Cell ss:StyleID="cellCenter"><Data ss:Type="String">${escapeXml(row.tanggal)}</Data></Cell>
                            <Cell ss:StyleID="cellText"><Data ss:Type="String">${escapeXml(row.deskripsi)}</Data></Cell>
                        </Row>
                    `
                )
                .join("");

            const excelXml = `<?xml version="1.0"?>
                <?mso-application progid="Excel.Sheet"?>
                <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                    xmlns:o="urn:schemas-microsoft-com:office:office"
                    xmlns:x="urn:schemas-microsoft-com:office:excel"
                    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
                    xmlns:html="http://www.w3.org/TR/REC-html40">
                    <Styles>
                        <Style ss:ID="title">
                            <Font ss:Bold="1" ss:Size="14"/>
                            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="subtitle">
                            <Font ss:Bold="1" ss:Size="12"/>
                            <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="filter">
                            <Font ss:Italic="1"/>
                            <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        </Style>
                        <Style ss:ID="header">
                            <Font ss:Bold="1" ss:Color="#FFFFFF"/>
                            <Interior ss:Color="#4F81BD" ss:Pattern="Solid"/>
                            <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                            <Borders>
                                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                            </Borders>
                        </Style>
                        <Style ss:ID="cellText">
                            <Alignment ss:Horizontal="Left" ss:Vertical="Top" ss:WrapText="1"/>
                            <Borders>
                                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                            </Borders>
                        </Style>
                        <Style ss:ID="cellCenter">
                            <Alignment ss:Horizontal="Center" ss:Vertical="Top" ss:WrapText="1"/>
                            <Borders>
                                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                            </Borders>
                        </Style>
                    </Styles>
                    <Worksheet ss:Name="Evaluasi RKT">
                        <Table>
                            <Column ss:Width="45"/>
                            <Column ss:Width="70"/>
                            <Column ss:Width="180"/>
                            <Column ss:Width="180"/>
                            <Column ss:Width="110"/>
                            <Column ss:Width="100"/>
                            <Column ss:Width="240"/>
                            <Row>
                                <Cell ss:MergeAcross="6" ss:StyleID="title"><Data ss:Type="String">LAPORAN EVALUASI RKT</Data></Cell>
                            </Row>
                            <Row>
                                <Cell ss:MergeAcross="6" ss:StyleID="subtitle"><Data ss:Type="String">UNIT SEKOLAH SMK BOPKRI 2 YOGYAKARTA</Data></Cell>
                            </Row>
                            <Row>
                                <Cell ss:MergeAcross="6" ss:StyleID="filter"><Data ss:Type="String">${escapeXml(filterText)}</Data></Cell>
                            </Row>
                            <Row></Row>
                            <Row>
                                <Cell ss:StyleID="header"><Data ss:Type="String">NO</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">ID EVALUASI</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">PROGRAM KERJA</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">INDIKATOR</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">STATUS PM</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">TANGGAL EVALUASI</Data></Cell>
                                <Cell ss:StyleID="header"><Data ss:Type="String">DESKRIPSI</Data></Cell>
                            </Row>
                            ${xmlRows}
                        </Table>
                    </Worksheet>
                </Workbook>`;

            downloadBlob(new Blob([excelXml], { type: "application/vnd.ms-excel;charset=utf-8;" }), "evaluasi_rkt.xls");
            return;
        }

        if (type === "pdf") {
            const printWindow = window.open("", "_blank", "width=1000,height=700");

            if (!printWindow) {
                setError("Popup diblokir browser. Izinkan popup untuk export PDF.");
                return;
            }

            const tableRows = exportRows
                .map(
                    (row) => `
                        <tr>
                            <td>${escapeHtml(row.id)}</td>
                            <td>${escapeHtml(row.programKerja)}</td>
                            <td>${escapeHtml(row.indikator)}</td>
                            <td>${escapeHtml(row.statusPm)}</td>
                            <td>${escapeHtml(row.tanggal)}</td>
                            <td>${escapeHtml(row.deskripsi)}</td>
                        </tr>
                    `
                )
                .join("");

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Evaluasi RKT</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 24px; }
                            h1 { margin: 0 0 6px; font-size: 22px; }
                            p { margin: 0 0 18px; color: #555; }
                            table { width: 100%; border-collapse: collapse; font-size: 12px; }
                            th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
                            th { background: #f3f4f6; }
                        </style>
                    </head>
                    <body>
                        <h1>Evaluasi RKT</h1>
                        <p>Jumlah data: ${exportRows.length}</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Evaluasi</th>
                                    <th>Program Kerja</th>
                                    <th>Indikator</th>
                                    <th>Status PM</th>
                                    <th>Tanggal Evaluasi</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    };

    const openDatePicker = (inputRef) => {
        if (!inputRef.current) {
            return;
        }

        inputRef.current.focus();

        if (typeof inputRef.current.showPicker === "function") {
            inputRef.current.showPicker();
        } else {
            inputRef.current.click();
        }
    };

    return (
        <div className="waka-evaluasi-shell">
            <SidebarPic />

            <main className="waka-evaluasi-main">
                <section className="waka-evaluasi-stats">
                    <article className="waka-evaluasi-stat-card waka-evaluasi-stat-card-blue">
                        <span>Total Evaluasi</span>
                        <strong>{stats.total}</strong>
                    </article>
                    <article className="waka-evaluasi-stat-card waka-evaluasi-stat-card-green">
                        <span>Status Selesai</span>
                        <strong>{stats.selesai}</strong>
                    </article>
                    <article className="waka-evaluasi-stat-card waka-evaluasi-stat-card-orange">
                        <span>Tanggal Terbaru</span>
                        <strong>{formatDateLabel(stats.terbaru)}</strong>
                    </article>
                </section>

                <section className="waka-evaluasi-card">
                    <div className="waka-evaluasi-card-head">
                        <div>
                            <h2>Form Evaluasi</h2>
                            <p>Pilih program kerja, status monitoring, tanggal evaluasi.</p>
                        </div>
                        <div className="waka-evaluasi-actions-top">
                            <button type="button" className="waka-evaluasi-button ghost" onClick={() => loadRows()}>
                                Refresh
                            </button>
                        </div>
                    </div>

                    {message ? <div className="waka-evaluasi-feedback success">{message}</div> : null}
                    {error ? <div className="waka-evaluasi-feedback error">{error}</div> : null}

                    <form className="waka-evaluasi-form" onSubmit={handleSubmit}>
                        <div className="waka-evaluasi-grid">
                            <label className="waka-evaluasi-field waka-evaluasi-field-full">
                                <span>Program Kerja</span>
                                <select name="ID_PROGRAM_KERJA" value={form.ID_PROGRAM_KERJA} onChange={handleChange} required>
                                    <option value="">Pilih program kerja</option>
                                    {programKerjaOptions.map((item) => (
                                        <option key={item.ID_PROGRAM_KERJA} value={item.ID_PROGRAM_KERJA}>
                                            {normalizeProgramKerjaLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-evaluasi-field">
                                <span>Status PM</span>
                                <select name="ID_REF_PM" value={form.ID_REF_PM} onChange={handleChange} required>
                                    <option value="">Pilih status PM</option>
                                    {refPmOptions.map((item) => (
                                        <option key={item.ID_REF_PM} value={item.ID_REF_PM}>
                                            {normalizePmLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-evaluasi-field">
                                <span>Tanggal Evaluasi</span>
                                <div className="waka-evaluasi-date-input">
                                    <input
                                        ref={tanggalEvaluasiRef}
                                        type="date"
                                        name="TGL_PM"
                                        value={form.TGL_PM}
                                        onChange={handleChange}
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="waka-evaluasi-date-trigger"
                                        onClick={() => openDatePicker(tanggalEvaluasiRef)}
                                        aria-label="Buka kalender tanggal evaluasi"
                                    >
                                        <i className="bi bi-calendar3"></i>
                                    </button>
                                </div>
                            </label>

                            <label className="waka-evaluasi-field waka-evaluasi-field-full">
                                <span>Deskripsi Evaluasi</span>
                                <textarea
                                    name="DESKRIPSI_TR_PM"
                                    value={form.DESKRIPSI_TR_PM}
                                    onChange={handleChange}
                                    rows="4"
                                    maxLength="100"
                                    placeholder="Contoh: Progress kegiatan sudah 80% dan siap masuk tahap pelaporan."
                                />
                            </label>
                        </div>

                        <div className="waka-evaluasi-submit">
                            <button type="button" className="waka-evaluasi-button secondary" onClick={resetForm}>
                                Reset
                            </button>
                            <button type="submit" className="waka-evaluasi-button primary" disabled={submitting}>
                                {submitting ? "Menyimpan..." : editId ? "Update Evaluasi" : "Simpan Evaluasi"}
                            </button>
                        </div>
                    </form>
                </section>

                <section className="waka-evaluasi-card">
                    <div className="waka-evaluasi-card-head">
                        <div>
                            <h2>Data Evaluasi</h2>
                            <p>Filter data dari database berdasarkan program kerja, PM, tanggal, atau kata kunci deskripsi.</p>
                        </div>
                    </div>

                    <form className="waka-evaluasi-filter-grid" onSubmit={applyFilters}>
                        <label className="waka-evaluasi-field">
                            <span>Keyword</span>
                            <input
                                type="text"
                                name="keyword"
                                value={filters.keyword}
                                onChange={handleFilterChange}
                                placeholder="Cari deskripsi evaluasi"
                            />
                        </label>

                        <label className="waka-evaluasi-field">
                            <span>Program Kerja</span>
                            <select name="ID_PROGRAM_KERJA" value={filters.ID_PROGRAM_KERJA} onChange={handleFilterChange}>
                                <option value="">Semua program kerja</option>
                                {programKerjaOptions.map((item) => (
                                    <option key={item.ID_PROGRAM_KERJA} value={item.ID_PROGRAM_KERJA}>
                                        {normalizeProgramKerjaLabel(item)}
                                    </option>
                                ))}
                            </select>
                        </label>

                        <label className="waka-evaluasi-field">
                            <span>Status PM</span>
                            <select name="ID_REF_PM" value={filters.ID_REF_PM} onChange={handleFilterChange}>
                                <option value="">Semua status PM</option>
                                {refPmOptions.map((item) => (
                                    <option key={item.ID_REF_PM} value={item.ID_REF_PM}>
                                        {normalizePmLabel(item)}
                                    </option>
                                ))}
                            </select>
                        </label>

                        <label className="waka-evaluasi-field">
                            <span>Tanggal</span>
                            <div className="waka-evaluasi-date-input">
                                <input
                                    ref={tanggalFilterRef}
                                    type="date"
                                    name="TGL_PM"
                                    value={filters.TGL_PM}
                                    onChange={handleFilterChange}
                                />
                                <button
                                    type="button"
                                    className="waka-evaluasi-date-trigger"
                                    onClick={() => openDatePicker(tanggalFilterRef)}
                                    aria-label="Buka kalender filter tanggal"
                                >
                                    <i className="bi bi-calendar3"></i>
                                </button>
                            </div>
                        </label>

                        <div className="waka-evaluasi-filter-actions">
                            <button type="button" className="waka-evaluasi-button secondary" onClick={resetFilters}>
                                Reset Filter
                            </button>
                            <button type="submit" className="waka-evaluasi-button primary">
                                Terapkan Filter
                            </button>
                        </div>
                    </form>

                    {loading ? (
                        <div className="waka-evaluasi-empty">Memuat data evaluasi RKT...</div>
                    ) : (
                        <div className="waka-evaluasi-table-wrap">
                            <table className="waka-evaluasi-table">
                                <thead>
                                    <tr>
                                        <th>Program Kerja</th>
                                        <th>Status PM</th>
                                        <th>Tanggal</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length === 0 ? (
                                        <tr>
                                            <td colSpan="5" className="waka-evaluasi-empty-cell">
                                                Belum ada data evaluasi yang cocok.
                                            </td>
                                        </tr>
                                    ) : (
                                        paginatedRows.map((item) => (
                                            <tr key={item.ID_PM}>
                                                <td>
                                                    <strong>{item?.program_kerja?.PROGRAM_KERJA ?? item?.programKerja?.PROGRAM_KERJA ?? "-"}</strong>
                                                    <small>{item?.program_kerja?.INDIKATOR ?? item?.programKerja?.INDIKATOR ?? "-"}</small>
                                                </td>
                                                <td>{item?.ref_pm?.NAMA_PM ?? item?.refPm?.NAMA_PM ?? "-"}</td>
                                                <td>{formatDateLabel(item.TGL_PM)}</td>
                                                <td>{item.DESKRIPSI_TR_PM || "-"}</td>
                                                <td>
                                                    <div className="waka-evaluasi-row-actions">
                                                        <button
                                                            type="button"
                                                            className="waka-evaluasi-mini-button edit"
                                                            onClick={() => handleEdit(item)}
                                                        >
                                                            Edit
                                                        </button>
                                                        <button
                                                            type="button"
                                                            className="waka-evaluasi-mini-button delete"
                                                            onClick={() => handleDelete(item.ID_PM)}
                                                        >
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {!loading ? (
                        <div className="waka-evaluasi-table-footer">
                            <div className="waka-evaluasi-pagination-info">
                                Menampilkan {startData} - {endData} dari {rows.length} data
                            </div>

                            <div className="waka-evaluasi-pagination">
                                <button
                                    type="button"
                                    className="waka-evaluasi-page-btn"
                                    disabled={currentPage === 1}
                                    onClick={() => setCurrentPage((page) => page - 1)}
                                >
                                    <i className="bi bi-chevron-left"></i>
                                </button>

                                {Array.from({ length: totalPages }, (_, index) => {
                                    const pageNumber = index + 1;

                                    return (
                                        <button
                                            key={pageNumber}
                                            type="button"
                                            className={`waka-evaluasi-page-btn ${currentPage === pageNumber ? "active" : ""}`}
                                            onClick={() => setCurrentPage(pageNumber)}
                                        >
                                            {pageNumber}
                                        </button>
                                    );
                                })}

                                <button
                                    type="button"
                                    className="waka-evaluasi-page-btn"
                                    disabled={currentPage === totalPages}
                                    onClick={() => setCurrentPage((page) => page + 1)}
                                >
                                    <i className="bi bi-chevron-right"></i>
                                </button>
                            </div>

                            <div className="waka-evaluasi-export-group">
                                <button
                                    type="button"
                                    className="waka-evaluasi-export-btn"
                                    onClick={() => openExport("excel")}
                                >
                                    <i className="bi bi-filetype-xlsx"></i>
                                    Export Excel
                                </button>
                                {/* <button
                                    type="button"
                                    className="waka-evaluasi-export-btn"
                                    onClick={() => openExport("csv")}
                                >
                                    <i className="bi bi-filetype-csv"></i>
                                    Export CSV
                                </button>*/}
                                <button
                                    type="button"
                                    className="btn-outline-danger custom-btn"
                                    onClick={() => openExport("pdf")}
                                >
                                    <i className="bi bi-file-earmark-pdf"></i>
                                    Export PDF
                                </button> 
                            </div>
                        </div>
                    ) : null}
                </section>
            </main>
        </div>
    );
}

import { useEffect, useMemo, useState } from "react";
import "../../styles/waka/RKT.css";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";
const WAKA_MENU = ["Dashboard", "RKT", "Realisasi RKT", "Bridging RKT", "Evaluasi RKT"];

const createEmptyForm = () => ({
    ID_TA_ANGGARAN: "",
    ID_UNIT: "",
    ID_TAN: "",
    ID_MASTER_COA: "",
    ID_KEGIATAN: "",
    NOMINAL: "",
    INDIKATOR: "",
    SASARAN: "",
    WAKTU_AWAL: "",
    WAKTU_AKHIR: "",
    KELUARAN_PROGKER: "",
    PROGRAM_KERJA: "",
    NIP_PENANGGUNG_JAWAB: "",
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

const formatCurrency = (value) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(Number(value || 0));

const normalizeUnitLabel = (item) =>
    item?.NAMA_UNIT ??
    item?.DESKRIPSI_UNIT ??
    item?.UNIT ??
    item?.DESKRIPSI ??
    item?.NAMA ??
    `Unit ${item?.ID_UNIT ?? ""}`.trim();

const normalizeTahunAnggaranLabel = (item) =>
    item?.DESKRIPSI_TAHUN_ANGGARAN ?? item?.label ?? `TA ${item?.ID_TA_ANGGARAN ?? ""}`.trim();

const normalizeTanLabel = (item) =>
    item?.DESKRIPSI_TAN ?? item?.TAHUN ?? `TAN ${item?.ID_TAN ?? ""}`.trim();

const normalizeCoaLabel = (item) =>
    [item?.KODE_COA, item?.DESKRIPSI_COA].filter(Boolean).join(" - ") || `COA ${item?.ID_MASTER_COA ?? ""}`;

const normalizeKegiatanItems = (items) => {
    const result = [];
    const pushItem = (item) => {
        if (!item || result.some((current) => current.ID_KEGIATAN === item.ID_KEGIATAN)) {
            return;
        }
        result.push(item);
    };

    items.forEach((item) => {
        pushItem(item);
        (item.children || []).forEach(pushItem);
    });

    return result;
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

export default function WakaRKT() {
    const [rows, setRows] = useState([]);
    const [unitOptions, setUnitOptions] = useState([]);
    const [tahunAnggaranOptions, setTahunAnggaranOptions] = useState([]);
    const [tanOptions, setTanOptions] = useState([]);
    const [coaOptions, setCoaOptions] = useState([]);
    const [kegiatanOptions, setKegiatanOptions] = useState([]);
    const [form, setForm] = useState(createEmptyForm());
    const [editId, setEditId] = useState(null);
    const [keyword, setKeyword] = useState("");
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");

    const filteredRows = useMemo(() => {
        const term = keyword.trim().toLowerCase();
        if (!term) {
            return rows;
        }

        return rows.filter((item) =>
            [
                item.PROGRAM_KERJA,
                item.INDIKATOR,
                item.SASARAN,
                item.KELUARAN_PROGKER,
                item.NIP_PENANGGUNG_JAWAB,
                item?.unit?.NAMA_UNIT,
                item?.unit?.DESKRIPSI_UNIT,
                item?.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN,
                item?.tahunAnggaran?.DESKRIPSI_TAHUN_ANGGARAN,
                item?.tan?.DESKRIPSI_TAN,
                item?.tan?.TAHUN,
                item?.coa?.KODE_COA,
                item?.coa?.DESKRIPSI_COA,
                item?.kegiatan?.DESKRIPSI_KEGIATAN,
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase()
                .includes(term)
        );
    }, [keyword, rows]);

    const loadAllData = async () => {
        setLoading(true);
        setError("");

        try {
            const [rktJson, unitJson, taJson, tanJson, coaJson, kegiatanJson] = await Promise.all([
                fetchJson(`${API_BASE_URL}/rkt`),
                fetchJson(`${API_BASE_URL}/unit`),
                fetchJson(`${API_BASE_URL}/tahun-anggaran`),
                fetchJson(`${API_BASE_URL}/ref-tan`),
                fetchJson(`${API_BASE_URL}/coa`),
                fetchJson(`${API_BASE_URL}/kegiatan`),
            ]);

            const nextRows = extractCollection(rktJson);
            const nextUnits = extractCollection(unitJson);
            const nextTa = extractCollection(taJson);
            const nextTan = extractCollection(tanJson);
            const nextCoa = extractCollection(coaJson);
            const nextKegiatan = normalizeKegiatanItems(extractCollection(kegiatanJson));

            setRows(nextRows);
            setUnitOptions(nextUnits);
            setTahunAnggaranOptions(nextTa);
            setTanOptions(nextTan);
            setCoaOptions(nextCoa);
            setKegiatanOptions(nextKegiatan);

            setForm((current) => ({
                ...current,
                ID_TA_ANGGARAN: current.ID_TA_ANGGARAN || String(nextTa.find((item) => item.IS_CURRENT)?.ID_TA_ANGGARAN ?? nextTa[0]?.ID_TA_ANGGARAN ?? ""),
                ID_TAN: current.ID_TAN || String(nextTan.find((item) => item.IS_CURRENT)?.ID_TAN ?? nextTan[0]?.ID_TAN ?? ""),
                ID_UNIT: current.ID_UNIT || String(nextUnits[0]?.ID_UNIT ?? ""),
                ID_MASTER_COA: current.ID_MASTER_COA || String(nextCoa[0]?.ID_MASTER_COA ?? ""),
                ID_KEGIATAN: current.ID_KEGIATAN || String(nextKegiatan[0]?.ID_KEGIATAN ?? ""),
            }));
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadAllData();
    }, []);

    const resetForm = () => {
        setEditId(null);
        setMessage("");
        setError("");
        setForm((current) => ({
            ...createEmptyForm(),
            ID_TA_ANGGARAN: current.ID_TA_ANGGARAN,
            ID_UNIT: current.ID_UNIT,
            ID_TAN: current.ID_TAN,
            ID_MASTER_COA: current.ID_MASTER_COA,
            ID_KEGIATAN: current.ID_KEGIATAN,
        }));
    };

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((current) => ({ ...current, [name]: value }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setMessage("");
        setError("");

        const payload = {
            ...form,
            ID_TA_ANGGARAN: Number(form.ID_TA_ANGGARAN),
            ID_UNIT: Number(form.ID_UNIT),
            ID_TAN: Number(form.ID_TAN),
            ID_MASTER_COA: Number(form.ID_MASTER_COA),
            ID_KEGIATAN: Number(form.ID_KEGIATAN),
            NOMINAL: Number(form.NOMINAL),
        };

        const url = editId ? `${API_BASE_URL}/rkt/update/${editId}` : `${API_BASE_URL}/rkt/store`;
        const method = editId ? "PUT" : "POST";

        try {
            await fetchJson(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            setMessage(editId ? "RKT berhasil diperbarui." : "RKT berhasil ditambahkan.");
            await loadAllData();
            resetForm();
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.ID_PROGRAM_KERJA);
        setMessage("");
        setError("");
        setForm({
            ID_TA_ANGGARAN: String(item.ID_TA_ANGGARAN ?? ""),
            ID_UNIT: String(item.ID_UNIT ?? ""),
            ID_TAN: String(item.ID_TAN ?? ""),
            ID_MASTER_COA: String(item.ID_MASTER_COA ?? ""),
            ID_KEGIATAN: String(item.ID_KEGIATAN ?? ""),
            NOMINAL: String(item.NOMINAL ?? ""),
            INDIKATOR: item.INDIKATOR ?? "",
            SASARAN: item.SASARAN ?? "",
            WAKTU_AWAL: item.WAKTU_AWAL ?? "",
            WAKTU_AKHIR: item.WAKTU_AKHIR ?? "",
            KELUARAN_PROGKER: item.KELUARAN_PROGKER ?? "",
            PROGRAM_KERJA: item.PROGRAM_KERJA ?? "",
            NIP_PENANGGUNG_JAWAB: item.NIP_PENANGGUNG_JAWAB ?? "",
        });
    };

    const handleDelete = async (id) => {
        if (!window.confirm("Yakin ingin menghapus data RKT ini?")) {
            return;
        }

        setMessage("");
        setError("");

        try {
            await fetchJson(`${API_BASE_URL}/rkt/delete/${id}`, { method: "DELETE" });
            setMessage("RKT berhasil dihapus.");
            await loadAllData();

            if (editId === id) {
                resetForm();
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const openExportExcel = () => {
        window.open(`${API_BASE_URL}/rkt/export/excel`, "_blank");
    };

    return (
        <div className="waka-rkt-shell">
            <aside className="waka-rkt-sidebar">
                <div className="waka-rkt-brand">
                    <div className="waka-rkt-badge">WK</div>
                    <div>
                        <strong>Portal Waka</strong>
                        <span>Rencana Kerja Tahunan</span>
                    </div>
                </div>

                <nav className="waka-rkt-nav" aria-label="Navigasi waka">
                    {WAKA_MENU.map((item) => (
                        <button
                            key={item}
                            type="button"
                            className={`waka-rkt-nav-item ${item === "RKT" ? "active" : ""}`}
                        >
                            {item}
                        </button>
                    ))}
                </nav>
            </aside>

            <main className="waka-rkt-main">
                <header className="waka-rkt-header">
                    <h1>RKT Waka</h1>
                    <p>Halaman ini langsung terhubung ke database melalui endpoint `api/rkt` dan siap dipakai untuk CRUD data program kerja tahunan.</p>
                </header>

                <section className="waka-rkt-card">
                    <div className="waka-rkt-card-head">
                        <div>
                            <h2>Form RKT</h2>
                            <p>Lengkapi data program kerja, lalu simpan langsung ke database.</p>
                        </div>
                        <div className="waka-rkt-actions-top">
                            <button type="button" className="waka-rkt-button ghost" onClick={loadAllData}>Refresh</button>
                            <button type="button" className="waka-rkt-button primary" onClick={openExportExcel}>Export Excel</button>
                        </div>
                    </div>

                    {message ? <div className="waka-rkt-feedback success">{message}</div> : null}
                    {error ? <div className="waka-rkt-feedback error">{error}</div> : null}

                    <form className="waka-rkt-form" onSubmit={handleSubmit}>
                        <div className="waka-rkt-grid">
                            <label className="waka-rkt-field">
                                <span>Tahun Anggaran</span>
                                <select name="ID_TA_ANGGARAN" value={form.ID_TA_ANGGARAN} onChange={handleChange} required>
                                    <option value="">Pilih tahun anggaran</option>
                                    {tahunAnggaranOptions.map((item) => (
                                        <option key={item.ID_TA_ANGGARAN} value={item.ID_TA_ANGGARAN}>
                                            {normalizeTahunAnggaranLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-rkt-field">
                                <span>Unit</span>
                                <select name="ID_UNIT" value={form.ID_UNIT} onChange={handleChange} required>
                                    <option value="">Pilih unit</option>
                                    {unitOptions.map((item) => (
                                        <option key={item.ID_UNIT} value={item.ID_UNIT}>
                                            {normalizeUnitLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-rkt-field">
                                <span>TAN</span>
                                <select name="ID_TAN" value={form.ID_TAN} onChange={handleChange} required>
                                    <option value="">Pilih TAN</option>
                                    {tanOptions.map((item) => (
                                        <option key={item.ID_TAN} value={item.ID_TAN}>
                                            {normalizeTanLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-rkt-field">
                                <span>COA</span>
                                <select name="ID_MASTER_COA" value={form.ID_MASTER_COA} onChange={handleChange} required>
                                    <option value="">Pilih COA</option>
                                    {coaOptions.map((item) => (
                                        <option key={item.ID_MASTER_COA} value={item.ID_MASTER_COA}>
                                            {normalizeCoaLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-rkt-field waka-rkt-field-full">
                                <span>Kegiatan</span>
                                <select name="ID_KEGIATAN" value={form.ID_KEGIATAN} onChange={handleChange} required>
                                    <option value="">Pilih kegiatan</option>
                                    {kegiatanOptions.map((item) => (
                                        <option key={item.ID_KEGIATAN} value={item.ID_KEGIATAN}>
                                            {item.DESKRIPSI_KEGIATAN}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="waka-rkt-field waka-rkt-field-full">
                                <span>Program Kerja</span>
                                <input type="text" name="PROGRAM_KERJA" value={form.PROGRAM_KERJA} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>Nominal</span>
                                <input type="number" min="0" name="NOMINAL" value={form.NOMINAL} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>NIP Penanggung Jawab</span>
                                <input type="text" name="NIP_PENANGGUNG_JAWAB" value={form.NIP_PENANGGUNG_JAWAB} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>Waktu Awal</span>
                                <input type="date" name="WAKTU_AWAL" value={form.WAKTU_AWAL} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>Waktu Akhir</span>
                                <input type="date" name="WAKTU_AKHIR" value={form.WAKTU_AKHIR} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>Indikator</span>
                                <input type="text" name="INDIKATOR" value={form.INDIKATOR} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field">
                                <span>Sasaran</span>
                                <input type="text" name="SASARAN" value={form.SASARAN} onChange={handleChange} required />
                            </label>

                            <label className="waka-rkt-field waka-rkt-field-full">
                                <span>Keluaran Program Kerja</span>
                                <textarea name="KELUARAN_PROGKER" value={form.KELUARAN_PROGKER} onChange={handleChange} rows="3" required />
                            </label>
                        </div>

                        <div className="waka-rkt-submit">
                            <button type="button" className="waka-rkt-button secondary" onClick={resetForm}>Reset</button>
                            <button type="submit" className="waka-rkt-button primary" disabled={submitting}>
                                {submitting ? "Menyimpan..." : editId ? "Update RKT" : "Simpan RKT"}
                            </button>
                        </div>
                    </form>
                </section>

                <section className="waka-rkt-card">
                    <div className="waka-rkt-card-head">
                        <div>
                            <h2>Data RKT</h2>
                            <p>Daftar program kerja tahunan yang tersimpan di database.</p>
                        </div>
                        <label className="waka-rkt-search">
                            <span>Cari</span>
                            <input type="text" value={keyword} onChange={(event) => setKeyword(event.target.value)} placeholder="Cari program kerja, indikator, sasaran..." />
                        </label>
                    </div>

                    {loading ? (
                        <div className="waka-rkt-empty">Memuat data RKT...</div>
                    ) : (
                        <div className="waka-rkt-table-wrap">
                            <table className="waka-rkt-table">
                                <thead>
                                    <tr>
                                        <th>Program Kerja</th>
                                        <th>Tahun</th>
                                        <th>Unit</th>
                                        <th>Nominal</th>
                                        <th>Periode</th>
                                        <th>PJ</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredRows.length === 0 ? (
                                        <tr>
                                            <td colSpan="7" className="waka-rkt-empty-cell">Belum ada data RKT yang cocok.</td>
                                        </tr>
                                    ) : (
                                        filteredRows.map((item) => (
                                            <tr key={item.ID_PROGRAM_KERJA}>
                                                <td>
                                                    <strong>{item.PROGRAM_KERJA}</strong>
                                                    <small>{item.KELUARAN_PROGKER}</small>
                                                </td>
                                                <td>{item?.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN ?? item?.tahunAnggaran?.DESKRIPSI_TAHUN_ANGGARAN ?? "-"}</td>
                                                <td>{normalizeUnitLabel(item?.unit ?? {})}</td>
                                                <td>{formatCurrency(item.NOMINAL)}</td>
                                                <td>{item.WAKTU_AWAL} s.d. {item.WAKTU_AKHIR}</td>
                                                <td>{item.NIP_PENANGGUNG_JAWAB}</td>
                                                <td>
                                                    <div className="waka-rkt-row-actions">
                                                        <button type="button" className="waka-rkt-mini-button edit" onClick={() => handleEdit(item)}>Edit</button>
                                                        <button type="button" className="waka-rkt-mini-button delete" onClick={() => handleDelete(item.ID_PROGRAM_KERJA)}>Hapus</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </main>
        </div>
    );
}

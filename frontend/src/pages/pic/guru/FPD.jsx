import { useEffect, useMemo, useState } from "react";
import "../../../styles/pic/guru/FPD.css";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";
const SIDEBAR_ITEMS = ["Dashboard", "Page RKT", "Page Realisasi RKT", "Page Bridging RKT", "Page Pengajuan Dana", "Page LPJ", "Page Evaluasi RKT"];
const currencyFormatter = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });
const dateFormatter = new Intl.DateTimeFormat("id-ID", { day: "2-digit", month: "short", year: "numeric" });
const createEmptyForm = (idFpd = "", idDetail = "", satuan = "") => ({ idFpd, idDetail, qty: "1", volume: "1", satuan, hargaSatuan: "0" });
const toArray = (value) => (Array.isArray(value) ? value : []);
const formatReadableDate = (value) => (value ? dateFormatter.format(new Date(value)) : "-");
const getProgramDetails = (fpd) => {
    const program = fpd?.program_kerja ?? fpd?.programKerja ?? {};
    return toArray(program.detail_program_kerja ?? program.detailProgramKerja);
};
const getSumberDanaName = (detail) => detail?.sumber_dana?.SUMBER_DANA ?? detail?.sumberDana?.SUMBER_DANA ?? "-";
const getDetailLabel = (detail, fallbackProgramName) => {
    const programName = detail?.program_kerja?.PROGRAM_KERJA ?? detail?.programKerja?.PROGRAM_KERJA ?? fallbackProgramName ?? "Detail Program";
    return `${programName} - ${getSumberDanaName(detail)}`;
};

async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const json = await response.json();

    if (!response.ok || json.success === false) {
        throw new Error(json.message || "Terjadi kesalahan pada server");
    }

    return json;
}

export default function PicGuruFPD() {
    const [fpdOptions, setFpdOptions] = useState([]);
    const [detailRows, setDetailRows] = useState([]);
    const [form, setForm] = useState(createEmptyForm());
    const [editId, setEditId] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");

    const selectedFpd = useMemo(() => fpdOptions.find((item) => String(item.ID_FPD) === String(form.idFpd)) ?? null, [fpdOptions, form.idFpd]);
    const availableDetails = useMemo(() => getProgramDetails(selectedFpd), [selectedFpd]);
    const selectedDetail = useMemo(() => availableDetails.find((item) => String(item.ID_DT_PROGKER) === String(form.idDetail)) ?? null, [availableDetails, form.idDetail]);
    const nominalPengajuan = Number(form.qty || 0) * Number(form.volume || 0) * Number(form.hargaSatuan || 0);

    const loadDetailRows = async (idFpd) => {
        if (!idFpd) {
            setDetailRows([]);
            return;
        }

        const params = new URLSearchParams({ id_fpd: String(idFpd) });
        const json = await fetchJson(`${API_BASE_URL}/dtl-fpd?${params.toString()}`);
        setDetailRows(json.data || []);
    };

    const syncFormToSelectedFpd = (fpdData, currentForm = form) => {
        const nextDetails = getProgramDetails(fpdData);
        const currentDetailExists = nextDetails.some((item) => String(item.ID_DT_PROGKER) === String(currentForm.idDetail));
        const defaultDetail = currentDetailExists ? currentForm.idDetail : nextDetails[0]?.ID_DT_PROGKER ?? "";
        const defaultSatuan = currentDetailExists ? currentForm.satuan : nextDetails[0]?.SATUAN ?? "";

        setForm((prev) => ({ ...prev, idFpd: fpdData?.ID_FPD ?? "", idDetail: String(defaultDetail), satuan: defaultSatuan || "" }));
    };

    const loadInitialData = async () => {
        setLoading(true);
        setError("");

        try {
            const json = await fetchJson(`${API_BASE_URL}/fpd-anggaran`);
            const options = json.data || [];
            setFpdOptions(options);

            if (options.length > 0) {
                const defaultFpd = options[0];
                const defaultDetails = getProgramDetails(defaultFpd);

                setForm(createEmptyForm(String(defaultFpd.ID_FPD), String(defaultDetails[0]?.ID_DT_PROGKER ?? ""), defaultDetails[0]?.SATUAN ?? ""));
                await loadDetailRows(defaultFpd.ID_FPD);
            } else {
                setForm(createEmptyForm());
                setDetailRows([]);
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadInitialData();
    }, []);

    const handleFpdChange = async (event) => {
        const nextFpdId = event.target.value;
        const nextFpd = fpdOptions.find((item) => String(item.ID_FPD) === nextFpdId) ?? null;

        setEditId(null);
        setMessage("");
        setError("");
        syncFormToSelectedFpd(nextFpd, { ...form, idFpd: nextFpdId, idDetail: "", satuan: "" });
        await loadDetailRows(nextFpdId);
    };

    const handleChange = (event) => {
        const { name, value } = event.target;

        if (name === "idDetail") {
            const nextDetail = availableDetails.find((item) => String(item.ID_DT_PROGKER) === String(value)) ?? null;

            setForm((current) => ({ ...current, idDetail: value, satuan: nextDetail?.SATUAN ?? current.satuan }));
            return;
        }

        setForm((current) => ({ ...current, [name]: value }));
    };

    const resetForm = () => {
        const defaultDetail = availableDetails[0];
        setEditId(null);
        setForm(createEmptyForm(form.idFpd, String(defaultDetail?.ID_DT_PROGKER ?? ""), defaultDetail?.SATUAN ?? ""));
    };

    const refreshSelectedData = async (idFpd) => {
        const json = await fetchJson(`${API_BASE_URL}/fpd-anggaran`);
        const options = json.data || [];
        setFpdOptions(options);

        const freshSelected = options.find((item) => String(item.ID_FPD) === String(idFpd)) ?? options[0] ?? null;

        if (freshSelected) {
            syncFormToSelectedFpd(freshSelected, { ...form, idFpd: String(freshSelected.ID_FPD) });
            await loadDetailRows(freshSelected.ID_FPD);
        } else {
            setDetailRows([]);
        }
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setMessage("");
        setError("");

        const payload = {
            ID_FPD: Number(form.idFpd),
            ID_DT_PROGKER: Number(form.idDetail),
            QTY: Number(form.qty),
            VOLUME: Number(form.volume),
            SATUAN: form.satuan,
            HARGA_SATUAN: Number(form.hargaSatuan),
        };

        const url = editId ? `${API_BASE_URL}/dtl-fpd/update/${editId}` : `${API_BASE_URL}/dtl-fpd/store`;
        const method = editId ? "PUT" : "POST";

        try {
            await fetchJson(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            setMessage(editId ? "Detail FPD berhasil diperbarui." : "Detail FPD berhasil ditambahkan.");
            await refreshSelectedData(payload.ID_FPD);
            resetForm();
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    const handleEdit = (item) => {
        setEditId(item.ID_DT_FPD);
        setMessage("");
        setError("");
        setForm({
            idFpd: String(item.ID_FPD),
            idDetail: String(item.ID_DT_PROGKER),
            qty: String(item.QTY ?? 1),
            volume: String(item.VOLUME ?? 1),
            satuan: item.SATUAN ?? "",
            hargaSatuan: String(item.HARGA_SATUAN ?? 0),
        });
    };

    const handleDelete = async (item) => {
        const confirmed = window.confirm("Yakin ingin menghapus detail FPD ini?");
        if (!confirmed) {
            return;
        }

        setMessage("");
        setError("");

        try {
            await fetchJson(`${API_BASE_URL}/dtl-fpd/delete/${item.ID_DT_FPD}`, { method: "DELETE" });
            setMessage("Detail FPD berhasil dihapus.");
            await refreshSelectedData(item.ID_FPD);

            if (editId === item.ID_DT_FPD) {
                resetForm();
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleExport = () => {
        if (!form.idFpd) {
            setError("Pilih FPD anggaran terlebih dahulu sebelum export.");
            return;
        }

        window.open(`${API_BASE_URL}/fpd-anggaran/export/${form.idFpd}`, "_blank");
    };

    const selectedProgramName = selectedFpd?.program_kerja?.PROGRAM_KERJA ?? selectedFpd?.programKerja?.PROGRAM_KERJA ?? "-";

    return (
        <div className="pic-fpd-shell">
            <aside className="pic-fpd-sidebar">
                <div className="pic-fpd-brand"><div className="pic-fpd-brand-badge">SMK</div><div><strong>Portal Guru</strong><span>SMK BOPKRI 2</span></div></div>
                <nav className="pic-fpd-nav" aria-label="Navigasi portal guru">
                    {SIDEBAR_ITEMS.map((item) => <button key={item} type="button" className={`pic-fpd-nav-item ${item === "Page Pengajuan Dana" ? "active" : ""}`}>{item}</button>)}
                </nav>
            </aside>
            <main className="pic-fpd-main">
                <header className="pic-fpd-page-heading">
                    <h1>Form Pengajuan Dana</h1>
                    <p>
                        Halaman ini sudah disiapkan untuk mengambil data FPD langsung dari database,
                        lalu dipakai untuk create, update, delete, refresh, dan export detail pengajuan.
                    </p>
                </header>
                <section className="pic-fpd-card">
                    <div className="pic-fpd-card-heading">
                        <div>
                            <h2>Form Pengajuan</h2>
                            <p>Pilih FPD anggaran aktif, lalu kelola detail pengajuan yang tersimpan di database.</p>
                        </div>
                        <div className="pic-fpd-toolbar">
                            <button type="button" className="pic-fpd-button ghost" onClick={loadInitialData}>Refresh Data</button>
                            <button type="button" className="pic-fpd-button primary" onClick={handleExport}>Export CSV</button>
                        </div>
                    </div>
                    {message ? <div className="pic-fpd-feedback success">{message}</div> : null}
                    {error ? <div className="pic-fpd-feedback error">{error}</div> : null}
                    {loading ? <div className="pic-fpd-empty">Memuat data dari database...</div> : (
                        <>
                            <form onSubmit={handleSubmit}>
                                <div className="pic-fpd-form-grid">
                                    <label className="pic-fpd-field pic-fpd-field-full">
                                        <span>Referensi FPD Anggaran</span>
                                        <select name="idFpd" value={form.idFpd} onChange={handleFpdChange} required>
                                            <option value="">Pilih FPD...</option>
                                            {fpdOptions.map((item) => (
                                                <option key={item.ID_FPD} value={item.ID_FPD}>
                                                    {`FPD #${item.ID_FPD} - ${item.program_kerja?.PROGRAM_KERJA ?? item.programKerja?.PROGRAM_KERJA ?? "Program Kerja"}`}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="pic-fpd-field pic-fpd-field-full">
                                        <span>Detail Program Kerja</span>
                                        <select name="idDetail" value={form.idDetail} onChange={handleChange} required disabled={!form.idFpd}>
                                            <option value="">Pilih detail program...</option>
                                            {availableDetails.map((item) => (
                                                <option key={item.ID_DT_PROGKER} value={item.ID_DT_PROGKER}>
                                                    {getDetailLabel(item, selectedProgramName)}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="pic-fpd-field">
                                        <span>Qty</span>
                                        <input type="number" min="1" name="qty" value={form.qty} onChange={handleChange} required />
                                    </label>
                                    <label className="pic-fpd-field">
                                        <span>Volume</span>
                                        <input type="number" min="1" name="volume" value={form.volume} onChange={handleChange} required />
                                    </label>
                                    <label className="pic-fpd-field">
                                        <span>Satuan</span>
                                        <input type="text" name="satuan" value={form.satuan} onChange={handleChange} maxLength="10" required />
                                    </label>
                                    <label className="pic-fpd-field">
                                        <span>Harga Satuan</span>
                                        <input type="number" min="0" name="hargaSatuan" value={form.hargaSatuan} onChange={handleChange} required />
                                    </label>
                                </div>
                                <div className="pic-fpd-actions">
                                    <button type="button" className="pic-fpd-button secondary" onClick={resetForm}>Reset Form</button>
                                    <button type="submit" className="pic-fpd-button primary" disabled={submitting || !form.idFpd || !form.idDetail}>
                                        {submitting ? "Menyimpan..." : editId ? "Update Detail" : "Simpan Detail"}
                                    </button>
                                </div>
                            </form>
                            <div className="pic-fpd-bottom-grid">
                                <section className="pic-fpd-subcard">
                                    <div className="pic-fpd-subcard-heading"><h3>Riwayat Detail Pengajuan</h3></div>
                                    <div className="pic-fpd-table-wrapper">
                                        <table className="pic-fpd-table">
                                            <thead>
                                                <tr>
                                                    <th>Detail Program</th>
                                                    <th>Sumber Dana</th>
                                                    <th>Qty</th>
                                                    <th>Volume</th>
                                                    <th>Total</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {detailRows.length === 0 ? (
                                                    <tr>
                                                        <td colSpan="6" className="pic-fpd-empty-cell">Belum ada detail FPD untuk referensi ini.</td>
                                                    </tr>
                                                ) : (
                                                    detailRows.map((item) => (
                                                        <tr key={item.ID_DT_FPD}>
                                                            <td>{getDetailLabel(item.detail_program ?? item.detailProgram, selectedProgramName)}</td>
                                                            <td>{getSumberDanaName(item.detail_program ?? item.detailProgram)}</td>
                                                            <td>{item.QTY}</td>
                                                            <td>{item.VOLUME} {item.SATUAN}</td>
                                                            <td>{currencyFormatter.format(Number(item.TOTAL ?? 0))}</td>
                                                            <td>
                                                                <div className="pic-fpd-row-actions">
                                                                    <button type="button" className="pic-fpd-mini-button edit" onClick={() => handleEdit(item)}>Edit</button>
                                                                    <button type="button" className="pic-fpd-mini-button delete" onClick={() => handleDelete(item)}>Hapus</button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ))
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                                <aside className="pic-fpd-subcard pic-fpd-summary-card">
                                    <div className="pic-fpd-subcard-heading"><h3>Ringkasan Pengajuan</h3></div>
                                    <div className="pic-fpd-summary-list">
                                        <div><span>ID FPD</span><strong>{selectedFpd?.ID_FPD ?? "-"}</strong></div>
                                        <div><span>Tanggal FPD</span><strong>{formatReadableDate(selectedFpd?.TGL_FPD)}</strong></div>
                                        <div><span>Program Kerja</span><strong>{selectedProgramName}</strong></div>
                                    </div>
                                    <div className="pic-fpd-summary-total"><span>Total Form Aktif</span><strong>{currencyFormatter.format(nominalPengajuan)}</strong></div>
                                    <div className="pic-fpd-summary-note"><span>Total detail tersimpan</span><strong>{detailRows.length} baris</strong></div>
                                </aside>
                            </div>
                        </>
                    )}
                </section>
            </main>
        </div>
    );
}

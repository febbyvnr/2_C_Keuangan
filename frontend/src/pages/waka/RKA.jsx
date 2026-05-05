import { useState, useEffect, useMemo } from "react";
import SidebarWaka from "../../components/SidebarWaka";
import { apiFetch } from "../../api/api";
import "../../styles/waka/RKA.css";

const BASE = "http://localhost:8000/api";

function formatRupiah(n) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency", currency: "IDR", minimumFractionDigits: 0,
    }).format(n || 0);
}

function formatDate(v) {
    if (!v) return "-";
    const d = new Date(v);
    return isNaN(d) ? v : d.toLocaleDateString("id-ID");
}

function getDetails(item) {
    return item?.details || item?.detail_program_kerja || [];
}

function getTotalRincian(item) {
    return getDetails(item).reduce((s, d) => s + Number(d.TOTAL_PROGKER || d.NOMINAL || 0), 0);
}

const EMPTY_FORM = {
    ID_REF_DANA: "", QTY: "", HARGA_SATUAN: "", VOLUME: "1",
    SATUAN: "", TGL_AWAL: "", TGL_AKHIR: "",
};

export default function WakaRKA() {
    const [data, setData] = useState([]);
    const [selectedId, setSelectedId] = useState(null);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState("");
    const [refDana, setRefDana] = useState([]);
    const [showModal, setShowModal] = useState(false);
    const [form, setForm] = useState(EMPTY_FORM);
    const [submitting, setSubmitting] = useState(false);
    const [toast, setToast] = useState(null);

    const selectedItem = useMemo(
        () => data.find((d) => d.ID_PROGRAM_KERJA === selectedId),
        [data, selectedId]
    );

    const filteredData = useMemo(() => {
        const kw = search.toLowerCase();
        return data.filter(
            (item) =>
                (item.PROGRAM_KERJA || "").toLowerCase().includes(kw) ||
                (item.INDIKATOR || "").toLowerCase().includes(kw)
        );
    }, [data, search]);

    useEffect(() => {
        fetchData();
        fetchRefDana();
    }, []);

    const fetchData = async () => {
        setLoading(true);
        try {
            const res = await apiFetch("/rka");
            const rows = res.data || [];
            setData(rows);
            if (rows.length && !selectedId) setSelectedId(rows[0].ID_PROGRAM_KERJA);
        } catch (err) {
            showToast("error", err.message || "Gagal memuat data RKA");
        } finally {
            setLoading(false);
        }
    };

    const fetchRefDana = async () => {
        try {
            const res = await apiFetch("/ref-sumber-dana");
            setRefDana(res.data || []);
        } catch (_) {}
    };

    const showToast = (type, msg) => {
        setToast({ type, msg });
        setTimeout(() => setToast(null), 3000);
    };

    const handleDeleteRka = async (id) => {
        if (!window.confirm("Hapus RKA ini? Data yang sudah dipakai di transaksi tidak bisa dihapus.")) return;
        try {
            await apiFetch(`/rka/delete/${id}`, { method: "DELETE" });
            showToast("success", "RKA berhasil dihapus");
            setSelectedId(null);
            fetchData();
        } catch (err) {
            showToast("error", err.message || "Gagal menghapus RKA");
        }
    };

    const handleAddDetail = async (e) => {
        e.preventDefault();
        if (!selectedId) return;
        setSubmitting(true);
        try {
            await apiFetch("/rka/store", {
                method: "POST",
                body: JSON.stringify({
                    ID_PROGRAM_KERJA: selectedId,
                    details: [{
                        ID_REF_DANA: Number(form.ID_REF_DANA),
                        QTY: Number(form.QTY),
                        HARGA_SATUAN: Number(form.HARGA_SATUAN),
                        VOLUME: Number(form.VOLUME) || 1,
                        SATUAN: form.SATUAN,
                        TGL_AWAL: form.TGL_AWAL,
                        TGL_AKHIR: form.TGL_AKHIR,
                    }],
                }),
            });
            showToast("success", "Rincian berhasil ditambahkan");
            setShowModal(false);
            setForm(EMPTY_FORM);
            fetchData();
        } catch (err) {
            showToast("error", err.message || "Gagal menyimpan rincian");
        } finally {
            setSubmitting(false);
        }
    };

    const downloadFile = async (endpoint, filename) => {
        const token = localStorage.getItem("token");
        try {
            const res = await fetch(`${BASE}${endpoint}`, {
                headers: { Authorization: `Bearer ${token}`, Accept: "*/*" },
            });
            if (!res.ok) {
                throw new Error("Terjadi kesalahan pada server, silakan coba lagi.");
            }
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            const cd = res.headers.get("content-disposition") || "";
            const match = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            a.download = match ? match[1].replace(/['"]/g, "") : filename;
            a.style.display = "none";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } catch (err) {
            showToast("error", err.message);
        }
    };

    const setField = (key, val) => setForm((p) => ({ ...p, [key]: val }));

    return (
        <div className="waka-rka-shell">
            <SidebarWaka />
            <main className="waka-rka-main">
                {toast && (
                    <div className={`waka-rka-toast ${toast.type}`}>{toast.msg}</div>
                )}

                <header className="waka-rka-page-header">
                    <div>
                        <h1>Rencana Kegiatan & Anggaran</h1>
                        <p>Kelola rincian anggaran dari program kerja yang sudah ada</p>
                    </div>
                    <div className="waka-rka-header-actions">
                        <button className="waka-rka-btn ghost" onClick={() => downloadFile("/rka/export", "RKA.xlsx")}>
                            <i className="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button className="waka-rka-btn ghost" onClick={() => downloadFile("/rka/export/pdf", "RKA.pdf")}>
                            <i className="bi bi-file-earmark-pdf"></i> Export PDF
                        </button>
                    </div>
                </header>

                <div className="waka-rka-body">
                    {/* Kiri: Tabel RKA */}
                    <div className="waka-rka-card">
                        <div className="waka-rka-card-head">
                            <h2>Daftar Program Kerja</h2>
                            <div className="waka-rka-search-row">
                                <input
                                    type="text"
                                    placeholder="Cari program kerja..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="waka-rka-search-input"
                                />
                                <button className="waka-rka-btn ghost" onClick={fetchData} title="Refresh">
                                    <i className="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>

                        {loading ? (
                            <div className="waka-rka-empty">Memuat data...</div>
                        ) : (
                            <div className="waka-rka-table-wrap">
                                <table className="waka-rka-table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Program Kerja</th>
                                            <th>Anggaran</th>
                                            <th>Total Rincian</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filteredData.length === 0 ? (
                                            <tr>
                                                <td colSpan={5} className="waka-rka-empty-cell">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        ) : (
                                            filteredData.map((item, i) => (
                                                <tr
                                                    key={item.ID_PROGRAM_KERJA}
                                                    className={selectedId === item.ID_PROGRAM_KERJA ? "active" : ""}
                                                    onClick={() => setSelectedId(item.ID_PROGRAM_KERJA)}
                                                >
                                                    <td>{i + 1}</td>
                                                    <td>
                                                        <strong>{item.PROGRAM_KERJA || "-"}</strong>
                                                        {item.INDIKATOR && (
                                                            <small>{item.INDIKATOR}</small>
                                                        )}
                                                    </td>
                                                    <td>{formatRupiah(item.NOMINAL)}</td>
                                                    <td>{formatRupiah(getTotalRincian(item))}</td>
                                                    <td>
                                                        <span className={`waka-rka-badge ${getDetails(item).length ? "ready" : "draft"}`}>
                                                            {getDetails(item).length ? "Ada Rincian" : "Kosong"}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>

                    {/* Kanan: Detail Panel */}
                    <div className="waka-rka-card">
                        {selectedItem ? (
                            <>
                                <div className="waka-rka-detail-head">
                                    <div>
                                        <h2>{selectedItem.PROGRAM_KERJA}</h2>
                                        <p>Anggaran: {formatRupiah(selectedItem.NOMINAL)}</p>
                                    </div>
                                    <div className="waka-rka-detail-actions">
                                        <button
                                            className="waka-rka-btn primary"
                                            onClick={() => setShowModal(true)}
                                        >
                                            <i className="bi bi-plus-lg"></i> Tambah Detail
                                        </button>
                                        <button
                                            className="waka-rka-btn danger"
                                            onClick={() => handleDeleteRka(selectedItem.ID_PROGRAM_KERJA)}
                                        >
                                            <i className="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </div>

                                <div className="waka-rka-info-grid">
                                    {selectedItem.INDIKATOR && (
                                        <div className="waka-rka-info-item">
                                            <span>Indikator</span>
                                            <strong>{selectedItem.INDIKATOR}</strong>
                                        </div>
                                    )}
                                    {selectedItem.SASARAN && (
                                        <div className="waka-rka-info-item">
                                            <span>Sasaran</span>
                                            <strong>{selectedItem.SASARAN}</strong>
                                        </div>
                                    )}
                                    <div className="waka-rka-info-item">
                                        <span>Total Rincian</span>
                                        <strong>{formatRupiah(getTotalRincian(selectedItem))}</strong>
                                    </div>
                                    <div className="waka-rka-info-item">
                                        <span>Jumlah Item</span>
                                        <strong>{getDetails(selectedItem).length} item</strong>
                                    </div>
                                </div>

                                <h3 className="waka-rka-detail-subtitle">Rincian Anggaran</h3>
                                {getDetails(selectedItem).length === 0 ? (
                                    <div className="waka-rka-empty">Belum ada rincian anggaran</div>
                                ) : (
                                    <div className="waka-rka-detail-table-wrap">
                                        <table className="waka-rka-detail-table">
                                            <thead>
                                                <tr>
                                                    <th>Sumber Dana</th>
                                                    <th>QTY</th>
                                                    <th>Vol</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Total</th>
                                                    <th>Satuan</th>
                                                    <th>Periode</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {getDetails(selectedItem).map((d) => (
                                                    <tr key={d.ID_DT_PROGKER}>
                                                        <td>{d.sumber_dana?.NAMA_SUMBER_DANA || `ID ${d.ID_REF_DANA}`}</td>
                                                        <td>{d.QTY}</td>
                                                        <td>{d.VOLUME}</td>
                                                        <td>{formatRupiah(d.HARGA_SATUAN)}</td>
                                                        <td>{formatRupiah(d.TOTAL_PROGKER || d.NOMINAL)}</td>
                                                        <td>{d.SATUAN}</td>
                                                        <td>{formatDate(d.TGL_AWAL)} – {formatDate(d.TGL_AKHIR)}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="waka-rka-empty-state">
                                <i className="bi bi-journal-text"></i>
                                <p>Pilih program kerja untuk melihat detail RKA</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Modal Tambah Detail */}
                {showModal && (
                    <div
                        className="waka-rka-modal-overlay"
                        onClick={() => setShowModal(false)}
                    >
                        <div
                            className="waka-rka-modal"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <div className="waka-rka-modal-head">
                                <h3>Tambah Rincian Anggaran</h3>
                                <button className="waka-rka-modal-close" onClick={() => setShowModal(false)}>
                                    <i className="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <p className="waka-rka-modal-subtitle">{selectedItem?.PROGRAM_KERJA}</p>

                            <form onSubmit={handleAddDetail} className="waka-rka-modal-form">
                                <label>
                                    Sumber Dana <span>*</span>
                                    <select
                                        required
                                        value={form.ID_REF_DANA}
                                        onChange={(e) => setField("ID_REF_DANA", e.target.value)}
                                    >
                                        <option value="">-- Pilih Sumber Dana --</option>
                                        {refDana.map((r) => (
                                            <option key={r.ID_REF_DANA} value={r.ID_REF_DANA}>
                                                {r.NAMA_SUMBER_DANA}
                                            </option>
                                        ))}
                                    </select>
                                </label>

                                <div className="waka-rka-modal-row">
                                    <label>
                                        QTY <span>*</span>
                                        <input
                                            type="number" min="1" required
                                            value={form.QTY}
                                            onChange={(e) => setField("QTY", e.target.value)}
                                        />
                                    </label>
                                    <label>
                                        Volume
                                        <input
                                            type="number" min="1"
                                            value={form.VOLUME}
                                            onChange={(e) => setField("VOLUME", e.target.value)}
                                        />
                                    </label>
                                </div>

                                <div className="waka-rka-modal-row">
                                    <label>
                                        Harga Satuan <span>*</span>
                                        <input
                                            type="number" min="0" required
                                            placeholder="0"
                                            value={form.HARGA_SATUAN}
                                            onChange={(e) => setField("HARGA_SATUAN", e.target.value)}
                                        />
                                    </label>
                                    <label>
                                        Satuan <span>*</span>
                                        <input
                                            type="text" required
                                            placeholder="rim, unit, buah..."
                                            value={form.SATUAN}
                                            onChange={(e) => setField("SATUAN", e.target.value)}
                                        />
                                    </label>
                                </div>

                                <div className="waka-rka-modal-row">
                                    <label>
                                        Tanggal Awal <span>*</span>
                                        <input
                                            type="date" required
                                            value={form.TGL_AWAL}
                                            onChange={(e) => setField("TGL_AWAL", e.target.value)}
                                        />
                                    </label>
                                    <label>
                                        Tanggal Akhir <span>*</span>
                                        <input
                                            type="date" required
                                            value={form.TGL_AKHIR}
                                            onChange={(e) => setField("TGL_AKHIR", e.target.value)}
                                        />
                                    </label>
                                </div>

                                <div className="waka-rka-modal-footer">
                                    <button
                                        type="button"
                                        className="waka-rka-btn ghost"
                                        onClick={() => setShowModal(false)}
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        className="waka-rka-btn primary"
                                        disabled={submitting}
                                    >
                                        {submitting ? "Menyimpan..." : "Simpan"}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}

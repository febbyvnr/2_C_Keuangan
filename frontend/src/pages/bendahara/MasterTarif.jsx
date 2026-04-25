import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterTarif.css";

export default function MasterTarif() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [jenisTarifList, setJenisTarifList] = useState([]);
    const [tahunAnggaranList, setTahunAnggaranList] = useState([]);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_REF_TARIF",
        direction: "desc"
    });
    const [form, setForm] = useState({
        ID_JENIS_TARIF: "",
        ID_TA_ANGGARAN: "",
        DESKRIPSI_TARIF: "",
        NOMINAL: "",
        TGL_PENETAPAN: "",
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/tarif?search=${keyword}`
                : "http://localhost:8000/api/tarif";

            const res = await fetch(url);
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text);
            }
            const json = await res.json();
            setData(json.data || json || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };
    
    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            setCurrentPage(1);
            fetchData(search);
        }
    };

    const handleSort = (key) => {
        let direction = "asc";
        if (sortConfig.key === key && sortConfig.direction === "asc") {
            direction = "desc";
        }
        setSortConfig({ key, direction });
    };

    const getIcon = (key) => {
        if (sortConfig.key !== key) return "bi bi-funnel";
        return "bi bi-funnel-fill";
    };

    const filteredData = data.filter((item) => {
        const keyword = search.toLowerCase();
        return (
            item.ID_REF_TARIF?.toString().includes(keyword) ||
            item.DESKRIPSI_TARIF?.toLowerCase().includes(keyword) ||
            item.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase().includes(keyword) ||
            item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase().includes(keyword)
        );
    });

    const sortedData = [...filteredData].sort((a, b) => {
        let valA, valB;
        switch (sortConfig.key) {
            case "ID_REF_TARIF":
                valA = Number(a.ID_REF_TARIF);
                valB = Number(b.ID_REF_TARIF);
                break;
            case "ID_JENIS_TARIF":
                valA = a.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase() || "";
                valB = b.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase() || "";
                break;
            case "ID_TA_ANGGARAN":
                valA = a.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase() || "";
                valB = b.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase() || "";
                break;
            case "DESKRIPSI_TARIF":
                valA = a.DESKRIPSI_TARIF?.toLowerCase() || "";
                valB = b.DESKRIPSI_TARIF?.toLowerCase() || "";
                break;
            case "NOMINAL":
                valA = Number(a.NOMINAL);
                valB = Number(b.NOMINAL);
                break;
            case "TGL_PENETAPAN":
                valA = new Date(a.TGL_PENETAPAN);
                valB = new Date(b.TGL_PENETAPAN);
                break;
            default:
                valA = "";
                valB = "";
        }
        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });

    const fetchDropdown = async () => {
        try {
            const [jenisRes, tahunRes] = await Promise.all([
                fetch("http://localhost:8000/api/jenis-tarif"),
                fetch("http://localhost:8000/api/tahun-anggaran")
            ]);
            const jenisJson = await jenisRes.json();
            const tahunJson = await tahunRes.json();
            setJenisTarifList(jenisJson.data || jenisJson || []);
            setTahunAnggaranList(tahunJson.data || tahunJson || []);
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchData();
        fetchDropdown();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_TARIF);
        setForm({
            ID_JENIS_TARIF: item.ID_JENIS_TARIF || "",
            ID_TA_ANGGARAN: item.ID_TA_ANGGARAN || "",
            DESKRIPSI_TARIF: item.DESKRIPSI_TARIF || "",
            NOMINAL: item.NOMINAL || "",
            TGL_PENETAPAN: item.TGL_PENETAPAN
                ? new Date(item.TGL_PENETAPAN).toISOString().split("T")[0]
                : ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const now = new Date();
        const formattedDateTime = form.TGL_PENETAPAN
            ? `${form.TGL_PENETAPAN} ${now.toTimeString().split(" ")[0]}`
            : null;
        const payload = {
            ...form,
            TGL_PENETAPAN: formattedDateTime
        };
        const url = isEdit
            ? `http://localhost:8000/api/tarif/update/${editId}`
            : "http://localhost:8000/api/tarif/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            showToast(isEdit ? "update" : "add");
            setForm({
                ID_JENIS_TARIF: "",
                ID_TA_ANGGARAN: "",
                DESKRIPSI_TARIF: "",
                NOMINAL: "",
                TGL_PENETAPAN: "",
            });
            fetchData();
        } else {
            showToast("error", json.message || "Gagal");
        }
    };

    const handleDelete = (id) => {
        setConfirmDeleteId(id);
    };

    const closeModal = () => {
        setShowModal(false);
        setIsEdit(false);
        setEditId(null);
        setForm({
            ID_JENIS_TARIF: "",
            ID_TA_ANGGARAN: "",
            DESKRIPSI_TARIF: "",
            NOMINAL: "",
            TGL_PENETAPAN: "",
        });
    };

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const totalData = data.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);
    const [confirmDeleteId, setConfirmDeleteId] = useState(null);

    const showToast = (type = "add", message = "") => {
        let action = "";
        if (type === "add") action = "Menambahkan";
        if (type === "update") action = "Memperbarui";
        if (type === "delete") action = "Menghapus";
        if (type === "error") action = "Gagal";
        setToast({ type, action, message });
        setVisible(true);
        setTimeout(() => setVisible(false), 2500);
        setTimeout(() => setToast(null), 3000);
    };

    const confirmDeleteAction = async () => {
        try {
            const res = await fetch(`http://localhost:8000/api/tarif/delete/${confirmDeleteId}`, {
                method: "DELETE"
            });
            const json = await res.json();
            if (json.success) {
                showToast("delete");
                fetchData();
            } else {
                showToast("error", json.message || "Gagal menghapus data");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Terjadi error");
        } finally {
            setConfirmDeleteId(null);
        }
    };

    return (
        <div className="tarif-container">
            <div className="tarif-header">
                <h2>Master Tarif</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari tarif..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
                            className="search-input"
                        />
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                    <button className="btn-primary" 
                        onClick={() => {
                            setIsEdit(false);
                            setEditId(null);
                            setForm({
                                ID_JENIS_TARIF: "",
                                ID_TA_ANGGARAN: "",
                                DESKRIPSI_TARIF: "",
                                NOMINAL: "",
                                TGL_PENETAPAN: "",
                            });
                            setShowModal(true);
                        }}
                    >
                        Tambah Tarif
                    </button>
                </div>
            </div>
            <div className="tarif-table-wrapper">
                <table className="tarif-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_REF_TARIF")}>
                                ID <i className={getIcon("ID_REF_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("ID_JENIS_TARIF")}>
                                Jenis Tarif <i className={getIcon("ID_JENIS_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("ID_TA_ANGGARAN")}>
                                TA Anggaran <i className={getIcon("ID_TA_ANGGARAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_TARIF")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("NOMINAL")}>
                                Nominal <i className={getIcon("NOMINAL")}></i>
                            </th>
                            <th onClick={() => handleSort("TGL_PENETAPAN")}>
                                Tanggal Penetapan <i className={getIcon("TGL_PENETAPAN")}></i>
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan="5" className="text-center">
                                    Loading...
                                </td>
                            </tr>
                        ) : currentData.length === 0 ? (
                            <tr>
                                <td colSpan="5" className="text-center">
                                    Tidak ada data
                                </td>
                            </tr>
                        ) : (
                            currentData.map((item) => (
                                <tr key={item.ID_REF_TARIF}>
                                    <td>{item.ID_REF_TARIF}</td>
                                    <td>
                                        {item.jenis_tarif?.DESKRIPSI_JENIS_TARIF || item.ID_JENIS_TARIF}
                                    </td>
                                    <td>
                                        {item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || item.ID_TA_ANGGARAN}
                                    </td>
                                    <td>{item.DESKRIPSI_TARIF}</td>
                                    <td>Rp {item.NOMINAL}</td>
                                    <td>{new Date(item.TGL_PENETAPAN).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Tarif sudah digunakan Program Kerja"
                                                    : "Hapus Tarif"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_REF_TARIF)
                                            }
                                        >
                                            <i className="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
            <div className="pagination-wrapper">
                <div className="pagination-info">
                    Menampilkan {startData} - {endData} dari {totalData} data
                </div>
                <div className="pagination">
                    <button className="page-btn" disabled={currentPage === 1} onClick={() => setCurrentPage(p => p - 1)}>
                        <i className="bi bi-chevron-left"></i>
                    </button>
                    {Array.from({ length: totalPages }, (_, i) => (
                        <button
                            key={i + 1}
                            onClick={() => changePage(i + 1)}
                            className={`page-btn ${
                                currentPage === i + 1 ? "active" : ""
                            }`}
                        >
                            {i + 1}
                        </button>
                    ))}
                    <button className="page-btn" disabled={currentPage === totalPages} onClick={() => setCurrentPage(p => p + 1)}>
                        <i className="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div className="export-wrapper">
                    <a href={`http://localhost:8000/api/tarif/export/excel?search=${search}`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    <a href={`http://localhost:8000/api/tarif/export/pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                       <h3>{isEdit ? "Edit Tarif" : "Tambah Tarif"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>ID Jenis Tarif</label>
                            <select
                                name="ID_JENIS_TARIF"
                                value={form.ID_JENIS_TARIF}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Jenis Tarif --</option>
                                {[...jenisTarifList]
                                    .sort((a, b) => b.ID_JENIS_TARIF - a.ID_JENIS_TARIF)
                                    .map((item) => (
                                        <option
                                            key={item.ID_JENIS_TARIF}
                                            value={item.ID_JENIS_TARIF}
                                        >
                                            {item.ID_JENIS_TARIF} - {item.DESKRIPSI_JENIS_TARIF}
                                        </option>
                                    ))
                                }
                            </select>
                            <label>ID TA Anggaran</label>
                            <select
                                name="ID_TA_ANGGARAN"
                                value={form.ID_TA_ANGGARAN}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Tahun Anggaran --</option>
                                {tahunAnggaranList.map((item) => (
                                    <option key={item.ID_TA_ANGGARAN} value={item.ID_TA_ANGGARAN}>
                                        {item.ID_TA_ANGGARAN} - {item.TAHUN_ANGGARAN || item.DESKRIPSI_TAHUN_ANGGARAN}
                                    </option>
                                ))}
                            </select>
                            <label>Deskripsi Tarif</label>
                            <input
                                type="text"
                                name="DESKRIPSI_TARIF"
                                value={form.DESKRIPSI_TARIF}
                                onChange={handleChange}
                                placeholder="Masukkan deskripsi"
                            />
                            <label>Deskripsi Nominal</label>
                            <input
                                type="number"
                                name="NOMINAL"
                                value={form.NOMINAL}
                                onChange={handleChange}
                                placeholder="Masukkan nominal"
                            />
                            <label>Tanggal Penetapan</label>
                            <div className="date-wrapper">
                                <input
                                    type="date"
                                    name="TGL_PENETAPAN"
                                    value={form.TGL_PENETAPAN}
                                    onChange={handleChange}
                                    placeholder="Masukkan tanggal penetapan"
                                />
                                <i className="bi bi-calendar-plus date-icon"></i>
                            </div>
                            <div className="modal-actions">
                                <button
                                    type="button"
                                    className="btn-cancel"
                                    onClick={() => setShowModal(false)}
                                >
                                    Batal
                                </button>
                                <button type="submit" className="btn-submit">
                                    {isEdit ? "Perbarui" : "Tambah"}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
            {toast && (
                <div className={`toast-container ${visible ? "show" : "hide"}`}>
                    <div className="toast-box">
                        <span className="toast-text">
                            {toast.type === "error" ? (
                                toast.message
                            ) : (
                                <>
                                    Berhasil{" "}
                                    <span className={`highlight ${toast.type}`}>
                                        {toast.action}
                                    </span>{" "}
                                    Tarif
                                </>
                            )}
                        </span>
                    </div>
                </div>
            )}
            {confirmDeleteId && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>Konfirmasi Hapus</h3>
                        <p>Yakin ingin menghapus Tarif ini?</p>
                        <div className="modal-actions">
                            <button
                                className="toast-btn-cancel"
                                onClick={() => setConfirmDeleteId(null)}
                            >
                                Batal
                            </button>
                            <button
                                className="toast-btn-delete"
                                onClick={confirmDeleteAction}
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
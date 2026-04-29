import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterKegiatan.css";

export default function MasterKegiatan() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null);
    const [parentList, setParentList] = useState([]);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_KEGIATAN",
        direction: "desc"
    });
    const [form, setForm] = useState({
        MST_ID_KEGIATAN: "",
        DESKRIPSI_KEGIATAN: ""
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/kegiatan?search=${keyword}`
                : "http://localhost:8000/api/kegiatan";

            const res = await fetch(url);
            const json = await res.json();
            setData(json.data || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
        fetchParent();
    }, []);

    const fetchParent = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/kegiatan");
            const json = await res.json();
            setParentList(json.data || []);
        } catch (err) {
            console.error(err);
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

    const sortedData = [...data].sort((a, b) => {
        let valA = a[sortConfig.key] || "";
        let valB = b[sortConfig.key] || "";
        if (sortConfig.key === "ID_KEGIATAN" || sortConfig.key === "MST_ID_KEGIATAN") {
            valA = Number(valA);
            valB = Number(valB);
        } else {
            valA = valA.toString().toLowerCase();
            valB = valB.toString().toLowerCase();
        }
        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const totalData = data.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_KEGIATAN);
        setForm({
            MST_ID_KEGIATAN: item.MST_ID_KEGIATAN || "",
            DESKRIPSI_KEGIATAN: item.DESKRIPSI_KEGIATAN || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/kegiatan/update/${editId}`
            : "http://localhost:8000/api/kegiatan/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            closeModal();
            showToast(isEdit ? "update" : "add");
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
        setForm({ MST_ID_KEGIATAN: "", DESKRIPSI_KEGIATAN: "" });
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
            const res = await fetch(`http://localhost:8000/api/kegiatan/delete/${confirmDeleteId}`, {
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
        <div className="kegiatan-container">
            <div className="kegiatan-header">
                <h2>Master Kegiatan</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari kegiatan..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
                            className="search-input"
                        />
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                    <button className="btn-primary" onClick={() => setShowModal(true)}>
                        Tambah Kegiatan
                    </button>
                </div>
            </div>
            <div className="kegiatan-table-wrapper">
                <table className="kegiatan-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_KEGIATAN")}>
                                ID <i className={getIcon("ID_KEGIATAN")}></i>
                            </th>
                            <th onClick={() => handleSort("MST_ID_KEGIATAN")}>
                                MST ID <i className={getIcon("MST_ID_KEGIATAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_KEGIATAN")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_KEGIATAN")}></i>
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr><td colSpan="4" className="text-center">Loading...</td></tr>
                        ) : currentData.length === 0 ? (
                            <tr><td colSpan="4" className="text-center">Tidak ada data</td></tr>
                        ) : (
                            currentData.map((item) => (
                                <tr key={item.ID_KEGIATAN}>
                                    <td>{item.ID_KEGIATAN}</td>
                                    <td>{item.MST_ID_KEGIATAN}</td>
                                    <td>{item.DESKRIPSI_KEGIATAN}</td>
                                    <td className="aksi">
                                        <button
                                            className="btn-edit"
                                            onClick={() => handleEdit(item)}
                                        >
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used > 0 || item.has_child > 0}
                                            title={
                                                item.is_used > 0
                                                    ? "Tidak bisa dihapus karena sudah dipakai program kerja"
                                                    : item.has_child > 0
                                                    ? "Tidak bisa dihapus karena punya sub kegiatan"
                                                    : ""
                                            }
                                            onClick={() => setConfirmDeleteId(item.ID_KEGIATAN)}
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
                    <a href={`http://localhost:8000/api/kegiatan/export/excel?search=${search}`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    {/* <a href={`http://localhost:8000/api/kegiatan/export/csv?search=${search}`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-csv"></i> Export CSV
                    </a> */}
                    <a href={`http://localhost:8000/api/kegiatan/export/pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>{isEdit ? "Edit Kegiatan" : "Tambah Kegiatan"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Kegiatan (opsional)</label>
                            <select
                                name="MST_ID_KEGIATAN"
                                value={form.MST_ID_KEGIATAN}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Parent --</option>
                                {parentList
                                    .filter(item => item.ID_KEGIATAN !== editId)
                                    .map((item) => (
                                        <option key={item.ID_KEGIATAN} value={item.ID_KEGIATAN}>
                                            {item.ID_KEGIATAN} - {item.DESKRIPSI_KEGIATAN}
                                        </option>
                                    ))}
                            </select>
                            <label>Deskripsi Kegiatan</label>
                            <input type="text" name="DESKRIPSI_KEGIATAN" value={form.DESKRIPSI_KEGIATAN} onChange={handleChange} required />
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={closeModal}>Batal</button>
                                <button type="submit" className="btn-submit">{isEdit ? "Perbarui" : "Tambah"}</button>
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
                                    Kegiatan
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
                        <p>Yakin ingin menghapus kegiatan ini?</p>
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
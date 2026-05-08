import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterJenisTarif.css";

export default function MasterJenisTarif() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_JENIS_TARIF",
        direction: "desc"
    });
    const [form, setForm] = useState({
        DESKRIPSI_JENIS_TARIF: ""
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/jenis-tarif?search=${keyword}`
                : "http://localhost:8000/api/jenis-tarif";

            const res = await fetch(url);
            const json = await res.json();
            setData(json.data || json || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

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
            item.ID_JENIS_TARIF?.toString().includes(keyword) ||
            item.DESKRIPSI_JENIS_TARIF?.toLowerCase().includes(keyword)
        );
    });

    const sortedData = [...filteredData].sort((a, b) => {
        let valA = a[sortConfig.key] || "";
        let valB = b[sortConfig.key] || "";
        if (sortConfig.key === "ID_JENIS_TARIF") {
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

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_JENIS_TARIF);
        setForm({
            DESKRIPSI_JENIS_TARIF: item.DESKRIPSI_JENIS_TARIF || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/jenis-tarif/update/${editId}`
            : "http://localhost:8000/api/jenis-tarif/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            showToast(isEdit ? "update" : "add");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({ DESKRIPSI_JENIS_TARIF: "" });
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
            DESKRIPSI_JENIS_TARIF: ""
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
            const res = await fetch(`http://localhost:8000/api/jenis-tarif/delete/${confirmDeleteId}`, {
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
        <div className="jenis-tarif-container">
            <div className="jenis-tarif-header">
                <h2>Master Jenis Tarif</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari jenis tarif..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
                            className="search-input"
                        />
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                    <button
                        className="btn-primary"
                        onClick={() => {
                            setIsEdit(false);
                            setEditId(null);
                            setForm({
                                DESKRIPSI_JENIS_TARIF: ""
                            });
                            setShowModal(true);
                        }}
                    >
                        Tambah Jenis Tarif
                    </button>
                </div>
            </div>
            <div className="jenis-tarif-table-wrapper">
                <table className="jenis-tarif-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_JENIS_TARIF")}>
                                ID <i className={getIcon("ID_JENIS_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_JENIS_TARIF")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_JENIS_TARIF")}></i>
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
                                <tr key={item.ID_JENIS_TARIF}>
                                    <td>{item.ID_JENIS_TARIF}</td>
                                    <td>{item.DESKRIPSI_JENIS_TARIF}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Jenis Tarif sudah digunakan"
                                                    : "Hapus Jenis Tarif"
                                            }
                                            onClick={() => handleDelete(item.ID_JENIS_TARIF)}
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
                    <a href={`http://localhost:8000/api/export/jenis-tarif`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    <a href={`http://localhost:8000/api/export/jenis-tarif/export-pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                       <h3>{isEdit ? "Edit Jenis Tarif" : "Tambah Jenis Tarif"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Deskripsi Jenis Tarif</label>
                            <input
                                type="text"
                                name="DESKRIPSI_JENIS_TARIF"
                                value={form.DESKRIPSI_JENIS_TARIF}
                                onChange={handleChange}
                                placeholder="Masukkan deskripsi"
                            />
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
                                    Jenis Tarif
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
                        <p>Yakin ingin menghapus jenis tarif ini?</p>
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
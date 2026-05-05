import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterTahunAnggaran.css";

export default function MasterTahunAnggaran() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_TA_ANGGARAN", 
        direction: "desc" 
    });

    const [form, setForm] = useState({
        DESKRIPSI_TAHUN_ANGGARAN: "",
        IS_CURRENT: 0
    });

    const fetchData = async () => {
        setLoading(true);
        try {
            const res = await fetch("http://localhost:8000/api/tahun-anggaran");
            const json = await res.json();
            setData(json);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

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

    const sortedData = [...data]
        .filter((item) =>
            item.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase().includes(search.toLowerCase())
        )
        .sort((a, b) => {
            let valA = a[sortConfig.key];
            let valB = b[sortConfig.key];
            if (typeof valA === 'number' && typeof valB === 'number') {
                return sortConfig.direction === "asc" ? valA - valB : valB - valA;
            }
            valA = (valA || "").toString().toLowerCase();
            valB = (valB || "").toString().toLowerCase();
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
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };

    const changePage = (page) => {
        setCurrentPage(page);
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_TA_ANGGARAN);
        setForm({
            DESKRIPSI_TAHUN_ANGGARAN: item.DESKRIPSI_TAHUN_ANGGARAN || "",
            IS_CURRENT: item.IS_CURRENT ?? 0
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/tahun-anggaran/update/${editId}`
            : "http://localhost:8000/api/tahun-anggaran/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ...form,
                IS_CURRENT: form.IS_CURRENT
            })
        });
        const json = await res.json();
        if (res.ok) {
            closeModal();
            showToast(isEdit ? "update" : "add");
            fetchData();
        } else {
            showToast(
                "error",
                json.message || 
                (json.errors ? Object.values(json.errors).flat().join(", ") : "Gagal")
            );
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
            DESKRIPSI_TAHUN_ANGGARAN: "",
            IS_CURRENT: 0
        });
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
            const res = await fetch(`http://localhost:8000/api/tahun-anggaran/delete/${confirmDeleteId}`, {
                method: "DELETE"
            });
            const json = await res.json();
            if (res.ok) {
                showToast("delete");
                fetchData();
            } else {
                showToast(
                    "error",
                    json.message || "Gagal menghapus data"
                );
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Terjadi error");
        } finally {
            setConfirmDeleteId(null);
        }
    };

    return (
        <div className="tahun-anggaran-container">
            <div className="tahun-anggaran-header">
                <h2>Master Tahun Anggaran</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input 
                            type="text" 
                            placeholder="Cari deskripsi..." 
                            className="search-input"
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setCurrentPage(1);
                            }}
                        />
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                    <button className="btn-primary" onClick={() => setShowModal(true)}>
                        Tambah Tahun Anggaran
                    </button>
                </div>
            </div>
            <div className="tahun-anggaran-table-wrapper">
                <table className="tahun-anggaran-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_TA_ANGGARAN")}>
                                ID <i className={getIcon("ID_TA_ANGGARAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_TAHUN_ANGGARAN")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_TAHUN_ANGGARAN")}></i>
                            </th>
                            <th onClick={() => handleSort("IS_CURRENT")}>
                                Status <i className={getIcon("IS_CURRENT")}></i>
                            </th>
                            <th className="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan="4" className="text-center">Loading...</td>
                            </tr>
                        ) : currentData.length === 0 ? (
                            <tr>
                                <td colSpan="4" className="text-center">Data tidak ditemukan</td>
                            </tr>
                        ) : (
                            currentData.map((item) => (
                                <tr key={item.ID_TA_ANGGARAN}>
                                    <td>{item.ID_TA_ANGGARAN}</td>
                                    <td>{item.DESKRIPSI_TAHUN_ANGGARAN}</td>
                                    <td>
                                        <span className={item.IS_CURRENT == 1 ? "status aktif" : "status nonaktif"}>
                                            {item.IS_CURRENT == 1 ? "Aktif" : "Tidak Aktif"}
                                        </span>
                                    </td>
                                    <td className="aksi">
                                        <button
                                            className="btn-edit"
                                            onClick={() => handleEdit(item)}
                                        >
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Tidak bisa dihapus karena sudah dipakai Program Kerja"
                                                    : item.IS_CURRENT == 1
                                                    ? "Tidak bisa dihapus karena sedang aktif"
                                                    : ""
                                            }
                                            onClick={() => setConfirmDeleteId(item.ID_TA_ANGGARAN)}
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
                <div></div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>{isEdit ? "Edit Tahun Anggaran" : "Tambah Tahun Anggaran"}</h3>
                        <form onSubmit={handleSubmit} className="form-container">
                            <div className="form-group">
                                <label>Deskripsi Tahun Anggaran</label>
                                <input
                                    type="text"
                                    name="DESKRIPSI_TAHUN_ANGGARAN"
                                    value={form.DESKRIPSI_TAHUN_ANGGARAN}
                                    onChange={handleChange}
                                    placeholder="Contoh: 2025"
                                />
                            </div>
                            <div className="checkbox-wrapper">
                                <div className="checkbox-inline">
                                    <label>Status</label>
                                    <input
                                        type="checkbox"
                                        checked={form.IS_CURRENT == 1}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                IS_CURRENT: e.target.checked ? 1 : 0
                                            })
                                        }
                                    />
                                    <span className={form.IS_CURRENT == 1 ? "text-aktif" : "text-nonaktif"}>
                                        {form.IS_CURRENT == 1 ? "Tahun Aktif" : "Tahun Tidak Aktif"}
                                    </span>
                                </div>
                            </div>
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={closeModal}>
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
                                    Tahun Anggaran
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
                        <p>Yakin ingin menghapus Tahun Anggaran?</p>
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
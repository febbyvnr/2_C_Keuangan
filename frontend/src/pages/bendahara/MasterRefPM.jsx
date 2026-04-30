import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterRefPM.css";

export default function MasterRefPM() {
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
        key: "ID_REF_PM", 
        direction: "desc" 
    });
    const [form, setForm] = useState({
        REF_ID_REF_PM: "",
        NAMA_PM: "",
        DESKRIPSI_PM: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-pm");
            const json = await res.json();
            const result = json.data || []; 
            setData(result);
            setParentList(result);
        } catch (err) {
            console.error(err);
        }
    };

    const fetchParent = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-pm");
            const json = await res.json();
            setParentList(json.data || []);
        } catch (err) {
            console.error(err);
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

    useEffect(() => {
        const initData = async () => {
            setLoading(true);
            try {
                const res = await fetch("http://localhost:8000/api/ref-pm");
                const json = await res.json();
                const result = json.data || [];
                setData(result);
                setParentList(result);
            } catch (err) {
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        initData();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_PM);
        setForm({
            REF_ID_REF_PM: item.REF_ID_REF_PM ? String(item.REF_ID_REF_PM) : "",
            NAMA_PM: item.NAMA_PM || "",
            DESKRIPSI_PM: item.DESKRIPSI_PM || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-pm/update/${editId}`
            : "http://localhost:8000/api/ref-pm/store";
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
            showToast(
                "error",
                json.message ||
                json.error ||
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
            REF_ID_REF_PM: "",
            NAMA_PM: "",
            DESKRIPSI_PM: ""
        });
    };

    const sortedData = [...data]
        .filter((item) =>
            (item.DESKRIPSI_PM || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.REF_ID_REF_PM + "").includes(search) ||
            (item.NAMA_PM || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.ID_REF_PM + "").includes(search)
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
    const totalPages = Math.ceil(sortedData.length / itemsPerPage);
    const totalData = sortedData.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);
    const [confirmDeleteId, setConfirmDeleteId] = useState(null);

    const showToast = (type = "success", message = "") => {
        setVisible(false);
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
            const res = await fetch(
                `http://localhost:8000/api/ref-pm/delete/${confirmDeleteId}`,
                { method: "DELETE" }
            );
            const json = await res.json();
            if (res.ok) {
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
        <div className="ref-pm-container">
            <div className="ref-pm-header">
                <h2>Master Referensi PM</h2>
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
                        <button className="search-btn" onClick={() => { setCurrentPage(1); }}>
                            Search
                        </button>
                    </div>
                    <button className="btn-primary" onClick={() => {
                        setIsEdit(false);
                        setEditId(null);
                        setForm({ REF_ID_REF_PM: "", DESKRIPSI_PM: "" });
                        setShowModal(true);
                    }}>
                        Tambah Referensi PM
                    </button>
                </div>
            </div>
            <div className="ref-pm-table-wrapper">
                <table className="ref-pm-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_REF_PM")}>
                                ID <i className={getIcon("ID_REF_PM")}></i>
                            </th>
                            <th onClick={() => handleSort("REF_ID_REF_PM")}>
                                REF ID <i className={getIcon("REF_ID_REF_PM")}></i>
                            </th>
                            <th onClick={() => handleSort("NAMA_PM")}>
                                Nama PM <i className={getIcon("NAMA_PM")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_PM")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_PM")}></i>
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
                                <tr key={item.ID_REF_PM}>
                                    <td>{item.ID_REF_PM}</td>
                                    <td>{item.REF_ID_REF_PM}</td>
                                    <td>{item.NAMA_PM}</td>
                                    <td>{item.DESKRIPSI_PM}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used} 
                                            title={
                                                item.is_used
                                                    ? "Data sudah digunakan di Transaksi PM atau memiliki child"
                                                    : "Hapus Referensi PM"
                                            }
                                            onClick={() => handleDelete(item.ID_REF_PM)}
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
                       <h3>{isEdit ? "Edit Referensi PM" : "Tambah Referensi PM"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Referensi PM (opsional)</label>
                            <select
                                name="REF_ID_REF_PM"
                                value={form.REF_ID_REF_PM || ""}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Parent --</option>
                                {parentList
                                    .filter(item => String(item.ID_REF_PM) !== String(editId)) // Gabisa ref diri sendiri
                                    .sort((a, b) => b.ID_REF_PM - a.ID_REF_PM) // Urutan ID descending
                                    .map((item) => (
                                        <option key={item.ID_REF_PM} value={String(item.ID_REF_PM)}>
                                            {item.ID_REF_PM} - {item.NAMA_PM}
                                        </option>
                                    ))
                                }
                            </select>
                            <label>Nama Referensi PM</label>
                            <input
                                type="text"
                                name="NAMA_PM"
                                value={form.NAMA_PM}
                                onChange={handleChange}
                                placeholder="Masukkan nama PM"
                            />
                            <label>Deskripsi Referensi PM (opsional)</label>
                            <input
                                type="text"
                                name="DESKRIPSI_PM"
                                value={form.DESKRIPSI_PM}
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
                <div className={`toast-container ${visible ? "show" : "hide"} ${toast.type === "error" ? "error" : ""}`}>
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
                                    Referensi Penjaminan Mutu
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
                        <p>Yakin ingin menghapus Referensi Penjaminan Mutu?</p>
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
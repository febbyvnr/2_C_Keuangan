import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterRefPenerimaan.css";

export default function MasterRefPenerimaan() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_REF_PENERIMAAN", 
        direction: "asc" 
    });
    const [form, setForm] = useState({
        REF_ID_REF_PENERIMAAN: "",
        DESKRIPSI_REF_PENERIMAAN: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-penerimaan");
            const json = await res.json();
            setData(json.data || []);
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
        fetchData();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_PENERIMAAN);
        setForm({
            REF_ID_REF_PENERIMAAN: item.REF_ID_REF_PENERIMAAN || "",
            DESKRIPSI_REF_PENERIMAAN: item.DESKRIPSI_REF_PENERIMAAN || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-penerimaan/update/${editId}`
            : "http://localhost:8000/api/ref-penerimaan/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            alert(isEdit ? "Berhasil update Referensi Penerimaan" : "Berhasil tambah Referensi Penerimaan");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({
                REF_ID_REF_PENERIMAAN: "",
                DESKRIPSI_REF_PENERIMAAN: ""
            });
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus Referensi Penerimaan ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/ref-penerimaan/delete/${id}`,
                { method: "DELETE" }
            );
            const json = await res.json();
            if (json.success) {
                alert("Berhasil hapus data");
                fetchData();
            } else {
                alert(json.message || "Gagal hapus data");
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi error saat menghapus");
        }
    };

    const closeModal = () => {
        setShowModal(false);
        setIsEdit(false);
        setEditId(null);
        setForm({
            REF_ID_REF_PENERIMAAN: "",
            DESKRIPSI_REF_PENERIMAAN: ""
        });
    };

    const sortedData = [...data]
        .filter((item) =>
            (item.DESKRIPSI_REF_PENERIMAAN || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.REF_ID_REF_PENERIMAAN + "").includes(search) ||
            (item.ID_REF_PENERIMAAN + "").includes(search)
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

    return (
        <div className="ref-penerimaan-container">
            <div className="ref-penerimaan-header">
                <h2>Master Referensi Penerimaan</h2>
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
                        Tambah Referensi Penerimaan
                    </button>
                </div>
            </div>
            <div className="ref-penerimaan-table-wrapper">
                <table className="ref-penerimaan-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_REF_PENERIMAAN")}>
                                ID <i className={getIcon("ID_REF_PENERIMAAN")}></i>
                            </th>
                            <th onClick={() => handleSort("REF_ID_REF_PENERIMAAN")}>
                                REF ID <i className={getIcon("REF_ID_REF_PENERIMAAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_REF_PENERIMAAN")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_REF_PENERIMAAN")}></i>
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
                                <tr key={item.ID_REF_PENERIMAAN}>
                                    <td>{item.ID_REF_PENERIMAAN}</td>
                                    <td>{item.REF_ID_REF_PENERIMAAN}</td>
                                    <td>{item.DESKRIPSI_REF_PENERIMAAN}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Referensi Penerimaan sudah digunakan Program Kerja"
                                                    : "Hapus Referensi Penerimaan"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_REF_PENERIMAAN)
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
                    Showing {startData} - {endData} of {totalData} data
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
                       <h3>{isEdit ? "Edit Referensi Penerimaan" : "Tambah Referensi Penerimaan"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Referensi Penerimaan (opsional)</label>
                            <input
                                type="number"
                                name="REF_ID_REF_PENERIMAAN"
                                value={form.REF_ID_REF_PENERIMAAN}
                                onChange={handleChange}
                                placeholder="ID Parent Referensi Penerimaan"
                            />
                            <label>Deskripsi Referensi Penerimaan</label>
                            <input
                                type="text"
                                name="DESKRIPSI_REF_PENERIMAAN"
                                value={form.DESKRIPSI_REF_PENERIMAAN}
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
        </div>
    );
}
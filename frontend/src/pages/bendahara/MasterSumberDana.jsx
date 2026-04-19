import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterSumberDana.css";

export default function MasterSumberDana() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_REF_DANA", 
        direction: "asc" 
    });
    const [form, setForm] = useState({
        REF_ID_REF_DANA: "",
        DESKRIPSI_SUMBER_DANA: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-sumber-dana");
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
            item.DESKRIPSI_SUMBER_DANA?.toLowerCase().includes(search.toLowerCase()) || 
            (item.REF_ID_REF_DANA + "").includes(search) ||
            (item.ID_REF_DANA + "").includes(search)
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

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_DANA);
        setForm({
            REF_ID_REF_DANA: item.REF_ID_REF_DANA || "",
            DESKRIPSI_SUMBER_DANA: item.DESKRIPSI_SUMBER_DANA || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-sumber-dana/update/${editId}`
            : "http://localhost:8000/api/ref-sumber-dana/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ...form,
            })
        });
        const json = await res.json();
        if (res.ok) {
            alert(isEdit ? "Berhasil update Sumber Dana" : "Berhasil tambah Sumber Dana");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({
                REF_ID_REF_DANA: "",
                DESKRIPSI_SUMBER_DANA: ""
            });
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus ref-sumber-dana ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/ref-sumber-dana/delete/${id}`,
                { method: "DELETE" }
            );
            const json = await res.json();
            if (res.ok) {
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
            REF_ID_REF_DANA: "",
            DESKRIPSI_SUMBER_DANA: ""
        });
    };

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
        <div className="sumber-dana-container">
            <div className="sumber-dana-header">
                <h2>Master Sumber Dana</h2>
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
                        Tambah Sumber Dana
                    </button>
                </div>
            </div>
            <div className="sumber-dana-table-wrapper">
                <table className="sumber-dana-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_REF_DANA")}>
                                ID <i className={getIcon("ID_REF_DANA")}></i>
                            </th>
                            <th onClick={() => handleSort("REF_ID_REF_DANA")}>
                                REF ID <i className={getIcon("REF_ID_REF_DANA")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_SUMBER_DANA")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_SUMBER_DANA")}></i>
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
                                <tr key={item.ID_REF_DANA}>
                                    <td>{item.ID_REF_DANA}</td>
                                    <td>{item.REF_ID_REF_DANA}</td>
                                    <td>{item.DESKRIPSI_SUMBER_DANA}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Sumber Dana sudah digunakan Program Kerja"
                                                    : "Sumber Dana Anggaran"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_REF_DANA)
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
                        <h3>{isEdit ? "Edit Sumber Dana" : "Tambah Sumber Dana"}</h3>
                        <form onSubmit={handleSubmit} className="form-container">
                            <div className="form-group">
                                <label>Referensi ID Sumber Dana</label>
                                <input
                                    type="text"
                                    name="REF_ID_REF_DANA"
                                    value={form.REF_ID_REF_DANA}
                                    onChange={handleChange}
                                    required
                                    placeholder="Contoh: 1"
                                />
                            </div>
                            <div className="form-group">
                                <label>Deskripsi Sumber Dana</label>
                                <input
                                    type="text"
                                    name="DESKRIPSI_SUMBER_DANA"
                                    value={form.DESKRIPSI_SUMBER_DANA}
                                    onChange={handleChange}
                                    required
                                    placeholder="Contoh: Dana Bantuan Pemerintah"
                                />
                            </div>
                            <div className="modal-actions">
                                <button
                                    type="button"
                                    className="btn-cancel"
                                    onClick={closeModal}
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
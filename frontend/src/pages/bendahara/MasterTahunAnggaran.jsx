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
        direction: "asc" 
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
                IS_CURRENT: form.IS_CURRENT === 1
            })
        });
        if (res.ok) {
            alert(isEdit ? "Berhasil update Tahun Anggaran" : "Berhasil tambah Tahun Anggaran");
            closeModal();
            fetchData();
        }
    };

    const handleDelete = async (id) => {
        if (!confirm("Yakin mau hapus tahun-anggaran ini?")) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/tahun-anggaran/delete/${id}`,
                { method: "DELETE" }
            );
            if (res.ok) {
                alert("Berhasil hapus data");
                fetchData();
            }
        } catch (err) {
            console.error(err);
        }
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
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            onClick={() => handleDelete(item.ID_TA_ANGGARAN)}
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
                        <h3>{isEdit ? "Edit Tahun Anggaran" : "Tambah Tahun Anggaran"}</h3>
                        <form onSubmit={handleSubmit} className="form-container">
                            <div className="form-group">
                                <label>Deskripsi Tahun Anggaran</label>
                                <input
                                    type="text"
                                    name="DESKRIPSI_TAHUN_ANGGARAN"
                                    value={form.DESKRIPSI_TAHUN_ANGGARAN}
                                    onChange={handleChange}
                                    required
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
        </div>
    );
}
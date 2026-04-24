import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterJenisPembayaran.css";

export default function MasterJenisPembayaran() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_JENIS_PEMBAYARAN",
        direction: "asc"
    });
    const [form, setForm] = useState({
        DESKRIPSI_JENIS_PEMBAYARAN: ""
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/jenis-pembayaran?search=${keyword}`
                : "http://localhost:8000/api/jenis-pembayaran";

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
            item.ID_JENIS_PEMBAYARAN?.toString().includes(keyword) ||
            item.DESKRIPSI_JENIS_PEMBAYARAN?.toLowerCase().includes(keyword)
        );
    });

    const sortedData = [...filteredData].sort((a, b) => {
        let valA = a[sortConfig.key] || "";
        let valB = b[sortConfig.key] || "";
        if (sortConfig.key === "ID_JENIS_PEMBAYARAN") {
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
        setEditId(item.ID_JENIS_PEMBAYARAN);
        setForm({
            DESKRIPSI_JENIS_PEMBAYARAN: item.DESKRIPSI_JENIS_PEMBAYARAN || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/jenis-pembayaran/update/${editId}`
            : "http://localhost:8000/api/jenis-pembayaran/store";
        const method = isEdit ? "PUT" : "POST";
        try {
            const res = await fetch(url, {
                method,
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(form)
            });
            const json = await res.json();
            if (!res.ok) {
                console.log(json);
                alert(json.message || "Validasi gagal");
                return;
            }
            if (json.success) {
                alert(isEdit ? "Berhasil update Jenis Pembayaran" : "Berhasil tambah Jenis Pembayaran");
                closeModal();
                fetchData();
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi error");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus Jenis Pembayaran ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/jenis-pembayaran/delete/${id}`,
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
            DESKRIPSI_JENIS_PEMBAYARAN: ""
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

    return (
        <div className="jenis-pembayaran-container">
            <div className="jenis-pembayaran-header">
                <h2>Master Jenis Pembayaran</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari jenis pembayaran..."
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
                                DESKRIPSI_JENIS_PEMBAYARAN: ""
                            });
                            setShowModal(true);
                        }}
                    >
                        Tambah Jenis Pembayaran
                    </button>
                </div>
            </div>
            <div className="jenis-pembayaran-table-wrapper">
                <table className="jenis-pembayaran-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_JENIS_PEMBAYARAN")}>
                                ID <i className={getIcon("ID_JENIS_PEMBAYARAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_JENIS_PEMBAYARAN")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_JENIS_PEMBAYARAN")}></i>
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
                                <tr key={item.ID_JENIS_PEMBAYARAN}>
                                    <td>{item.ID_JENIS_PEMBAYARAN}</td>
                                    <td>{item.DESKRIPSI_JENIS_PEMBAYARAN}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Jenis Pembayaran sudah digunakan Program Kerja"
                                                    : "Hapus Jenis Pembayaran"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_JENIS_PEMBAYARAN)
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
                    <a href={`http://localhost:8000/api/jenis-pembayaran/export`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    {/* <a href={`http://localhost:8000/api/jenis-pembayaran/export/pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a> */}
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                       <h3>{isEdit ? "Edit Jenis Pembayaran" : "Tambah Jenis Pembayaran"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Deskripsi Jenis Pembayaran</label>
                            <input
                                type="text"
                                name="DESKRIPSI_JENIS_PEMBAYARAN"
                                value={form.DESKRIPSI_JENIS_PEMBAYARAN}
                                onChange={handleChange}
                                placeholder="Masukkan deskripsi"
                            />
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
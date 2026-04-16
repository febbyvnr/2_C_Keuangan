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
    const [form, setForm] = useState({
        MST_ID_KEGIATAN: "",
        DESKRIPSI_KEGIATAN: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/kegiatan");
            const json = await res.json();
            setData(json.data || []);
        } catch (err) {
            console.error(err);
        }
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
            alert(isEdit ? "Berhasil update Kegiatan" : "Berhasil tambah Kegiatan");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({
                MST_ID_KEGIATAN: "",
                DESKRIPSI_KEGIATAN: ""
            });
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus Kegiatan ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/kegiatan/delete/${id}`,
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
            MST_ID_KEGIATAN: "",
            DESKRIPSI_KEGIATAN: ""
        });
    };

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = data.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="coa-container">
            <div className="coa-header">
                <h2>Master Kegiatan</h2>
                <button className="btn-primary" onClick={() => setShowModal(true)}>
                    Tambah Kegiatan
                </button>
            </div>
            <div className="coa-table-wrapper">
                <table className="coa-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>MST ID</th>
                            <th>Deskripsi</th>
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
                                <tr key={item.ID_KEGIATAN}>
                                    <td>{item.ID_KEGIATAN}</td>
                                    <td>{item.MST_ID_KEGIATAN}</td>
                                    <td>{item.DESKRIPSI_KEGIATAN}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "COA sudah digunakan Program Kerja"
                                                    : "Hapus COA"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_KEGIATAN)
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
            <div className="pagination">
                <button
                    className="page-btn"
                    onClick={() =>
                        setCurrentPage((prev) => Math.max(prev - 1, 1))
                    }
                    disabled={currentPage === 1}
                >
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
                <button
                    className="page-btn"
                    onClick={() =>
                        setCurrentPage((prev) =>
                            Math.min(prev + 1, totalPages)
                        )
                    }
                    disabled={currentPage === totalPages}
                >
                    <i className="bi bi-chevron-right"></i>
                </button>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                       <h3>{isEdit ? "Edit Kegiatan" : "Tambah Kegiatan"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Kegiatan (opsional)</label>
                            <input
                                type="number"
                                name="MST_ID_KEGIATAN"
                                value={form.MST_ID_KEGIATAN}
                                onChange={handleChange}
                                placeholder="ID Parent Kegiatan"
                            />
                            <label>Deskripsi Kegiatan</label>
                            <input
                                type="text"
                                name="DESKRIPSI_KEGIATAN"
                                value={form.DESKRIPSI_KEGIATAN}
                                onChange={handleChange}
                                required
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
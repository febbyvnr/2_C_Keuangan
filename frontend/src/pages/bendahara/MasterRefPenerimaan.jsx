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

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = data.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="ref-penerimaan-container">
            <div className="ref-penerimaan-header">
                <h2>Master Referensi Penerimaan</h2>
                <button className="btn-primary" onClick={() => setShowModal(true)}>
                    Tambah Referensi Penerimaan
                </button>
            </div>
            <div className="ref-penerimaan-table-wrapper">
                <table className="ref-penerimaan-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>REF ID</th>
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
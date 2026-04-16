import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterTahunAkademik.css";

export default function MasterTahunAkademik() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [form, setForm] = useState({
        TAHUN: "",
        IS_CURRENT: 0
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-tan");
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

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_TAN);
        setForm({
            TAHUN: item.TAHUN || "",
            DESKRIPSI: item.DESKRIPSI_TAN || "",
            IS_CURRENT: item.IS_CURRENT ?? 0
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-tan/update/${editId}`
            : "http://localhost:8000/api/ref-tan/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                TAHUN: form.TAHUN,
                DESKRIPSI_TAN: `${form.DESKRIPSI}`,
                IS_CURRENT: form.IS_CURRENT == 1
            })
        });
        const json = await res.json();
        if (!res.ok) {
            console.log(json);
            alert(
                json.errors
                    ? Object.values(json.errors).flat().join("\n")
                    : json.message || "Gagal"
            );
            return;
        }
        if (res.ok) {
            alert(isEdit ? "Berhasil update Tahun Akademik" : "Berhasil tambah Tahun Akademik");
            closeModal();
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus tahun-akademik ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/ref-tan/delete/${id}`,
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
            TAHUN: "",
            IS_CURRENT: 0
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
        <div className="tahun-akademik-container">
            <div className="tahun-akademik-header">
                <h2>Master Tahun Akademik</h2>
                <button className="btn-primary" onClick={() => setShowModal(true)}>
                    Tambah Tahun Akademik
                </button>
            </div>
            <div className="tahun-akademik-table-wrapper">
                <table className="tahun-akademik-table">
                    <thead>
                        <tr>
                            <th>ID TAN</th>
                            <th>Tahun</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
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
                                <tr key={item.ID_TAN}>
                                    <td>{item.ID_TAN}</td>
                                    <td>{item.TAHUN}</td>
                                    <td>{item.DESKRIPSI_TAN}</td>
                                    <td>
                                        <span
                                            className={
                                                item.IS_CURRENT == 1 ? "status aktif" : "status nonaktif"
                                            }
                                        >
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
                                            title={
                                                item.is_used
                                                    ? "Tahun Akademik sudah digunakan Program Kerja"
                                                    : "Hapus Tahun Akademik"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_TAN)
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
                        <h3>{isEdit ? "Edit Tahun Akademik" : "Tambah Tahun Akademik"}</h3>
                        <form onSubmit={handleSubmit} className="form-container">
                            <div className="form-group">
                                <label>Tahun Ajaran</label>
                                <input
                                    type="text"
                                    name="TAHUN"
                                    value={form.TAHUN}
                                    onChange={handleChange}
                                    required
                                    placeholder="Contoh: 2025"
                                />
                            </div>
                            <div className="form-group">
                                <label>Deskripsi</label>
                                <input
                                    type="text"
                                    name="DESKRIPSI"
                                    value={form.DESKRIPSI}
                                    onChange={handleChange}
                                    required
                                    placeholder="Contoh: Tan 2025"
                                />
                            </div>
                            <div className="form-group">
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
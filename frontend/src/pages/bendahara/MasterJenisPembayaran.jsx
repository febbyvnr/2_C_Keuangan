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
    const [form, setForm] = useState({
        DESKRIPSI_JENIS_PEMBAYARAN: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/jenis-pembayaran");
            const json = await res.json();
            setData(json.data || json || []);
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
    const currentData = data.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="jenis-pembayaran-container">
            <div className="jenis-pembayaran-header">
                <h2>Master Jenis Pembayaran</h2>
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
            <div className="jenis-pembayaran-table-wrapper">
                <table className="jenis-pembayaran-table">
                    <thead>
                        <tr>
                            <th>ID</th>
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
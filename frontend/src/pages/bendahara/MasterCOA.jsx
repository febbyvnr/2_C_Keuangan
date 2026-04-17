import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterCOA.css";

export default function MasterCOA() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [coaList, setCoaList] = useState([]);
    const [form, setForm] = useState({
        MST_ID_MASTER_COA: "",
        DESKRIPSI_COA: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/coa");
            const json = await res.json();
            setData(json.data || []);
            setCoaList(json.data || []);
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
        setEditId(item.ID_MASTER_COA);
        setForm({
            MST_ID_MASTER_COA: item.MST_ID_MASTER_COA || "",
            DESKRIPSI_COA: item.DESKRIPSI_COA || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/coa/update/${editId}`
            : "http://localhost:8000/api/coa/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            alert(isEdit ? "Berhasil update COA" : "Berhasil tambah COA");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({
                MST_ID_MASTER_COA: "",
                DESKRIPSI_COA: ""
            });
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus COA ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/coa/delete/${id}`,
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
            MST_ID_MASTER_COA: "",
            DESKRIPSI_COA: ""
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
                <h2>Master COA</h2>
                <button className="btn-primary" onClick={() => setShowModal(true)}>
                    Tambah COA
                </button>
            </div>
            <div className="coa-table-wrapper">
                <table className="coa-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>MST ID</th>
                            <th>Kode COA</th>
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
                                <tr key={item.ID_MASTER_COA}>
                                    <td>{item.ID_MASTER_COA}</td>
                                    <td>{item.MST_ID_MASTER_COA}</td>
                                    <td>{item.KODE_COA}</td>
                                    <td>{item.DESKRIPSI_COA}</td>
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
                                                handleDelete(item.ID_MASTER_COA)
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
                       <h3>{isEdit ? "Edit COA" : "Tambah COA"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent COA (opsional)</label>
                            <select
                                name="MST_ID_MASTER_COA"
                                value={form.MST_ID_MASTER_COA}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Parent --</option>
                                {coaList.map((coa) => (
                                    <option key={coa.ID_MASTER_COA} value={coa.ID_MASTER_COA}>
                                        {coa.KODE_COA} - {coa.DESKRIPSI_COA}
                                    </option>
                                ))}
                            </select>
                            <label>Deskripsi COA</label>
                            <input
                                type="text"
                                name="DESKRIPSI_COA"
                                value={form.DESKRIPSI_COA}
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
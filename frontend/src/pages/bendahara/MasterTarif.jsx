import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterTarif.css";

export default function MasterTarif() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [jenisTarifList, setJenisTarifList] = useState([]);
    const [tahunAnggaranList, setTahunAnggaranList] = useState([]);
    const [form, setForm] = useState({
        ID_JENIS_TARIF: "",
        ID_TA_ANGGARAN: "",
        DESKRIPSI_TARIF: "",
        NOMINAL: "",
        TGL_PENETAPAN: "",
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/tarif");
            const json = await res.json();
            setData(json.data || json || []);
        } catch (err) {
            console.error(err);
        }
    };

    const fetchDropdown = async () => {
        try {
            const [jenisRes, tahunRes] = await Promise.all([
                fetch("http://localhost:8000/api/jenis-tarif"),
                fetch("http://localhost:8000/api/tahun-anggaran")
            ]);
            const jenisJson = await jenisRes.json();
            const tahunJson = await tahunRes.json();
            setJenisTarifList(jenisJson.data || jenisJson || []);
            setTahunAnggaranList(tahunJson.data || tahunJson || []);
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchData();
        fetchDropdown();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_TARIF);
        setForm({
            ID_JENIS_TARIF: item.ID_JENIS_TARIF || "",
            ID_TA_ANGGARAN: item.ID_TA_ANGGARAN || "",
            DESKRIPSI_TARIF: item.DESKRIPSI_TARIF || "",
            NOMINAL: item.NOMINAL || "",
            TGL_PENETAPAN: item.TGL_PENETAPAN
                ? item.TGL_PENETAPAN.replace(" ", "T").slice(0, 16)
                : ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/tarif/update/${editId}`
            : "http://localhost:8000/api/tarif/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            alert(isEdit ? "Berhasil update Tarif" : "Berhasil tambah Tarif");
            setShowModal(false);
            setIsEdit(false);
            setEditId(null);
            setForm({
                ID_JENIS_TARIF: "",
                ID_TA_ANGGARAN: "",
                DESKRIPSI_TARIF: "",
                NOMINAL: "",
                TGL_PENETAPAN: "",
            });
            fetchData();
        } else {
            alert(json.message || "Gagal");
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = confirm("Yakin mau hapus Tarif ini?");
        if (!confirmDelete) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/tarif/delete/${id}`,
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
            ID_JENIS_TARIF: "",
            ID_TA_ANGGARAN: "",
            DESKRIPSI_TARIF: "",
            NOMINAL: "",
            TGL_PENETAPAN: "",
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
        <div className="tarif-container">
            <div className="tarif-header">
                <h2>Master Tarif</h2>
                <button className="btn-primary" 
                    onClick={() => {
                        setIsEdit(false);
                        setEditId(null);
                        setForm({
                            ID_JENIS_TARIF: "",
                            ID_TA_ANGGARAN: "",
                            DESKRIPSI_TARIF: "",
                            NOMINAL: "",
                            TGL_PENETAPAN: "",
                        });
                        setShowModal(true);
                    }}
                >
                    Tambah Tarif
                </button>
            </div>
            <div className="tarif-table-wrapper">
                <table className="tarif-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Jenis Tarif</th>
                            <th>ID TA Anggaran</th>
                            <th>Deskripsi</th>
                            <th>Nominal</th>
                            <th>Tanggal Penetapan</th>
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
                                <tr key={item.ID_REF_TARIF}>
                                    <td>{item.ID_REF_TARIF}</td>
                                    <td>
                                        {item.jenis_tarif?.DESKRIPSI_JENIS_TARIF || item.ID_JENIS_TARIF}
                                    </td>
                                    <td>
                                        {item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || item.ID_TA_ANGGARAN}
                                    </td>
                                    <td>{item.DESKRIPSI_TARIF}</td>
                                    <td>Rp {item.NOMINAL}</td>
                                    <td>{item.TGL_PENETAPAN}</td>
                                    <td className="aksi">
                                        <button className="btn-edit" onClick={() => handleEdit(item)}>
                                            <i className="bi bi-pencil"></i>
                                        </button>
                                        <button
                                            className="btn-delete"
                                            disabled={item.is_used}
                                            title={
                                                item.is_used
                                                    ? "Tarif sudah digunakan Program Kerja"
                                                    : "Hapus Tarif"
                                            }
                                            onClick={() =>
                                                handleDelete(item.ID_REF_TARIF)
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
                       <h3>{isEdit ? "Edit Tarif" : "Tambah Tarif"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>ID Jenis Tarif</label>
                            <select
                                name="ID_JENIS_TARIF"
                                value={form.ID_JENIS_TARIF}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Jenis Tarif --</option>
                                {jenisTarifList.map((item) => (
                                    <option key={item.ID_JENIS_TARIF} value={item.ID_JENIS_TARIF}>
                                        [{item.ID_JENIS_TARIF}] {item.DESKRIPSI_JENIS_TARIF}
                                    </option>
                                ))}
                            </select>
                            <label>ID TA Anggaran</label>
                            <select
                                name="ID_TA_ANGGARAN"
                                value={form.ID_TA_ANGGARAN}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Tahun Anggaran --</option>
                                {tahunAnggaranList.map((item) => (
                                    <option key={item.ID_TA_ANGGARAN} value={item.ID_TA_ANGGARAN}>
                                        [{item.ID_TA_ANGGARAN}] {item.TAHUN_ANGGARAN || item.DESKRIPSI_TAHUN_ANGGARAN}
                                    </option>
                                ))}
                            </select>
                            <label>Deskripsi Tarif</label>
                            <input
                                type="text"
                                name="DESKRIPSI_TARIF"
                                value={form.DESKRIPSI_TARIF}
                                onChange={handleChange}
                                placeholder="Masukkan deskripsi"
                            />
                            <label>Deskripsi Nominal</label>
                            <input
                                type="number"
                                name="NOMINAL"
                                value={form.NOMINAL}
                                onChange={handleChange}
                                placeholder="Masukkan nominal"
                            />
                            <label>Tanggal Penetapan</label>
                            <input
                                type="datetime-local"
                                name="TGL_PENETAPAN"
                                value={form.TGL_PENETAPAN}
                                onChange={handleChange}
                                placeholder="Masukkan tanggal penetapan"
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
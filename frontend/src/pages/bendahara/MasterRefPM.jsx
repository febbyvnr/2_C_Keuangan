import { useEffect, useState } from "react";
import "../../styles/bendahara/MasterRefPM.css";

export default function MasterRefPM() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [editId, setEditId] = useState(null); 
    const [parentList, setParentList] = useState([]);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_REF_PM", 
        direction: "asc" 
    });
    const [form, setForm] = useState({
        REF_ID_REF_PM: "",
        NAMA_PM: "",
        DESKRIPSI_PM: ""
    });

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-pm");
            const json = await res.json();
            const result = json.data || []; 
            setData(result);
            setParentList(result);
        } catch (err) {
            console.error(err);
        }
    };

    const fetchParent = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-pm");
            const json = await res.json();
            setParentList(json.data || []);
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
        const initData = async () => {
            setLoading(true);
            try {
                const res = await fetch("http://localhost:8000/api/ref-pm");
                const json = await res.json();
                const result = json.data || [];
                setData(result);
                setParentList(result);
            } catch (err) {
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        initData();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };
    
    const handleEdit = (item) => {
        setIsEdit(true);
        setEditId(item.ID_REF_PM);
        setForm({
            REF_ID_REF_PM: item.REF_ID_REF_PM ? String(item.REF_ID_REF_PM) : "",
            NAMA_PM: item.NAMA_PM || "",
            DESKRIPSI_PM: item.DESKRIPSI_PM || ""
        });
        setShowModal(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-pm/update/${editId}`
            : "http://localhost:8000/api/ref-pm/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success) {
            closeModal();
            showToast(isEdit ? "update" : "add");
            fetchData();
        } else {
            showToast(
                "error",
                json.message ||
                json.error ||
                (json.errors ? Object.values(json.errors).flat().join(", ") : "Gagal")
            );
        }
    };

    const handleDelete = (id) => {
        setConfirmDeleteId(id);
    };

    const closeModal = () => {
        setShowModal(false);
        setIsEdit(false);
        setEditId(null);
        setForm({
            REF_ID_REF_PM: "",
            NAMA_PM: "",
            DESKRIPSI_PM: ""
        });
    };

    const sortedData = [...data]
        .filter((item) =>
            (item.DESKRIPSI_PM || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.REF_ID_REF_PM + "").includes(search) ||
            (item.NAMA_PM || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.ID_REF_PM + "").includes(search)
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

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);
    const [confirmDeleteId, setConfirmDeleteId] = useState(null);

    const showToast = (type = "success", message = "") => {
        setVisible(false);
        let action = "";
        if (type === "add") action = "Menambahkan";
        if (type === "update") action = "Memperbarui";
        if (type === "delete") action = "Menghapus";
        if (type === "error") action = "Gagal";
        setToast({ type, action, message });
        setVisible(true);
        setTimeout(() => setVisible(false), 2500);
        setTimeout(() => setToast(null), 3000);
    };

    const confirmDeleteAction = async () => {
        try {
            const res = await fetch(
                `http://localhost:8000/api/ref-pm/delete/${confirmDeleteId}`,
                { method: "DELETE" }
            );
            const json = await res.json();
            if (res.ok) {
                showToast("delete");
                fetchData();
            } else {
                showToast("error", json.message || "Gagal menghapus data");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Terjadi error");
        } finally {
            setConfirmDeleteId(null);
        }
    };
    
    const buildTree = (nodes) => {
        const map = {};
        const roots = [];
        nodes.forEach(item => {
            map[item.ID_REF_PM] = { ...item, children: [] };
        });
        nodes.forEach(item => {
            const parentId = item.REF_ID_REF_PM;
            const isParent = !parentId || parentId === 0 || parentId === "0";
            if (!isParent) {
                if (map[parentId]) {
                    map[parentId].children.push(map[item.ID_REF_PM]);
                } else {
                    roots.push(map[item.ID_REF_PM]);
                }
            } else {
                roots.push(map[item.ID_REF_PM]);
            }
        });
        return roots;
    };

    const sortTree = (nodes) => {
        return nodes
            .sort((a, b) => {
                const valA = isNaN(a[sortConfig.key]) ? (a[sortConfig.key] || "").toString().toLowerCase() : Number(a[sortConfig.key]);
                const valB = isNaN(b[sortConfig.key]) ? (b[sortConfig.key] || "").toString().toLowerCase() : Number(b[sortConfig.key]);
                
                if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
                if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
                return 0;
            })
            .map(node => ({
                ...node,
                children: sortTree(node.children)
            }));
    };

    const addNumbering = (nodes, prefix = "") => {
        return nodes.map((node, index) => {
            const number = prefix ? `${prefix}.${index + 1}` : `${index + 1}`;
            const isLast = index === nodes.length - 1;
            return {
                ...node,
                number,
                isLast,
                children: addNumbering(node.children, number)
            };
        });
    };

    const flattenTree = (nodes, level = 0, parentLines = []) => {
        let result = [];
        nodes.forEach(node => {
            result.push({
                ...node,
                level,
                parentLines: level === 0 ? [] : parentLines 
            });
            if (node.children.length > 0) {
                result = result.concat(
                    flattenTree(
                        node.children,
                        level + 1,
                        level === 0 ? [] : [...parentLines, !node.isLast]
                    )
                );
            }
        });
        return result;
    };

    const tree = sortTree(buildTree(data));
    const numbered = addNumbering(tree);
    const allFlatRows = flattenTree(numbered); 
    const totalData = allFlatRows.length;
    const totalPages = Math.ceil(totalData / itemsPerPage);
    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentRows = allFlatRows.slice(indexOfFirst, indexOfLast);
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    return (
        <div className="ref-pm-container">
            <div className="ref-pm-header">
                <h2>Master Referensi PM</h2>
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
                        <button className="search-btn" onClick={() => { setCurrentPage(1); }}>
                            Search
                        </button>
                    </div>
                    <button className="btn-primary" onClick={() => {
                        setIsEdit(false);
                        setEditId(null);
                        setForm({ REF_ID_REF_PM: "", DESKRIPSI_PM: "" });
                        setShowModal(true);
                    }}>
                        Tambah Referensi PM
                    </button>
                </div>
            </div>
            <div className="ref-pm-table-wrapper">
                <div className="tree-list">
                    {loading ? (
                        <div className="text-center" style={{padding: "20px"}}>Loading...</div>
                    ) : currentRows.length === 0 ? (
                        <div className="text-center" style={{padding: "20px"}}>Tidak ada data</div>
                    ) : (
                        currentRows.map((item) => (
                            <div 
                                className={`tree-row ${item.level > 0 ? "child-row" : "parent-row"}`} 
                                key={item.ID_REF_PM}
                            >
                                <div className="tree-left" style={{ paddingLeft: "10px" }}>
                                    {item.parentLines.map((hasActiveLine, idx) => (
                                        <div 
                                            key={idx} 
                                            className={`tree-line ${hasActiveLine ? "active" : ""}`} 
                                        />
                                    ))}
                                    {item.level > 0 && (
                                        <div className={`tree-connector ${item.isLast ? "is-last" : ""}`}></div>
                                    )}
                                    <span className="tree-number">{item.number}</span>
                                    <span className="tree-text">{item.NAMA_PM} | {item.DESKRIPSI_PM}</span>
                                </div>
                                <div className="tree-actions">
                                    <button className="btn-edit" onClick={() => handleEdit(item)}>
                                        <i className="bi bi-pencil"></i>
                                    </button>
                                    <button
                                        className="btn-delete"
                                        disabled={item.is_used > 0 || (item.children && item.children.length > 0)}
                                        onClick={() => setConfirmDeleteId(item.ID_REF_PM)}
                                    >
                                        <i className="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        ))
                    )}
                </div>
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
                    <a href={`http://localhost:8000/api/ref-pm/export/excel?search=${search}`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    <a href={`http://localhost:8000/api/ref-pm/export/pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                       <h3>{isEdit ? "Edit Referensi PM" : "Tambah Referensi PM"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Referensi PM (opsional)</label>
                            <select
                                name="REF_ID_REF_PM"
                                value={form.REF_ID_REF_PM || ""}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Parent --</option>
                                {parentList
                                    .filter(item => String(item.ID_REF_PM) !== String(editId)) // Gabisa ref diri sendiri
                                    .sort((a, b) => b.ID_REF_PM - a.ID_REF_PM) // Urutan ID descending
                                    .map((item) => (
                                        <option key={item.ID_REF_PM} value={String(item.ID_REF_PM)}>
                                            {item.ID_REF_PM} - {item.NAMA_PM}
                                        </option>
                                    ))
                                }
                            </select>
                            <label>Nama Referensi PM</label>
                            <input
                                type="text"
                                name="NAMA_PM"
                                value={form.NAMA_PM}
                                onChange={handleChange}
                                placeholder="Masukkan nama PM"
                            />
                            <label>Deskripsi Referensi PM (opsional)</label>
                            <input
                                type="text"
                                name="DESKRIPSI_PM"
                                value={form.DESKRIPSI_PM}
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
            {toast && (
                <div className={`toast-container ${visible ? "show" : "hide"} ${toast.type === "error" ? "error" : ""}`}>
                    <div className="toast-box">
                        <span className="toast-text">
                            {toast.type === "error" ? (
                                toast.message
                            ) : (
                                <>
                                    Berhasil{" "}
                                    <span className={`highlight ${toast.type}`}>
                                        {toast.action}
                                    </span>{" "}
                                    Referensi Penjaminan Mutu
                                </>
                            )}
                        </span>
                    </div>
                </div>
            )}
            {confirmDeleteId && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>Konfirmasi Hapus</h3>
                        <p>Yakin ingin menghapus Referensi Penjaminan Mutu?</p>
                        <div className="modal-actions">
                            <button
                                className="toast-btn-cancel"
                                onClick={() => setConfirmDeleteId(null)}
                            >
                                Batal
                            </button>
                            <button
                                className="toast-btn-delete"
                                onClick={confirmDeleteAction}
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
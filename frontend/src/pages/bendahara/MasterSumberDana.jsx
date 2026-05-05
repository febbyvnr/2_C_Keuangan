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
    const [parentList, setParentList] = useState([]);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_REF_DANA",
        direction: "asc"
    });
    const [form, setForm] = useState({
        REF_ID_REF_DANA: "",
        DESKRIPSI_SUMBER_DANA: ""
    });

    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);
    const [confirmDeleteId, setConfirmDeleteId] = useState(null);

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = `http://localhost:8000/api/ref-sumber-dana?search=${keyword}`;
            const res = await fetch(url);
            const json = await res.json();
            setData(json.data || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const fetchParent = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/ref-sumber-dana?limit=1000");
            const json = await res.json();
            setParentList(json.data || []);
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchData();
        fetchParent();
    }, []);

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            setCurrentPage(1);
            fetchData(search);
        }, 200);
        return () => clearTimeout(delayDebounceFn);
    }, [search]);

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

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
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

    const closeModal = () => {
        setShowModal(false);
        setIsEdit(false);
        setEditId(null);
        setForm({ REF_ID_REF_DANA: "", DESKRIPSI_SUMBER_DANA: "" });
    };

    const showToast = (type = "add", message = "") => {
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

    const handleSubmit = async (e) => {
        e.preventDefault();
        const url = isEdit
            ? `http://localhost:8000/api/ref-sumber-dana/update/${editId}`
            : "http://localhost:8000/api/ref-sumber-dana/store";
        const method = isEdit ? "PUT" : "POST";
        const res = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(form)
        });
        const json = await res.json();
        if (json.success || res.ok) {
            closeModal();
            showToast(isEdit ? "update" : "add");
            fetchData();
        } else {
            showToast("error", json.message || "Gagal");
        }
    };

    const confirmDeleteAction = async () => {
        try {
            const res = await fetch(`http://localhost:8000/api/ref-sumber-dana/delete/${confirmDeleteId}`, {
                method: "DELETE"
            });
            const json = await res.json();
            if (json.success || res.ok) {
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
            map[item.ID_REF_DANA] = { ...item, children: [] };
        });
        nodes.forEach(item => {
            const parentId = item.REF_ID_REF_DANA;
            const isParent = !parentId || parentId === 0 || parentId === "0";
            if (!isParent) {
                if (map[parentId]) {
                    map[parentId].children.push(map[item.ID_REF_DANA]);
                } else {
                    roots.push(map[item.ID_REF_DANA]);
                }
            } else {
                roots.push(map[item.ID_REF_DANA]);
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
                            placeholder="Cari sumber dana..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="search-input"
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
                <div className="tree-list">
                    {loading ? (
                        <div className="text-center" style={{padding: "20px"}}>Loading...</div>
                    ) : currentRows.length === 0 ? (
                        <div className="text-center" style={{padding: "20px"}}>Tidak ada data</div>
                    ) : (
                        currentRows.map((item) => (
                            <div 
                                className={`tree-row ${item.level > 0 ? "child-row" : "parent-row"}`} 
                                key={item.ID_REF_DANA}
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
                                    <span className="tree-number">{item.nomor_urut}</span>
                                    <span className="tree-text">{item.DESKRIPSI_SUMBER_DANA}</span>
                                </div>
                                <div className="tree-actions">
                                    <button className="btn-edit" onClick={() => handleEdit(item)}>
                                        <i className="bi bi-pencil"></i>
                                    </button>
                                    <button
                                        className="btn-delete"
                                        disabled={item.is_used > 0 || (item.children && item.children.length > 0)}
                                        onClick={() => setConfirmDeleteId(item.ID_REF_DANA)}
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
                            onClick={() => setCurrentPage(i + 1)}
                            className={`page-btn ${currentPage === i + 1 ? "active" : ""}`}
                        >
                            {i + 1}
                        </button>
                    ))}
                    <button className="page-btn" disabled={currentPage === totalPages || totalPages === 0} onClick={() => setCurrentPage(p => p + 1)}>
                        <i className="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div className="export-wrapper">
                    {/* <a href={`http://localhost:8000/api/ref-sumber-dana/export/excel?search=${search}`} className="btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i> Export Excel
                    </a>
                    <a href={`http://localhost:8000/api/ref-sumber-dana/export/pdf?search=${search}`} className="btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i> Export PDF
                    </a> */}
                </div>
            </div>
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>{isEdit ? "Edit Sumber Dana" : "Tambah Sumber Dana"}</h3>
                        <form onSubmit={handleSubmit}>
                            <label>Parent Sumber Dana (opsional)</label>
                            <select
                                name="REF_ID_REF_DANA"
                                value={form.REF_ID_REF_DANA}
                                onChange={handleChange}
                            >
                                <option value="">-- Pilih Parent --</option>
                                {parentList
                                    .filter(item => String(item.ID_REF_DANA) !== String(editId))
                                    .map((item) => (
                                        <option key={item.ID_REF_DANA} value={item.ID_REF_DANA}>
                                            {item.ID_REF_DANA} - {item.DESKRIPSI_SUMBER_DANA}
                                        </option>
                                    ))}
                            </select>
                            <label>Deskripsi Sumber Dana</label>
                            <input 
                                type="text" 
                                name="DESKRIPSI_SUMBER_DANA" 
                                value={form.DESKRIPSI_SUMBER_DANA} 
                                onChange={handleChange} 
                                required 
                            />
                            <div className="modal-actions">
                                <button type="button" className="btn-cancel" onClick={closeModal}>Batal</button>
                                <button type="submit" className="btn-submit">{isEdit ? "Perbarui" : "Tambah"}</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
            {toast && (
                <div className={`toast-container ${visible ? "show" : "hide"}`}>
                    <div className="toast-box">
                        <span className="toast-text">
                            {toast.type === "error" ? toast.message : (
                                <>Berhasil <span className={`highlight ${toast.type}`}>{toast.action}</span> Sumber Dana</>
                            )}
                        </span>
                    </div>
                </div>
            )}
            {confirmDeleteId && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3>Konfirmasi Hapus</h3>
                        <p>Yakin ingin menghapus sumber dana ini?</p>
                        <div className="modal-actions">
                            <button className="toast-btn-cancel" onClick={() => setConfirmDeleteId(null)}>Batal</button>
                            <button className="toast-btn-delete" onClick={confirmDeleteAction}>Hapus</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
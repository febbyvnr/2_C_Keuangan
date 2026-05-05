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
    const [search, setSearch] = useState("");
    const [toast, setToast] = useState(null);
    const [confirmDeleteId, setConfirmDeleteId] = useState(null);
    const [sortConfig, setSortConfig] = useState({
        key: "ID_MASTER_COA",
        direction: "asc"
    });
    const [form, setForm] = useState({
        MST_ID_MASTER_COA: "",
        DESKRIPSI_COA: ""
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/coa?search=${keyword}`
                : "http://localhost:8000/api/coa";

            const res = await fetch(url);
            const json = await res.json();
            setData(json.data || []);
            setCoaList(json.data || []);
        } catch (err) {
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            setCurrentPage(1);
            fetchData(search);
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
        return sortConfig.direction === "asc"
            ? "bi bi-funnel-fill"
            : "bi bi-funnel-fill";
    };

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
            setShowModal(false);
            if (isEdit) {
                showToast("update");
            } else {
                showToast("add");
            }
            setIsEdit(false);
            setEditId(null);
            setForm({
                MST_ID_MASTER_COA: "",
                DESKRIPSI_COA: ""
            });
            fetchData();
        } else {
            showToast(json.message || "Gagal", "error");
        }
    };

    const confirmDeleteAction = async () => {
        try {
            const res = await fetch(
                `http://localhost:8000/api/coa/delete/${confirmDeleteId}`,
                { method: "DELETE" }
            );
            const json = await res.json();
            if (json.success) {
                showToast("delete");
                fetchData();
            } else {
                showToast("Gagal menghapus data", "error");
            }
        } catch (err) {
            console.error(err);
            showToast("Terjadi error saat menghapus", "error");
        } finally {
            setConfirmDeleteId(null);
        }
    };

    const handleDelete = (item) => {
        if (item.is_used) {
            showToast("error", "COA sudah digunakan");
            return;
        }
        if (item.has_child) {
            showToast("error", "COA tidak boleh dihapus karena masih memiliki sub COA");
            return;
        }
        setConfirmDeleteId(item.ID_MASTER_COA);
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

    const sortedData = [...data].sort((a, b) => {
        let valA = a[sortConfig.key] || "";
        let valB = b[sortConfig.key] || "";
        if (sortConfig.key === "ID_MASTER_COA" || sortConfig.key === "MST_ID_MASTER_COA") {
            valA = Number(valA);
            valB = Number(valB);
        }
        if (typeof valA === "string") valA = valA.toLowerCase();
        if (typeof valB === "string") valB = valB.toLowerCase();
        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const [visible, setVisible] = useState(false);

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

    const buildTree = (nodes) => {
        const map = {};
        const roots = [];
        nodes.forEach(item => {
            map[item.ID_MASTER_COA] = { ...item, children: [] };
        });
        nodes.forEach(item => {
            const parentId = item.MST_ID_MASTER_COA;
            const isParent = !parentId || parentId === 0 || parentId === "0";
            if (!isParent) {
                if (map[parentId]) {
                    map[parentId].children.push(map[item.ID_MASTER_COA]);
                } else {
                    roots.push(map[item.ID_MASTER_COA]);
                }
            } else {
                roots.push(map[item.ID_MASTER_COA]);
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
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);

    return (
        <div className="coa-container">
            <div className="coa-header">
                <h2>Master COA</h2>
                <div className="header-actions">
                    <button
                        className="btn-reset"
                        onClick={() => {
                            setSearch("");
                            fetchData();
                        }}
                    >
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari deskripsi / kode COA..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
                            className="search-input"
                        />
                        <button
                            className="search-btn"
                            onClick={() => {
                                setCurrentPage(1);
                                fetchData(search);
                            }}
                        >
                            Search
                        </button>
                    </div>
                    <button
                        className="btn-primary"
                        onClick={() => {
                            setIsEdit(false);
                            setEditId(null);
                            setForm({
                                MST_ID_MASTER_COA: "",
                                DESKRIPSI_COA: ""
                            });
                            setShowModal(true);
                        }}
                    >
                        Tambah COA
                    </button>
                </div>
            </div>
            <div className="coa-table-wrapper">
                <div className="tree-list">
                    {loading ? (
                        <div className="text-center" style={{padding: "20px"}}>Loading...</div>
                    ) : currentRows.length === 0 ? (
                        <div className="text-center" style={{padding: "20px"}}>Tidak ada data</div>
                    ) : (
                        currentRows.map((item) => (
                            <div 
                                className={`tree-row ${item.level > 0 ? "child-row" : "parent-row"}`} 
                                key={item.ID_MASTER_COA}
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
                                    <span className="tree-text">{item.KODE_COA} | {item.DESKRIPSI_COA}</span>
                                </div>
                                <div className="tree-actions">
                                    <button className="btn-edit" onClick={() => handleEdit(item)}>
                                        <i className="bi bi-pencil"></i>
                                    </button>
                                    <button
                                        className="btn-delete"
                                        disabled={item.is_used > 0 || (item.children && item.children.length > 0)}
                                        onClick={() => setConfirmDeleteId(item.ID_MASTER_COA)}
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
                <div className="export-wrapper">
                    <a href={`http://localhost:8000/api/coa/export/excel?search=${search}`} className="btn btn-outline-success custom-btn">
                        <i className="bi bi-filetype-xlsx"></i>Export Excel
                    </a>
                    <a href={`http://localhost:8000/api/coa/export/pdf?search=${search}`} className="btn btn-outline-danger custom-btn">
                        <i className="bi bi-file-earmark-pdf"></i>Export PDF
                    </a>
                </div>
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
                                {coaList
                                    .filter((coa) => coa.ID_MASTER_COA !== editId) 
                                    .map((coa) => (
                                        <option key={coa.ID_MASTER_COA} value={coa.ID_MASTER_COA}>
                                            {coa.KODE_COA} - {coa.DESKRIPSI_COA}
                                        </option>
                                    ))
                                }
                            </select>
                            <label>Deskripsi COA</label>
                            <input
                                type="text"
                                name="DESKRIPSI_COA"
                                value={form.DESKRIPSI_COA}
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
            {toast && (
                <div className={`toast-container ${visible ? "show" : "hide"}`}>
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
                                    COA
                                </>
                            )}
                        </span>
                    </div>
                </div>
            )}
            {confirmDeleteId && (
                <div className="modal-overlay">
                    <div className="modal-box">
                        <h3 className="toast-modal-box-h3">Konfirmasi Hapus</h3>
                        <p style={{ fontSize: "14px", marginBottom: "16px" }}>
                            Yakin ingin menghapus COA ini?
                        </p>
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
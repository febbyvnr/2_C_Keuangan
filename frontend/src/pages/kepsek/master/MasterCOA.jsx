import { useEffect, useState } from "react";
import "../../../styles/bendahara/MasterCOA.css";

export default function MasterCOA() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_MASTER_COA",
        direction: "asc"
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            setCurrentPage(1);
            const url = keyword
                ? `http://localhost:8000/api/coa?search=${keyword}`
                : "http://localhost:8000/api/coa";

            const res = await fetch(url);
            const json = await res.json();
            setData(json.data || []);
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

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            setCurrentPage(1);
            fetchData(search);
        }, 200);
        return () => clearTimeout(delayDebounceFn);
    }, [search]);

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

    const changePage = (page) => {
        setCurrentPage(page);
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
            const currentNumber = prefix ? `${prefix}.${index + 1}` : `${index + 1}`;
            const isLast = index === nodes.length - 1;
            return {
                ...node,
                number,
                path: node.path || currentNumber,
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
                                    <span className="tree-number">{item.path}</span>
                                    <span className="tree-text">{item.KODE_COA} | {item.DESKRIPSI_COA}</span>
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
            </div>
        </div>
    );
}
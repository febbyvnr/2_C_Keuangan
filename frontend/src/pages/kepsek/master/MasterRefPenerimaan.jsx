import { useEffect, useState } from "react";
import "../../../styles/bendahara/MasterRefPenerimaan.css";

export default function MasterRefPenerimaan() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_REF_PENERIMAAN", 
        direction: "asc" 
    });

    const fetchData = async (keyword = "") => {
        setLoading(true);
        try {
            const url = keyword 
                ? `http://localhost:8000/api/ref-penerimaan/search?DESKRIPSI_REF_PENERIMAAN=${keyword}`
                : "http://localhost:8000/api/ref-penerimaan";
            const res = await fetch(url);
            const json = await res.json();
            if (res.ok) {
                const resultData = Array.isArray(json) ? json : (json.data || []);
                setData(resultData);
            } else {
                if (res.status === 404) {
                    setData([]);
                }
                console.error(json.message);
            }
        } catch (err) {
            console.error("Error fetching data:", err);
        } finally {
            setLoading(false);
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
        fetchData();
    }, []);

    useEffect(() => {
        setCurrentPage(1);
    }, [search]);

    const filteredData = data.filter((item) =>
        (item.DESKRIPSI_REF_PENERIMAAN || "").toLowerCase().includes(search.toLowerCase()) ||
        (item.nomor_urut + "").includes(search)
    );
    
    const processedData = filteredData.map(item => {
        const dots = (item.nomor_urut.match(/\./g) || []).length;
        return {
            ...item,
            level: dots,
            parentLines: item.parentLines || []
        };
    });

    const sortedData = [...data]
        .filter((item) =>
            (item.DESKRIPSI_REF_PENERIMAAN || "").toLowerCase().includes(search.toLowerCase()) ||
            (item.REF_ID_REF_PENERIMAAN + "").includes(search) ||
            (item.ID_REF_PENERIMAAN + "").includes(search)
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

    const buildTree = (nodes) => {
        const map = {};
        const roots = [];
        nodes.forEach(item => {
            map[item.ID_REF_PENERIMAAN] = { ...item, children: [] };
        });
        nodes.forEach(item => {
            const parentId = item.REF_ID_REF_PENERIMAAN;
            const isParent = !parentId || parentId === 0 || parentId === "0";
            if (!isParent) {
                if (map[parentId]) {
                    map[parentId].children.push(map[item.ID_REF_PENERIMAAN]);
                } else {
                    roots.push(map[item.ID_REF_PENERIMAAN]);
                }
            } else {
                roots.push(map[item.ID_REF_PENERIMAAN]);
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
    const indexOfLastRow = currentPage * itemsPerPage;
    const indexOfFirstRow = indexOfLastRow - itemsPerPage;
    const currentRows = processedData.slice(indexOfFirstRow, indexOfLastRow);
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    return (
        <div className="ref-penerimaan-container">
            <div className="ref-penerimaan-header">
                <h2>Master Referensi Penerimaan</h2>
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
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                </div>
            </div>
            <div className="ref-penerimaan-table-wrapper">
                <div className="tree-list">
                    {loading ? (
                        <div className="text-center" style={{padding: "20px"}}>Loading...</div>
                    ) : currentRows.length === 0 ? (
                        <div className="text-center" style={{padding: "20px"}}>Tidak ada data</div>
                    ) : (
                        currentRows.map((item) => (
                            <div 
                                className={`tree-row ${item.level > 0 ? "child-row" : "parent-row"}`} 
                                key={item.ID_REF_PENERIMAAN}
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
                                    <span className="tree-text">{item.DESKRIPSI_REF_PENERIMAAN}</span>
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
            </div>
        </div>
    );
}
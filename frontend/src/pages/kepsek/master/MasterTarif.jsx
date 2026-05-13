import { useEffect, useState } from "react";
import "../../../styles/bendahara/MasterTarif.css";

export default function MasterTarif() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [jenisTarifList, setJenisTarifList] = useState([]);
    const [tahunAnggaranList, setTahunAnggaranList] = useState([]);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({
        key: "ID_REF_TARIF",
        direction: "desc"
    });

    const fetchData = async (keyword = "") => {
        try {
            setLoading(true);
            const url = keyword
                ? `http://localhost:8000/api/tarif?search=${keyword}`
                : "http://localhost:8000/api/tarif";

            const res = await fetch(url);
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text);
            }
            const json = await res.json();
            setData(json.data || json || []);
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

    const filteredData = data.filter((item) => {
        const keyword = search.toLowerCase();
        return (
            item.ID_REF_TARIF?.toString().includes(keyword) ||
            item.DESKRIPSI_TARIF?.toLowerCase().includes(keyword) ||
            item.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase().includes(keyword) ||
            item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase().includes(keyword)
        );
    });

    const sortedData = [...filteredData].sort((a, b) => {
        let valA, valB;
        switch (sortConfig.key) {
            case "ID_REF_TARIF":
                valA = Number(a.ID_REF_TARIF);
                valB = Number(b.ID_REF_TARIF);
                break;
            case "ID_JENIS_TARIF":
                valA = a.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase() || "";
                valB = b.jenis_tarif?.DESKRIPSI_JENIS_TARIF?.toLowerCase() || "";
                break;
            case "ID_TA_ANGGARAN":
                valA = a.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase() || "";
                valB = b.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN?.toLowerCase() || "";
                break;
            case "DESKRIPSI_TARIF":
                valA = a.DESKRIPSI_TARIF?.toLowerCase() || "";
                valB = b.DESKRIPSI_TARIF?.toLowerCase() || "";
                break;
            case "NOMINAL":
                valA = Number(a.NOMINAL);
                valB = Number(b.NOMINAL);
                break;
            case "TGL_PENETAPAN":
                valA = new Date(a.TGL_PENETAPAN);
                valB = new Date(b.TGL_PENETAPAN);
                break;
            default:
                valA = "";
                valB = "";
        }
        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });

    useEffect(() => {
        fetchData();
    }, []);

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const totalData = data.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="tarif-container">
            <div className="tarif-header">
                <h2>Master Tarif</h2>
                <div className="header-actions">
                    <button className="btn-reset" onClick={() => { setSearch(""); fetchData(); }}>
                        Reset
                    </button>
                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            type="text"
                            placeholder="Cari tarif..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
                            className="search-input"
                        />
                        <button className="search-btn" onClick={() => { setCurrentPage(1); fetchData(search); }}>
                            Search
                        </button>
                    </div>
                </div>
            </div>
            <div className="tarif-table-wrapper">
                <table className="tarif-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleSort("ID_REF_TARIF")}>
                                ID <i className={getIcon("ID_REF_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("ID_JENIS_TARIF")}>
                                Jenis Tarif <i className={getIcon("ID_JENIS_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("ID_TA_ANGGARAN")}>
                                TA Anggaran <i className={getIcon("ID_TA_ANGGARAN")}></i>
                            </th>
                            <th onClick={() => handleSort("DESKRIPSI_TARIF")}>
                                Deskripsi <i className={getIcon("DESKRIPSI_TARIF")}></i>
                            </th>
                            <th onClick={() => handleSort("NOMINAL")}>
                                Nominal <i className={getIcon("NOMINAL")}></i>
                            </th>
                            <th onClick={() => handleSort("TGL_PENETAPAN")}>
                                Tanggal Penetapan <i className={getIcon("TGL_PENETAPAN")}></i>
                            </th>
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
                                    <td>{new Date(item.TGL_PENETAPAN).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
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
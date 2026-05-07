import { useEffect, useState } from "react";
import "../../../styles/waka/approve/RKT.css";

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(value || 0);
}

export default function RKAPage({ setHasPending }) {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);

    const itemsPerPage = 10;

    const [sortConfig, setSortConfig] = useState({
        key: "ID_PROGRAM_KERJA",
        direction: "desc",
    });

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async (searchValue = "") => {
        try {
            const url = searchValue
                ? `http://localhost:8000/api/rka/search?keyword=${searchValue}`
                : "http://localhost:8000/api/rka";

            const res = await fetch(url);
            const json = await res.json();

            const result = json.data || [];

            const grouped = Object.values(
                result.reduce((acc, item) => {
                    const key = item.ID_PROGRAM_KERJA;

                    if (!acc[key]) {
                        acc[key] = {
                            ...item,
                            details: [],
                        };
                    }

                    acc[key].details.push(item);

                    return acc;
                }, {})
            );

            setData(grouped);

            if (grouped.length > 0) {
                setSelected(grouped[0]);
            }

            setHasPending && setHasPending(grouped.length > 0);
        } catch (err) {
            console.error(err);
        }
    };

    const handleSearch = (value) => {
        setSearch(value);
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

        return sortConfig.direction === "asc"
            ? "bi bi-funnel-fill"
            : "bi bi-funnel-fill";
    };

    const getTotalRincian = (item) => {
        return item.details.reduce(
            (total, detail) => total + Number(detail.NOMINAL || 0),
            0
        );
    };

    const getStatus = (item) => {
        const totalRincian = getTotalRincian(item);
        const totalRkt = Number(item.rkt?.TOTAL_PROGKER || 0);

        if (totalRincian === 0) {
            return {
                label: "Belum Ada Rincian",
                className: "pending",
            };
        }

        if (totalRincian > totalRkt) {
            return {
                label: "Melebihi Anggaran",
                className: "rejected",
            };
        }

        return {
            label: "Sesuai Anggaran",
            className: "approved",
        };
    };

    const sortedData = [...data].sort((a, b) => {
        let valA;
        let valB;

        if (sortConfig.key === "PROGRAM_KERJA") {
            valA = a.rkt?.PROGRAM_KERJA || "";
            valB = b.rkt?.PROGRAM_KERJA || "";
        } else if (sortConfig.key === "TOTAL_PROGKER") {
            valA = Number(a.rkt?.TOTAL_PROGKER || 0);
            valB = Number(b.rkt?.TOTAL_PROGKER || 0);
        } else if (sortConfig.key === "TOTAL_RINCIAN") {
            valA = getTotalRincian(a);
            valB = getTotalRincian(b);
        } else {
            valA = a[sortConfig.key] ?? "";
            valB = b[sortConfig.key] ?? "";
        }

        if (valA < valB) {
            return sortConfig.direction === "asc" ? -1 : 1;
        }

        if (valA > valB) {
            return sortConfig.direction === "asc" ? 1 : -1;
        }

        return 0;
    });

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;

    const currentData = sortedData.slice(indexOfFirst, indexOfLast);

    const totalPages = Math.ceil(sortedData.length / itemsPerPage);

    const totalData = sortedData.length;

    const startData = totalData === 0 ? 0 : indexOfFirst + 1;

    const endData = Math.min(indexOfLast, totalData);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="rkt-approval-container">
            <div className="rkt-header">
                <div></div>

                <div className="header-actions">
                    <button
                        className="btn-reset"
                        onClick={() => {
                            setSearch("");
                            fetchData("");
                        }}
                    >
                        Reset
                    </button>

                    <div style={{ display: "flex", gap: "10px" }}>
                        <input
                            className="search-input"
                            type="text"
                            placeholder="Cari program kerja..."
                            value={search}
                            onChange={(e) => handleSearch(e.target.value)}
                            onKeyDown={handleKeyDown}
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

            <div className="rkt-approval-grid">
                <div className="rkt-table-section">
                    <div className="rkt-table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th onClick={() => handleSort("ID_PROGRAM_KERJA")}>
                                        ID{" "}
                                        <i
                                            className={getIcon("ID_PROGRAM_KERJA")}
                                        ></i>
                                    </th>

                                    <th onClick={() => handleSort("PROGRAM_KERJA")}>
                                        Program Kerja{" "}
                                        <i
                                            className={getIcon("PROGRAM_KERJA")}
                                        ></i>
                                    </th>

                                    <th onClick={() => handleSort("TOTAL_PROGKER")}>
                                        Anggaran RKT{" "}
                                        <i
                                            className={getIcon("TOTAL_PROGKER")}
                                        ></i>
                                    </th>

                                    <th onClick={() => handleSort("TOTAL_RINCIAN")}>
                                        Total Rincian{" "}
                                        <i
                                            className={getIcon("TOTAL_RINCIAN")}
                                        ></i>
                                    </th>

                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                {currentData.length > 0 ? (
                                    currentData.map((item) => {
                                        const status = getStatus(item);

                                        return (
                                            <tr
                                                key={item.ID_PROGRAM_KERJA}
                                                onClick={() => setSelected(item)}
                                                className={
                                                    selected?.ID_PROGRAM_KERJA ===
                                                    item.ID_PROGRAM_KERJA
                                                        ? "active-row"
                                                        : ""
                                                }
                                            >
                                                <td>
                                                    {item.ID_PROGRAM_KERJA}
                                                </td>

                                                <td>
                                                    {item.rkt?.PROGRAM_KERJA}
                                                </td>

                                                <td>
                                                    {formatRupiah(
                                                        item.rkt?.TOTAL_PROGKER
                                                    )}
                                                </td>

                                                <td>
                                                    {formatRupiah(
                                                        getTotalRincian(item)
                                                    )}
                                                </td>

                                                <td>
                                                    <span
                                                        className={`status ${status.className}`}
                                                    >
                                                        {status.label}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="empty">
                                            Tidak ada data RKA
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="pagination-wrapper">
                        <div className="pagination-info">
                            Menampilkan {startData} - {endData} dari{" "}
                            {totalData} data
                        </div>

                        <div className="pagination">
                            <button
                                className="page-btn"
                                onClick={() =>
                                    setCurrentPage((prev) =>
                                        Math.max(prev - 1, 1)
                                    )
                                }
                                disabled={currentPage === 1}
                            >
                                <i className="bi bi-chevron-left"></i>
                            </button>

                            {Array.from(
                                { length: totalPages },
                                (_, i) => (
                                    <button
                                        key={i + 1}
                                        onClick={() => changePage(i + 1)}
                                        className={`page-btn ${
                                            currentPage === i + 1
                                                ? "active"
                                                : ""
                                        }`}
                                    >
                                        {i + 1}
                                    </button>
                                )
                            )}

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
                            <a
                                href="http://localhost:8000/api/rka/export"
                                className="btn-outline-success custom-btn"
                            >
                                <i className="bi bi-filetype-xlsx"></i>
                                Export Excel
                            </a>

                            <a
                                href="http://localhost:8000/api/rka/export/pdf"
                                className="btn-outline-danger custom-btn"
                            >
                                <i className="bi bi-filetype-pdf"></i>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>

                <div className="rkt-detail-section">
                    {selected ? (
                        <>
                            <div className="detail-header">
                                <h3>Detail RKA</h3>
                            </div>

                            <div className="detail-content">
                                <div className="detail-row">
                                    <span>ID Program Kerja</span>
                                    <span>
                                        {selected.ID_PROGRAM_KERJA}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Program Kerja</span>
                                    <span>
                                        {selected.rkt?.PROGRAM_KERJA || "-"}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Indikator</span>
                                    <span>
                                        {selected.rkt?.INDIKATOR || "-"}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Sasaran</span>
                                    <span>
                                        {selected.rkt?.SASARAN || "-"}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Anggaran RKT</span>
                                    <span>
                                        {formatRupiah(
                                            selected.rkt?.TOTAL_PROGKER
                                        )}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Total Rincian</span>
                                    <span>
                                        {formatRupiah(
                                            getTotalRincian(selected)
                                        )}
                                    </span>
                                </div>

                                <div className="detail-row">
                                    <span>Status</span>

                                    <span
                                        className={`status ${
                                            getStatus(selected).className
                                        }`}
                                    >
                                        {getStatus(selected).label}
                                    </span>
                                </div>
                            </div>

                            <div className="detail-header">
                                <h3>Rincian Anggaran</h3>
                            </div>

                            <div className="detail-content">
                                {selected.details.map((detail, index) => (
                                    <div
                                        className="detail-row"
                                        key={detail.ID_DT_PROGKER}
                                    >
                                        <span>
                                            {index + 1}.{" "}
                                            {detail.ref_dana
                                                ?.DESKRIPSI_SUMBER_DANA ||
                                                "-"}
                                        </span>

                                        <span>
                                            {detail.QTY} x {detail.VOLUME}{" "}
                                            {detail.SATUAN} (
                                            {formatRupiah(
                                                detail.HARGA_SATUAN
                                            )}
                                            )
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </>
                    ) : (
                        <div className="empty">
                            <p>
                                Pilih Salah Satu Baris Untuk Melihat Detail RKA
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
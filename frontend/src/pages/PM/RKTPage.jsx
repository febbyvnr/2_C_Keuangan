import { useEffect, useState } from "react";
import "../../styles/pm/RKT.css";

export default function RKTPage({ setHasPending }) {
    const [data, setData] = useState([]);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const [showModal, setShowModal] = useState(false);
    const [detailData, setDetailData] = useState(null);

    const itemsPerPage = 10;

    const [sortConfig, setSortConfig] = useState({
        key: "ID_PROGRAM_KERJA",
        direction: "desc"
    });

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async (searchValue = "") => {
        try {
            const url = searchValue
                ? `http://localhost:8000/api/rkt?search=${searchValue}`
                : "http://localhost:8000/api/rkt";

            const res = await fetch(url);
            const json = await res.json();

            const rawResult = json.data || [];

            const result = rawResult.filter((item) => {
                return getStatus(item).label !== "Draft";
            });

            setData(result);

            const hasPending = result.some(
                (item) => getStatus(item).label === "Pending"
            );

            setHasPending && setHasPending(hasPending);
        } catch (err) {
            console.error(err);
        }
    };

    const handleDetail = async (id) => {
        try {
            const res = await fetch(
                `http://localhost:8000/api/rkt/${id}`
            );

            const json = await res.json();

            if (json.success) {
                setDetailData(json.data);
                setShowModal(true);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleSort = (key) => {
        let direction = "asc";

        if (
            sortConfig.key === key &&
            sortConfig.direction === "asc"
        ) {
            direction = "desc";
        }

        setSortConfig({ key, direction });
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

    const getIcon = (key) => {
        if (sortConfig.key !== key) {
            return "bi bi-funnel";
        }

        return "bi bi-funnel-fill";
    };

    const statusOrder = {
        Disetujui: 1,
        Revisi: 2,
        Pending: 3,
        Ditolak: 4
    };

    const getStatus = (item) => {
        const lastPm = item.tr_pm?.length
            ? item.tr_pm[item.tr_pm.length - 1]
            : null;

        const aksi = String(
            lastPm?.AKSI || lastPm?.aksi || ""
        ).toUpperCase();

        const note = String(
            lastPm?.DESKRIPSI_TR_PM || ""
        ).toLowerCase();

        if (
            aksi === "DRAFT" ||
            note.startsWith("draft")
        ) {
            return {
                label: "Draft",
                className: "draft"
            };
        }

        if (
            aksi === "TOLAK" ||
            aksi === "DITOLAK" ||
            note.includes("ditolak")
        ) {
            return {
                label: "Ditolak",
                className: "rejected"
            };
        }

        if (
            aksi === "REVISI" ||
            note.includes("revisi")
        ) {
            return {
                label: "Revisi",
                className: "revisi"
            };
        }

        if (
            aksi === "SETUJUI" ||
            item.NIP_VALIDATOR_PROGKER
        ) {
            return {
                label: "Disetujui",
                className: "approved"
            };
        }

        return {
            label: "Pending",
            className: "pending"
        };
    };

    const sortedData = [...data].sort((a, b) => {
        let valA, valB;

        if (sortConfig.key === "status") {
            const statusA = getStatus(a).label;
            const statusB = getStatus(b).label;

            valA = statusOrder[statusA] || 99;
            valB = statusOrder[statusB] || 99;
        } else if (
            sortConfig.key === "tahun_anggaran"
        ) {
            valA =
                a.tahun_anggaran
                    ?.DESKRIPSI_TAHUN_ANGGARAN || "";

            valB =
                b.tahun_anggaran
                    ?.DESKRIPSI_TAHUN_ANGGARAN || "";
        } else if (
            sortConfig.key === "TOTAL_PROGKER"
        ) {
            valA = Number(a.TOTAL_PROGKER || 0);
            valB = Number(b.TOTAL_PROGKER || 0);
        } else {
            valA = a[sortConfig.key] ?? "";
            valB = b[sortConfig.key] ?? "";
        }

        if (valA < valB) {
            return sortConfig.direction === "asc"
                ? -1
                : 1;
        }

        if (valA > valB) {
            return sortConfig.direction === "asc"
                ? 1
                : -1;
        }

        return 0;
    });

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;

    const totalPages = Math.ceil(
        data.length / itemsPerPage
    );

    const totalData = data.length;

    const startData =
        totalData === 0 ? 0 : indexOfFirst + 1;

    const endData = Math.min(
        indexOfLast,
        totalData
    );

    const currentData = sortedData.slice(
        indexOfFirst,
        indexOfLast
    );

    const changePage = (page) => {
        setCurrentPage(page);
    };

    return (
        <div className="pmrkt-container">
            <div className="pmrkt-header">
                <h2>Rencana Kegiatan Tahunan</h2>
                <div className="pmrkt-header-actions">
                    <button
                        className="pmrkt-reset-btn"
                        onClick={() => {
                            setSearch("");
                            fetchData("");
                        }}
                    >
                        Reset
                    </button>
                    <div className="pmrkt-search-group">
                        <input
                            className="pmrkt-search-input"
                            type="text"
                            placeholder="Cari..."
                            value={search}
                            onKeyDown={handleKeyDown}
                            onChange={(e) =>
                                handleSearch(
                                    e.target.value
                                )
                            }
                        />
                        <button
                            className="pmrkt-search-btn"
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
            <div className="pmrkt-table-section">
                <div className="pmrkt-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    onClick={() =>
                                        handleSort(
                                            "ID_PROGRAM_KERJA"
                                        )
                                    }
                                >
                                    ID{" "}
                                    <i
                                        className={getIcon(
                                            "ID_PROGRAM_KERJA"
                                        )}
                                    ></i>
                                </th>
                                <th
                                    onClick={() =>
                                        handleSort(
                                            "PROGRAM_KERJA"
                                        )
                                    }
                                >
                                    Program{" "}
                                    <i
                                        className={getIcon(
                                            "PROGRAM_KERJA"
                                        )}
                                    ></i>
                                </th>
                                <th
                                    onClick={() =>
                                        handleSort(
                                            "tahun_anggaran"
                                        )
                                    }
                                >
                                    Tahun{" "}
                                    <i
                                        className={getIcon(
                                            "tahun_anggaran"
                                        )}
                                    ></i>
                                </th>
                                <th
                                    onClick={() =>
                                        handleSort(
                                            "TOTAL_PROGKER"
                                        )
                                    }
                                >
                                    Anggaran{" "}
                                    <i
                                        className={getIcon(
                                            "TOTAL_PROGKER"
                                        )}
                                    ></i>
                                </th>
                                <th
                                    onClick={() =>
                                        handleSort("status")
                                    }
                                >
                                    Status{" "}
                                    <i
                                        className={getIcon(
                                            "status"
                                        )}
                                    ></i>
                                </th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            {currentData.map((item) => (
                                <tr
                                    key={
                                        item.ID_PROGRAM_KERJA
                                    }
                                >
                                    <td>
                                        {
                                            item.ID_PROGRAM_KERJA
                                        }
                                    </td>
                                    <td>
                                        {
                                            item.PROGRAM_KERJA
                                        }
                                    </td>
                                    <td>
                                        {
                                            item
                                                .tahun_anggaran
                                                ?.DESKRIPSI_TAHUN_ANGGARAN
                                        }
                                    </td>
                                    <td>
                                        Rp{" "}
                                        {Number(
                                            item.TOTAL_PROGKER ||
                                                0
                                        ).toLocaleString(
                                            "id-ID"
                                        )}
                                    </td>
                                    <td>
                                        {(() => {
                                            const status =
                                                getStatus(
                                                    item
                                                );

                                            return (
                                                <span
                                                    className={`pmrkt-status ${status.className}`}
                                                >
                                                    {
                                                        status.label
                                                    }
                                                </span>
                                            );
                                        })()}
                                    </td>
                                    <td>
                                        <button
                                            className="pmrkt-detail-btn"
                                            onClick={() =>
                                                handleDetail(
                                                    item.ID_PROGRAM_KERJA
                                                )
                                            }
                                        >
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            ))}
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
            {showModal && detailData && (
                <div className="pmrkt-modal-overlay">
                    <div className="pmrkt-modal-content">
                        <div className="pmrkt-modal-header">
                            <h2>Detail Program Kerja</h2>
                            <button
                                className="pmrkt-close-btn"
                                onClick={() => {
                                    setShowModal(false);
                                    setDetailData(null);
                                }}
                            >
                                ✕
                            </button>
                        </div>
                        <div className="pmrkt-detail-title">
                            Detail
                        </div>
                        <div className="pmrkt-detail-grid">
                            <div className="pmrkt-detail-item">
                                <span>
                                    Program Kerja
                                </span>
                                <p>
                                    {
                                        detailData.PROGRAM_KERJA
                                    }
                                </p>
                            </div>
                            <div className="pmrkt-detail-item">
                                <span>
                                    Tahun Anggaran
                                </span>
                                <p>
                                    {
                                        detailData
                                            .tahun_anggaran
                                            ?.DESKRIPSI_TAHUN_ANGGARAN
                                    }
                                </p>
                            </div>
                            <div className="pmrkt-detail-item">
                                <span>Unit</span>
                                <p>
                                    {
                                        detailData.unit
                                            ?.NAMA_UNIT
                                    }
                                </p>
                            </div>
                            <div className="pmrkt-detail-item">
                                <span>COA</span>
                                <p>
                                    {
                                        detailData.coa
                                            ?.DESKRIPSI_COA
                                    }
                                </p>
                            </div>
                        </div>
                        <div className="pmrkt-detail-title">
                            RKA
                        </div>
                        <table className="pmrkt-rka-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Satuan</th>
                                    <th>Qty</th>
                                    <th>Volume</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {detailData.detail_program_kerja?.length > 0 ? (
                                    detailData.detail_program_kerja.map((item, index) => {
                                        const qty =
                                            item.QTY ??
                                            item.KUANTITAS ??
                                            item.JUMLAH ??
                                            0;
                                        const harga =
                                            item.HARGA ??
                                            item.HARGA_SATUAN ??
                                            item.NOMINAL ??
                                            0;
                                        const total =
                                            item.TOTAL ??
                                            item.TOTAL_RINCIAN ??
                                            item.SUBTOTAL ??
                                            qty * harga;
                                        const satuan =
                                            item.SATUAN ?? "-";
                                        const volume =
                                            item.VOLUME ?? "-";
                                        return (
                                            <tr key={index}>
                                                <td>{index + 1}</td>
                                                <td>{satuan}</td>
                                                <td>{Number(qty).toLocaleString("id-ID")}</td>
                                                <td>{volume}</td>
                                                <td>Rp{" "}{Number(harga).toLocaleString("id-ID")}</td>
                                                <td>Rp{" "}{Number(total).toLocaleString("id-ID")}</td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="5">
                                            Tidak ada data RKA
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}
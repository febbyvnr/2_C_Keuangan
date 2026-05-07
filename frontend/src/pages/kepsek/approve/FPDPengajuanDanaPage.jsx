import { useEffect, useState } from "react";
import "../../../styles/waka/ApprovalCenter.css";

export default function FPDPengajuanDanaPage({ setHasPending }) {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const [statusFilter, setStatusFilter] = useState("default");
    const itemsPerPage = 10;
    const [sortConfig, setSortConfig] = useState({
        key: "ID_FPD",
        direction: "desc"
    });

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const res = await fetch(`http://localhost:8000/api/fpd-anggaran`);
            const json = await res.json();
            const result = json.data || [];
            setData(result);
            const hasPending = result.some(item => !item.NIP_VALIDATOR_FPD);
            setHasPending && setHasPending(hasPending);
        } catch (err) {
            console.error(err);
        }
    };

    const handleSearch = async (value) => {
        setSearch(value);
        setCurrentPage(1);
        try {
            const res = await fetch(`http://localhost:8000/api/fpd-anggaran/search?keyword=${value}`);
            const json = await res.json();
            setData(json.data || []);
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

    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            setCurrentPage(1);
            fetchData(search);
        }
    };

    const getIcon = (key) => {
        if (sortConfig.key !== key) return "bi bi-funnel";
        return sortConfig.direction === "asc"
            ? "bi bi-funnel-fill"
            : "bi bi-funnel-fill";
    };
    
    const getStatus = (item) => {
        const validator = item.NIP_VALIDATOR_FPD?.toString().trim();
        if (!validator) {
            return { type: "pending" };
        }
        if (validator.toLowerCase() === "ditolak") {
            return { type: "rejected" };
        }
        const jabatan =
            item.validator?.jabatan?.ref_jabatan?.DESKRIPSI_JABATAN;
        if (jabatan?.toLowerCase() === "bendahara") {
            return { type: "pending" };
        }
        return { type: "approved" };
    };

    const getStatusPriority = (item) => {
        const type = getStatus(item).type;
        return statusOrder[type] || 99;
    };

    const sortedData = [...data]
        .filter((item) => {
            if (statusFilter === "all" || statusFilter === "default") return true;
            return getStatus(item).type === statusFilter;
        })
        .sort((a, b) => {
            let valA = a[sortConfig.key];
            let valB = b[sortConfig.key];
            if (sortConfig.key === "PROGRAM") {
                valA = a.program_kerja?.INDIKATOR || "";
                valB = b.program_kerja?.INDIKATOR || "";
            }
            if (sortConfig.key === "TGL_FPD") {
                valA = new Date(a.TGL_FPD);
                valB = new Date(b.TGL_FPD);
            }
            if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
            if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
            return 0;
        });

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const totalData = data.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);

    const handleApprove = async () => {
        if (!selected) return;
        const userData = localStorage.getItem("user"); 
        if (!userData) {
            showToast("error", "Sesi login tidak ditemukan");
            return;
        }
        const user = JSON.parse(userData);
        const nip = user.NIP_KARYAWAN;
        if (!nip) {
            showToast("error", "NIP tidak ditemukan");
            return;
        }
        try {
            const res = await fetch(`http://localhost:8000/api/fpd-anggaran/update/${selected.ID_FPD}`, {
                method: "PUT",
                headers: { 
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    ID_PROGRAM_KERJA: selected.ID_PROGRAM_KERJA,
                    TGL_FPD: selected.TGL_FPD,
                    NOMINAL_ANGGARAN: selected.NOMINAL_ANGGARAN,
                    NIP_VALIDATOR_FPD: nip 
                })
            });
            const result = await res.json();
            if (res.ok) {
                showToast("success", "Program Kerja Disetujui");
                fetchData();
                setSelected(null);
            } else {
                showToast("error", result.message || "Terjadi kesalahan validasi");
                console.error("Validation Error:", result.errors);
            }
        } catch (err) {
            console.error("Fetch Error:", err);
            showToast("error", "Koneksi ke server gagal");
        }
    };

    const handleReject = async () => {
        if (!selected) return;
        try {
            console.log("Reject ID:", selected);
            const res = await fetch(
                `http://localhost:8000/api/fpd-anggaran/reject/${selected.ID_FPD}`,
                { method: "PUT" }
            );
            if (res.ok) {
                showToast("success", "Program Kerja Ditolak");
                fetchData();
                setSelected(null);
            } else {
                showToast("error", "Gagal menolak");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Koneksi gagal");
        }
    };

    const getDetailStatus = (item) => {
        if (!item?.NIP_VALIDATOR_FPD) {
            return {
                label: "Menunggu Verifikasi",
                className: "warning"
            };
        }
        if (item.NIP_VALIDATOR_FPD === "Ditolak") {
            return {
                label: "Ditolak",
                className: "danger"
            };
        }
        return {
            label: "Disetujui",
            className: "success"
        };
    };
    const status = getDetailStatus(selected);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const isApproved = selected?.NIP_VALIDATOR_FPD;

    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);

    const showToast = (type = "success", message = "") => {
        setToast({ type, message });
        setVisible(true);
        setTimeout(() => setVisible(false), 2500);
        setTimeout(() => setToast(null), 3000);
    };

    return (
        <div className="fpd-container">
            <div className="fpd-header">
                <div>
                    <div className="status-filter">
                        <span>Urutkan Status Berdasarkan :</span>
                        <div className="custom-dropdown">
                            <button className={`dropdown-btn ${statusFilter !== "default" ? "active" : ""}`}>
                                {statusFilter === "default" && "Semua"}
                                {statusFilter === "pending" && "Menunggu Verifikasi"}
                                {statusFilter === "approved" && "Disetujui"}
                                {statusFilter === "rejected" && "Ditolak"}
                                <i className="bi bi-chevron-down"></i>
                            </button>
                            <div className="dropdown-menu">
                                <div onClick={() => setStatusFilter("default")}>Semua</div>
                                <div onClick={() => setStatusFilter("pending")}>Menunggu Verifikasi</div>
                                <div onClick={() => setStatusFilter("approved")}>Disetujui</div>
                                <div onClick={() => setStatusFilter("rejected")}>Ditolak</div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            className="search-input"
                            type="text"
                            placeholder="Cari..."
                            value={search}
                            onChange={(e) => handleSearch(e.target.value)}
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
            <div className="grid-approval">
                <div className="fpd-table-section">
                    <div className="fpd-table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th onClick={() => handleSort("ID_FPD")}>
                                        ID <i className={getIcon("ID_FPD")}></i>
                                    </th>
                                    <th onClick={() => handleSort("PROGRAM")}>
                                        Program <i className={getIcon("PROGRAM")}></i>
                                    </th>
                                    <th onClick={() => handleSort("TGL_FPD")}>
                                        Tanggal <i className={getIcon("TGL_FPD")}></i>
                                    </th>
                                    <th onClick={() => handleSort("NOMINAL_ANGGARAN")}>
                                        Anggaran <i className={getIcon("NOMINAL_ANGGARAN")}></i>
                                    </th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {currentData.map((item) => (
                                    <tr
                                        key={item.ID_FPD}
                                        onClick={() => setSelected(item)}
                                        className={selected?.ID_FPD === item.ID_FPD ? "active-row" : ""}
                                    >
                                        <td>{item.ID_FPD}</td>
                                        <td>{item.program_kerja?.INDIKATOR || "Tidak ada Indikator"}</td>
                                        <td>{new Date(item.TGL_FPD).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}</td>
                                        <td>
                                            Rp {Number(item.NOMINAL_ANGGARAN).toLocaleString("id-ID")}
                                        </td>
                                        <td>
                                            {(() => {
                                                const status = getStatus(item);
                                                if (status.type === "pending") {
                                                    return (
                                                        <i className="bi bi-exclamation-circle-fill icon-danger icon-warning-animate"></i>
                                                    );
                                                }
                                                if (status.type === "rejected") {
                                                    return (
                                                        <span className="status rejected">
                                                            Ditolak
                                                        </span>
                                                    );
                                                }
                                                if (status.type === "approved") {
                                                    return (
                                                        <span className="status approved">
                                                            Disetujui
                                                        </span>
                                                    );
                                                }
                                                return null;
                                            })()}
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
                            <a href={`http://localhost:8000/api/fpd-anggaran/export/${selected?.ID_FPD || ""}`} className="btn-outline-success custom-btn">
                                <i className="bi bi-filetype-xlsx"></i> Export Excel
                            </a>
                            <a href={`http://localhost:8000/api/fpd-anggaran/export/pdf/${selected?.ID_FPD || ""}`} className="btn-outline-danger custom-btn">
                                <i className="bi bi-filetype-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div className="fpd-detail-section">
                    {selected ? (
                        <div className="fpd-detail-content">
                            <div className="fpd-detail-header">
                                <h3>Detail FPD</h3>
                                <div className={`status-pill ${status.className}`}>
                                    {status.label}
                                </div>
                            </div>
                            <div className="detail-body">
                                <div className="detail-row">
                                    <span>ID</span>
                                    <span>{selected.ID_FPD}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Program</span>
                                    <span>{selected.program_kerja?.INDIKATOR}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Tanggal</span>
                                    <span>
                                        {selected.TGL_FPD
                                            ? new Date(selected.TGL_FPD).toLocaleDateString("id-ID", {
                                                day: "numeric",
                                                month: "long",
                                                year: "numeric",
                                            })
                                            : "-"}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Anggaran</span>
                                    <span>Rp {Number(selected.NOMINAL_ANGGARAN).toLocaleString("id-ID")}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Nominal FPD</span>
                                    <span>Rp {Number(selected.NOMINAL_FPD).toLocaleString("id-ID")}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Sisa</span>
                                    <span>Rp {Number(selected.NOMINAL_SISA).toLocaleString("id-ID")}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Validator</span>
                                    <span>{selected.NIP_VALIDATOR_FPD || "-"}</span>
                                </div>
                            </div>
                            <div className="fpd-detail-footer">
                                <div className="button-group">
                                    <button
                                        className="approve-btn"
                                        onClick={handleApprove}
                                        disabled={getStatus(selected).type !== "pending"}
                                    >
                                        Setujui
                                    </button>
                                    <button
                                        className="reject-btn"
                                        onClick={handleReject}
                                        disabled={getStatus(selected).type !== "pending"}
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="empty-state">
                            <p>Pilih Data Untuk Melihat Detail Pengajuan Dana</p>
                        </div>
                    )}
                    {toast && (
                        <div className={`toast-container ${visible ? "show" : "hide"}`}>
                            <div className="toast-box">
                                <span className="toast-text">
                                    {toast.type === "error" ? (
                                        <>
                                            <span className="highlight danger">
                                                {toast.message}
                                            </span>
                                        </>
                                    ) : (
                                        <>
                                            <span className="highlight success">
                                                {toast.message}
                                            </span>
                                        </>
                                    )}
                                </span>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
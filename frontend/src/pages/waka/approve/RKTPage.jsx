import { useEffect, useState } from "react";
import "../../../styles/waka/approve/RKT.css";

export default function RKTPage({ setHasPending }) {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const [sortConfig, setSortConfig] = useState({
        key: "ID_PROGRAM_KERJA",
        direction: "desc"
    });
    const [toast, setToast] = useState(null);
    const [visible, setVisible] = useState(false);
    const [revisiText, setRevisiText] = useState("");
    const [showRevisiInput, setShowRevisiInput] = useState(false);

    const showToast = (type = "success", message = "") => {
        setToast({ type, message });
        setVisible(true);
        setTimeout(() => setVisible(false), 2500);
        setTimeout(() => setToast(null), 3000);
    };

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
            const result = json.data || [];
            setData(result);
            const hasPending = result.some(item => !item.NIP_VALIDATOR_PROGKER);
            setHasPending && setHasPending(hasPending);
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

    const handleSearch = (value) => {
        setSearch(value);
    };

    const getIcon = (key) => {
        if (sortConfig.key !== key) return "bi bi-funnel";
        return sortConfig.direction === "asc"
            ? "bi bi-funnel-fill"
            : "bi bi-funnel-fill";
    };

    const statusOrder = {
        "Disetujui": 1,
        "Revisi": 2,
        "Pending": 3,
        "Ditolak": 4
    };

    const getStatus = (item) => {
        const lastPm = item.tr_pm?.length
            ? item.tr_pm[item.tr_pm.length - 1]
            : null;
        const note = lastPm?.DESKRIPSI_TR_PM?.toLowerCase() || "";
        if (note.includes("ditolak")) {
            return { label: "Ditolak", className: "rejected" };
        }
        if (note.includes("revisi")) {
            return { label: "Revisi", className: "revisi" };
        }
        if (item.NIP_VALIDATOR_PROGKER) {
            return { label: "Disetujui", className: "approved" };
        }
        return { label: "Pending", className: "pending" };
    };

    const sortedData = [...data].sort((a, b) => {
        let valA, valB;
        if (sortConfig.key === "status") {
            const statusA = getStatus(a).label;
            const statusB = getStatus(b).label;
            valA = statusOrder[statusA] || 99;
            valB = statusOrder[statusB] || 99;
        } 
        else if (sortConfig.key === "tahun_anggaran") {
            valA = a.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "";
            valB = b.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "";
        } 
        else if (sortConfig.key === "NOMINAL") {
            valA = Number(a.NOMINAL || 0);
            valB = Number(b.NOMINAL || 0);
        } 
        else {
            valA = a[sortConfig.key] ?? "";
            valB = b[sortConfig.key] ?? "";
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
        try {
            const res = await fetch(
                `http://localhost:8000/api/rkt/approve/${selected.ID_PROGRAM_KERJA}`,
                {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        NIP_VALIDATOR_PROGKER: user.NIP_KARYAWAN
                    })
                }
            );
            if (res.ok) {
                showToast("success", "Program Kerja Berhasil Disetujui");
                fetchData();
                setSelected(null);
            } else {
                const err = await res.json();
                showToast("error", err.message || "Gagal Menyetujui Program Kerja");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Koneksi gagal");
        }
    };

    const isDisabled = (item) => {
        const status = getStatus(item).label;
        return status === "Disetujui" || status === "Ditolak" || status === "Revisi";
    };

    const handleReject = async () => {
        if (!selected) return;
        const userData = localStorage.getItem("user");
        if (!userData) {
            showToast("error", "Sesi login tidak ditemukan");
            return;
        }
        const user = JSON.parse(userData);
        try {
            const res = await fetch(
                `http://localhost:8000/api/rkt/reject/${selected.ID_PROGRAM_KERJA}`,
                {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        NIP_VALIDATOR_PM: user.NIP_KARYAWAN,
                        DESKRIPSI: "Ditolak"
                    })
                }
            );
            const json = await res.json();
            if (res.ok) {
                showToast("success", "Program Kerja Ditolak");
                fetchData();
                setSelected(null);
            } else {
                showToast("error", json.message || "Gagal menolak");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Koneksi gagal");
        }
    };

    const handleRevisi = async () => {
        if (!selected) return;
        const userData = localStorage.getItem("user");
        if (!userData) {
            showToast("error", "Sesi login tidak ditemukan");
            return;
        }
        const user = JSON.parse(userData);
        if (!revisiText.trim()) {
            showToast("error", "Alasan revisi wajib diisi");
            return;
        }
        try {
            const res = await fetch(
                `http://localhost:8000/api/rkt/revisi/${selected.ID_PROGRAM_KERJA}`,
                {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        NIP_VALIDATOR_PM: user.NIP_KARYAWAN,
                        DESKRIPSI: `Revisi: ${revisiText}`
                    })
                }
            );
            const json = await res.json();
            if (res.ok) {
                showToast("success", "Berhasil mengajukan revisi");
                fetchData();
                setSelected(null);
                setRevisiText("");
                setShowRevisiInput(false);
            } else {
                showToast("error", json.message || "Gagal revisi");
            }
        } catch (err) {
            console.error(err);
            showToast("error", "Koneksi gagal");
        }
    };

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
                            placeholder="Cari..."
                            value={search}
                            onKeyDown={handleKeyDown}
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
            <div className="rkt-approval-grid">
                <div className="rkt-table-section">
                    <div className="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th onClick={() => handleSort("ID_PROGRAM_KERJA")}>
                                        ID <i className={getIcon("ID_PROGRAM_KERJA")}></i>
                                    </th>
                                    <th onClick={() => handleSort("PROGRAM_KERJA")}>
                                        Program <i className={getIcon("PROGRAM_KERJA")}></i>
                                    </th>
                                    <th onClick={() => handleSort("tahun_anggaran")}>
                                        Tahun <i className={getIcon("tahun_anggaran")}></i>
                                    </th>
                                    <th onClick={() => handleSort("NOMINAL")}>
                                        Anggaran <i className={getIcon("NOMINAL")}></i>
                                    </th>
                                    <th onClick={() => handleSort("status")}>
                                        Status <i className={getIcon("status")}></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {currentData.map((item) => (
                                    <tr
                                        key={item.ID_PROGRAM_KERJA}
                                        onClick={() => setSelected(item)}
                                        className={
                                            selected?.ID_PROGRAM_KERJA === item.ID_PROGRAM_KERJA
                                                ? "active-row"
                                                : ""
                                        }
                                    >
                                        <td>{item.ID_PROGRAM_KERJA}</td>
                                        <td>{item.PROGRAM_KERJA}</td>
                                        <td>{item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN}</td>
                                        <td>
                                            Rp {Number(item.NOMINAL || 0).toLocaleString("id-ID")}
                                        </td>
                                        <td>
                                            {(() => {
                                                const status = getStatus(item);
                                                if (status.label === "Pending") {
                                                    return (
                                                        <i className="bi bi-exclamation-circle-fill icon-danger icon-warning-animate"></i>
                                                    );
                                                }
                                                return (
                                                    <span className={`status ${status.className}`}>
                                                        {status.label}
                                                    </span>
                                                );
                                            })()}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
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
                                <a href={`http://localhost:8000/api/rkt/export/excel/${selected?.ID_PROGRAM_KERJA || ""}`} className="btn-outline-success custom-btn-excel">
                                    <i className="bi bi-filetype-xlsx"></i> Export Excel
                                </a>
                                <a href={`http://localhost:8000/api/rkt/export/pdf/${selected?.ID_PROGRAM_KERJA || ""}`} className="btn-outline-success custom-btn-pdf">
                                    <i className="bi bi-filetype-pdf"></i> Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="rkt-detail-section">
                    {selected ? (
                        <>
                            <div className="detail-header">
                                <h3>Detail Program</h3>
                            </div>
                            <div className="detail-content">
                                <div className="detail-row">
                                    <span>ID Program Kerja</span>
                                    <span>{selected.ID_PROGRAM_KERJA}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Program Kerja</span>
                                    <span>{selected.PROGRAM_KERJA}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Tahun Anggaran</span>
                                    <span>{selected.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Unit</span>
                                    <span>{selected.unit?.NAMA_UNIT}</span>
                                </div>
                                <div className="detail-row">
                                    <span>COA</span>
                                    <span>{selected.coa?.DESKRIPSI_COA}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Kegiatan</span>
                                    <span>{selected.kegiatan?.DESKRIPSI_KEGIATAN}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Nominal</span>
                                    <span>Rp {selected.NOMINAL}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Sasaran</span>
                                    <span>{selected.SASARAN}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Indikator</span>
                                    <span>{selected.INDIKATOR}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Keluaran</span>
                                    <span>{selected.KELUARAN_PROGKER}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Waktu</span>
                                    <span>
                                        {new Date(selected.WAKTU_AWAL).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}
                                        - 
                                        {new Date(selected.WAKTU_AKHIR).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Penanggung Jawab</span>
                                    <span>{selected.NIP_PENANGGUNG_JAWAB || "-"}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Validator</span>
                                    <span>{selected.NIP_VALIDATOR_PROGKER || "-"}</span>
                                </div>
                            </div>
                            <div className="rkt-detail-footer">
                                {showRevisiInput && (
                                    <div className="revisi-input-wrapper">
                                        <textarea
                                            placeholder="Masukkan alasan revisi..."
                                            value={revisiText}
                                            onChange={(e) => setRevisiText(e.target.value)}
                                            className="revisi-textarea"
                                        />
                                    </div>
                                )}
                                <div className="button-group">
                                    <button
                                        className="approve-btn"
                                        onClick={handleApprove}
                                        disabled={!selected || isDisabled(selected)}
                                    >
                                        Setujui
                                    </button>
                                    <button
                                        className="revisi-btn"
                                        onClick={() => setShowRevisiInput(!showRevisiInput)}
                                        disabled={!selected || isDisabled(selected)}
                                    >
                                        Ajukan Revisi
                                    </button>
                                    <button
                                        className="reject-btn"
                                        onClick={handleReject}
                                        disabled={!selected || isDisabled(selected)}
                                    >
                                        Tolak
                                    </button>
                                </div>
                                {showRevisiInput && (
                                    <button
                                        className="revisi-submit-btn"
                                        onClick={handleRevisi}
                                        disabled={!revisiText.trim()}
                                    >
                                        Kirim Revisi
                                    </button>
                                )}
                            </div>
                        </>
                    ) : (
                        <div className="empty">
                            <p>Pilih Salah Satu Baris Untuk Melihat Detail Program Kerja</p>
                        </div>
                    )}
                </div>
            </div>
            {toast && (
                <div className={`toast-container ${visible ? "show" : "hide"}`}>
                    <div className="toast-box">
                        <span className="toast-text">
                            {toast.message}
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
}
import { useEffect, useState } from "react";
import "../../styles/PM/ApprovalCenter.css";

export default function VerifikasiEvaluasiRKT({ setHasPending }) {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const [statusFilter, setStatusFilter] = useState("default");

    const itemsPerPage = 10;

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/evaluasi-rkt");
            const json = await res.json();
            const result = json.data || [];
            setData(result);
            const hasPending = result.some(
                (item) => getStatus(item).type === "pending"
            );
            setHasPending && setHasPending(hasPending);
        } catch (err) {
            console.error(err);
        }
    };

    const handleSearch = async (value) => {
        setSearch(value);
        try {
            const res = await fetch(
                `http://localhost:8000/api/evaluasi-rkt/search?keyword=${value}`
            );
            const json = await res.json();
            const result = json.data || [];
            setData(result);
            const hasPending = result.some(
                (item) => getStatus(item).type === "pending"
            );
            setHasPending && setHasPending(hasPending);
            setCurrentPage(1);
        } catch (err) {
            console.error(err);
        }
    };
    const getStatus = (item) => {
        const validator = item.NIP_VALIDATOR_PM;

        if (!validator) {
            return {
                type: "pending",
                label: "Menunggu Validasi"
            };
        }

        if (validator === "Ditolak") {
            return {
                type: "rejected",
                label: "Ditolak"
            };
        }

        const jabatan =
            item.validator?.jabatan?.ref_jabatan?.DESKRIPSI_JABATAN;

        if (jabatan?.toLowerCase() === "bendahara") {
            return {
                type: "pending",
                label: "Pending Kepsek"
            };
        }

        return {
            type: "approved",
            label: "Disetujui"
        };
    };

    const filteredData = data.filter((item) => {
        if (statusFilter === "default") return true;
        return getStatus(item).type === statusFilter;
    });

    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;

    const currentData = filteredData.slice(indexOfFirst, indexOfLast);

    const totalPages = Math.ceil(filteredData.length / itemsPerPage);

    const handleApprove = async () => {
        if (!selected) return;
        const user = JSON.parse(localStorage.getItem("user"));
        try {
            const res = await fetch(
                `http://localhost:8000/api/evaluasi-rkt/approve/${selected.ID_PM}`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        NIP_VALIDATOR_PM: user.NIP_KARYAWAN
                    })
                }
            );
            if (res.ok) {
                alert("Evaluasi berhasil disetujui");
                fetchData();
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleReject = async () => {
        if (!selected) return;
        try {
            const res = await fetch(
                `http://localhost:8000/api/evaluasi-rkt/reject/${selected.ID_PM}`,
                {
                    method: "PUT"
                }
            );
            if (res.ok) {
                alert("Evaluasi berhasil ditolak");
                fetchData();
            }
        } catch (err) {
            console.error(err);
        }
    };

    return (
        <div className="fpd-container">
            <div className="fpd-header">
                <div className="status-filter">
                    <span>Filter Status :</span>
                    <div className="custom-dropdown">
                        <button className="dropdown-btn">
                            {statusFilter === "default" && "Semua"}
                            {statusFilter === "waiting-bendahara" &&
                                "Menunggu Tim PM"}
                            {statusFilter === "pending" &&
                                "Pending Kepsek"}
                            {statusFilter === "approved" &&
                                "Disetujui"}
                            {statusFilter === "rejected" &&
                                "Ditolak"}
                            <i className="bi bi-chevron-down"></i>
                        </button>
                        <div className="dropdown-menu">
                            <div onClick={() => setStatusFilter("default")}>
                                Semua
                            </div>
                            <div
                                onClick={() =>
                                    setStatusFilter("waiting-bendahara")
                                }
                            >
                                Menunggu Tim PM
                            </div>
                            <div onClick={() => setStatusFilter("pending")}>
                                Pending Kepsek
                            </div>
                            <div onClick={() => setStatusFilter("approved")}>
                                Disetujui
                            </div>
                            <div onClick={() => setStatusFilter("rejected")}>
                                Ditolak
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
                            type="text"
                            className="search-input"
                            placeholder="Cari evaluasi..."
                            value={search}
                            onChange={(e) =>
                                handleSearch(e.target.value)
                            }
                        />
                        <button
                            className="search-btn"
                            onClick={() => handleSearch(search)}
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
                                    <th>ID</th>
                                    <th>Program Kerja</th>
                                    <th>Status PM</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {currentData.map((item) => {
                                    const status = getStatus(item);
                                    return (
                                        <tr
                                            key={item.ID_PM}
                                            onClick={() => setSelected(item)}
                                            className={
                                                selected?.ID_PM === item.ID_PM
                                                    ? "active-row"
                                                    : ""
                                            }
                                        >
                                            <td>{item.ID_PM}</td>
                                            <td>
                                                {item.program_kerja
                                                    ?.PROGRAM_KERJA || "-"}
                                            </td>
                                            <td>
                                                {item.ref_pm?.NAMA_PM || "-"}
                                            </td>
                                            <td>
                                                {new Date(
                                                    item.TGL_PM
                                                ).toLocaleDateString("id-ID")}
                                            </td>
                                            <td>
                                                {status.type === "pending" ? (
                                                    <i className="bi bi-exclamation-circle-fill icon-danger icon-warning-animate"></i>
                                                ) : (
                                                    <span className={`status ${status.type}`}>
                                                        {status.label}
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    <div className="pagination-wrapper">
                        <div className="pagination-info">
                            Menampilkan {indexOfFirst + 1} -
                            {Math.min(indexOfLast, filteredData.length)}
                            dari {filteredData.length} data
                        </div>
                        <div className="pagination">
                            <button
                                className="page-btn"
                                disabled={currentPage === 1}
                                onClick={() =>
                                    setCurrentPage((prev) => prev - 1)
                                }
                            >
                                <i className="bi bi-chevron-left"></i>
                            </button>
                            {Array.from(
                                { length: totalPages },
                                (_, i) => (
                                    <button
                                        key={i + 1}
                                        className={`page-btn ${
                                            currentPage === i + 1
                                                ? "active"
                                                : ""
                                        }`}
                                        onClick={() =>
                                            setCurrentPage(i + 1)
                                        }
                                    >
                                        {i + 1}
                                    </button>
                                )
                            )}
                            <button
                                className="page-btn"
                                disabled={currentPage === totalPages}
                                onClick={() =>
                                    setCurrentPage((prev) => prev + 1)
                                }
                            >
                                <i className="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div className="fpd-detail-section">
                    {selected ? (
                        <div className="fpd-detail-content">
                            <div className="fpd-detail-header">
                                <h3>Detail Evaluasi</h3>
                            </div>
                            <div className="detail-body">
                                <div className="detail-row">
                                    <span>ID</span>
                                    <span>{selected.ID_PM}</span>
                                </div>
                                <div className="detail-row">
                                    <span>Program Kerja</span>
                                    <span>
                                        {selected.program_kerja
                                            ?.PROGRAM_KERJA || "-"}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Status PM</span>
                                    <span>
                                        {selected.ref_pm?.NAMA_PM || "-"}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Tanggal</span>
                                    <span>
                                        {new Date(
                                            selected.TGL_PM
                                        ).toLocaleDateString("id-ID")}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Deskripsi</span>
                                    <span>
                                        {selected.DESKRIPSI_TR_PM || "-"}
                                    </span>
                                </div>
                                <div className="detail-row">
                                    <span>Validator</span>
                                    <span>
                                        {selected.NIP_VALIDATOR_PM || "Menunggu Validasi Tim PM"}
                                    </span>
                                </div>
                            </div>
                            <div className="fpd-detail-footer">
                                <div className="button-group">
                                    <button
                                        className="approve-btn"
                                        onClick={handleApprove}
                                        disabled={
                                            getStatus(selected).type !==
                                            "pending"
                                        }
                                    >
                                        Setujui
                                    </button>
                                    <button
                                        className="reject-btn"
                                        onClick={handleReject}
                                        disabled={
                                            getStatus(selected).type !==
                                            "pending"
                                        }
                                    >
                                        Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="empty-state">
                            <p>Pilih evaluasi untuk melihat detail</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
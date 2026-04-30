import { useEffect, useState } from "react";
import "../../styles/yayasan/ApprovalCenter.css";

export default function ApprovalCenter() {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const totalPages = Math.ceil(data.length / itemsPerPage);
    const totalData = data.length;
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);
    const currentData = data.slice(indexOfFirst, indexOfLast);
    const [search, setSearch] = useState("");
    const [sortConfig, setSortConfig] = useState({ 
        key: "ID_PROGRAM_KERJA", 
        direction: "asc" 
    });

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const res = await fetch("http://localhost:8000/api/rkt");
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

    const getIcon = (key) => {
        if (sortConfig.key !== key) return "bi bi-funnel";
        return "bi bi-funnel-fill";
    };

    const sortedData = [...data]
        .filter((item) =>
            item.PROGRAM_KERJA?.toLowerCase().includes(search.toLowerCase()) || 
            (item.TAHUN + "").includes(search) ||
            (item.ANGGARAN)
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

    const handleApprove = async () => {
        if (!selected) return;
        const userData = localStorage.getItem("user");
        if (!userData) {
            alert("Login dulu!");
            return;
        }
        const user = JSON.parse(userData);
        const nip = user.NIP_KARYAWAN;
        try {
            const res = await fetch(
                `http://localhost:8000/api/rkt/approve/${selected.ID_PROGRAM_KERJA}`,
                {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        NIP_VALIDATOR_PROGKER: nip
                    })
                }
            );
            if (res.ok) {
                alert("Berhasil disetujui!");
                fetchData();
                setSelected(null);
            } else {
                const err = await res.json();
                alert(err.message);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const handleReject = async () => {
        if (!selected) return;
        try {
            const res = await fetch(`http://localhost:8000/api/rkt/reject/${selected.ID_PROGRAM_KERJA}`, {method: "PUT"});
            if (res.ok) {
                alert("Program kerja ditolak!");
                fetchData();
                setSelected(null);
            }
        } catch (err) {
            console.error(err);
        }
    };

    return (
        <div className="approval-container">
            <h2>Approval Program Kerja (RKT)</h2>
            <div className="approval-grid">
                <div className="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Program Kerja</th>
                                <th>Tahun</th>
                                <th>Anggaran</th>
                                <th>Status</th>
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
                                    <td>{item.PROGRAM_KERJA || "-"}</td>
                                    <td>{item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN}</td>
                                    <td>
                                        Rp {Number(item.TOTAL_PROGKER || 0).toLocaleString("id-ID")}
                                    </td>
                                    <td>
                                        {item.STATUS_APPROVAL === "Ditolak" ? (
                                            <span className="status rejected">Ditolak</span>
                                        ) : item.STATUS_APPROVAL === "Disetujui" ? (
                                            <span className="status approved">Disetujui</span>
                                        ) : (
                                            <span className="status pending">Pending</span>
                                        )}
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
                                    onClick={() => setCurrentPage(i + 1)}
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
                <div className="detail-section">
                    {selected ? (
                        <>
                            <h3>Detail Program</h3>
                            <div className="detail-row">
                                <span>ID</span>
                                <span>{selected.ID_PROGRAM_KERJA}</span>
                            </div>
                            <div className="detail-row">
                                <span>Program Kerja</span>
                                <span>{selected.PROGRAM_KERJA}</span>
                            </div>
                            <div className="detail-row">
                                <span>Tahun</span>
                                <span>{selected.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN}</span>
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
                                <span>Indikator Program</span>
                                <span>{selected.INDIKATOR}</span>
                            </div>
                            <div className="detail-row">
                                <span>Sasaran</span>
                                <span>{selected.SASARAN}</span>
                            </div>
                            <div className="detail-row">
                                <span>Keluaran</span>
                                <span>{selected.KELUARAN_PROGKER}</span>
                            </div>
                            <div className="detail-row">
                                <span>Anggaran</span>
                                <span>
                                    Rp {Number(selected.TOTAL_PROGKER || 0).toLocaleString("id-ID")}
                                </span>
                            </div>
                            <div className="detail-row">
                                <span>Penanggung Jawab</span>
                                <span>{selected.NIP_PENANGGUNG_JAWAB}</span>
                            </div>
                            <div className="detail-row">
                                <span>Validator</span>
                                <span>{selected.NIP_VALIDATOR_PROGKER || "-"}</span>
                            </div>
                            <div className="detail-row">
                                <span>Status</span>
                                <span>{selected.STATUS_APPROVAL || "Pending"}</span>
                            </div>
                            <div className="action-buttons">
                                <button
                                    className="approve-btn"
                                    onClick={handleApprove}
                                    disabled={
                                        selected?.STATUS_APPROVAL === "Disetujui" ||
                                        selected?.STATUS_APPROVAL === "Ditolak"
                                    }
                                >
                                    Setujui
                                </button>
                                <button
                                    className="reject-btn"
                                    onClick={handleReject}
                                    disabled={
                                        selected?.STATUS_APPROVAL === "Disetujui" ||
                                        selected?.STATUS_APPROVAL === "Ditolak"
                                    }
                                >
                                    Tolak
                                </button>
                            </div>
                        </>
                    ) : (
                        <div className="empty">
                            <p>Pilih program kerja untuk melihat detail</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
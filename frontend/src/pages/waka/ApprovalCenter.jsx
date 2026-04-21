import { useEffect, useState } from "react";
import "../../styles/waka/ApprovalCenter.css";

export default function ApprovalCenter() {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [search, setSearch] = useState("");
    const [currentPage, setCurrentPage] = useState(1);

    const itemsPerPage = 10;
    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;

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
            setData(json.data || []);
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

    const sortedData = [...data].sort((a, b) => {
        let valA = a[sortConfig.key];
        let valB = b[sortConfig.key];

        if (sortConfig.key === "PROGRAM") {
            valA = a.programKerja?.PROGRAM_KERJA || "";
            valB = b.programKerja?.PROGRAM_KERJA || "";
        }

        if (sortConfig.key === "TGL_FPD") {
            valA = new Date(a.TGL_FPD);
            valB = new Date(b.TGL_FPD);
        }

        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });

    const currentData = sortedData.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);

    const handleApprove = async () => {
        if (!selected) return;

        const user = JSON.parse(localStorage.getItem("user"));
        const nip = user?.NIP_KARYAWAN;

        try {
            const res = await fetch(`http://localhost:8000/api/fpd-anggaran/update/${selected.ID_FPD}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    ...selected,
                    NIP_VALIDATOR_FPD: nip
                })
            });

            if (res.ok) {
                alert("FPD berhasil disetujui");
                fetchData();
                setSelected(null);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const isApproved = selected?.NIP_VALIDATOR_FPD;

    return (
        <div className="container">
            <h2>Approval FPD Anggaran</h2>
            <div className="top-bar">
                <input
                    type="text"
                    placeholder="Cari..."
                    value={search}
                    onChange={(e) => handleSearch(e.target.value)}
                />
                <div className="export-wrapper">
                    <a
                        href={`http://localhost:8000/api/fpd-anggaran/export/${selected?.ID_FPD || ""}`}
                        className="btn-outline-success custom-btn"
                    >
                        <i className="bi bi-filetype-csv"></i> Export CSV
                    </a>
                </div>
            </div>
            <div className="grid">
                <div className="table-section">
                    <div className="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th onClick={() => handleSort("ID_FPD")}>ID</th>
                                    <th onClick={() => handleSort("PROGRAM")}>Program</th>
                                    <th onClick={() => handleSort("TGL_FPD")}>Tanggal</th>
                                    <th onClick={() => handleSort("NOMINAL_ANGGARAN")}>Anggaran</th>
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
                                        <td>{item.programKerja?.PROGRAM_KERJA}</td>
                                        <td>
                                            {new Date(item.TGL_FPD).toLocaleDateString("id-ID")}
                                        </td>
                                        <td>
                                            Rp {Number(item.NOMINAL_ANGGARAN).toLocaleString("id-ID")}
                                        </td>
                                        <td>
                                            {item.NIP_VALIDATOR_FPD ? (
                                                <span className="status-ok"><i className="bi bi-check-circle"></i></span>
                                            ) : (
                                                <span className="status-wait"><i className="bi bi-exclamation-circle"></i></span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="pagination">
                        {Array.from({ length: totalPages }, (_, i) => (
                            <button
                                key={i}
                                onClick={() => setCurrentPage(i + 1)}
                                className={currentPage === i + 1 ? "active" : ""}
                            >
                                {i + 1}
                            </button>
                        ))}
                    </div>
                </div>
                <div className="detail-section">
                    {selected ? (
                        <div className="detail-content">
                            <h3>Detail FPD</h3>
                            <div className={`status-pill ${isApproved ? "success" : "danger"}`}>
                                {isApproved ? "Disetujui" : "Menunggu"}
                            </div>
                            <div className="detail-row">
                                <span>ID</span>
                                <span>{selected.ID_FPD}</span>
                            </div>
                            <div className="detail-row">
                                <span>Program</span>
                                <span>{selected.programKerja?.PROGRAM_KERJA}</span>
                            </div>
                            <div className="detail-row">
                                <span>Anggaran</span>
                                <span>Rp {Number(selected.NOMINAL_ANGGARAN).toLocaleString("id-ID")}</span>
                            </div>
                            <div className="detail-row">
                                <span>Total FPD</span>
                                <span>Rp {Number(selected.NOMINAL_FPD).toLocaleString("id-ID")}</span>
                            </div>
                            <div className="detail-row">
                                <span>Sisa</span>
                                <span>Rp {Number(selected.NOMINAL_SISA).toLocaleString("id-ID")}</span>
                            </div>
                            <h4>Rincian</h4>
                            <div className="detail-list">
                                {selected.detail_fpd?.map((d) => (
                                    <div key={d.ID_DT_FPD} className="detail-item">
                                        <div>{d.detail_program?.program_kerja?.PROGRAM_KERJA}</div>
                                        <div>Rp {Number(d.TOTAL).toLocaleString("id-ID")}</div>
                                    </div>
                                ))}
                            </div>
                            <button
                                className="btn-approve"
                                onClick={handleApprove}
                                disabled={isApproved}
                            >
                                Setujui
                            </button>
                        </div>
                    ) : (
                        <div className="empty-state">
                            <p>Pilih data untuk melihat detail</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
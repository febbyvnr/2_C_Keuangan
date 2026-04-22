import { useEffect, useState } from "react";
import "../../styles/bendahara/Verifikasi.css";

export default function Verifikasi() {
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
    const [sortConfig, setSortConfig] = useState({
        key: "ID_PEMBAYARAN",
        direction: "asc"
    });

    const handleSort = (key) => {
        let direction = "asc";
        if (sortConfig.key === key && sortConfig.direction === "asc") {
            direction = "desc";
        }
        setSortConfig({ key, direction });
        setCurrentPage(1);
    };

    const getIcon = (key) => {
        if (sortConfig.key !== key) return "bi bi-funnel";
        return sortConfig.direction === "asc"
            ? "bi bi-funnel-fill"
            : "bi bi-funnel-fill";
    };

    const sortedData = [...data].sort((a, b) => {
        let valA, valB;
        switch (sortConfig.key) {
            case "siswa":
                valA = a.siswa?.NAMA_SISWA_TETAP || "";
                valB = b.siswa?.NAMA_SISWA_TETAP || "";
                break;
            case "ta":
                valA = a.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "";
                valB = b.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || "";
                break;
            case "jenis":
                const order = { "Bank": 1, "Tunai": 2 };
                valA = order[a.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN] || 99;
                valB = order[b.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN] || 99;
                break;
            case "bulan":
                const bulanOrder = {
                    "Januari": 1,
                    "Februari": 2,
                    "Maret": 3,
                    "April": 4,
                    "Mei": 5,
                    "Juni": 6,
                    "Juli": 7,
                    "Agustus": 8,
                    "September": 9,
                    "Oktober": 10,
                    "November": 11,
                    "Desember": 12
                };
                valA = bulanOrder[a.tagihan?.BULAN_TAGIHAN_SISWA] || 0;
                valB = bulanOrder[b.tagihan?.BULAN_TAGIHAN_SISWA] || 0;
                break;
            case "tanggal":
                valA = new Date(a.TGL_BAYAR);
                valB = new Date(b.TGL_BAYAR);
                break;
            case "jumlah":
                valA = a.JUMLAH_BAYAR;
                valB = b.JUMLAH_BAYAR;
                break;
            case "aksi":
                valA = a.NIP_VALIDATOR_PEMBAYARAN ? 1 : 0;
                valB = b.NIP_VALIDATOR_PEMBAYARAN ? 1 : 0;
                break;
            default:
                valA = a[sortConfig.key];
                valB = b[sortConfig.key];
        }
        if (valA < valB) return sortConfig.direction === "asc" ? -1 : 1;
        if (valA > valB) return sortConfig.direction === "asc" ? 1 : -1;
        return 0;
    });
    const currentData = sortedData.slice(indexOfFirst, indexOfLast);
    

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
        const res = await fetch("http://localhost:8000/api/tr-pembayaran");
        const json = await res.json();
            setData(json.data || []);
        } catch (err) {
            console.error(err);
        }
    };

    const handleApprove = async () => {
        if (!selected) return;
        const userData = localStorage.getItem("user"); 
        if (!userData) {
            alert("Sesi login tidak ditemukan. Silakan login kembali.");
            return;
        }
        const user = JSON.parse(userData);
        const nipBendahara = user.NIP_KARYAWAN;
        if (!nipBendahara) {
            alert("Data NIP tidak ditemukan pada akun Anda.");
            return;
        }
        try {
            const response = await fetch(`http://localhost:8000/api/tr-pembayaran/update/${selected.ID_PEMBAYARAN}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    ...selected,
                    NIP_VALIDATOR_PEMBAYARAN: nipBendahara 
                })
            });
            if (response.ok) {
                alert("Pembayaran berhasil diverifikasi!");
                fetchData();
                setSelected(null);
            } else {
                const errorData = await response.json();
                alert("Gagal verifikasi: " + (errorData.message || "Terjadi kesalahan"));
            }
        } catch (err) {
            console.error("Error update:", err);
            alert("Terjadi kesalahan koneksi ke server.");
        }
    };

    const isVerified = selected?.NIP_VALIDATOR_PEMBAYARAN;

    return (
        <div className="container">
            <h2>Verifikasi Pembayaran Siswa</h2>
            <div className="grid">
                <div className="table-section">
                    <div className="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th onClick={() => handleSort("ID_PEMBAYARAN")}>
                                        ID <i className={getIcon("ID_PEMBAYARAN")}></i>
                                    </th>
                                    <th onClick={() => handleSort("siswa")}>
                                        Siswa <i className={getIcon("siswa")}></i>
                                    </th>
                                    <th onClick={() => handleSort("ta")}>
                                        TA <i className={getIcon("ta")}></i>
                                    </th>
                                    <th onClick={() => handleSort("bulan")}>
                                        Bulan <i className={getIcon("bulan")}></i>
                                    </th>
                                    <th onClick={() => handleSort("jenis")}>
                                        Jenis <i className={getIcon("jenis")}></i>
                                    </th>
                                    <th onClick={() => handleSort("tanggal")}>
                                        Tanggal <i className={getIcon("tanggal")}></i>
                                    </th>
                                    <th onClick={() => handleSort("jumlah")}>
                                        Jumlah <i className={getIcon("jumlah")}></i>
                                    </th>
                                    <th onClick={() => handleSort("aksi")}>
                                        Status <i className={getIcon("aksi")}></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {currentData.map((item) => (
                                    <tr
                                        key={item.ID_PEMBAYARAN}
                                        onClick={() => setSelected(item)}
                                        className={selected?.ID_PEMBAYARAN === item.ID_PEMBAYARAN ? "active-row" : ""}
                                    >
                                        <td>{item.ID_PEMBAYARAN}</td>
                                        <td>{item.siswa?.NAMA_SISWA_TETAP || '-'}</td>
                                        <td>{item.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN || '-'}</td>
                                        <td>{item.tagihan?.BULAN_TAGIHAN_SISWA || "-"}</td>
                                        <td>{item.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN || "-"}</td>
                                        <td>
                                            {new Date(item.TGL_BAYAR).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}
                                        </td>
                                        <td>Rp {Number(item.JUMLAH_BAYAR).toLocaleString("id-ID")}</td>
                                        <td>
                                            {item.NIP_VALIDATOR_PEMBAYARAN ? (
                                                <span title={`Divalidasi oleh: ${item.NIP_VALIDATOR_PEMBAYARAN}`}>
                                                    <i className="bi bi-check-circle icon-success"></i>
                                                </span>
                                            ) : (
                                                <i className="bi bi-exclamation-circle-fill icon-danger"></i>
                                            )}
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
                        <div className="detail-content">
                            <h3>Rincian Pembayaran</h3>
                            <div className={`status-pill ${isVerified ? "success" : "danger"}`}>
                                {isVerified ? "Diverifikasi" : "Menunggu Verifikasi"}
                            </div>
                            <div className="detail-row">
                                <span className="label">Total Tagihan</span>
                                <span className="value">Rp {Number(selected.JUMLAH_BAYAR).toLocaleString("id-ID")}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">ID Pembayaran</span>
                                <span className="value">{selected.ID_PEMBAYARAN}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">Siswa</span>
                                <span className="value">{selected.siswa?.NAMA_SISWA_TETAP || "-"}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">Jenis</span>
                                <span className="value">{selected.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">Tahun Anggaran</span>
                                <span className="value">{selected.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">Bulan Tagihan</span>
                                <span className="value">{selected.tagihan?.BULAN_TAGIHAN_SISWA}</span>
                            </div>
                            <div className="detail-row">
                                <span className="label">Tanggal</span>
                                <span className="value">{new Date(selected.TGL_BAYAR).toLocaleDateString("id-ID", {day: "numeric", month: "long", year: "numeric"})}</span>
                            </div>
                            <div className="bukti">
                                <div className="bukti-header">
                                    <p><b>Bukti Pembayaran</b></p>
                                    <a 
                                        href={selected.LINK_BUKTI_BAYAR} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="btn-open"
                                    >
                                        {selected.LINK_BUKTI_BAYAR}
                                    </a>
                                </div>
                                {selected.LINK_BUKTI_BAYAR?.includes(".pdf") ? (
                                    <iframe
                                        src={selected.LINK_BUKTI_BAYAR}
                                        title="PDF Viewer"
                                    />
                                ) : (
                                    <img 
                                        src={selected.LINK_BUKTI_BAYAR} 
                                        alt="Bukti Pembayaran" 
                                    />
                                )}
                            </div>
                            <div className="detail-item">
                                <b>Status:</b> {isVerified ? "Sudah Diverifikasi" : "Belum Diverifikasi"}
                            </div>
                            <button
                                className="btn-approve"
                                onClick={handleApprove}
                                disabled={isVerified}
                            >
                                <i className="check-circle"></i> Setujui
                            </button>
                        </div>
                    ) : (
                        <div className="empty-state">
                            <i className="bi bi-receipt"></i>
                            <p>Klik salah satu baris untuk menampilkan rincian pembayaran siswa</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
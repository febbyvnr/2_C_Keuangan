import { useEffect, useState } from "react";
import "../../styles/bendahara/Persetujuan.css";

export default function Persetujuan() {
    const [data, setData] = useState([]);
    const [selected, setSelected] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 15;
    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentData = data.slice(indexOfFirst, indexOfLast);
    const totalPages = Math.ceil(data.length / itemsPerPage);

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
        await fetch(`http://localhost:8000/api/tr-pembayaran/${selected.ID_PEMBAYARAN}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ...selected,
                NIP_VALIDATOR_PEMBAYARAN: "VALID"
            })
        });
        fetchData();
        setSelected(null);
    };

    const isVerified = selected?.NIP_VALIDATOR_PEMBAYARAN;

    return (
        <div className="container">
            <h2>Verifikasi Pembayaran Siswa</h2>
            <div className={`grid ${!selected ? "single" : ""}`}>
                <div className="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Siswa</th>
                                <th>TA</th>
                                <th>Jenis</th>
                                <th>Tagihan</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
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
                                    <td>{item.ID_SISWA_TETAP}</td>
                                    <td>{item.KODE_TA}</td>
                                    <td>{item.ID_JENIS_PEMBAYARAN}</td>
                                    <td>{item.ID_TAGIHAN_SISWA}</td>
                                    <td>{item.TGL_BAYAR}</td>
                                    <td>Rp {Number(item.JUMLAH_BAYAR).toLocaleString()}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <div className="pagination">
                        <button
                            onClick={() => setCurrentPage(prev => prev - 1)}
                            disabled={currentPage === 1}
                        >
                            <i className="bi bi-arrow-left"></i>
                        </button>
                        <span style={{ margin: "0 10px" }}>
                            Page {currentPage} / {totalPages}
                        </span>
                        <button
                            onClick={() => setCurrentPage(prev => prev + 1)}
                            disabled={currentPage === totalPages}
                        >
                            <i className="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <div className="detail-section">
                    {selected ? (
                        <>
                        <h3>Rincian Pembayaran</h3>
                        <div className={`status-pill ${isVerified ? "success" : "danger"}`}>
                            {isVerified ? "Diverifikasi" : "Menunggu Verifikasi"}
                        </div>
                        <div className="detail-item"><b>Total Tagihan:</b> Rp {selected.JUMLAH_BAYAR}</div>
                        <div className="detail-item"><b>ID Pembayaran:</b> {selected.ID_PEMBAYARAN}</div>
                        <div className="detail-item"><b>Siswa:</b> {selected.ID_SISWA_TETAP}</div>
                        <div className="detail-item"><b>Jenis:</b> {selected.ID_JENIS_PEMBAYARAN}</div>
                        <div className="detail-item"><b>Tanggal:</b> {selected.TGL_BAYAR}</div>
                        <div className="bukti">
                            <p><b>Bukti Pembayaran:</b></p>
                            {selected.LINK_BUKTI_BAYAR?.includes(".pdf") ? (
                            <iframe
                                src={selected.LINK_BUKTI_BAYAR}
                                title="PDF Viewer"
                            />
                            ) : (
                            <img src={selected.LINK_BUKTI_BAYAR} alt="Bukti" />
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
                        </>
                    ) : (
                        <p>Pilih data dari tabel</p>
                    )}
                </div>

            </div>
        </div>
    );
}
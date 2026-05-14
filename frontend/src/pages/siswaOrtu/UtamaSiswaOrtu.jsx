import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./../../styles/siswaOrtu/UtamaSiswaOrtu.css";

function UtamaSiswaOrtu() {
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const [siswaData, setSiswaData] = useState(null);
  const [tagihanData, setTagihanData] = useState([]);
  const [paymentHistory, setPaymentHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  const ITEMS_PER_PAGE = 10;

  const [activeBillPage, setActiveBillPage] = useState(1);
  const [historyPage, setHistoryPage] = useState(1);

  const navigate = useNavigate();
  // Catatan: useParams() dan { id } dihapus karena mengambil data murni dari Token Login

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(Number(value || 0));

  const formatTanggal = (value) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return new Intl.DateTimeFormat("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    }).format(date);
  };

  const announcements = [
    {
      id: 1,
      title: "Jadwal Ujian Akhir Semester",
      content: "Ujian akan dimulai pada minggu pertama bulan depan. Pastikan seluruh administrasi keuangan telah diselesaikan.",
    },
    {
      id: 2,
      title: "Pembayaran Daftar Ulang",
      content: "Bagi siswa yang memiliki tunggakan, mohon segera mengonfirmasi ke bagian bendahara sekolah.",
    },
  ];

  useEffect(() => {
    const fetchData = async () => {
      const token = localStorage.getItem("token");
      if (!token) {
        navigate("/login");
        return;
      }

      try {
        setLoading(true);
        const response = await fetch("http://localhost:8000/api/siswa-ortu/tagihan", {
          headers: {
            "Authorization": `Bearer ${token}`,
            "Accept": "application/json",
          },
        });

        const resData = await response.json();

        if (response.ok && resData.success) {
          // Menyimpan rincian data siswa dari respons API atau fallback dari localStorage
          const userStorage = JSON.parse(localStorage.getItem("user") || "{}");
          setSiswaData(resData.info_siswa || resData.siswa || userStorage);
          setTagihanData(resData.data || []);

          // Ekstrak dan petakan histori pembayaran jika disematkan dalam relasi data tagihan
          const history = [];
          (resData.data || []).forEach((item) => {
            if (item.HISTORI_PEMBAYARAN && Array.isArray(item.HISTORI_PEMBAYARAN)) {
              item.HISTORI_PEMBAYARAN.forEach((h) => {
                history.push({
                  ...h,
                  DESKRIPSI_TAGIHAN: item.JENIS_TAGIHAN?.DESKRIPSI_JENIS_TAGIHAN || "Tagihan",
                });
              });
            }
          });
          setPaymentHistory(history);
        } else {
          console.error("Gagal memuat rincian:", resData.message);
        }
      } catch (error) {
        console.error("Kesalahan jaringan:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [navigate]);

  // Paginasi Tagihan Aktif
  const activeBills = useMemo(() => {
    return tagihanData.filter((item) => item.SISA_TAGIHAN > 0);
  }, [tagihanData]);

  const billTotalPages = Math.ceil(activeBills.length / ITEMS_PER_PAGE) || 1;
  const currentBills = useMemo(() => {
    const start = (activeBillPage - 1) * ITEMS_PER_PAGE;
    return activeBills.slice(start, start + ITEMS_PER_PAGE);
  }, [activeBills, activeBillPage]);

  // Paginasi Histori
  const historyTotalPages = Math.ceil(paymentHistory.length / ITEMS_PER_PAGE) || 1;
  const currentHistory = useMemo(() => {
    const start = (historyPage - 1) * ITEMS_PER_PAGE;
    return paymentHistory.slice(start, start + ITEMS_PER_PAGE);
  }, [paymentHistory, historyPage]);

  const handleLogout = () => {
    localStorage.clear();
    navigate("/login");
  };

  return (
    <div className="portal-container">
      <header className="portal-header">
        <div className="portal-header-left">
          <h1>Portal Informasi Keuangan Siswa</h1>
          {siswaData && (
            <p>
              Siswa: <strong>{siswaData.NAMA_SISWA_TETAP || siswaData.nama_siswa || "-"}</strong> | NISN: {siswaData.NISN_SISWA || siswaData.nisn || "-"}
            </p>
          )}
        </div>
        <div className="portal-header-right">
          <button onClick={() => navigate("/siswa-ortu/profile")} className="portal-btn profile-btn">
            Profil Saya
          </button>
          <button onClick={handleLogout} className="portal-btn logout-btn">
            Keluar
          </button>
        </div>
      </header>

      <div className="portal-content-wrapper">
        <div className="portal-main-grid">
          <div className="portal-main-left">
            {/* Bagian Tagihan Aktif */}
            <section className="content-card">
              <div className="section-header">
                <h2>Tagihan Aktif</h2>
                <p>Kewajiban administrasi yang belum diselesaikan.</p>
              </div>

              {loading ? (
                <p className="loading-text">Memuat rincian tagihan...</p>
              ) : currentBills.length === 0 ? (
                <p className="empty-text">Tidak ada tagihan aktif saat ini.</p>
              ) : (
                <div className="bill-list">
                  {currentBills.map((item) => (
                    <div className="bill-card" key={item.ID_TAGIHAN_SISWA}>
                      <div className="bill-card-header">
                        <h3>{item.JENIS_TAGIHAN?.DESKRIPSI_JENIS_TAGIHAN || "Tagihan Biaya"}</h3>
                        <span className="status-badge warning">{item.STATUS_TAGIHAN_SISWA}</span>
                      </div>
                      <div className="bill-details">
                        <p>Periode: {item.BULAN_TAGIHAN_SISWA} {item.TAHUN_TAGIHAN_SISWA}</p>
                        <p className="amount">Total Tagihan: {formatRupiah(item.JUMLAH_TAGIHAN_SISWA)}</p>
                        <p className="remaining">Sisa Bayar: <strong>{formatRupiah(item.SISA_TAGIHAN)}</strong></p>
                        {item.DUEDATETIME_TAGIHAN_SISWA && (
                          <p className="duedate">Jatuh Tempo: {formatTanggal(item.DUEDATETIME_TAGIHAN_SISWA)}</p>
                        )}
                      </div>
                      <button
                        onClick={() => navigate(`/siswa-ortu/pembayaran/${item.ID_TAGIHAN_SISWA}`)}
                        className="pay-btn"
                      >
                        Bayar Tagihan
                      </button>
                    </div>
                  ))}

                  {/* Kontrol Paginasi Tagihan */}
                  {billTotalPages > 1 && (
                    <div className="portal-pagination">
                      <button
                        type="button"
                        className="portal-page-btn"
                        disabled={activeBillPage === 1}
                        onClick={() => setActiveBillPage((prev) => Math.max(1, prev - 1))}
                      >
                        ‹
                      </button>
                      <span className="portal-page-number">{activeBillPage}</span>
                      <button
                        type="button"
                        className="portal-page-btn"
                        disabled={activeBillPage === billTotalPages}
                        onClick={() => setActiveBillPage((prev) => Math.min(billTotalPages, prev + 1))}
                      >
                        ›
                      </button>
                    </div>
                  )}
                </div>
              )}
            </section>

            {/* Bagian Riwayat Pembayaran */}
            <section className="content-card history-section">
              <div className="section-header">
                <h2>Riwayat Pembayaran</h2>
                <p>Histori transaksi yang telah tervalidasi.</p>
              </div>

              {loading ? (
                <p className="loading-text">Memuat histori...</p>
              ) : currentHistory.length === 0 ? (
                <p className="empty-text">Belum ada riwayat pembayaran.</p>
              ) : (
                <div className="history-table-wrapper">
                  <table className="history-table">
                    <thead>
                      <tr>
                        <th>Tanggal Bayar</th>
                        <th>Tagihan</th>
                        <th>Metode</th>
                        <th>Nominal</th>
                      </tr>
                    </thead>
                    <tbody>
                      {currentHistory.map((h, idx) => (
                        <tr key={h.ID_PEMBAYARAN || idx}>
                          <td>{formatTanggal(h.TGL_BAYAR)}</td>
                          <td>{h.DESKRIPSI_TAGIHAN}</td>
                          <td>{h.METODE_PEMBAYARAN?.DESKRIPSI_METODE_PEMBAYARAN || "Tunai"}</td>
                          <td className="amount-cell">{formatRupiah(h.JUMLAH_BAYAR)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>

                  {/* Kontrol Paginasi Histori */}
                  {historyTotalPages > 1 && (
                    <div className="portal-pagination">
                      <button
                        type="button"
                        className="portal-page-btn"
                        disabled={historyPage === 1}
                        onClick={() => setHistoryPage((prev) => Math.max(1, prev - 1))}
                      >
                        ‹
                      </button>
                      <span className="portal-page-number">{historyPage}</span>
                      <button
                        type="button"
                        className="portal-page-btn"
                        disabled={historyPage === historyTotalPages}
                        onClick={() => setHistoryPage((prev) => Math.min(historyTotalPages, prev + 1))}
                      >
                        ›
                      </button>
                    </div>
                  )}
                </div>
              )}
            </section>
          </div>

          <aside className="portal-main-right">
            <section className="content-card announcement-card">
              <div className="section-header">
                <h2>Papan Pengumuman</h2>
                <p>Informasi penting terkait administrasi sekolah.</p>
              </div>

              <div className="announcement-list">
                {announcements.map((item) => (
                  <div className="announcement-item" key={item.id}>
                    <h3>{item.title}</h3>
                    <p>{item.content}</p>
                  </div>
                ))}
              </div>
            </section>
          </aside>
        </div>
      </div>
    </div>
  );
}

export default UtamaSiswaOrtu;
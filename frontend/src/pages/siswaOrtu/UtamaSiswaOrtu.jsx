import { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import "./../../styles/siswaOrtu/UtamaSiswaOrtu.css";

function UtamaSiswaOrtu() {
  const [isProfileOpen, setIsProfileOpen] = useState(false);
const [siswaData, setSiswaData] = useState(null);
  const [tagihanData, setTagihanData] = useState([]);
  const [paymentHistory, setPaymentHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  const navigate = useNavigate();
  const { id } = useParams();

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(Number(value || 0));

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);

        const [tagihanRes, pembayaranRes] = await Promise.all([
          fetch(`http://localhost:8000/api/tagihan-siswa?ID_SISWA_TETAP=${id}`),
          fetch(`http://localhost:8000/api/tr-pembayaran?ID_SISWA_TETAP=${id}`),
        ]);

        const tagihanJson = await tagihanRes.json();
        const pembayaranJson = await pembayaranRes.json();

        const tagihan = Array.isArray(tagihanJson.data) ? tagihanJson.data : [];
        const pembayaran = Array.isArray(pembayaranJson.data)
          ? pembayaranJson.data
          : [];

        setSiswaData(tagihanJson.siswa || null);
        setTagihanData(tagihan);
        setPaymentHistory(pembayaran);
      } catch (error) {
        console.error("Gagal mengambil data:", error);
        setSiswaData(null);
        setTagihanData([]);
        setPaymentHistory([]);
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchData();
    }
  }, [id]);

  // const siswa = useMemo(() => {
  //   if (tagihanData.length > 0 && tagihanData[0]?.SISWA) {
  //     return tagihanData[0].SISWA;
  //   }

  //   if (paymentHistory.length > 0 && paymentHistory[0]?.siswa) {
  //     return paymentHistory[0].siswa;
  //   }

  //   return null;
  // }, [tagihanData, paymentHistory]);

  const activeBills = useMemo(() => {
    return tagihanData.map((item) => {
      const totalTagihan = Number(item.JUMLAH_TAGIHAN_SISWA || 0);
      const totalBayar = Number(item.TOTAL_PEMBAYARAN || 0);
      const sisa = Number(item.SISA_TAGIHAN || totalTagihan - totalBayar);

      let status = "Belum Bayar";
      if (sisa <= 0) {
        status = "Lunas";
      } else if (totalBayar > 0) {
        status = "Belum Lunas";
      }

      return {
        id: item.ID_TAGIHAN_SISWA,
        tagihan:
          item?.JENIS_PEMBAYARAN?.DESKRIPSI_JENIS_PEMBAYARAN ||
          item?.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN ||
          "Tagihan",
        totalTagihan,
        totalBayar,
        sisa,
        status,
      };
    });
  }, [tagihanData]);

  const summaryCards = useMemo(() => {
    const totalTagihan = activeBills.reduce(
      (sum, item) => sum + Number(item.totalTagihan || 0),
      0
    );

    const sudahDibayar = activeBills.reduce(
      (sum, item) => sum + Number(item.totalBayar || 0),
      0
    );

    const sisaTagihan = activeBills.reduce(
      (sum, item) => sum + Number(item.sisa || 0),
      0
    );

    return [
      { title: "Total Tagihan", value: totalTagihan },
      { title: "Sudah Dibayar", value: sudahDibayar },
      { title: "Sisa Tagihan", value: sisaTagihan },
    ];
  }, [activeBills]);

  const sisaTagihanBulanIni = useMemo(() => {
    const belumLunas = activeBills.filter((item) => item.status !== "Lunas");
    return belumLunas.reduce((sum, item) => sum + Number(item.sisa || 0), 0);
  }, [activeBills]);

  const announcements = [
    {
      id: 1,
      title: "Batas Pembayaran SPP",
      content:
        "Mohon melunasi SPP paling lambat tanggal 10 setiap bulannya agar dapat mengikuti kegiatan belajar dengan lancar.",
    },
  ];

  const renderStatusBadge = (status) => {
    let className = "status-badge";

    if (status === "Lunas" || status === "Terverifikasi") {
      className += " success";
    } else if (status === "Belum Bayar") {
      className += " danger";
    } else if (status === "Belum Lunas") {
      className += " info";
    } else if (status === "Menunggu Verifikasi") {
      className += " warning";
    }

    return <span className={className}>{status}</span>;
  };

  const renderActionButton = (status, billId) => {
    if (status === "Lunas") {
      return (
        <button className="action-btn action-btn-disabled" disabled>
          Lunas
        </button>
      );
    }

    if (status === "Menunggu Verifikasi") {
      return (
        <button className="action-btn action-btn-waiting" disabled>
          Menunggu Verifikasi
        </button>
      );
    }

    return (
      <button
        className="action-btn action-btn-pay"
        onClick={() => navigate(`/siswa-ortu/pembayaran/${billId}`)}
      >
        Bayar
      </button>
    );
  };

  const handlePayNow = () => {
    const unpaidBill = activeBills.find(
      (item) => item.status === "Belum Bayar" || item.status === "Belum Lunas"
    );

    if (unpaidBill) {
      navigate(`/siswa-ortu/pembayaran/${unpaidBill.id}`);
    }
  };

  if (loading) {
    return <div className="portal-page">Loading...</div>;
  }

  return (
    <div className="portal-page">
      <div className="portal-container">
        <header className="portal-header">
          <div className="portal-header-left">
            <p className="portal-label">Portal Siswa / Ortu</p>
            <h1 className="portal-title">
              Halo, {siswaData?.NAMA_SISWA_TETAP || "Siswa"}!
            </h1>
            <p className="portal-subtitle">
              Pantau tagihan administrasi sekolah dan riwayat pembayaran Anda di
              sini.
            </p>
          </div>

          <div className="profile-menu">
            <button
              className="profile-button"
              type="button"
              onClick={() => setIsProfileOpen(!isProfileOpen)}
            >
              <div className="profile-avatar">
                {(siswaData?.NAMA_SISWA_TETAP || "S")
                  .split(" ")
                  .map((word) => word[0])
                  .slice(0, 2)
                  .join("")
                  .toUpperCase()}
              </div>

              <div className="profile-text">
                <span className="profile-name">
                  {siswaData?.NAMA_SISWA_TETAP || "Siswa"}
                </span>
                <span className="profile-class">
                  Siswa
                </span>
              </div>

              <span className={`profile-caret ${isProfileOpen ? "rotate" : ""}`}>
                ▾
              </span>
            </button>

            {isProfileOpen && (
              <div className="profile-dropdown">
                <button
                  type="button"
                  className="dropdown-item"
                  onClick={() => {
                    setIsProfileOpen(false);
                    navigate("/siswa-ortu/profile");
                  }}
                >
                  Lihat Profile
                </button>

                <button
                  type="button"
                  className="dropdown-item logout-item"
                  onClick={() => {
                    setIsProfileOpen(false);
                    navigate("/");
                  }}
                >
                  Logout
                </button>
              </div>
            )}
          </div>
        </header>

        <section className="hero-card">
          <div>
            <p className="hero-label">Total Sisa Tagihan</p>
            <h2 className="hero-value">{formatRupiah(sisaTagihanBulanIni)}</h2>
          </div>

          <button className="hero-button" onClick={handlePayNow}>
            Bayar Sekarang
          </button>
        </section>

        <section className="summary-grid">
          {summaryCards.map((card) => (
            <div className="summary-card" key={card.title}>
              <p className="summary-title">{card.title}</p>
              <h3 className="summary-value">{formatRupiah(card.value)}</h3>
            </div>
          ))}
        </section>

        <div className="portal-main-grid">
          <div className="portal-main-left">
            <section className="content-card">
              <div className="section-header">
                <h2>Tagihan Aktif</h2>
                <p>Daftar tagihan yang masih perlu diperhatikan.</p>
              </div>

              <div className="table-wrapper">
                <table className="custom-table">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Tagihan</th>
                      <th>Total Tagihan</th>
                      <th>Total Bayar</th>
                      <th>Sisa</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {activeBills.length > 0 ? (
                      activeBills.map((item, index) => (
                        <tr key={item.id}>
                          <td>{index + 1}</td>
                          <td>{item.tagihan}</td>
                          <td>{formatRupiah(item.totalTagihan)}</td>
                          <td>{formatRupiah(item.totalBayar)}</td>
                          <td>{formatRupiah(item.sisa)}</td>
                          <td>{renderStatusBadge(item.status)}</td>
                          <td>{renderActionButton(item.status, item.id)}</td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="7">Tidak ada data tagihan</td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </section>

            <section className="content-card">
              <div className="section-header">
                <h2>Riwayat Pembayaran</h2>
                <p>Riwayat pembayaran terbaru yang telah dilakukan.</p>
              </div>

              <div className="table-wrapper">
                <table className="custom-table">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Tanggal</th>
                      <th>Nominal</th>
                      <th>Metode</th>
                      <th>ID Tagihan</th>
                    </tr>
                  </thead>
                  <tbody>
                    {paymentHistory.length > 0 ? (
                      paymentHistory.map((item, index) => (
                        <tr key={item.ID_PEMBAYARAN}>
                          <td>{index + 1}</td>
                          <td>{item.TGL_BAYAR || "-"}</td>
                          <td>{formatRupiah(item.JUMLAH_BAYAR)}</td>
                          <td>
                            {item?.jenis_pembayaran?.DESKRIPSI_JENIS_PEMBAYARAN ||
                              item?.jenisPembayaran?.DESKRIPSI_JENIS_PEMBAYARAN ||
                              "-"}
                          </td>
                          <td>{item.ID_TAGIHAN_SISWA || "-"}</td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="5">Tidak ada riwayat pembayaran</td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
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
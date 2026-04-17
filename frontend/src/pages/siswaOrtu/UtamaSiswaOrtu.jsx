import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "./../../styles/siswaOrtu/UtamaSiswaOrtu.css";

function UtamaSiswaOrtu() {
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const navigate = useNavigate();

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value);

  const summaryCards = [
    { title: "Total Tagihan", value: 3500000 },
    { title: "Sudah Dibayar", value: 1500000 },
    { title: "Sisa Tagihan", value: 2000000 },
  ];

  const activeBills = [
    {
      id: 1,
      tagihan: "SPP Februari 2026",
      totalTagihan: 500000,
      totalBayar: 0,
      sisa: 500000,
      status: "Belum Bayar",
    },
    {
      id: 2,
      tagihan: "SPP Januari 2026",
      totalTagihan: 500000,
      totalBayar: 500000,
      sisa: 0,
      status: "Lunas",
    },
    {
      id: 3,
      tagihan: "Uang Kegiatan",
      totalTagihan: 1000000,
      totalBayar: 500000,
      sisa: 500000,
      status: "Belum Lunas",
    },
    {
      id: 4,
      tagihan: "Uang Gedung",
      totalTagihan: 2000000,
      totalBayar: 1000000,
      sisa: 1000000,
      status: "Menunggu Verifikasi",
    },
  ];

  const paymentHistory = [
    {
      id: 1,
      tanggal: "10 Feb 2026",
      tagihan: "SPP Januari 2026",
      nominal: 500000,
      metode: "Transfer Bank",
      status: "Terverifikasi",
    },
    {
      id: 2,
      tanggal: "05 Feb 2026",
      tagihan: "Uang Kegiatan",
      nominal: 500000,
      metode: "Transfer Bank",
      status: "Terverifikasi",
    },
    {
      id: 3,
      tanggal: "12 Feb 2026",
      tagihan: "Uang Gedung",
      nominal: 1000000,
      metode: "Transfer Bank",
      status: "Menunggu Verifikasi",
    },
  ];

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

  const renderActionButton = (status, id) => {
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

    if (status === "Belum Bayar" || status === "Belum Lunas") {
      return (
        <button
          className="action-btn action-btn-pay"
          onClick={() => navigate(`/siswa-ortu/pembayaran/${id}`)}
        >
          Bayar
        </button>
      );
    }

    return null;
  };

  const handlePayNow = () => {
    const unpaidBill = activeBills.find(
      (item) =>
        item.status === "Belum Bayar" || item.status === "Belum Lunas"
    );

    if (unpaidBill) {
      navigate(`/siswa-ortu/pembayaran/${unpaidBill.id}`);
    }
  };

  return (
    <div className="portal-page">
      <div className="portal-container">
        <header className="portal-header">
          <div className="portal-header-left">
            <p className="portal-label">Portal Siswa / Ortu</p>
            <h1 className="portal-title">Halo, Andi Susanto!</h1>
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
              <div className="profile-avatar">AS</div>

              <div className="profile-text">
                <span className="profile-name">Andi Susanto</span>
                <span className="profile-class">Siswa · Kelas X RPL</span>
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
            <p className="hero-label">Total Sisa Tagihan Bulan Ini</p>
            <h2 className="hero-value">{formatRupiah(350000)}</h2>
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
                    {activeBills.map((item, index) => (
                      <tr key={item.id}>
                        <td>{index + 1}</td>
                        <td>{item.tagihan}</td>
                        <td>{formatRupiah(item.totalTagihan)}</td>
                        <td>{formatRupiah(item.totalBayar)}</td>
                        <td>{formatRupiah(item.sisa)}</td>
                        <td>{renderStatusBadge(item.status)}</td>
                        <td>{renderActionButton(item.status, item.id)}</td>
                      </tr>
                    ))}
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
                      <th>Tagihan</th>
                      <th>Nominal</th>
                      <th>Metode</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {paymentHistory.map((item, index) => (
                      <tr key={item.id}>
                        <td>{index + 1}</td>
                        <td>{item.tanggal}</td>
                        <td>{item.tagihan}</td>
                        <td>{formatRupiah(item.nominal)}</td>
                        <td>{item.metode}</td>
                        <td>{renderStatusBadge(item.status)}</td>
                      </tr>
                    ))}
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
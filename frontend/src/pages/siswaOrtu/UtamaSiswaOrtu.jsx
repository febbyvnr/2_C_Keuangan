import { useState } from "react";
import "./../../styles/siswaOrtu/UtamaSiswaOrtu.css";

function UtamaSiswaOrtu() {
  const [isProfileOpen, setIsProfileOpen] = useState(false);

  const summaryCards = [
    { title: "Total Tagihan", value: "Rp3.500.000" },
    { title: "Sudah Dibayar", value: "Rp1.500.000" },
    { title: "Sisa Tagihan", value: "Rp2.000.000" },
  ];

  const activeBills = [
    {
      id: 1,
      tagihan: "SPP Februari 2026",
      totalTagihan: "Rp500.000",
      totalBayar: "Rp0",
      sisa: "Rp500.000",
      status: "Belum Bayar",
    },
    {
      id: 2,
      tagihan: "SPP Januari 2026",
      totalTagihan: "Rp500.000",
      totalBayar: "Rp500.000",
      sisa: "Rp0",
      status: "Lunas",
    },
    {
      id: 3,
      tagihan: "Uang Kegiatan",
      totalTagihan: "Rp1.000.000",
      totalBayar: "Rp500.000",
      sisa: "Rp500.000",
      status: "Menunggu Verifikasi",
    },
  ];

  const paymentHistory = [
    {
      id: 1,
      tanggal: "10 Feb 2026",
      tagihan: "SPP Januari 2026",
      nominal: "Rp500.000",
      metode: "Transfer Bank",
      status: "Terverifikasi",
    },
    {
      id: 2,
      tanggal: "05 Feb 2026",
      tagihan: "Uang Kegiatan",
      nominal: "Rp500.000",
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
    } else if (status === "Menunggu Verifikasi") {
      className += " warning";
    }

    return <span className={className}>{status}</span>;
  };

  const renderActionButton = (status) => {
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

    return <button className="action-btn action-btn-pay">Bayar</button>;
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
                <button type="button" className="dropdown-item">
                  Lihat Profile
                </button>
                <button type="button" className="dropdown-item logout-item">
                  Logout
                </button>
              </div>
            )}
          </div>
        </header>

        <section className="hero-card">
          <div>
            <p className="hero-label">Total Sisa Tagihan Bulan Ini</p>
            <h2 className="hero-value">Rp 350.000</h2>
          </div>

          <button className="hero-button">Bayar Sekarang</button>
        </section>

        <section className="summary-grid">
          {summaryCards.map((card, index) => (
            <div className="summary-card" key={index}>
              <p className="summary-title">{card.title}</p>
              <h3 className="summary-value">{card.value}</h3>
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
                        <td>{item.totalTagihan}</td>
                        <td>{item.totalBayar}</td>
                        <td>{item.sisa}</td>
                        <td>{renderStatusBadge(item.status)}</td>
                        <td>{renderActionButton(item.status)}</td>
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
                        <td>{item.nominal}</td>
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
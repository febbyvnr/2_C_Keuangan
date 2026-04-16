import { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import logoSekolah from "../../assets/logo.png";
import "../../styles/siswaOrtu/PembayaranTagihanSiswaOrtu.css";

function PembayaranTagihanSiswaOrtu() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [metode, setMetode] = useState("");
  const [isOpen, setIsOpen] = useState(false);

  const tagihanDummy = {
    id,
    namaTagihan: "Uang Kegiatan",
    totalTagihan: 1000000,
    totalBayar: 500000,
    sisa: 500000,
    status: "Belum Lunas",
  };

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value);

  const handleSubmit = (e) => {
    e.preventDefault();

    const nominal = Number(e.target.nominalBayar.value);
    const buktiTransfer = e.target.buktiTransfer?.files?.length || 0;

    if (!metode) {
      alert("Pilih metode pembayaran terlebih dahulu.");
      return;
    }

    if (!nominal || nominal <= 0) {
      alert("Nominal pembayaran harus lebih dari 0.");
      return;
    }

    if (nominal > tagihanDummy.sisa) {
      alert("Nominal pembayaran tidak boleh melebihi sisa tagihan.");
      return;
    }

    if (metode === "bank" && !buktiTransfer) {
      alert("Untuk pembayaran bank, bukti pembayaran wajib diunggah.");
      return;
    }

    if (metode === "bank") {
      alert("Pembayaran bank berhasil diajukan dan menunggu verifikasi bendahara.");
    } else {
      alert("Pengajuan pembayaran tunai berhasil dikirim. Silakan lakukan pembayaran langsung ke bendahara sekolah.");
    }
  };

  return (
    <main className="payment-page">
      <div className="payment-center">
        <div className="payment-shell">
          <button
            type="button"
            className="back-button"
            onClick={() => navigate(-1)}
          >
            <span className="back-icon">←</span>
            <span>Kembali</span>
          </button>

          <div className="payment-card">
            <header className="payment-header">
              <div className="payment-brand">
                <img
                  src={logoSekolah}
                  alt="Logo Sekolah"
                  className="school-logo"
                />

                <div className="school-text">
                  <p className="school-system">Sistem Pembayaran Sekolah</p>
                  <h2 className="school-name">SMK BOPKRI 2 YOGYAKARTA</h2>
                </div>
              </div>

              <div className="payment-heading">
                <h1 className="payment-title">Pembayaran Tagihan</h1>
                <p className="payment-subtitle">
                  Lengkapi form berikut untuk mengajukan pembayaran tagihan.
                </p>
              </div>
            </header>

            <section className="payment-detail-grid">
              <div className="payment-detail-item">
                <label>Tagihan</label>
                <p>{tagihanDummy.namaTagihan}</p>
              </div>

              <div className="payment-detail-item">
                <label>Status</label>
                <p>{tagihanDummy.status}</p>
              </div>

              <div className="payment-detail-item">
                <label>Total Tagihan</label>
                <p>{formatRupiah(tagihanDummy.totalTagihan)}</p>
              </div>

              <div className="payment-detail-item">
                <label>Sudah Dibayar</label>
                <p>{formatRupiah(tagihanDummy.totalBayar)}</p>
              </div>

              <div className="payment-detail-item payment-detail-item-full">
                <label>Sisa Tagihan</label>
                <p>{formatRupiah(tagihanDummy.sisa)}</p>
              </div>
            </section>

            <form className="payment-form" onSubmit={handleSubmit}>
              <div className="form-group">
                <label htmlFor="nominalBayar">Nominal Bayar</label>
                <input
                  type="number"
                  min="1"
                  max={tagihanDummy.sisa}
                  id="nominalBayar"
                  name="nominalBayar"
                  placeholder="Masukkan nominal pembayaran"
                />
              </div>

              <div className="form-group">
                <label>Metode Pembayaran</label>

                <div className="custom-select">
                  <button
                    type="button"
                    className={`custom-select-trigger ${!metode ? "is-placeholder" : ""}`}
                    onClick={() => setIsOpen(!isOpen)}
                  >
                    <span>
                      {!metode
                        ? "Pilih metode pembayaran"
                        : metode === "bank"
                        ? "Transfer Bank"
                        : "Tunai"}
                    </span>
                    <span className="custom-select-arrow">▾</span>
                  </button>

                  {isOpen && (
                    <div className="custom-select-menu">
                      <button
                        type="button"
                        className="custom-select-option placeholder-option"
                        onClick={() => {
                          setMetode("");
                          setIsOpen(false);
                        }}
                      >
                        Pilih metode pembayaran
                      </button>

                      <button
                        type="button"
                        className="custom-select-option"
                        onClick={() => {
                          setMetode("bank");
                          setIsOpen(false);
                        }}
                      >
                        Transfer Bank
                      </button>

                      <button
                        type="button"
                        className="custom-select-option"
                        onClick={() => {
                          setMetode("tunai");
                          setIsOpen(false);
                        }}
                      >
                        Tunai
                      </button>
                    </div>
                  )}
                </div>
              </div>

              <div className="payment-method-content">
                {metode === "bank" && (
                  <>
                    <div className="payment-info">
                      <p className="payment-info-title">Informasi Rekening</p>
                      <p>Bank BRI - 1234567890</p>
                      <p>a.n. SMK BOPKRI 2 Yogyakarta</p>
                      <p className="payment-info-note">
                        Silakan transfer sesuai nominal pembayaran lalu unggah
                        bukti pembayaran untuk diverifikasi bendahara.
                      </p>
                    </div>

                    <div className="form-group">
                      <label htmlFor="buktiTransfer">Upload Bukti Pembayaran</label>
                      <input
                        type="file"
                        id="buktiTransfer"
                        name="buktiTransfer"
                        accept=".jpg,.jpeg,.png,.pdf"
                      />
                    </div>
                  </>
                )}

                {metode === "tunai" && (
                  <div className="payment-info payment-info-tunai">
                    <p className="payment-info-title">Instruksi Pembayaran Tunai</p>
                    <p>
                      Silakan melakukan pembayaran langsung ke bendahara sekolah
                      pada jam layanan yang tersedia.
                    </p>
                    <p className="payment-info-note">
                      Setelah pembayaran diterima, bendahara akan memverifikasi dan
                      memperbarui status tagihan Anda.
                    </p>
                  </div>
                )}
              </div>

              <div className="form-group">
                <label htmlFor="catatan">Catatan</label>
                <textarea
                  id="catatan"
                  name="catatan"
                  rows="4"
                  placeholder="Tambahkan catatan jika perlu"
                />
              </div>

              <button type="submit" className="submit-button">
                {metode === "tunai" ? "Konfirmasi Pembayaran Tunai" : "Kirim Pembayaran"}
              </button>
            </form>
          </div>
        </div>
      </div>
    </main>
  );
}

export default PembayaranTagihanSiswaOrtu;
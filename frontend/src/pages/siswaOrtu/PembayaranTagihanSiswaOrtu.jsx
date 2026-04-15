import { useParams, useNavigate } from "react-router-dom";
import "../../styles/siswaOrtu/PembayaranTagihanSiswaOrtu.css";

function PembayaranTagihanSiswaOrtu() {
  const { id } = useParams();
  const navigate = useNavigate();

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
    alert("Pembayaran berhasil diajukan dan menunggu verifikasi bendahara.");
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
          ← Kembali
        </button>

        <div className="payment-card">
          <header className="payment-header">
            <h1 className="payment-title">Pembayaran Tagihan</h1>
            <p className="payment-subtitle">
              Lengkapi form berikut untuk mengajukan pembayaran tagihan.
            </p>
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
                id="nominalBayar"
                name="nominalBayar"
                placeholder="Masukkan nominal pembayaran"
              />
            </div>

            <div className="form-group">
              <label htmlFor="metodePembayaran">Metode Pembayaran</label>
              <select id="metodePembayaran" name="metodePembayaran" defaultValue="">
                <option value="" disabled>
                  Pilih metode pembayaran
                </option>
                <option value="transfer">Transfer Bank</option>
                <option value="tunai">Tunai</option>
              </select>
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
              Kirim Pembayaran
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>
);
}

export default PembayaranTagihanSiswaOrtu;
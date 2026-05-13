import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import logoSekolah from "../../assets/logo.png";
import "../../styles/siswaOrtu/PembayaranTagihanSiswaOrtu.css";

function PembayaranTagihanSiswaOrtu() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [metode, setMetode] = useState("");
  const [isOpen, setIsOpen] = useState(false);

  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  const [tagihan, setTagihan] = useState(null);

  useEffect(() => {
    const fetchTagihan = async () => {
      try {
        setLoading(true);

        const response = await fetch(
          `http://localhost:8000/api/tagihan-siswa/${id}`
        );

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Gagal mengambil data tagihan");
        }

        setTagihan(result.data);
      } catch (error) {
        console.error(error);
        alert("Gagal mengambil data tagihan");
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchTagihan();
    }
  }, [id]);

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(Number(value || 0));

    const handleSubmit = async (e) => {
    e.preventDefault();

    if (!tagihan) return;

    const nominal = Number(e.target.nominalBayar.value);

    const file =
      metode === "bank"
        ? e.target.buktiTransfer?.files?.[0]
        : null;

    if (!metode) {
      alert("Pilih metode pembayaran terlebih dahulu.");
      return;
    }

    if (!nominal || nominal <= 0) {
      alert("Nominal pembayaran harus lebih dari 0.");
      return;
    }

    if (nominal > Number(tagihan.SISA_TAGIHAN || 0)) {
      alert("Nominal pembayaran tidak boleh melebihi sisa tagihan.");
      return;
    }

    if (metode === "bank" && !file) {
      alert("Untuk pembayaran bank, bukti pembayaran wajib diunggah.");
      return;
    }

    try {
      setSubmitting(true);

      const payload = {
        ID_TAGIHAN_SISWA: tagihan.ID_TAGIHAN_SISWA,

        ID_METODE_PEMBAYARAN:
          metode === "bank" ? 1 : 2,

        JUMLAH_BAYAR: nominal,

        LINK_BUKTI_BAYAR:
          metode === "bank"
            ? file.name
            : "pembayaran-tunai",
      };

      const response = await fetch(
        "http://localhost:8000/api/tr-pembayaran/store",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        }
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(
          result.message ||
            "Gagal mengirim pembayaran"
        );
      }

      alert("Pembayaran berhasil dikirim");

      navigate(`/siswa-ortu/utama/${tagihan.ID_SISWA_TETAP}`);
    } catch (error) {
      console.error(error);

      alert(
        error.message || "Terjadi kesalahan saat pembayaran"
      );
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <div className="payment-page">Loading...</div>;
  }

  if (!tagihan) {
    return (
      <div className="payment-page">
        Data tagihan tidak ditemukan
      </div>
    );
  }

  return (
    <main className="payment-page">
      <div className="payment-center">
        <div className="payment-shell">
          <button
            type="button"
            className="back-button"
            onClick={() =>
              navigate(`/siswa-ortu/utama/${tagihan.ID_SISWA_TETAP}`)
            }
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
                  <p className="school-system">
                    Sistem Pembayaran Sekolah
                  </p>

                  <h2 className="school-name">
                    SMK BOPKRI 2 YOGYAKARTA
                  </h2>
                </div>
              </div>

              <div className="payment-heading">
                <h1 className="payment-title">
                  Pembayaran Tagihan
                </h1>

                <p className="payment-subtitle">
                  Lengkapi form berikut untuk mengajukan pembayaran tagihan.
                </p>
              </div>
            </header>

            <section className="payment-detail-grid">
              <div className="payment-detail-item">
                <label>Tagihan</label>

                <p>
                  {tagihan?.JENIS_TAGIHAN
                    ?.DESKRIPSI_JENIS_TAGIHAN || "-"}
                </p>
              </div>

              <div className="payment-detail-item">
                <label>Status</label>
                <p>{tagihan.STATUS_TAGIHAN_SISWA}</p>
              </div>

              <div className="payment-detail-item">
                <label>Total Tagihan</label>

                <p>
                  {formatRupiah(
                    tagihan.JUMLAH_TAGIHAN_SISWA
                  )}
                </p>
              </div>

              <div className="payment-detail-item">
                <label>Sudah Dibayar</label>

                <p>
                  {formatRupiah(
                    tagihan.TOTAL_PEMBAYARAN
                  )}
                </p>
              </div>

              <div className="payment-detail-item payment-detail-item-full">
                <label>Sisa Tagihan</label>

                <p>
                  {formatRupiah(tagihan.SISA_TAGIHAN)}
                </p>
              </div>
            </section>

            <form
              className="payment-form"
              onSubmit={handleSubmit}
            >
              <div className="form-group">
                <label htmlFor="nominalBayar">
                  Nominal Bayar
                </label>

                <input
                  type="number"
                  min="1"
                  max={tagihan.SISA_TAGIHAN}
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
                    className={`custom-select-trigger ${
                      !metode ? "is-placeholder" : ""
                    }`}
                    onClick={() => setIsOpen(!isOpen)}
                  >
                    <span>
                      {!metode
                        ? "Pilih metode pembayaran"
                        : metode === "bank"
                        ? "Transfer Bank"
                        : "Tunai"}
                    </span>

                    <span className="custom-select-arrow">
                      ▾
                    </span>
                  </button>

                  {isOpen && (
                    <div className="custom-select-menu">
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
                      <p className="payment-info-title">
                        Informasi Rekening
                      </p>

                      <p>Bank BRI - 1234567890</p>

                      <p>
                        a.n. SMK BOPKRI 2 Yogyakarta
                      </p>
                    </div>

                    <div className="form-group">
                      <label htmlFor="buktiTransfer">
                        Upload Bukti Pembayaran
                      </label>

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
                    <p className="payment-info-title">
                      Instruksi Pembayaran Tunai
                    </p>

                    <p>
                      Silakan melakukan pembayaran langsung ke bendahara sekolah.
                    </p>
                  </div>
                )}
              </div>

              <div className="form-group">
                <label htmlFor="catatan">
                  Catatan
                </label>

                <textarea
                  id="catatan"
                  name="catatan"
                  rows="4"
                  placeholder="Tambahkan catatan jika perlu"
                />
              </div>

              <button
                type="submit"
                className="submit-button"
                disabled={submitting}
              >
                {submitting
                  ? "Mengirim..."
                  : metode === "tunai"
                  ? "Konfirmasi Pembayaran Tunai"
                  : "Kirim Pembayaran"}
              </button>
            </form>
          </div>
        </div>
      </div>
    </main>
  );
}

export default PembayaranTagihanSiswaOrtu;
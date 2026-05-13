import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

import logoSekolah from "../../assets/logo.png";

import "../../styles/siswaOrtu/PembayaranTagihanSiswaOrtu.css";

function PembayaranTagihanSiswaOrtu() {
  const { id } = useParams();

  const navigate = useNavigate();

  const [metode, setMetode] = useState(null);

  const [metodeList, setMetodeList] = useState([]);

  const [isOpen, setIsOpen] = useState(false);

  const [loading, setLoading] = useState(true);

  const [submitting, setSubmitting] = useState(false);

  const [tagihan, setTagihan] = useState(null);

  const [toast, setToast] = useState(null);

  const showToast = (
    type = "success",
    message = ""
  ) => {
    setToast({ type, message });

    setTimeout(() => {
      setToast(null);
    }, 3000);
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);

        const [tagihanResponse, metodeResponse] =
          await Promise.all([
            fetch(
              `http://localhost:8000/api/tagihan-siswa/${id}`
            ),

            fetch(
              "http://localhost:8000/api/ref-metode-pembayaran"
            ),
          ]);

        const tagihanResult =
          await tagihanResponse.json();

        const metodeResult =
          await metodeResponse.json();

        if (!tagihanResponse.ok) {
          throw new Error(
            tagihanResult.message ||
              "Gagal mengambil data tagihan"
          );
        }

        setTagihan(tagihanResult.data);

        setMetodeList(
          metodeResult.data || []
        );
      } catch (error) {
        console.error(error);

        showToast(
          "error",
          error.message ||
            "Gagal mengambil data"
        );
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchData();
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

    const nominal = Number(
      e.target.nominalBayar.value
    );

    const file =
      e.target.buktiTransfer?.files?.[0];

    if (!metode) {
      showToast(
        "error",
        "Pilih metode pembayaran terlebih dahulu."
      );

      return;
    }

    if (!nominal || nominal <= 0) {
      showToast(
        "error",
        "Nominal pembayaran harus lebih dari 0."
      );

      return;
    }

    if (
      nominal >
      Number(tagihan.SISA_TAGIHAN || 0)
    ) {
      showToast(
        "error",
        "Nominal pembayaran tidak boleh melebihi sisa tagihan."
      );

      return;
    }

    const namaMetode =
      metode.DESKRIPSI_METODE_PEMBAYARAN?.toLowerCase();

    const isBank =
      namaMetode?.includes("bank") ||
      namaMetode?.includes("transfer");

    if (isBank && !file) {
      showToast(
        "error",
        "Untuk pembayaran bank, bukti pembayaran wajib diunggah."
      );

      return;
    }

    try {
      setSubmitting(true);

      const formData = new FormData();

      formData.append(
        "ID_TAGIHAN_SISWA",
        tagihan.ID_TAGIHAN_SISWA
      );

      formData.append(
        "ID_METODE_PEMBAYARAN",
        metode.ID_METODE_PEMBAYARAN
      );

      formData.append(
        "JUMLAH_BAYAR",
        nominal
      );

      formData.append(
        "catatan",
        e.target.catatan.value || ""
      );

      if (isBank && file) {
        formData.append(
          "LINK_BUKTI_BAYAR",
          file
        );
      }

      const response = await fetch(
        "http://localhost:8000/api/tr-pembayaran/store",
        {
          method: "POST",
          body: formData,
        }
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(
          result.message ||
            "Gagal mengirim pembayaran"
        );
      }

      showToast(
        "success",
        "Pembayaran berhasil dikirim"
      );

      setTimeout(() => {
        navigate(
          `/siswa-ortu/utama/${tagihan.ID_SISWA_TETAP}`
        );
      }, 3000);
    } catch (error) {
      console.error(error);

      showToast(
        "error",
        error.message ||
          "Terjadi kesalahan saat pembayaran"
      );
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="payment-page">
        Loading...
      </div>
    );
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
              navigate(
                `/siswa-ortu/utama/${tagihan.ID_SISWA_TETAP}`
              )
            }
          >
            <span className="back-icon">
              ←
            </span>

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
                    Sistem Pembayaran
                    Sekolah
                  </p>

                  <h2 className="school-name">
                    SMK BOPKRI 2
                    YOGYAKARTA
                  </h2>
                </div>
              </div>

              <div className="payment-heading">
                <h1 className="payment-title">
                  Pembayaran Tagihan
                </h1>

                <p className="payment-subtitle">
                  Lengkapi form berikut
                  untuk mengajukan
                  pembayaran tagihan.
                </p>
              </div>
            </header>

            <section className="payment-detail-grid">
              <div className="payment-detail-item">
                <label>Tagihan</label>

                <p>
                  {tagihan?.JENIS_TAGIHAN
                    ?.DESKRIPSI_JENIS_TAGIHAN ||
                    "-"}
                </p>
              </div>

              <div className="payment-detail-item">
                <label>Status</label>

                <p>
                  {
                    tagihan.STATUS_TAGIHAN_SISWA
                  }
                </p>
              </div>

              <div className="payment-detail-item">
                <label>
                  Total Tagihan
                </label>

                <p>
                  {formatRupiah(
                    tagihan.JUMLAH_TAGIHAN_SISWA
                  )}
                </p>
              </div>

              <div className="payment-detail-item">
                <label>
                  Sudah Dibayar
                </label>

                <p>
                  {formatRupiah(
                    tagihan.TOTAL_PEMBAYARAN
                  )}
                </p>
              </div>

              <div className="payment-detail-item payment-detail-item-full">
                <label>
                  Sisa Tagihan
                </label>

                <p>
                  {formatRupiah(
                    tagihan.SISA_TAGIHAN
                  )}
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
                  id="nominalBayar"
                  name="nominalBayar"
                  placeholder="Masukkan nominal pembayaran"
                />
              </div>

              <div className="form-group">
                <label>
                  Metode Pembayaran
                </label>

                <div className="custom-select">
                  <button
                    type="button"
                    className={`custom-select-trigger ${
                      !metode
                        ? "is-placeholder"
                        : ""
                    }`}
                    onClick={() =>
                      setIsOpen(!isOpen)
                    }
                  >
                    <span>
                      {!metode
                        ? "Pilih metode pembayaran"
                        : metode.DESKRIPSI_METODE_PEMBAYARAN}
                    </span>

                    <span className="custom-select-arrow">
                      ▾
                    </span>
                  </button>

                  {isOpen && (
                    <div className="custom-select-menu">
                      {metodeList.map(
                        (item) => (
                          <button
                            key={
                              item.ID_METODE_PEMBAYARAN
                            }
                            type="button"
                            className="custom-select-option"
                            onClick={() => {
                              setMetode(
                                item
                              );

                              setIsOpen(
                                false
                              );
                            }}
                          >
                            {
                              item.DESKRIPSI_METODE_PEMBAYARAN
                            }
                          </button>
                        )
                      )}
                    </div>
                  )}
                </div>
              </div>

              <div className="payment-method-content">
                {metode &&
                  (metode.DESKRIPSI_METODE_PEMBAYARAN
                    ?.toLowerCase()
                    ?.includes(
                      "bank"
                    ) ||
                    metode.DESKRIPSI_METODE_PEMBAYARAN
                      ?.toLowerCase()
                      ?.includes(
                        "transfer"
                      )) && (
                    <>
                      <div className="payment-info">
                        <p className="payment-info-title">
                          Informasi
                          Rekening
                        </p>

                        <p>
                          Bank BRI -
                          1234567890
                        </p>

                        <p>
                          a.n. SMK
                          BOPKRI 2
                          Yogyakarta
                        </p>
                      </div>

                      <div className="form-group">
                        <label htmlFor="buktiTransfer">
                          Upload Bukti
                          Pembayaran
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

                {metode &&
                  metode.DESKRIPSI_METODE_PEMBAYARAN
                    ?.toLowerCase()
                    ?.includes(
                      "tunai"
                    ) && (
                    <div className="payment-info payment-info-tunai">
                      <p className="payment-info-title">
                        Instruksi
                        Pembayaran
                        Tunai
                      </p>

                      <p>
                        Silakan
                        melakukan
                        pembayaran
                        langsung ke
                        bendahara
                        sekolah.
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
                  : "Kirim Pembayaran"}
              </button>
            </form>
          </div>
        </div>
      </div>

    {toast && (
      <div className="payment-toast-container">
        <div
          className={`payment-toast-box ${toast.type}`}
        >
          <span className="payment-toast-text">
            {toast.message}
          </span>
        </div>
      </div>
    )}
    </main>
  );
}

export default PembayaranTagihanSiswaOrtu;
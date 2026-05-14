import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import logoSekolah from "../../assets/logo.png";
import "../../styles/siswaOrtu/PembayaranTagihanSiswaOrtu.css";

function PembayaranTagihanSiswaOrtu() {
  // Variabel id merujuk pada ID_TAGIHAN_SISWA yang akan dibayar
  const { id } = useParams();
  const navigate = useNavigate();

  const [metode, setMetode] = useState(null);
  const [metodeList, setMetodeList] = useState([]);
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [tagihan, setTagihan] = useState(null);
  const [toast, setToast] = useState(null);

  const showToast = (type = "success", message = "") => {
    setToast({ type, message });
    setTimeout(() => {
      setToast(null);
    }, 3000);
  };

  useEffect(() => {
    const fetchData = async () => {
      const token = localStorage.getItem("token");
      if (!token) {
        navigate("/login");
        return;
      }

      try {
        setLoading(true);
        const [tagihanResponse, metodeResponse] = await Promise.all([
          fetch(`http://localhost:8000/api/tagihan-siswa/${id}`, {
            headers: {
              "Authorization": `Bearer ${token}`,
              "Accept": "application/json",
            },
          }),
          fetch("http://localhost:8000/api/ref-metode-pembayaran", {
            headers: {
              "Authorization": `Bearer ${token}`,
              "Accept": "application/json",
            },
          }),
        ]);

        const tagihanData = await tagihanResponse.json();
        const metodeData = await metodeResponse.json();

        if (tagihanResponse.ok && tagihanData.success) {
          setTagihan(tagihanData.data);
        } else {
          showToast("error", "Gagal memuat rincian tagihan.");
        }

        if (metodeResponse.ok && (metodeData.success || Array.isArray(metodeData.data))) {
          const list = Array.isArray(metodeData) ? metodeData : metodeData.data || [];
          setMetodeList(list);
          if (list.length > 0) setMetode(list[0]);
        }
      } catch (error) {
        showToast("error", "Kesalahan koneksi ke server.");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [id, navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    const token = localStorage.getItem("token");
    const catatan = e.target.catatan?.value || "";

    try {
      const response = await fetch("http://localhost:8000/api/tr-pembayaran/store", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json",
        },
        body: JSON.stringify({
          ID_TAGIHAN_SISWA: id,
          ID_METODE_PEMBAYARAN: metode?.ID_METODE_PEMBAYARAN || 1,
          JUMLAH_BAYAR: tagihan?.SISA_TAGIHAN || 0,
          CATATAN: catatan,
        }),
      });

      const resData = await response.json();

      if (response.ok && resData.success) {
        showToast("success", "Konfirmasi pembayaran berhasil dikirim!");
        setTimeout(() => {
          navigate("/siswa-ortu/utama");
        }, 1500);
      } else {
        showToast("error", resData.message || "Gagal memproses pembayaran.");
      }
    } catch (error) {
      showToast("error", "Terjadi galat pada jaringan.");
    } finally {
      setSubmitting(false);
    }
  };

  const formatRupiah = (val) =>
    new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(val || 0);

  if (loading) {
    return <div className="payment-loading">Menyiapkan gerbang pembayaran...</div>;
  }

  if (!tagihan) {
    return (
      <div className="payment-container">
        <p className="error-text">Informasi tagihan tidak ditemukan atau akses ditolak.</p>
        <button onClick={() => navigate("/siswa-ortu/utama")} className="back-button">Kembali</button>
      </div>
    );
  }

  return (
    <main className="payment-page-container">
      <div className="payment-wrapper">
        <header className="payment-header">
          <img src={logoSekolah} alt="Logo Sekolah" className="school-logo" />
          <h2>Konfirmasi Pembayaran Administrasi</h2>
        </header>

        <div className="payment-card">
          <div className="payment-info-box">
            <h3>Rincian Pembayaran</h3>
            <div className="info-row">
              <span>Jenis Tagihan</span>
              <strong>{tagihan.JENIS_TAGIHAN?.DESKRIPSI_JENIS_TAGIHAN || "Tagihan Sekolah"}</strong>
            </div>
            <div className="info-row">
              <span>Periode</span>
              <span>{tagihan.BULAN_TAGIHAN_SISWA} {tagihan.TAHUN_TAGIHAN_SISWA}</span>
            </div>
            <div className="info-row total-row">
              <span>Nominal Harus Dibayar</span>
              <strong className="amount-highlight">{formatRupiah(tagihan.SISA_TAGIHAN)}</strong>
            </div>
          </div>

          <div className="payment-form-box">
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Pilih Metode Pembayaran</label>
                <div className="dropdown-container">
                  <button
                    type="button"
                    className="dropdown-button"
                    onClick={() => setIsOpen(!isOpen)}
                  >
                    {metode ? metode.DESKRIPSI_METODE_PEMBAYARAN || metode.nama_metode : "Pilih Metode"}
                    <span className="arrow">{isOpen ? "▲" : "▼"}</span>
                  </button>

                  {isOpen && metodeList.length > 0 && (
                    <ul className="dropdown-list">
                      {metodeList.map((m) => (
                        <li
                          key={m.ID_METODE_PEMBAYARAN || m.id}
                          onClick={() => {
                            setMetode(m);
                            setIsOpen(false);
                          }}
                        >
                          {m.DESKRIPSI_METODE_PEMBAYARAN || m.nama_metode}
                        </li>
                      ))}
                    </ul>
                  )}
                </div>

                {metode && (
                  <div className="method-instruction">
                    <p className="instruction-title">Instruksi Pembayaran {metode.DESKRIPSI_METODE_PEMBAYARAN}</p>
                    <p>Silakan melakukan pembayaran langsung ke bendahara sekolah atau mentransfer ke rekening resmi yang terdaftar.</p>
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

              <button type="submit" className="submit-button" disabled={submitting}>
                {submitting ? "Mengirim..." : "Kirim Pembayaran"}
              </button>
            </form>
          </div>
        </div>
      </div>

      {toast && (
        <div className="payment-toast-container">
          <div className={`payment-toast-box ${toast.type}`}>
            <span className="payment-toast-text">{toast.message}</span>
          </div>
        </div>
      )}
    </main>
  );
}

export default PembayaranTagihanSiswaOrtu;

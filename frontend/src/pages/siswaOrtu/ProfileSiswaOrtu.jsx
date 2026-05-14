import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../../styles/siswaOrtu/ProfileSiswaOrtu.css";

function ProfileSiswaOrtu() {
  const navigate = useNavigate();
  // Catatan: useParams() dihapus karena identitas sudah diurus secara otomatis oleh Token backend

  const [isEdit, setIsEdit] = useState(false);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const [formData, setFormData] = useState({
    namaSiswa: "",
    nis: "",
    nisn: "",
    kelas: "",
    jenisKelamin: "",
    tempatLahir: "",
    tanggalLahir: "",
    alamat: "",
    noHp: "",
    tahunLulus: "",
    namaAyah: "",
    pekerjaanAyah: "",
    namaIbu: "",
    pekerjaanIbu: "",
    namaWali: "",
  });

  const formatTanggalIndonesia = (dateStr) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;

    return date.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  };

  const formatAlamat = (siswa) => {
    const parts = [];
    if (siswa?.ALAMAT) parts.push(siswa.ALAMAT);
    return parts.join(", ") || "-";
  };

  useEffect(() => {
    const fetchProfile = async () => {
      const token = localStorage.getItem("token");
      if (!token) {
        navigate("/login");
        return;
      }

      try {
        setLoading(true);
        const response = await fetch("http://localhost:8000/api/siswa-ortu/profile", {
          headers: {
            "Authorization": `Bearer ${token}`,
            "Accept": "application/json",
          },
        });
        const resData = await response.json();

        if (response.ok && resData.success) {
          const s = resData.data;
          setFormData({
            namaSiswa: s.NAMA_SISWA_TETAP || s.nama_siswa || "",
            nis: s.ID_SISWA_TETAP || "",
            nisn: s.NISN_SISWA || s.nisn || "",
            kelas: s.KODE_TA || s.kelas || "",
            jenisKelamin: s.JENIS_KELAMIN || "-",
            tempatLahir: s.TEMPAT_LAHIR || "-",
            tanggalLahir: s.TANGGAL_LAHIR || "",
            alamat: formatAlamat(s),
            noHp: s.NO_HP_SISWA || "",
            tahunLulus: s.TAHUN_LULUS || "-",
            namaAyah: s.NAMA_AYAH || "-",
            pekerjaanAyah: s.PEKERJAAN_AYAH_SISWA || "",
            namaIbu: s.NAMA_IBU || "-",
            pekerjaanIbu: s.PEKERJAAN_IBU_SISWA || "",
            namaWali: s.NAMA_WALI_SISWA || "",
          });
        }
      } catch (error) {
        console.error("Gagal memuat profil:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchProfile();
  }, [navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    const token = localStorage.getItem("token");
    setSaving(true);
    try {
      const response = await fetch("http://localhost:8000/api/siswa-ortu/profile", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json",
        },
        body: JSON.stringify({
          NO_HP_SISWA: formData.noHp,
          PEKERJAAN_AYAH_SISWA: formData.pekerjaanAyah,
          PEKERJAAN_IBU_SISWA: formData.pekerjaanIbu,
          NAMA_WALI_SISWA: formData.namaWali,
        }),
      });

      const resData = await response.json();
      if (response.ok && resData.success) {
        alert("Profil berhasil diperbarui!");
        setIsEdit(false);
      } else {
        alert(resData.message || "Gagal memperbarui profil.");
      }
    } catch (error) {
      alert("Terjadi kesalahan jaringan.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="loading-container">Memuat rincian profil...</div>;
  }

  return (
    <div className="profile-container">
      <header className="profile-top-bar">
        <button onClick={() => navigate("/siswa-ortu/utama")} className="back-btn">
          ‹ Kembali ke Beranda
        </button>
        <h2>Profil Siswa & Data Orang Tua</h2>
        <div className="action-buttons">
          {isEdit ? (
            <>
              <button onClick={handleSave} disabled={saving} className="save-btn">
                {saving ? "Menyimpan..." : "Simpan Perubahan"}
              </button>
              <button onClick={() => setIsEdit(false)} className="cancel-btn">
                Batal
              </button>
            </>
          ) : (
            <button onClick={() => setIsEdit(true)} className="edit-btn">
              Edit Data Wali/Ortu
            </button>
          )}
        </div>
      </header>

      <div className="profile-card-wrapper">
        {/* Seksi Data Diri Siswa (Disabled) */}
        <div className="profile-section">
          <h3>Data Diri Siswa</h3>
          <div className="profile-form-grid">
            <div className="form-group">
              <label>Nama Lengkap</label>
              <input value={formData.namaSiswa} disabled />
            </div>
            <div className="form-group">
              <label>NISN</label>
              <input value={formData.nisn} disabled />
            </div>
            <div className="form-group">
              <label>ID Siswa / No Induk</label>
              <input value={formData.nis} disabled />
            </div>
            <div className="form-group">
              <label>Kelas</label>
              <input value={formData.kelas} disabled />
            </div>
            <div className="form-group">
              <label>Tempat Lahir</label>
              <input value={formData.tempatLahir} disabled />
            </div>
            <div className="form-group">
              <label>Tanggal Lahir</label>
              <input value={formatTanggalIndonesia(formData.tanggalLahir)} disabled />
            </div>
            <div className="form-group full-width">
              <label>Alamat Tinggal</label>
              <input value={formData.alamat} disabled />
            </div>
            <div className="form-group">
              <label>Nomor HP Siswa</label>
              <input
                name="noHp"
                value={formData.noHp}
                onChange={handleChange}
                disabled={!isEdit}
                placeholder="Masukkan No HP aktif"
              />
            </div>
          </div>
        </div>

        {/* Seksi Data Wali / Orang Tua */}
        <div className="profile-section">
          <h3>Data Orang Tua / Wali</h3>
          <div className="profile-form-grid">
            <div className="form-group">
              <label>Nama Ayah</label>
              <input value={formData.namaAyah} disabled />
            </div>
            <div className="form-group">
              <label>Pekerjaan Ayah</label>
              <input
                name="pekerjaanAyah"
                value={formData.pekerjaanAyah}
                onChange={handleChange}
                disabled={!isEdit}
              />
            </div>
            <div className="form-group">
              <label>Nama Ibu</label>
              <input value={formData.namaIbu} disabled />
            </div>
            <div className="form-group">
              <label>Pekerjaan Ibu</label>
              <input
                name="pekerjaanIbu"
                value={formData.pekerjaanIbu}
                onChange={handleChange}
                disabled={!isEdit}
              />
            </div>
            <div className="form-group full-width">
              <label>Nama Wali</label>
              <input
                name="namaWali"
                value={formData.namaWali}
                onChange={handleChange}
                disabled={!isEdit}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProfileSiswaOrtu;
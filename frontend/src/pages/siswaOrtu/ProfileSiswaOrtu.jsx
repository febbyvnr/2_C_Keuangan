import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../../styles/siswaOrtu/ProfileSiswaOrtu.css";

function ProfileSiswaOrtu() {
  const navigate = useNavigate();

  const [isEdit, setIsEdit] = useState(false);
  const [loading, setLoading] = useState(true);

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

    if (siswa.ALAMAT_JALAN_SISWA) parts.push(siswa.ALAMAT_JALAN_SISWA);

    const rtRw = [];
    if (siswa.RT_SISWA) rtRw.push(`RT ${siswa.RT_SISWA}`);
    if (siswa.RW_SISWA) rtRw.push(`RW ${siswa.RW_SISWA}`);
    if (rtRw.length) parts.push(rtRw.join(" "));

    if (siswa.KELURAHAN_SISWA) parts.push(siswa.KELURAHAN_SISWA);
    if (siswa.KECAMATAN_SISWA) parts.push(siswa.KECAMATAN_SISWA);
    if (siswa.KOTA_KAB_SISWA) parts.push(siswa.KOTA_KAB_SISWA);
    if (siswa.PROVINSI_SISWA) parts.push(siswa.PROVINSI_SISWA);
    if (siswa.KODE_POS_SISWA) parts.push(siswa.KODE_POS_SISWA);

    return parts.join(", ");
  };

  useEffect(() => {
    fetchProfile();
  }, []);

  const fetchProfile = async () => {
    try {
      setLoading(true);

      const res = await fetch("http://localhost:8000/api/siswa-ortu/profile", {
        headers: {
          Accept: "application/json",
        },
      });
      const json = await res.json();

      console.log("PROFILE RESPONSE:", json);

      const siswa = json?.data || null;

      if (siswa) {
        setFormData({
          namaSiswa: siswa.NAMA_SISWA_TETAP || "",
          nis: siswa.ID_PENDAFTARAN || "",
          nisn: siswa.NISN_SISWA || "",
          kelas: "-",
          jenisKelamin:
            siswa.GENDER_SISWA === "L"
              ? "Laki-laki"
              : siswa.GENDER_SISWA === "P"
              ? "Perempuan"
              : siswa.GENDER_SISWA || "",
          tempatLahir: siswa.TEMPAT_LAHIR_SISWA || "",
          tanggalLahir: formatTanggalIndonesia(siswa.TGL_LAHIR_SISWA),
          alamat: formatAlamat(siswa),
          noHp: siswa.NO_HP_SISWA || "",
          tahunLulus: siswa.TAHUN_LULUS || "",
          namaAyah: siswa.NAMA_AYAH_SISWA || "",
          pekerjaanAyah: siswa.PEKERJAAN_AYAH_SISWA || "",
          namaIbu: siswa.NAMA_IBU_SISWA || "",
          pekerjaanIbu: siswa.PEKERJAAN_IBU_SISWA || "",
          namaWali: siswa.NAMA_WALI_SISWA || "-",
        });
      }
    } catch (error) {
      console.error("Gagal mengambil profile:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleCancel = async () => {
    await fetchProfile();
    setIsEdit(false);
  };

  const handleSave = async () => {
    try {
      const payload = {
        NO_HP_SISWA: formData.noHp,
        PEKERJAAN_AYAH_SISWA: formData.pekerjaanAyah,
        PEKERJAAN_IBU_SISWA: formData.pekerjaanIbu,
        NAMA_WALI_SISWA: formData.namaWali,
      };

      const res = await fetch("http://localhost:8000/api/siswa-ortu/profile", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      });

      const json = await res.json();

      if (!res.ok) {
        throw new Error(json.message || "Gagal menyimpan profile");
      }

      await fetchProfile();
      alert("Profile berhasil disimpan");
      setIsEdit(false);
    } catch (error) {
      console.error("Gagal menyimpan profile:", error);
      alert(error.message || "Terjadi kesalahan saat menyimpan");
    }
  };

  const avatarText = useMemo(() => {
    return (formData.namaSiswa || "S")
      .split(" ")
      .map((word) => word[0])
      .slice(0, 2)
      .join("")
      .toUpperCase();
  }, [formData.namaSiswa]);

  if (loading) {
    return <div className="profile-page">Loading...</div>;
  }

  return (
    <div className="profile-page">
      <div className="profile-container">
        <div className="profile-topbar">
          <button
            className="back-btn"
            onClick={() => navigate("/siswa-ortu/utama")}
          >
            ← Kembali
          </button>

          {!isEdit ? (
            <button className="edit-btn" onClick={() => setIsEdit(true)}>
              Edit Profile
            </button>
          ) : (
            <div className="topbar-actions">
              <button className="cancel-btn" onClick={handleCancel}>
                Batal
              </button>
              <button className="save-btn" onClick={handleSave}>
                Simpan
              </button>
            </div>
          )}
        </div>

        <div className="profile-card profile-header-card">
          <div className="profile-avatar-large">{avatarText}</div>
          <div className="profile-header-info">
            <h1>{formData.namaSiswa || "Siswa"}</h1>
            <p>{formData.kelas || "-"}</p>
            <span className="profile-chip">Siswa / Orang Tua</span>
          </div>
        </div>

        <div className="profile-grid">
          <div className="profile-card">
            <h2>Data Siswa</h2>

            <div className="profile-form-grid">
              <div className="form-group">
                <label>Nama Siswa</label>
                <input value={formData.namaSiswa} disabled />
              </div>

              <div className="form-group">
                <label>NIS</label>
                <input value={formData.nis} disabled />
              </div>

              <div className="form-group">
                <label>NISN</label>
                <input value={formData.nisn} disabled />
              </div>

              <div className="form-group">
                <label>Kelas</label>
                <input value={formData.kelas} disabled />
              </div>

              <div className="form-group">
                <label>Jenis Kelamin</label>
                <input value={formData.jenisKelamin} disabled />
              </div>

              <div className="form-group">
                <label>Tempat Lahir</label>
                <input value={formData.tempatLahir} disabled />
              </div>

              <div className="form-group">
                <label>Tanggal Lahir</label>
                <input value={formData.tanggalLahir} disabled />
              </div>

              <div className="form-group">
                <label>No HP</label>
                <input
                  name="noHp"
                  value={formData.noHp}
                  onChange={handleChange}
                  disabled={!isEdit}
                />
              </div>

              <div className="form-group full-width">
                <label>Alamat</label>
                <textarea
                  name="alamat"
                  value={formData.alamat}
                  onChange={handleChange}
                  disabled
                  rows="3"
                />
              </div>

              <div className="form-group">
                <label>Tahun Lulus</label>
                <input value={formData.tahunLulus} disabled />
              </div>
            </div>
          </div>

          <div className="profile-card">
            <h2>Data Orang Tua / Wali</h2>

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
    </div>
  );
}

export default ProfileSiswaOrtu;

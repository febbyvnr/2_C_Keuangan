import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../../styles/siswaOrtu/ProfileSiswaOrtu.css";

function ProfileSiswaOrtu() {
  const navigate = useNavigate();
  const [isEdit, setIsEdit] = useState(false);

  const [formData, setFormData] = useState({
    namaSiswa: "Andi Susanto",
    nis: "2026001",
    nisn: "1234567890",
    kelas: "X RPL",
    jenisKelamin: "Laki-laki",
    tempatLahir: "Yogyakarta",
    tanggalLahir: "12 Januari 2010",
    alamat: "Jl. Kaliurang No. 10, Sleman, Yogyakarta",
    noHp: "081234567890",
    tahunLulus: "2028",
    namaAyah: "Budi Santoso",
    pekerjaanAyah: "Wiraswasta",
    namaIbu: "Siti Aminah",
    pekerjaanIbu: "Ibu Rumah Tangga",
    namaWali: "-",
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSave = () => {
    // nanti sambungkan ke API PUT/POST
    console.log("Data disimpan:", formData);
    setIsEdit(false);
  };

  return (
    <div className="profile-page">
      <div className="profile-container">
        <div className="profile-topbar">
          <button className="back-btn" onClick={() => navigate("/siswa-ortu")}>
            ← Kembali
          </button>

          {!isEdit ? (
            <button className="edit-btn" onClick={() => setIsEdit(true)}>
              Edit Profile
            </button>
          ) : (
            <div className="topbar-actions">
              <button className="cancel-btn" onClick={() => setIsEdit(false)}>
                Batal
              </button>
              <button className="save-btn" onClick={handleSave}>
                Simpan
              </button>
            </div>
          )}
        </div>

        <div className="profile-card profile-header-card">
          <div className="profile-avatar-large">AS</div>
          <div className="profile-header-info">
            <h1>{formData.namaSiswa}</h1>
            <p>{formData.kelas}</p>
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
                  disabled={!isEdit}
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
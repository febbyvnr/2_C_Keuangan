import { useEffect, useMemo, useRef, useState } from "react";
import axios from "axios";
import "../../styles/bendahara/Tagihan.css";

const API_BASE_URL = "http://127.0.0.1:8000/api";

function Tagihan() {
  const [search, setSearch] = useState("");
  const [filterKelas, setFilterKelas] = useState("Semua");
  const [filterStatus, setFilterStatus] = useState("Semua");

  const [tagihanList, setTagihanList] = useState([]);
  const [siswaOptions, setSiswaOptions] = useState([]);
  const [jenisPembayaranOptions, setJenisPembayaranOptions] = useState([]);

  const [loading, setLoading] = useState(false);
  const [submitLoading, setSubmitLoading] = useState(false);

  const [siswaKeyword, setSiswaKeyword] = useState("");
  const [showSiswaDropdown, setShowSiswaDropdown] = useState(false);
  const siswaDropdownRef = useRef(null);

  const initialForm = {
    ID_SISWA_TETAP: "",
    ID_JENIS_PEMBAYARAN: "",
    BULAN_TAGIHAN_SISWA: "",
    TAHUN_TAGIHAN_SISWA: "",
    JUMLAH_TAGIHAN_SISWA: "",
    STATUS_TAGIHAN_SISWA: "Belum Bayar",
    DUEDATE_TAGIHAN_SISWA: "",
  };

  const [formData, setFormData] = useState(initialForm);
  const [isEdit, setIsEdit] = useState(false);
  const [selectedId, setSelectedId] = useState(null);

  const formatRupiah = (value) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(Number(value) || 0);

  const formatTanggal = (value) => {
    if (!value) return "-";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "-";

    return date.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  };

  const getStatusClass = (status) => {
    switch ((status || "").toLowerCase()) {
      case "belum bayar":
      case "belum dibayar":
        return "belum-bayar";
      case "mengangsur":
      case "menunggu verifikasi":
        return "mengangsur";
      case "belum lunas":
        return "belum-lunas";
      case "lunas":
      case "sudah bayar":
        return "lunas";
      default:
        return "";
    }
  };

  const normalizeSiswaOptions = (rawData) => {
    if (!Array.isArray(rawData)) return [];

    return rawData
      .map((item) => {
        const id =
          item.ID_SISWA_TETAP ??
          item.id_siswa_tetap ??
          item.ID_SISWA ??
          item.id_siswa ??
          item.id ??
          "";

        const nama =
          item.NAMA_SISWA_TETAP ??
          item.nama_siswa_tetap ??
          item.NAMA_SISWA ??
          item.nama_siswa ??
          item.nama ??
          "";

        const kelas =
          item.KELAS_SISWA ??
          item.kelas_siswa ??
          item.KELAS ??
          item.kelas ??
          item.NAMA_KELAS ??
          item.nama_kelas ??
          "";

        return {
          id: String(id),
          nama: String(nama || "").trim(),
          kelas: String(kelas || "").trim(),
          raw: item,
        };
      })
      .filter((item) => item.id !== "")
      .map((item) => ({
        ...item,
        label: item.kelas ? `${item.nama} - ${item.kelas}` : item.nama || `Siswa #${item.id}`,
      }));
  };

  const kelasOptions = useMemo(() => {
    const kelasSet = new Set(
      tagihanList.map((item) => item.SISWA?.KELAS_SISWA).filter(Boolean)
    );
    return ["Semua", ...Array.from(kelasSet)];
  }, [tagihanList]);

  const summary = useMemo(() => {
    const total = tagihanList.length;

    const belumLunas = tagihanList.filter((item) => {
      const status = (item.STATUS_TAGIHAN_SISWA || "").toLowerCase();
      return (
        status === "belum bayar" ||
        status === "belum dibayar" ||
        status === "mengangsur" ||
        status === "belum lunas"
      );
    }).length;

    const lunas = tagihanList.filter((item) => {
      const status = (item.STATUS_TAGIHAN_SISWA || "").toLowerCase();
      return status === "lunas" || status === "sudah bayar";
    }).length;

    const totalNominal = tagihanList.reduce(
      (acc, item) => acc + Number(item.JUMLAH_TAGIHAN_SISWA || 0),
      0
    );

    return { total, belumLunas, lunas, totalNominal };
  }, [tagihanList]);

  const filteredData = useMemo(() => {
    return tagihanList.filter((item) => {
      const namaSiswa = item.SISWA?.NAMA_SISWA_TETAP || "";
      const jenisTagihan =
        item.JENIS_PEMBAYARAN?.DESKRIPSI_JENIS_PEMBAYARAN || "";
      const kode = String(item.ID_TAGIHAN_SISWA || "");
      const kelas = item.SISWA?.KELAS_SISWA || "";
      const status = item.STATUS_TAGIHAN_SISWA || "";

      const keyword = search.toLowerCase();

      const matchesSearch =
        namaSiswa.toLowerCase().includes(keyword) ||
        jenisTagihan.toLowerCase().includes(keyword) ||
        kode.toLowerCase().includes(keyword);

      const matchesKelas =
        filterKelas === "Semua" ? true : kelas === filterKelas;

      const matchesStatus =
        filterStatus === "Semua" ? true : status === filterStatus;

      return matchesSearch && matchesKelas && matchesStatus;
    });
  }, [tagihanList, search, filterKelas, filterStatus]);

  const filteredSiswaOptions = useMemo(() => {
    const keyword = siswaKeyword.trim().toLowerCase();

    if (!keyword) return siswaOptions.slice(0, 8);

    return siswaOptions
      .filter((item) => {
        return (
          item.nama.toLowerCase().includes(keyword) ||
          item.kelas.toLowerCase().includes(keyword) ||
          item.id.toLowerCase().includes(keyword)
        );
      })
      .slice(0, 8);
  }, [siswaKeyword, siswaOptions]);

  useEffect(() => {
    fetchTagihan();
    fetchSiswaOptions();
    fetchJenisPembayaranOptions();
  }, []);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        siswaDropdownRef.current &&
        !siswaDropdownRef.current.contains(event.target)
      ) {
        setShowSiswaDropdown(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const fetchTagihan = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API_BASE_URL}/tagihan-siswa`);
      setTagihanList(Array.isArray(res.data?.data) ? res.data.data : []);
    } catch (error) {
      console.error("Gagal mengambil data tagihan:", error);
      alert("Gagal mengambil data tagihan siswa.");
    } finally {
      setLoading(false);
    }
  };

  const fetchSiswaOptions = async () => {
    try {
      const res = await axios.get(`${API_BASE_URL}/siswa`);

      const rawData =
        res.data?.data ??
        res.data?.results ??
        res.data?.siswa ??
        res.data ??
        [];

      const normalized = normalizeSiswaOptions(rawData);
      setSiswaOptions(normalized);
    } catch (error) {
      console.warn("Endpoint siswa belum tersedia, dropdown siswa masih kosong.", error);
      setSiswaOptions([]);
    }
  };

  const fetchJenisPembayaranOptions = async () => {
    try {
      const res = await axios.get(`${API_BASE_URL}/jenis-pembayaran`);
      setJenisPembayaranOptions(Array.isArray(res.data?.data) ? res.data.data : []);
    } catch (error) {
      console.warn(
        "Endpoint jenis pembayaran belum tersedia, dropdown jenis pembayaran masih kosong."
      );
      setJenisPembayaranOptions([]);
    }
  };

  const resetForm = () => {
    setFormData(initialForm);
    setSiswaKeyword("");
    setShowSiswaDropdown(false);
    setIsEdit(false);
    setSelectedId(null);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSelectSiswa = (item) => {
    setFormData((prev) => ({
      ...prev,
      ID_SISWA_TETAP: item.id,
    }));
    setSiswaKeyword(item.label);
    setShowSiswaDropdown(false);
  };

  const handleSiswaInputChange = (e) => {
    const value = e.target.value;
    setSiswaKeyword(value);
    setShowSiswaDropdown(true);

    setFormData((prev) => ({
      ...prev,
      ID_SISWA_TETAP: "",
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (
      !formData.ID_SISWA_TETAP ||
      !formData.ID_JENIS_PEMBAYARAN ||
      !formData.BULAN_TAGIHAN_SISWA ||
      !formData.TAHUN_TAGIHAN_SISWA ||
      !formData.JUMLAH_TAGIHAN_SISWA ||
      !formData.DUEDATE_TAGIHAN_SISWA
    ) {
      alert("Semua field wajib diisi.");
      return;
    }

    if (Number(formData.JUMLAH_TAGIHAN_SISWA) <= 0) {
      alert("Jumlah tagihan harus lebih dari 0.");
      return;
    }

    const payload = {
      ...formData,
      JUMLAH_TAGIHAN_SISWA: Number(formData.JUMLAH_TAGIHAN_SISWA),
    };

    try {
      setSubmitLoading(true);

      if (isEdit && selectedId) {
        await axios.put(`${API_BASE_URL}/tagihan-siswa/${selectedId}`, payload);
        alert("Tagihan berhasil diperbarui.");
      } else {
        await axios.post(`${API_BASE_URL}/tagihan-siswa`, payload);
        alert("Tagihan berhasil ditambahkan.");
      }

      await fetchTagihan();
      resetForm();
    } catch (error) {
      console.error("Gagal menyimpan tagihan:", error);
      alert(error.response?.data?.message || "Gagal menyimpan data tagihan.");
    } finally {
      setSubmitLoading(false);
    }
  };

  const handleEdit = (item) => {
    const siswaId =
      item.ID_SISWA_TETAP ||
      item.SISWA?.ID_SISWA_TETAP ||
      "";

    const siswaNama =
      item.SISWA?.NAMA_SISWA_TETAP ||
      "";

    const siswaKelas =
      item.SISWA?.KELAS_SISWA ||
      "";

    setIsEdit(true);
    setSelectedId(item.ID_TAGIHAN_SISWA);
    setFormData({
      ID_SISWA_TETAP: siswaId,
      ID_JENIS_PEMBAYARAN: item.ID_JENIS_PEMBAYARAN || "",
      BULAN_TAGIHAN_SISWA: item.BULAN_TAGIHAN_SISWA || "",
      TAHUN_TAGIHAN_SISWA: item.TAHUN_TAGIHAN_SISWA || "",
      JUMLAH_TAGIHAN_SISWA: item.JUMLAH_TAGIHAN_SISWA || "",
      STATUS_TAGIHAN_SISWA: item.STATUS_TAGIHAN_SISWA || "Belum Bayar",
      DUEDATE_TAGIHAN_SISWA: item.DUEDATE_TAGIHAN_SISWA
        ? String(item.DUEDATE_TAGIHAN_SISWA).slice(0, 10)
        : "",
    });

    setSiswaKeyword(
      siswaKelas ? `${siswaNama} - ${siswaKelas}` : siswaNama
    );

    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleDelete = async (item) => {
    if (Number(item.TOTAL_PEMBAYARAN || 0) > 0) {
      alert("Tagihan tidak dapat dihapus karena sudah memiliki pembayaran.");
      return;
    }

    const confirmed = window.confirm(
      `Hapus tagihan #${item.ID_TAGIHAN_SISWA} untuk ${
        item.SISWA?.NAMA_SISWA_TETAP || "siswa ini"
      }?`
    );

    if (!confirmed) return;

    try {
      await axios.delete(`${API_BASE_URL}/tagihan-siswa/${item.ID_TAGIHAN_SISWA}`);
      alert("Tagihan berhasil dihapus.");
      await fetchTagihan();
    } catch (error) {
      console.error("Gagal menghapus tagihan:", error);
      alert(error.response?.data?.message || "Gagal menghapus tagihan.");
    }
  };

  const handleResetFilter = () => {
    setSearch("");
    setFilterKelas("Semua");
    setFilterStatus("Semua");
  };

  const handleExport = () => {
    alert("Export laporan tagihan siswa masih dummy.");
  };

  return (
    <div className="tagihan-page">
      <div className="tagihan-header">
        <h1>Manajemen Tagihan Siswa</h1>
        <p>Buat, kelola, cari, dan pantau tagihan administrasi sekolah untuk siswa.</p>
      </div>

      <div className="tagihan-summary">
        <div className="summary-box">
          <p>Total Tagihan</p>
          <h3>{summary.total}</h3>
        </div>
        <div className="summary-box">
          <p>Belum Lunas</p>
          <h3>{summary.belumLunas}</h3>
        </div>
        <div className="summary-box">
          <p>Lunas</p>
          <h3>{summary.lunas}</h3>
        </div>
        <div className="summary-box">
          <p>Total Nominal</p>
          <h3>{formatRupiah(summary.totalNominal)}</h3>
        </div>
      </div>

      <section className="tagihan-card">
        <h2 className="tagihan-card-title">
          {isEdit ? "Ubah Tagihan" : "Buat Tagihan Baru"}
        </h2>

        <form className="tagihan-form" onSubmit={handleSubmit}>
          <div className="tagihan-form-grid">
            <div className="form-group siswa-search-group" ref={siswaDropdownRef}>
              <label htmlFor="siswa-search">Siswa</label>
              <input
                id="siswa-search"
                type="text"
                placeholder="Cari nama siswa / kelas / ID siswa"
                value={siswaKeyword}
                onChange={handleSiswaInputChange}
                onFocus={() => setShowSiswaDropdown(true)}
                autoComplete="off"
              />

              <input
                type="hidden"
                name="ID_SISWA_TETAP"
                value={formData.ID_SISWA_TETAP}
              />

              {showSiswaDropdown && (
                <div className="siswa-dropdown">
                  {filteredSiswaOptions.length > 0 ? (
                    filteredSiswaOptions.map((item) => (
                      <button
                        key={item.id}
                        type="button"
                        className={`siswa-dropdown-item ${
                          formData.ID_SISWA_TETAP === item.id ? "active" : ""
                        }`}
                        onClick={() => handleSelectSiswa(item)}
                      >
                        <span className="siswa-dropdown-name">{item.nama || `Siswa #${item.id}`}</span>
                        <span className="siswa-dropdown-meta">
                          {item.kelas ? `${item.kelas} • ` : ""}ID: {item.id}
                        </span>
                      </button>
                    ))
                  ) : (
                    <div className="siswa-dropdown-empty">
                      Siswa tidak ditemukan
                    </div>
                  )}
                </div>
              )}
            </div>

            <div className="form-group">
              <label htmlFor="ID_JENIS_PEMBAYARAN">Jenis Tagihan</label>
              <select
                id="ID_JENIS_PEMBAYARAN"
                name="ID_JENIS_PEMBAYARAN"
                value={formData.ID_JENIS_PEMBAYARAN}
                onChange={handleChange}
              >
                <option value="">Pilih jenis tagihan</option>
                {jenisPembayaranOptions.map((item) => (
                  <option
                    key={item.ID_JENIS_PEMBAYARAN}
                    value={item.ID_JENIS_PEMBAYARAN}
                  >
                    {item.DESKRIPSI_JENIS_PEMBAYARAN}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="BULAN_TAGIHAN_SISWA">Bulan Tagihan</label>
              <input
                type="text"
                id="BULAN_TAGIHAN_SISWA"
                name="BULAN_TAGIHAN_SISWA"
                placeholder="Contoh: Februari"
                value={formData.BULAN_TAGIHAN_SISWA}
                onChange={handleChange}
              />
            </div>

            <div className="form-group">
              <label htmlFor="TAHUN_TAGIHAN_SISWA">Tahun Tagihan</label>
              <input
                type="text"
                id="TAHUN_TAGIHAN_SISWA"
                name="TAHUN_TAGIHAN_SISWA"
                placeholder="Contoh: 2026"
                value={formData.TAHUN_TAGIHAN_SISWA}
                onChange={handleChange}
              />
            </div>

            <div className="form-group">
              <label htmlFor="JUMLAH_TAGIHAN_SISWA">Jumlah Tagihan (Rp)</label>
              <input
                type="number"
                id="JUMLAH_TAGIHAN_SISWA"
                name="JUMLAH_TAGIHAN_SISWA"
                placeholder="Masukkan jumlah tagihan"
                value={formData.JUMLAH_TAGIHAN_SISWA}
                onChange={handleChange}
                min="0"
              />
            </div>

            <div className="form-group">
              <label htmlFor="DUEDATE_TAGIHAN_SISWA">Jatuh Tempo</label>
              <input
                type="date"
                id="DUEDATE_TAGIHAN_SISWA"
                name="DUEDATE_TAGIHAN_SISWA"
                value={formData.DUEDATE_TAGIHAN_SISWA}
                onChange={handleChange}
              />
            </div>
          </div>

          <div className="tagihan-form-actions">
            <button
              type="button"
              className="btn btn-secondary"
              onClick={resetForm}
              disabled={submitLoading}
            >
              {isEdit ? "Batal" : "Reset"}
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={submitLoading}
            >
              {submitLoading
                ? "Menyimpan..."
                : isEdit
                ? "Simpan Perubahan"
                : "Generate Tagihan"}
            </button>
          </div>
        </form>
      </section>

      <section className="tagihan-card">
        <div className="table-section-header">
          <div>
            <h2 className="table-section-title">Daftar Tagihan Berjalan</h2>
            <p className="table-section-subtitle">
              Menampilkan tagihan dan sisa tunggakan per siswa.
            </p>
          </div>
        </div>

        <div className="filter-bar">
          <div className="filter-item">
            <label htmlFor="search">Cari Siswa / Tagihan</label>
            <input
              id="search"
              type="text"
              placeholder="Cari nama siswa, tagihan, atau ID..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>

          <div className="filter-item">
            <label htmlFor="filterKelas">Kelas</label>
            <select
              id="filterKelas"
              value={filterKelas}
              onChange={(e) => setFilterKelas(e.target.value)}
            >
              {kelasOptions.map((kelas) => (
                <option key={kelas} value={kelas}>
                  {kelas}
                </option>
              ))}
            </select>
          </div>

          <div className="filter-item">
            <label htmlFor="filterStatus">Status</label>
            <select
              id="filterStatus"
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
            >
              <option value="Semua">Semua</option>
              <option value="Belum Bayar">Belum Bayar</option>
              <option value="Belum Lunas">Belum Lunas</option>
              <option value="Lunas">Lunas</option>
            </select>
          </div>

          <div className="filter-actions">
            <button
              type="button"
              className="btn btn-secondary"
              onClick={handleResetFilter}
            >
              Reset Filter
            </button>
            <button
              type="button"
              className="btn btn-primary"
              onClick={handleExport}
            >
              Export
            </button>
          </div>
        </div>

        <div className="table-wrapper">
          <table className="tagihan-table">
            <thead>
              <tr>
                <th>ID Tagihan</th>
                <th>Nama Siswa</th>
                <th>Jenis Tagihan</th>
                <th>Bulan/Tahun</th>
                <th>Jatuh Tempo</th>
                <th>Total</th>
                <th>Sudah Bayar</th>
                <th>Sisa</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>

            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="10">
                    <div className="empty-state">Memuat data tagihan...</div>
                  </td>
                </tr>
              ) : filteredData.length > 0 ? (
                filteredData.map((item) => (
                  <tr key={item.ID_TAGIHAN_SISWA}>
                    <td className="tagihan-id">#{item.ID_TAGIHAN_SISWA}</td>

                    <td>
                      <div className="nama-siswa-cell">
                        <span className="nama-siswa-text">
                          {item.SISWA?.NAMA_SISWA_TETAP || "-"}
                        </span>
                        <small className="kelas-siswa-text">
                          {item.SISWA?.KELAS_SISWA || "-"}
                        </small>
                      </div>
                    </td>

                    <td>
                      {item.JENIS_PEMBAYARAN?.DESKRIPSI_JENIS_PEMBAYARAN || "-"}
                    </td>

                    <td>
                      {item.BULAN_TAGIHAN_SISWA || "-"}{" "}
                      {item.TAHUN_TAGIHAN_SISWA || ""}
                    </td>

                    <td>{formatTanggal(item.DUEDATE_TAGIHAN_SISWA)}</td>

                    <td className="nominal-text">
                      {formatRupiah(item.JUMLAH_TAGIHAN_SISWA)}
                    </td>

                    <td className="nominal-text">
                      {formatRupiah(item.TOTAL_PEMBAYARAN)}
                    </td>

                    <td className="sisa-highlight">
                      {formatRupiah(item.SISA_TAGIHAN)}
                    </td>

                    <td>
                      <span
                        className={`status-badge ${getStatusClass(
                          item.STATUS_TAGIHAN_SISWA
                        )}`}
                      >
                        {item.STATUS_TAGIHAN_SISWA}
                      </span>
                    </td>

                    <td>
                      <div className="action-group">
                        <button
                          type="button"
                          className="btn btn-secondary btn-sm"
                          onClick={() => handleEdit(item)}
                        >
                          Edit
                        </button>

                        {Number(item.TOTAL_PEMBAYARAN || 0) > 0 ? (
                          <button
                            type="button"
                            className="btn btn-muted btn-sm"
                            disabled
                          >
                            Tidak Bisa Hapus
                          </button>
                        ) : (
                          <button
                            type="button"
                            className="btn btn-danger btn-sm"
                            onClick={() => handleDelete(item)}
                          >
                            Hapus
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="10">
                    <div className="empty-state">
                      Tidak ada data tagihan yang sesuai filter.
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </section>
    </div>
  );
}

export default Tagihan;
import { useEffect, useMemo, useRef, useState } from "react";
import axios from "axios";
import "../../styles/bendahara/Tagihan.css";

const API_BASE_URL = "http://127.0.0.1:8000/api";
const ITEMS_PER_PAGE = 10;

const BULAN_OPTIONS = [
  "Januari",
  "Februari",
  "Maret",
  "April",
  "Mei",
  "Juni",
  "Juli",
  "Agustus",
  "September",
  "Oktober",
  "November",
  "Desember",
];

function Tagihan() {
  const [search, setSearch] = useState("");
  const [filterKelas, setFilterKelas] = useState("Semua");
  const [filterStatus, setFilterStatus] = useState("Semua");

  const [tagihanList, setTagihanList] = useState([]);
  const [siswaOptions, setSiswaOptions] = useState([]);
  const [jenisTagihanOptions, setJenisTagihanOptions] = useState([]);
  const [loading, setLoading] = useState(false);
  const [submitLoading, setSubmitLoading] = useState(false);

  const [siswaKeyword, setSiswaKeyword] = useState("");
  const [showSiswaDropdown, setShowSiswaDropdown] = useState(false);
  const siswaDropdownRef = useRef(null);

  const [currentPage, setCurrentPage] = useState(1);

  const initialForm = {
    ID_SISWA_TETAP: "",
    ID_JENIS_TAGIHAN: "",
    BULAN_TAGIHAN_SISWA: "",
    TAHUN_TAGIHAN_SISWA: "",
    JUMLAH_TAGIHAN_SISWA: "",
    STATUS_TAGIHAN_SISWA: "Belum Bayar",
    DUEDATETIME_TAGIHAN_SISWA: "",
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
        label: item.kelas
          ? `${item.nama} - ${item.kelas}`
          : item.nama || `Siswa #${item.id}`,
      }));
  };

  const getComputedStatus = (item) => {
    const totalBayar = Number(item.TOTAL_PEMBAYARAN || 0);
    const sisa = Number(item.SISA_TAGIHAN ?? 0);

    if (sisa <= 0) return "Lunas";
    if (totalBayar > 0) return "Cicilan";
    return "Belum Bayar";
  };

  const getStatusClass = (status) => {
    switch ((status || "").toLowerCase()) {
      case "belum bayar":
      case "belum dibayar":
        return "belum-bayar";
      case "cicilan":
      case "mengangsur":
      case "menunggu verifikasi":
      case "belum lunas":
        return "belum-lunas";
      case "lunas":
      case "sudah bayar":
        return "lunas";
      default:
        return "";
    }
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
      const computedStatus = getComputedStatus(item).toLowerCase();

      return (
        computedStatus === "belum bayar" ||
        computedStatus === "cicilan" ||
        computedStatus === "belum lunas"
      );
    }).length;

    const lunas = tagihanList.filter((item) => {
      const computedStatus = getComputedStatus(item).toLowerCase();

      return computedStatus === "lunas" || computedStatus === "sudah bayar";
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
        item.JENIS_TAGIHAN?.DESKRIPSI_JENIS_TAGIHAN ||
        item.jenis_tagihan?.DESKRIPSI_JENIS_TAGIHAN ||
        "";
      const kode = String(item.ID_TAGIHAN_SISWA || "");
      const kelas = item.SISWA?.KELAS_SISWA || "";
      const computedStatus = getComputedStatus(item);

      const keyword = search.toLowerCase();

      const matchesSearch =
        namaSiswa.toLowerCase().includes(keyword) ||
        jenisTagihan.toLowerCase().includes(keyword) ||
        kode.toLowerCase().includes(keyword);

      const matchesKelas =
        filterKelas === "Semua" ? true : kelas === filterKelas;

      const matchesStatus =
        filterStatus === "Semua" ? true : computedStatus === filterStatus;

      return matchesSearch && matchesKelas && matchesStatus;
    });
  }, [tagihanList, search, filterKelas, filterStatus]);

  const totalPages = Math.ceil(filteredData.length / ITEMS_PER_PAGE);

  const paginatedData = useMemo(() => {
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    return filteredData.slice(startIndex, startIndex + ITEMS_PER_PAGE);
  }, [filteredData, currentPage]);

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
    fetchJenisTagihanOptions();
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

  useEffect(() => {
    setCurrentPage(1);
  }, [search, filterKelas, filterStatus]);

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
      const res = await axios.get(`${API_BASE_URL}/tagihan-siswa/siswa-options`, {
        params: {
          search: siswaKeyword,
        },
      });
      const rawData =
        res.data?.data ??
        res.data?.results ??
        res.data?.siswa ??
        res.data ??
        [];

      const normalized = normalizeSiswaOptions(rawData);
      setSiswaOptions(normalized);
    } catch (error) {
      console.warn(
        "Endpoint siswa belum tersedia, dropdown siswa masih kosong.",
        error
      );
      setSiswaOptions([]);
    }
  };

  const fetchJenisTagihanOptions = async () => {
    try {
      const res = await axios.get(`${API_BASE_URL}/ref-jenis-tagihan`);
      setJenisTagihanOptions(Array.isArray(res.data?.data) ? res.data.data : []);
    } catch (error) {
      console.warn(
        "Endpoint jenis tagihan belum tersedia, dropdown jenis tagihan masih kosong.",
        error
      );
      setJenisTagihanOptions([]);
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
      !formData.ID_JENIS_TAGIHAN ||
      !formData.BULAN_TAGIHAN_SISWA ||
      !formData.TAHUN_TAGIHAN_SISWA ||
      !formData.JUMLAH_TAGIHAN_SISWA ||
      !formData.DUEDATETIME_TAGIHAN_SISWA
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

      // Sesuaikan endpoint ini kalau backend kamu sudah RESTful penuh
      if (isEdit && selectedId) {
        await axios.put(
          `${API_BASE_URL}/tagihan-siswa/update/${selectedId}`,
          payload
        );
        alert("Tagihan berhasil diperbarui.");
      } else {
        await axios.post(`${API_BASE_URL}/tagihan-siswa/store`, payload);
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
    if (Number(item.TOTAL_PEMBAYARAN || 0) > 0) {
      alert("Tagihan yang sudah memiliki pembayaran tidak bisa diedit.");
      return;
    }

    const siswaId = item.ID_SISWA_TETAP || item.SISWA?.ID_SISWA_TETAP || "";
    const siswaNama = item.SISWA?.NAMA_SISWA_TETAP || "";
    const siswaKelas = item.SISWA?.KELAS_SISWA || "";

    setIsEdit(true);
    setSelectedId(item.ID_TAGIHAN_SISWA);
    setFormData({
      ID_SISWA_TETAP: siswaId,
      ID_JENIS_TAGIHAN: item.ID_JENIS_TAGIHAN || "",
      BULAN_TAGIHAN_SISWA: item.BULAN_TAGIHAN_SISWA || "",
      TAHUN_TAGIHAN_SISWA: item.TAHUN_TAGIHAN_SISWA || "",
      JUMLAH_TAGIHAN_SISWA: item.JUMLAH_TAGIHAN_SISWA || "",
      STATUS_TAGIHAN_SISWA: "Belum Bayar",
      DUEDATETIME_TAGIHAN_SISWA: item.DUEDATETIME_TAGIHAN_SISWA
        ? String(item.DUEDATETIME_TAGIHAN_SISWA).slice(0, 10)
        : "",
    });

    setSiswaKeyword(siswaKelas ? `${siswaNama} - ${siswaKelas}` : siswaNama);

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
      await axios.delete(
        `${API_BASE_URL}/tagihan-siswa/delete/${item.ID_TAGIHAN_SISWA}`
      );
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

  const buildExportParams = () => {
    const params = new URLSearchParams();

    if (search) params.append("search", search);

    return params.toString();
  };

  const handleExportExcel = () => {
    const query = buildExportParams();
    const url = `${API_BASE_URL}/tagihan-siswa/export/excel${query ? `?${query}` : ""}`;
    window.open(url, "_blank");
  };

  const handleExportPdf = async () => {
    try {
      const token = localStorage.getItem("token");

      const response = await axios.get(
        "http://127.0.0.1:8000/api/tagihan-siswa/export/pdf",
        {
          responseType: "blob",
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/pdf",
          },
        }
      );

      const blob = new Blob([response.data], { type: "application/pdf" });
      const url = window.URL.createObjectURL(blob);

      window.open(url, "_blank");
    } catch (error) {
      console.error("Gagal export PDF:", error);
    }
  };

  return (
    <div className="tagihan-page">
      <div className="tagihan-header">
        <h1>Manajemen Tagihan Siswa</h1>
        <p>
          Buat, kelola, cari, dan pantau tagihan administrasi sekolah untuk
          siswa.
        </p>
      </div>

      <div className="tagihan-summary">
        <div className="tagihan-summary-box">
          <p>Total Tagihan</p>
          <h3>{summary.total}</h3>
        </div>
        <div className="tagihan-summary-box">
          <p>Belum Lunas</p>
          <h3>{summary.belumLunas}</h3>
        </div>
        <div className="tagihan-summary-box">
          <p>Lunas</p>
          <h3>{summary.lunas}</h3>
        </div>
        <div className="tagihan-summary-box">
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
            <div className="tagihan-form-group tagihan-siswa-search-group" ref={siswaDropdownRef}>
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
                <div className="tagihan-siswa-dropdown">
                  {filteredSiswaOptions.length > 0 ? (
                    filteredSiswaOptions.map((item) => (
                      <button
                        key={item.id}
                        type="button"
                        className={`tagihan-siswa-dropdown-item ${
                          formData.ID_SISWA_TETAP === item.id ? "active" : ""
                        }`}
                        onClick={() => handleSelectSiswa(item)}
                      >
                        <span className="tagihan-siswa-dropdown-name">
                          {item.nama || `Siswa #${item.id}`}
                        </span>
                        <span className="tagihan-siswa-dropdown-meta">
                          {item.kelas ? `${item.kelas} • ` : ""}ID: {item.id}
                        </span>
                      </button>
                    ))
                  ) : (
                    <div className="tagihan-siswa-dropdown-empty">
                      Siswa tidak ditemukan
                    </div>
                  )}
                </div>
              )}
            </div>

            <div className="tagihan-form-group">
              <label htmlFor="ID_JENIS_TAGIHAN">Jenis Tagihan</label>
              <select
                id="ID_JENIS_TAGIHAN"
                name="ID_JENIS_TAGIHAN"
                value={formData.ID_JENIS_TAGIHAN}
                onChange={handleChange}
                className={!formData.ID_JENIS_TAGIHAN ? "tagihan-select-placeholder" : ""}
              >
                <option value="">Pilih jenis tagihan</option>
                {jenisTagihanOptions.map((item) => (
                  <option
                    key={item.ID_JENIS_TAGIHAN}
                    value={item.ID_JENIS_TAGIHAN}
                  >
                    {item.DESKRIPSI_JENIS_TAGIHAN}
                  </option>
                ))}
              </select>
            </div>

            <div className="tagihan-form-group">
              <label htmlFor="BULAN_TAGIHAN_SISWA">Bulan Tagihan</label>
              <select
                id="BULAN_TAGIHAN_SISWA"
                name="BULAN_TAGIHAN_SISWA"
                value={formData.BULAN_TAGIHAN_SISWA}
                onChange={handleChange}
                className={!formData.BULAN_TAGIHAN_SISWA ? "tagihan-select-placeholder" : ""}
              >
                <option value="">Pilih bulan tagihan</option>
                {BULAN_OPTIONS.map((bulan) => (
                  <option key={bulan} value={bulan}>
                    {bulan}
                  </option>
                ))}
              </select>
            </div>

            <div className="tagihan-form-group">
              <label htmlFor="TAHUN_TAGIHAN_SISWA">Tahun Tagihan</label>
              <input
                type="number"
                id="TAHUN_TAGIHAN_SISWA"
                name="TAHUN_TAGIHAN_SISWA"
                placeholder="Contoh: 2026"
                value={formData.TAHUN_TAGIHAN_SISWA}
                onChange={handleChange}
                min="2000"
              />
            </div>

            <div className="tagihan-form-group">
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

            <div className="tagihan-form-group">
              <label htmlFor="DUEDATETIME_TAGIHAN_SISWA">Jatuh Tempo</label>
              <input
                type="date"
                id="DUEDATETIME_TAGIHAN_SISWA"
                name="DUEDATETIME_TAGIHAN_SISWA"
                value={formData.DUEDATETIME_TAGIHAN_SISWA}
                onChange={handleChange}
              />
            </div>
          </div>

          <div className="tagihan-form-actions">
            <button
              type="button"
              className="tagihan-btn tagihan-btn-secondary"
              onClick={resetForm}
              disabled={submitLoading}
            >
              {isEdit ? "Batal" : "Reset"}
            </button>
            <button
              type="submit"
              className="tagihan-btn tagihan-btn-primary"
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
        <div className="tagihan-table-section-header">
          <div>
            <h2 className="tagihan-table-section-title">Daftar Tagihan Berjalan</h2>
            <p className="tagihan-table-section-subtitle">
              Menampilkan tagihan dan sisa tunggakan per siswa.
            </p>
          </div>
        </div>

        <div className="tagihan-filter-bar">
          <div className="tagihan-filter-item">
            <label htmlFor="search">Cari Siswa / Tagihan</label>
            <input
              id="search"
              type="text"
              placeholder="Cari nama siswa, tagihan, atau ID..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>

          {/* <div className="tagihan-filter-item">
            <label htmlFor="filterKelas">Kelas</label>
            <select
              id="filterKelas"
              value={filterKelas}
              onChange={(e) => setFilterKelas(e.target.value)}
              className={filterKelas === "Semua" ? "tagihan-select-placeholder" : ""}
            >
              {kelasOptions.map((kelas) => (
                <option key={kelas} value={kelas}>
                  {kelas}
                </option>
              ))}
            </select>
          </div> */}

          <div className="tagihan-filter-item">
            <label htmlFor="filterStatus">Status</label>
            <select
              id="filterStatus"
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
              className={filterStatus === "Semua" ? "tagihan-select-placeholder" : ""}
            >
              <option value="Semua">Semua</option>
              <option value="Belum Bayar">Belum Bayar</option>
              <option value="Cicilan">Cicilan</option>
              <option value="Lunas">Lunas</option>
            </select>
          </div>

          <div className="tagihan-filter-actions">
            <button
              type="button"
              className="tagihan-btn tagihan-btn-secondary"
              onClick={handleResetFilter}
            >
              Reset Filter
            </button>

            <div className="tagihan-export-group">
              <button
                type="button"
                className="tagihan-btn tagihan-btn-export-excel"
                onClick={handleExportExcel}
              >
                Export Excel
              </button>

              <button
                type="button"
                className="tagihan-btn tagihan-btn-export-pdf"
                onClick={handleExportPdf}
              >
                Export PDF
              </button>
            </div>

          </div>
        </div>

        <div className="tagihan-table-wrapper">
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
                    <div className="tagihan-empty-state">Memuat data tagihan...</div>
                  </td>
                </tr>
              ) : paginatedData.length > 0 ? (
                paginatedData.map((item) => {
                  const computedStatus = getComputedStatus(item);
                  const hasPayment = Number(item.TOTAL_PEMBAYARAN || 0) > 0;

                  return (
                    <tr key={item.ID_TAGIHAN_SISWA}>
                      <td className="tagihan-id">#{item.ID_TAGIHAN_SISWA}</td>

                      <td>
                        <div className="tagihan-nama-siswa-cell">
                          <span className="tagihan-nama-siswa-text">
                            {item.SISWA?.NAMA_SISWA_TETAP || "-"}
                          </span>
                          <small className="tagihan-kelas-siswa-text">
                            {item.SISWA?.KELAS_SISWA || "-"}
                          </small>
                        </div>
                      </td>

                      <td>
                        {item.JENIS_TAGIHAN?.DESKRIPSI_JENIS_TAGIHAN ||
                          item.jenis_tagihan?.DESKRIPSI_JENIS_TAGIHAN ||
                          "-"}
                      </td>

                      <td>
                        {item.BULAN_TAGIHAN_SISWA || "-"}{" "}
                        {item.TAHUN_TAGIHAN_SISWA || ""}
                      </td>

                      <td>{formatTanggal(item.DUEDATETIME_TAGIHAN_SISWA)}</td>

                      <td className="tagihan-nominal-text">
                        {formatRupiah(item.JUMLAH_TAGIHAN_SISWA)}
                      </td>

                      <td className="tagihan-nominal-text">
                        {formatRupiah(item.TOTAL_PEMBAYARAN)}
                      </td>

                      <td className="tagihan-sisa-highlight">
                        {formatRupiah(item.SISA_TAGIHAN)}
                      </td>

                      <td>
                        <span
                          className={`tagihan-status-badge ${getStatusClass(computedStatus)}`}
                        >
                          {computedStatus}
                        </span>
                      </td>

                      <td>
                        <div className="tagihan-action-group">
                          <button
                            type="button"
                            className="tagihan-btn tagihan-btn-secondary tagihan-btn-sm"
                            onClick={() => handleEdit(item)}
                            disabled={hasPayment}
                            title={
                              hasPayment
                                ? "Tagihan yang sudah memiliki pembayaran tidak bisa diedit"
                                : "Edit tagihan"
                            }
                          >
                            Edit
                          </button>

                          <button
                            type="button"
                            className="tagihan-btn tagihan-btn-danger tagihan-btn-sm"
                            onClick={() => handleDelete(item)}
                            disabled={hasPayment}
                            title={
                              hasPayment
                                ? "Tagihan yang sudah memiliki pembayaran tidak bisa dihapus"
                                : "Hapus tagihan"
                            }
                          >
                            Hapus
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })
              ) : (
                <tr>
                  <td colSpan="10">
                    <div className="tagihan-empty-state">
                      Tidak ada data tagihan yang sesuai filter.
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div className="tagihan-table-pagination">
          <p>
            Menampilkan{" "}
            {filteredData.length === 0
              ? 0
              : (currentPage - 1) * ITEMS_PER_PAGE + 1}
            {" - "}
            {Math.min(currentPage * ITEMS_PER_PAGE, filteredData.length)}
            {" dari "}
            {filteredData.length} data
          </p>

          <div className="tagihan-pagination-actions">
            <button
              type="button"
              className="tagihan-pagination-arrow"
              disabled={currentPage === 1}
              onClick={() => setCurrentPage((prev) => prev - 1)}
              aria-label="Halaman sebelumnya"
            >
              ‹
            </button>

            <span className="tagihan-pagination-page">{currentPage}</span>

            <button
              type="button"
              className="tagihan-pagination-arrow"
              disabled={currentPage === totalPages || totalPages === 0}
              onClick={() => setCurrentPage((prev) => prev + 1)}
              aria-label="Halaman berikutnya"
            >
              ›
            </button>
          </div>
        </div>
      </section>
    </div>
  );
}

export default Tagihan;
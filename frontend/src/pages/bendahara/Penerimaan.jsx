import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import {
  Pencil,
  Trash2,
  Filter,
  Plus,
  FileSpreadsheet,
  FileText,
} from "lucide-react";
import "../../styles/bendahara/Penerimaan.css";

const API_BASE = "http://127.0.0.1:8000/api";

const getAuthHeaders = () => {
  const token =
    localStorage.getItem("token") ||
    localStorage.getItem("access_token") ||
    localStorage.getItem("authToken");

  return {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: "application/json",
    },
  };
};

export default function Penerimaan() {
  const [dataPenerimaan, setDataPenerimaan] = useState([]);
  const [refPenerimaan, setRefPenerimaan] = useState([]);
  const [refDana, setRefDana] = useState([]);
  const [loadingTable, setLoadingTable] = useState(true);
  const [loadingRef, setLoadingRef] = useState(true);
  const [loadingDana, setLoadingDana] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [deletingId, setDeletingId] = useState(null);

  const [searchTerm, setSearchTerm] = useState("");
  const [searchInput, setSearchInput] = useState("");

  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const [showModal, setShowModal] = useState(false);
  const [isEditMode, setIsEditMode] = useState(false);
  const [selectedId, setSelectedId] = useState(null);

  const initialForm = {
    ID_REF_PENERIMAAN: "",
    ID_REF_DANA: "",
    DESKRIPSI_TR_PENERIMAAN: "",
    TANGGAL_TR_PENERIMAAN: "",
    JUMLAH_TR_PENERIMAAN: "",
    NIP_PENERIMA: "",
  };

  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    fetchRefPenerimaan();
    fetchRefDana();
    fetchPenerimaan();
  }, []);

  const fetchRefPenerimaan = async () => {
    try {
      setLoadingRef(true);
      const res = await axios.get(`${API_BASE}/ref-penerimaan`, getAuthHeaders());
      setRefPenerimaan(res.data.data || []);
    } catch (err) {
      console.error("Gagal ambil referensi penerimaan:", err);
      setError("Gagal memuat jenis penerimaan");
    } finally {
      setLoadingRef(false);
    }
  };

  const fetchRefDana = async () => {
    try {
      setLoadingDana(true);
      const res = await axios.get(`${API_BASE}/ref-sumber-dana`, getAuthHeaders());
      setRefDana(res.data.data || res.data || []);
    } catch (err) {
      console.error("Gagal ambil referensi sumber dana:", err);
      setError("Gagal memuat sumber dana");
    } finally {
      setLoadingDana(false);
    }
  };

  const fetchPenerimaan = async () => {
    try {
      setLoadingTable(true);
      setError("");

      const res = await axios.get(
        `${API_BASE}/keuangan/penerimaan`,
        getAuthHeaders()
      );

      const payload = Array.isArray(res.data)
        ? res.data
        : Array.isArray(res.data.data)
        ? res.data.data
        : [];

      setDataPenerimaan(payload);
    } catch (err) {
      console.error("Gagal ambil data penerimaan:", err);
      setError(err.response?.data?.message || "Gagal memuat daftar penerimaan");
      setDataPenerimaan([]);
    } finally {
      setLoadingTable(false);
    }
  };

  const resetForm = () => {
    setForm(initialForm);
    setSelectedId(null);
    setIsEditMode(false);
  };

  const closeModal = () => {
    setShowModal(false);
    resetForm();
    setError("");
    setMessage("");
  };

  const openTambahModal = () => {
    resetForm();
    setShowModal(true);
    setError("");
    setMessage("");
  };

  const normalizeDateForInput = (value) => {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      const plain = String(value).slice(0, 10);
      return plain.includes("-") ? plain : "";
    }
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  };

  const openEditModal = (item) => {
    setIsEditMode(true);
    setSelectedId(item.ID_TR_PENERIMAAN);

    setForm({
      ID_REF_PENERIMAAN: item.ID_REF_PENERIMAAN
        ? String(item.ID_REF_PENERIMAAN)
        : "",
      ID_REF_DANA: item.ID_REF_DANA ? String(item.ID_REF_DANA) : "",
      DESKRIPSI_TR_PENERIMAAN: item.DESKRIPSI_TR_PENERIMAAN || "",
      TANGGAL_TR_PENERIMAAN: normalizeDateForInput(item.TANGGAL_TR_PENERIMAAN),
      JUMLAH_TR_PENERIMAAN: item.JUMLAH_TR_PENERIMAAN
        ? String(item.JUMLAH_TR_PENERIMAAN)
        : "",
      NIP_PENERIMA: item.NIP_PENERIMA || "",
    });

    setShowModal(true);
    setError("");
    setMessage("");
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSearch = () => {
    setSearchTerm(searchInput.trim().toLowerCase());
  };

  const handleResetSearch = () => {
    setSearchInput("");
    setSearchTerm("");
  };

  const downloadFile = async (url, filename) => {
    try {
      const token =
        localStorage.getItem("token") ||
        localStorage.getItem("access_token") ||
        localStorage.getItem("authToken");

      const response = await fetch(url, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/octet-stream",
        },
      });

      if (!response.ok) {
        throw new Error(`Export gagal: ${response.status}`);
      }

      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);

      const link = document.createElement("a");
      link.href = blobUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      setError("");
      setMessage("");

      setTimeout(() => {
        window.URL.revokeObjectURL(blobUrl);
      }, 1000);
    } catch (err) {
      console.error("Gagal download file:", err);
    }
  };

  const handleExportExcel = () => {
    downloadFile(
      `${API_BASE}/keuangan/penerimaan/export?type=excel`,
      "laporan_penerimaan.xlsx"
    );
  };

  const handleExportPdf = () => {
    downloadFile(
      `${API_BASE}/keuangan/penerimaan/export?type=pdf`,
      "laporan_penerimaan.pdf"
    );
  };

  const filteredData = useMemo(() => {
    if (!searchTerm) return dataPenerimaan;

    return dataPenerimaan.filter((item) => {
      const joined = [
        item.ID_TR_PENERIMAAN,
        item.DESKRIPSI_TR_PENERIMAAN,
        item.JUMLAH_TR_PENERIMAAN,
        item.TANGGAL_TR_PENERIMAAN,
        item.nama_ref_penerimaan,
        item.DESKRIPSI_REF_PENERIMAAN,
        item.ID_REF_DANA,
        item.NIP_PENERIMA,
      ]
        .join(" ")
        .toLowerCase();

      return joined.includes(searchTerm);
    });
  }, [dataPenerimaan, searchTerm]);

  const formatTanggal = (value) => {
    if (!value) return "-";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString("id-ID");
  };

  const formatRupiah = (value) => {
    const number = Number(value || 0);
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0,
    }).format(number);
  };

  const getNamaRefPenerimaan = (item) => {
    return (
      item.DESKRIPSI_REF_PENERIMAAN ||
      item.nama_ref_penerimaan ||
      refPenerimaan.find(
        (ref) => String(ref.ID_REF_PENERIMAAN) === String(item.ID_REF_PENERIMAAN)
      )?.DESKRIPSI_REF_PENERIMAAN ||
      "-"
    );
  };

  const validateForm = () => {
    if (!form.TANGGAL_TR_PENERIMAAN) return "Tanggal penerimaan wajib diisi";
    if (!form.ID_REF_PENERIMAAN) return "Jenis penerimaan wajib dipilih";
    if (!form.JUMLAH_TR_PENERIMAAN) return "Jumlah penerimaan wajib diisi";
    if (Number(form.JUMLAH_TR_PENERIMAAN) <= 0) {
      return "Jumlah penerimaan harus lebih dari 0";
    }
    if (!form.DESKRIPSI_TR_PENERIMAAN) return "Deskripsi wajib diisi";
    if (!form.ID_REF_DANA) return "Sumber dana wajib dipilih";
    if (!form.NIP_PENERIMA) return "NIP Penerima wajib diisi";
    return "";
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setMessage("");
    setError("");

    const validationError = validateForm();
    if (validationError) {
      setError(validationError);
      setSubmitting(false);
      return;
    }

    try {
      if (isEditMode && selectedId) {
        await axios.put(
          `${API_BASE}/keuangan/penerimaan/${selectedId}`,
          form,
          getAuthHeaders()
        );
        setMessage("Data penerimaan berhasil diupdate");
      } else {
        await axios.post(
          `${API_BASE}/keuangan/penerimaan`,
          form,
          getAuthHeaders()
        );
        setMessage("Data penerimaan berhasil disimpan");
      }

      await fetchPenerimaan();

      setTimeout(() => {
        closeModal();
      }, 500);
    } catch (err) {
      console.error("Gagal simpan/update penerimaan:", err);
      console.error("Detail error:", err.response?.data);

      if (err.response?.data?.errors) {
        const firstKey = Object.keys(err.response.data.errors)[0];
        const firstMsg = err.response.data.errors[firstKey][0];
        setError(firstMsg);
      } else {
        setError(
          err.response?.data?.message ||
            (isEditMode
              ? "Gagal mengupdate data penerimaan"
              : "Gagal menyimpan data penerimaan")
        );
      }
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    const confirmed = window.confirm("Yakin mau menghapus data penerimaan ini?");
    if (!confirmed) return;

    try {
      setDeletingId(id);
      setMessage("");
      setError("");

      await axios.delete(
        `${API_BASE}/keuangan/penerimaan/${id}`,
        getAuthHeaders()
      );
      setMessage("Data penerimaan berhasil dihapus");
      await fetchPenerimaan();
    } catch (err) {
      console.error("Gagal hapus penerimaan:", err);
      setError(err.response?.data?.message || "Gagal menghapus data penerimaan");
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <div className="penerimaan-container">
      <div className="penerimaan-header">
        <h2>Penerimaan</h2>

        <div className="penerimaan-toolbar">
          <button className="btn-reset" onClick={handleResetSearch}>
            Reset
          </button>

          <input
            type="text"
            className="search-input"
            placeholder="Cari penerimaan..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
          />

          <button className="search-btn" onClick={handleSearch}>
            Search
          </button>

          <button className="btn-primary" onClick={openTambahModal}>
            <Plus size={16} />
            Tambah Penerimaan
          </button>
        </div>
      </div>

      {message && !showModal && <div className="alert-success">{message}</div>}
      {error && !showModal && <div className="alert-error">{error}</div>}

      <div className="penerimaan-table-wrapper">
        <table className="penerimaan-table">
          <thead>
            <tr>
              <th>No</th>
              <th>
                ID <Filter size={14} className="th-icon" />
              </th>
              <th>Jenis Penerimaan</th>
              <th>Tanggal</th>
              <th>Jumlah</th>
              <th>Deskripsi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {loadingTable ? (
              <tr>
                <td colSpan="7" className="text-center">
                  Memuat data penerimaan...
                </td>
              </tr>
            ) : filteredData.length === 0 ? (
              <tr>
                <td colSpan="7" className="text-center">
                  Tidak ada data penerimaan
                </td>
              </tr>
            ) : (
              filteredData.map((item, index) => (
                <tr key={item.ID_TR_PENERIMAAN || index}>
                  <td>{index + 1}</td>
                  <td>{item.ID_TR_PENERIMAAN || "-"}</td>
                  <td>{getNamaRefPenerimaan(item)}</td>
                  <td>{formatTanggal(item.TANGGAL_TR_PENERIMAAN)}</td>
                  <td>{formatRupiah(item.JUMLAH_TR_PENERIMAAN)}</td>
                  <td
                    title={item.DESKRIPSI_TR_PENERIMAAN || "-"}
                    className="deskripsi-cell"
                  >
                    {item.DESKRIPSI_TR_PENERIMAAN || "-"}
                  </td>
                  <td>
                    <div className="aksi">
                      <button
                        className="btn-edit"
                        title="Edit"
                        onClick={() => openEditModal(item)}
                      >
                        <Pencil size={15} />
                      </button>

                      <button
                        className="btn-delete"
                        title="Hapus"
                        onClick={() => handleDelete(item.ID_TR_PENERIMAAN)}
                        disabled={deletingId === item.ID_TR_PENERIMAAN}
                      >
                        <Trash2 size={15} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>

        <div className="table-footer">
          <div className="pagination-info">
            Menampilkan {filteredData.length} data
          </div>

          <div style={{ display: "flex", gap: "10px" }}>
            <button className="btn-export" onClick={handleExportExcel}>
              <FileSpreadsheet size={16} />
              Export Excel
            </button>

            <button className="btn-export" onClick={handleExportPdf}>
              <FileText size={16} />
              Export PDF
            </button>
          </div>
        </div>
      </div>

      {showModal && (
        <div className="modal-overlay">
          <div className="modal-box penerimaan-modal">
            <h3>{isEditMode ? "Edit Penerimaan" : "Tambah Penerimaan"}</h3>

            {message && <div className="alert-success">{message}</div>}
            {error && <div className="alert-error">{error}</div>}

            <form onSubmit={handleSubmit}>
              <label>Tanggal Penerimaan</label>
              <input
                type="date"
                name="TANGGAL_TR_PENERIMAAN"
                value={form.TANGGAL_TR_PENERIMAAN}
                onChange={handleChange}
              />

              <label>Jenis Penerimaan</label>
              <select
                name="ID_REF_PENERIMAAN"
                value={form.ID_REF_PENERIMAAN}
                onChange={handleChange}
              >
                <option value="">
                  {loadingRef
                    ? "Memuat jenis penerimaan..."
                    : "Pilih jenis penerimaan"}
                </option>
                {refPenerimaan.map((item) => (
                  <option
                    key={item.ID_REF_PENERIMAAN}
                    value={item.ID_REF_PENERIMAAN}
                  >
                    {item.DESKRIPSI_REF_PENERIMAAN}
                  </option>
                ))}
              </select>

              <label>Jumlah Penerimaan</label>
              <input
                type="number"
                name="JUMLAH_TR_PENERIMAAN"
                value={form.JUMLAH_TR_PENERIMAAN}
                onChange={handleChange}
                placeholder="Masukkan nominal"
              />

              <label>Deskripsi</label>
              <textarea
                name="DESKRIPSI_TR_PENERIMAAN"
                value={form.DESKRIPSI_TR_PENERIMAAN}
                onChange={handleChange}
                placeholder="Masukkan deskripsi"
                rows="4"
              />

              <label>ID Ref Dana</label>
              <select
                name="ID_REF_DANA"
                value={form.ID_REF_DANA}
                onChange={handleChange}
              >
                <option value="">
                  {loadingDana ? "Memuat ID Ref Dana..." : "Pilih ID Ref Dana"}
                </option>

                {refDana.map((item) => {
                  const id = item.ID_REF_DANA || item.id || item.ID;
                  const nama =
                    item.DESKRIPSI_REF_DANA ||
                    item.NAMA_REF_DANA ||
                    item.DESKRIPSI_SUMBER_DANA ||
                    item.nama ||
                    "";

                  return (
                    <option key={id} value={id}>
                      {nama ? `${id} - ${nama}` : id}
                    </option>
                  );
                })}
              </select>

              <br />


              <label>NIP Penerima</label>
              <input
                type="text"
                name="NIP_PENERIMA"
                value={form.NIP_PENERIMA}
                onChange={handleChange}
                placeholder="Isi sementara contoh: 19800101"
              />

              <div className="modal-actions">
                <button
                  type="button"
                  className="btn-cancel"
                  onClick={closeModal}
                  disabled={submitting}
                >
                  Batal
                </button>
                <button
                  type="submit"
                  className="btn-submit"
                  disabled={submitting}
                >
                  {submitting
                    ? isEditMode
                      ? "Mengupdate..."
                      : "Menyimpan..."
                    : isEditMode
                    ? "Update"
                    : "Simpan"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
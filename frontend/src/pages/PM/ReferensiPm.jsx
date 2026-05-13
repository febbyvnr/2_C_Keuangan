import React, { useEffect, useMemo, useState } from "react";
import axios from "axios";
import {
  FaSearch,
  FaSyncAlt,
  FaPlus,
  FaEdit,
  FaTrash,
  FaEye,
} from "react-icons/fa";
import "../../styles/PM/ReferensiPm.css";

const API_BASE_URL = "http://127.0.0.1:8000/api";

function getAuthConfig() {
  const token = localStorage.getItem("token");
  return {
    headers: {
      Authorization: token ? `Bearer ${token}` : "",
      Accept: "application/json",
      "Content-Type": "application/json",
    },
  };
}

function useSort() {
  const [sortConfig, setSortConfig] = useState({
    key: null,
    direction: "asc",
  });

  const handleSort = (key) => {
    setSortConfig((prev) => {
      if (prev.key === key) {
        return {
          key,
          direction: prev.direction === "asc" ? "desc" : "asc",
        };
      }
      return { key, direction: "asc" };
    });
  };

  return [sortConfig, handleSort];
}

function SortableTh({ sortConfig, sortKey, onSort, children, className = "" }) {
  const isActive = sortConfig.key === sortKey;
  const icon = !isActive ? "⇅" : sortConfig.direction === "asc" ? "↑" : "↓";

  return (
    <th
      className={`sortable-th ${className}`}
      onClick={() => onSort(sortKey)}
      title={`Urutkan ${children}`}
    >
      <span>{children}</span>
      <span className={`sort-icon ${isActive ? "active" : "neutral"}`}>
        {icon}
      </span>
    </th>
  );
}

export default function ReferensiPm() {
  const [dataList, setDataList] = useState([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [deleteLoadingId, setDeleteLoadingId] = useState(null);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const [showModal, setShowModal] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [selectedDetail, setSelectedDetail] = useState(null);
  const [isEditMode, setIsEditMode] = useState(false);
  const [selectedId, setSelectedId] = useState(null);

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 8;

  const [sortConfig, handleSort] = useSort();

  const initialForm = {
    NAMA_PM: "",
    REF_ID_REF_PM: "",
    DESKRIPSI_PM: "",
  };

  const [form, setForm] = useState(initialForm);

  const fetchData = async () => {
    setLoading(true);
    setError("");

    try {
      const res = await axios.get(`${API_BASE_URL}/ref-pm`, getAuthConfig());
      const raw = Array.isArray(res.data?.data)
        ? res.data.data
        : Array.isArray(res.data)
        ? res.data
        : [];

      setDataList(raw);
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal mengambil data referensi PM."
      );
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const parentOptions = useMemo(() => {
    return dataList.map((item) => ({
      id: item.ID_REF_PM,
      nama: item.NAMA_PM || "-",
    }));
  }, [dataList]);

  const normalizedData = useMemo(() => {
    const idToName = new Map(
      dataList.map((item) => [String(item.ID_REF_PM), item.NAMA_PM || "-"])
    );

    return dataList.map((item, index) => {
      const kategori =
        item.REF_ID_REF_PM !== null && item.REF_ID_REF_PM !== undefined
          ? idToName.get(String(item.REF_ID_REF_PM)) || "-"
          : "-";

      const statusValue = Number(item.is_used) === 1 ? 1 : 0;

      return {
        no: index + 1,
        id: item.ID_REF_PM,
        nama: item.NAMA_PM || "-",
        kategori,
        kategoriId: item.REF_ID_REF_PM ?? "",
        deskripsi: item.DESKRIPSI_PM || "-",
        statusValue,
        statusLabel: statusValue ? "Digunakan" : "Belum Digunakan",
        nomorUrut: item.nomor_urut || "-",
        hasChild: Number(item.has_child) === 1,
        raw: item,
      };
    });
  }, [dataList]);

  const filteredData = useMemo(() => {
    const keyword = String(search || "").toLowerCase().trim();

    if (!keyword) return normalizedData;

    return normalizedData.filter((item) => {
      const text = [
        item.nama,
        item.kategori,
        item.deskripsi,
        item.statusLabel,
        item.id,
        item.nomorUrut,
      ]
        .join(" ")
        .toLowerCase();

      return text.includes(keyword);
    });
  }, [search, normalizedData]);

  useEffect(() => {
    setCurrentPage(1);
  }, [search, sortConfig]);

  const sortedData = useMemo(() => {
    const cloned = [...filteredData];
    if (!sortConfig.key) return cloned;

    cloned.sort((a, b) => {
      let aValue = "";
      let bValue = "";

      switch (sortConfig.key) {
        case "nama":
          aValue = a.nama;
          bValue = b.nama;
          break;
        case "kategori":
          aValue = a.kategori;
          bValue = b.kategori;
          break;
        case "deskripsi":
          aValue = a.deskripsi;
          bValue = b.deskripsi;
          break;
        case "status":
          aValue = a.statusLabel;
          bValue = b.statusLabel;
          break;
        default:
          break;
      }

      const compareResult =
        typeof aValue === "number" && typeof bValue === "number"
          ? aValue - bValue
          : String(aValue).localeCompare(String(bValue), "id", {
              sensitivity: "base",
            });

      return sortConfig.direction === "asc" ? compareResult : -compareResult;
    });

    return cloned;
  }, [filteredData, sortConfig]);

  const totalPages = Math.ceil(sortedData.length / itemsPerPage) || 1;
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  const paginatedList = useMemo(() => {
    return sortedData.slice(startIndex, endIndex);
  }, [sortedData, startIndex, endIndex]);

  const handleReset = () => {
    setSearch("");
    setCurrentPage(1);
    setError("");
    setSuccess("");
  };

  const openAddModal = () => {
    setIsEditMode(false);
    setSelectedId(null);
    setForm(initialForm);
    setError("");
    setSuccess("");
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setIsEditMode(true);
    setSelectedId(item.id);
    setForm({
      NAMA_PM: item.nama === "-" ? "" : item.nama,
      REF_ID_REF_PM: item.kategoriId || "",
      DESKRIPSI_PM: item.deskripsi === "-" ? "" : item.deskripsi,
    });
    setError("");
    setSuccess("");
    setShowModal(true);
  };

  const openDetailModal = (item) => {
    setSelectedDetail(item);
    setShowDetailModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setIsEditMode(false);
    setSelectedId(null);
    setForm(initialForm);
    setError("");
  };

  const closeDetailModal = () => {
    setShowDetailModal(false);
    setSelectedDetail(null);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const validateForm = () => {
    if (!form.NAMA_PM.trim()) return "Nama Referensi PM wajib diisi.";
    if (!form.DESKRIPSI_PM.trim()) return "Deskripsi wajib diisi.";
    return "";
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError("");
    setSuccess("");

    const validationError = validateForm();
    if (validationError) {
      setError(validationError);
      setSubmitting(false);
      return;
    }

    try {
      const payload = {
        NAMA_PM: form.NAMA_PM,
        REF_ID_REF_PM: form.REF_ID_REF_PM || null,
        DESKRIPSI_PM: form.DESKRIPSI_PM,
      };

      if (isEditMode && selectedId) {
        await axios.put(
          `${API_BASE_URL}/ref-pm/${selectedId}`,
          payload,
          getAuthConfig()
        );
        setSuccess("Data berhasil diupdate.");
      } else {
        await axios.post(`${API_BASE_URL}/ref-pm`, payload, getAuthConfig());
        setSuccess("Data berhasil ditambahkan.");
      }

      await fetchData();
      closeModal();
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menyimpan data."
      );
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    const confirmed = window.confirm(
      "Yakin ingin menghapus referensi PM ini?"
    );
    if (!confirmed) return;

    setDeleteLoadingId(id);
    setError("");
    setSuccess("");

    try {
      await axios.delete(`${API_BASE_URL}/ref-pm/${id}`, getAuthConfig());
      setSuccess("Data berhasil dihapus.");
      await fetchData();
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menghapus data."
      );
    } finally {
      setDeleteLoadingId(null);
    }
  };

  return (
    <div className="referensi-pm-container">
      <div className="referensi-pm-header">
        <h2>Referensi Penjaminan Mutu</h2>

        <div className="referensi-pm-toolbar">
          <button className="btn-reset" onClick={handleReset}>
            <FaSyncAlt />
            Reset
          </button>

          <input
            type="text"
            className="search-input"
            placeholder="Cari referensi..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />

          <button className="search-btn" type="button">
            <FaSearch />
            Cari
          </button>

          <button className="btn-primary" onClick={openAddModal}>
            <FaPlus />
            Tambah Referensi
          </button>
        </div>
      </div>

      {success && <div className="alert-success">{success}</div>}
      {error && !showModal && <div className="alert-error">{error}</div>}

      <div className="referensi-pm-table-wrapper">
        <table className="referensi-pm-table">
          <thead>
            <tr>
              <th className="th-center">No</th>

              <SortableTh
                sortConfig={sortConfig}
                sortKey="nama"
                onSort={handleSort}
              >
                Nama Referensi PM
              </SortableTh>

              <SortableTh
                sortConfig={sortConfig}
                sortKey="kategori"
                onSort={handleSort}
              >
                Kategori
              </SortableTh>

              <SortableTh
                sortConfig={sortConfig}
                sortKey="deskripsi"
                onSort={handleSort}
              >
                Deskripsi
              </SortableTh>

              <SortableTh
                sortConfig={sortConfig}
                sortKey="status"
                onSort={handleSort}
                className="th-center"
              >
                Status
              </SortableTh>

              <th className="th-center">Aksi</th>
            </tr>
          </thead>

          <tbody>
            {loading ? (
              <tr>
                <td colSpan="6" className="text-center">
                  Memuat data...
                </td>
              </tr>
            ) : paginatedList.length === 0 ? (
              <tr>
                <td colSpan="6" className="text-center">
                  Tidak ada data referensi.
                </td>
              </tr>
            ) : (
              paginatedList.map((item, index) => (
                <tr key={item.id || index}>
                  <td className="th-center">{startIndex + index + 1}</td>
                  <td>{item.nama}</td>
                  <td>{item.kategori}</td>
                  <td className="deskripsi-cell" title={item.deskripsi}>
                    {item.deskripsi}
                  </td>
                  <td className="th-center">
                    <span
                      className={`status-badge ${
                        item.statusValue ? "active" : "inactive"
                      }`}
                    >
                      {item.statusLabel}
                    </span>
                  </td>
                  <td>
                    <div className="aksi">
                      <button
                        className="btn-detail"
                        title="Detail"
                        onClick={() => openDetailModal(item)}
                      >
                        <FaEye />
                      </button>

                      <button
                        className="btn-edit"
                        title="Edit"
                        onClick={() => openEditModal(item)}
                      >
                        <FaEdit />
                      </button>

                      <button
                        className="btn-delete"
                        title="Hapus"
                        onClick={() => handleDelete(item.id)}
                        disabled={deleteLoadingId === item.id}
                      >
                        <FaTrash />
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
            Menampilkan {sortedData.length === 0 ? 0 : startIndex + 1} -{" "}
            {Math.min(endIndex, sortedData.length)} dari {sortedData.length} data
          </div>

          <div className="pagination-controls">
            <button
              className="btn-pagination"
              onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
            >
              Prev
            </button>

            <span className="page-number">
              Halaman {currentPage} / {totalPages}
            </span>

            <button
              className="btn-pagination"
              onClick={() =>
                setCurrentPage((prev) => Math.min(prev + 1, totalPages))
              }
              disabled={currentPage === totalPages}
            >
              Next
            </button>
          </div>
        </div>
      </div>

      {showModal && (
        <div className="modal-overlay">
          <div className="modal-box referensi-modal">
            <h3>{isEditMode ? "Edit Referensi PM" : "Tambah Referensi PM"}</h3>

            {error && <div className="alert-error">{error}</div>}

            <form onSubmit={handleSubmit}>
              <label>Nama Referensi PM</label>
              <input
                type="text"
                name="NAMA_PM"
                value={form.NAMA_PM}
                onChange={handleChange}
                placeholder="Masukkan nama referensi PM"
              />

              <label>Kategori / Parent</label>
              <select
                name="REF_ID_REF_PM"
                value={form.REF_ID_REF_PM}
                onChange={handleChange}
              >
                <option value="">Tidak ada parent</option>
                {parentOptions
                  .filter((item) => String(item.id) !== String(selectedId || ""))
                  .map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.nama}
                    </option>
                  ))}
              </select>

              <label>Deskripsi</label>
              <textarea
                name="DESKRIPSI_PM"
                value={form.DESKRIPSI_PM}
                onChange={handleChange}
                rows="4"
                placeholder="Masukkan deskripsi referensi PM"
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

      {showDetailModal && selectedDetail && (
        <div className="modal-overlay">
          <div className="modal-box referensi-modal">
            <h3>Detail Referensi PM</h3>

            <div className="detail-grid">
              <div>
                <label>ID Referensi PM</label>
                <p>{selectedDetail.id || "-"}</p>
              </div>

              <div>
                <label>Nomor Urut</label>
                <p>{selectedDetail.nomorUrut || "-"}</p>
              </div>

              <div>
                <label>Nama Referensi PM</label>
                <p>{selectedDetail.nama}</p>
              </div>

              <div>
                <label>Kategori / Parent</label>
                <p>{selectedDetail.kategori}</p>
              </div>

              <div>
                <label>Status</label>
                <p>{selectedDetail.statusLabel}</p>
              </div>

              <div>
                <label>Memiliki Child</label>
                <p>{selectedDetail.hasChild ? "Ya" : "Tidak"}</p>
              </div>

              <div className="detail-full">
                <label>Deskripsi</label>
                <p>{selectedDetail.deskripsi}</p>
              </div>
            </div>

            <div className="modal-actions">
              <button
                type="button"
                className="btn-cancel"
                onClick={closeDetailModal}
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
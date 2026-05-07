import React, { useEffect, useMemo, useState } from "react";
import axios from "axios";
import {
  FaSearch,
  FaSyncAlt,
  FaEdit,
  FaTrash,
  FaPlus,
} from "react-icons/fa";
import "../../styles/PM/ReferensiPm.css";

const API_BASE_URL = "http://127.0.0.1:8000/api";

function normalizeText(value) {
  return String(value ?? "").toLowerCase();
}

function getAuthConfig() {
  const token = localStorage.getItem("token");
  return {
    headers: {
      Authorization: token ? `Bearer ${token}` : "",
      Accept: "application/json",
    },
  };
}

export default function ReferensiPm() {
  const [dataList, setDataList] = useState([]);
  const [filteredList, setFilteredList] = useState([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const [showModal, setShowModal] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [selectedId, setSelectedId] = useState(null);

  const [form, setForm] = useState({
    REF_ID_REF_PM: "",
    NAMA_PM: "",
    DESKRIPSI_PM: "",
  });

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 8;

 const fetchData = async () => {
  setLoading(true);
  setError("");

  try {
    const res = await axios.get(`${API_BASE_URL}/ref-pm`, getAuthConfig());
    const raw = Array.isArray(res.data?.data) ? res.data.data : [];

    const flattened = [];

    const flattenAll = (items) => {
      items.forEach((item) => {
        flattened.push(item);
        if (Array.isArray(item.children) && item.children.length > 0) {
          flattenAll(item.children);
        }
      });
    };

    flattenAll(raw);

    const withKategori = flattened.map((item) => {
      const parent = flattened.find(
        (p) => Number(p.ID_REF_PM) === Number(item.REF_ID_REF_PM)
      );

      return {
        ...item,
        KATEGORI: parent
          ? parent.NAMA_PM || parent.NAMA_REF_PM || "-"
          : "-",
      };
    });

    withKategori.sort((a, b) => {
      const idA = Number(a.ID_REF_PM || 0);
      const idB = Number(b.ID_REF_PM || 0);
      return idA - idB;
    });

    setDataList(withKategori);
    setFilteredList(withKategori);

    return withKategori;
  } catch (err) {
    setError(
      err?.response?.data?.message ||
        err?.response?.data?.error ||
        "Gagal mengambil data Referensi PM."
    );
    return [];
  } finally {
    setLoading(false);
  }
};

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    const keyword = normalizeText(search);

    if (!keyword) {
      setFilteredList(dataList);
      return;
    }

    const result = dataList.filter((item) => {
      const merged = [
        item?.NAMA_PM,
        item?.DESKRIPSI_PM,
        item?.KATEGORI,
      ]
        .map(normalizeText)
        .join(" ");

      return merged.includes(keyword);
    });

    setFilteredList(result);
  }, [search, dataList]);

  useEffect(() => {
    setCurrentPage(1);
  }, [search]);

  const totalPages = Math.ceil(filteredList.length / itemsPerPage);

  const paginatedList = useMemo(() => {
    const startIndex = (currentPage - 1) * itemsPerPage;
    return filteredList.slice(startIndex, startIndex + itemsPerPage);
  }, [filteredList, currentPage]);

  const handleReset = () => {
    setSearch("");
    setFilteredList(dataList);
    setCurrentPage(1);
    setError("");
    setSuccess("");
  };

  const openTambahModal = () => {
    setIsEdit(false);
    setSelectedId(null);
    setForm({
      REF_ID_REF_PM: "",
      NAMA_PM: "",
      DESKRIPSI_PM: "",
    });
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setIsEdit(true);
    setSelectedId(item.ID_REF_PM);
    setForm({
      REF_ID_REF_PM: item.REF_ID_REF_PM ?? "",
      NAMA_PM: item.NAMA_PM ?? "",
      DESKRIPSI_PM: item.DESKRIPSI_PM ?? "",
    });
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setIsEdit(false);
    setSelectedId(null);
    setForm({
      REF_ID_REF_PM: "",
      NAMA_PM: "",
      DESKRIPSI_PM: "",
    });
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError("");
    setSuccess("");

    const payload = {
      REF_ID_REF_PM:
        form.REF_ID_REF_PM === "" ? null : Number(form.REF_ID_REF_PM),
      NAMA_PM: form.NAMA_PM,
      DESKRIPSI_PM: form.DESKRIPSI_PM,
    };

    try {
      let res;

      if (isEdit && selectedId) {
        res = await axios.put(
          `${API_BASE_URL}/ref-pm/${selectedId}`,
          payload,
          getAuthConfig()
        );
      } else {
        res = await axios.post(
          `${API_BASE_URL}/ref-pm`,
          payload,
          getAuthConfig()
        );
      }

      setSuccess(
        res.data?.message ||
          (isEdit
            ? "Referensi PM berhasil diperbarui."
            : "Referensi PM berhasil ditambahkan.")
      );

      closeModal();

      const latestData = await fetchData();

      if (!isEdit) {
        const lastPage = Math.ceil(latestData.length / itemsPerPage);
        setCurrentPage(lastPage || 1);
      }
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menyimpan data Referensi PM."
      );
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    const confirmDelete = window.confirm(
      "Yakin ingin menghapus Referensi PM ini?"
    );
    if (!confirmDelete) return;

    setError("");
    setSuccess("");

    try {
      const res = await axios.delete(
        `${API_BASE_URL}/ref-pm/${id}`,
        getAuthConfig()
      );

      setSuccess(res.data?.message || "Referensi PM berhasil dihapus.");

      const latestData = await fetchData();
      const newTotalPages = Math.ceil(latestData.length / itemsPerPage);

      if (currentPage > newTotalPages && newTotalPages > 0) {
        setCurrentPage(newTotalPages);
      } else if (latestData.length === 0) {
        setCurrentPage(1);
      }
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menghapus Referensi PM."
      );
    }
  };

  const parentOptions = dataList.filter(
    (item) => !item.REF_ID_REF_PM || item.KATEGORI === "-"
  );

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

          <button className="btn-primary" onClick={openTambahModal}>
            <FaPlus />
            Tambah Referensi
          </button>
        </div>
      </div>

      {success ? <div className="alert-success">{success}</div> : null}
      {error ? <div className="alert-error">{error}</div> : null}

      <div className="referensi-pm-table-wrapper">
        <table className="referensi-pm-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Referensi PM</th>
              <th>Kategori</th>
              <th>Deskripsi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan="5" className="text-center">
                  Memuat data...
                </td>
              </tr>
            ) : paginatedList.length === 0 ? (
              <tr>
                <td colSpan="5" className="text-center">
                  Tidak ada data.
                </td>
              </tr>
            ) : (
              paginatedList.map((item, index) => (
                <tr key={item.ID_REF_PM || index}>
                  <td>{(currentPage - 1) * itemsPerPage + index + 1}</td>
                  <td className="ellipsis-cell">
                    {item.NAMA_PM || item.NAMA_REF_PM || "-"}
                  </td>
                  <td className="ellipsis-cell">{item.KATEGORI || "-"}</td>
                  <td className="ellipsis-cell">
                    {item.DESKRIPSI_PM || "-"}
                  </td>
                  <td>
                    <div className="aksi">
                      <button
                        className="btn-edit"
                        onClick={() => openEditModal(item)}
                        title="Edit"
                      >
                        <FaEdit />
                      </button>
                      <button
                        className="btn-delete"
                        onClick={() => handleDelete(item.ID_REF_PM)}
                        title="Hapus"
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
            Menampilkan{" "}
            {filteredList.length === 0
              ? 0
              : (currentPage - 1) * itemsPerPage + 1}
            {" - "}
            {Math.min(currentPage * itemsPerPage, filteredList.length)}
            {" dari "}
            {filteredList.length} data
          </div>

          <div className="pagination-controls">
            <button
              className="btn-reset"
              onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
            >
              Prev
            </button>

            <span className="page-number">
              Halaman {currentPage} / {totalPages || 1}
            </span>

            <button
              className="btn-reset"
              onClick={() =>
                setCurrentPage((prev) => Math.min(prev + 1, totalPages))
              }
              disabled={currentPage === totalPages || totalPages === 0}
            >
              Next
            </button>
          </div>
        </div>
      </div>

      {showModal && (
        <div className="modal-overlay">
          <div className="modal-box">
            <h3>{isEdit ? "Edit Referensi PM" : "Tambah Referensi PM"}</h3>

            <form onSubmit={handleSubmit}>
              <label>Kategori / Parent</label>
              <select
                name="REF_ID_REF_PM"
                value={form.REF_ID_REF_PM}
                onChange={handleChange}
              >
                <option value="">- Tanpa Kategori -</option>
                {parentOptions.map((item) => (
                  <option key={item.ID_REF_PM} value={item.ID_REF_PM}>
                    {item.NAMA_PM || item.NAMA_REF_PM}
                  </option>
                ))}
              </select>

              <label>Nama Referensi PM</label>
              <input
                type="text"
                name="NAMA_PM"
                placeholder="Masukkan nama referensi PM"
                value={form.NAMA_PM}
                onChange={handleChange}
                required
              />

              <label>Deskripsi</label>
              <textarea
                name="DESKRIPSI_PM"
                rows="4"
                placeholder="Masukkan deskripsi referensi PM"
                value={form.DESKRIPSI_PM}
                onChange={handleChange}
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
                    ? "Menyimpan..."
                    : isEdit
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
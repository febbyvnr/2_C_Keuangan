import React, { useEffect, useMemo, useState } from "react";
import axios from "axios";
import {
  FaSearch,
  FaSyncAlt,
  FaEye,
  FaCheck,
  FaTimes,
  FaCheckCircle,
  FaFileExcel,
  FaFilePdf,
} from "react-icons/fa";
import "../../styles/PM/VerifikasiEvaluasiPm.css";

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

function normalizeText(value) {
  return String(value ?? "").toLowerCase();
}

function formatDate(value) {
  if (!value) return "-";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString("id-ID");
}

function getStatus(item) {
  if (!item?.NIP_VALIDATOR_PM) return "Belum Diverifikasi";
  if (String(item.NIP_VALIDATOR_PM).toLowerCase() === "ditolak") {
    return "Ditolak";
  }
  return "Disetujui";
}

function getProgramKerja(item) {
  return (
    item?.programKerja?.PROGRAM_KERJA ||
    item?.program_kerja?.PROGRAM_KERJA ||
    item?.PROGRAM_KERJA ||
    item?.NAMA_PROGRAM_KERJA ||
    item?.programKerja ||
    "-"
  );
}

function getReferensiPm(item) {
  return (
    item?.refPm?.NAMA_PM ||
    item?.ref_pm?.NAMA_PM ||
    item?.NAMA_PM ||
    item?.REFERENSI_PM ||
    item?.REF_PM ||
    "-"
  );
}

export default function VerifikasiEvaluasiPm() {
  const [dataList, setDataList] = useState([]);
  const [filteredList, setFilteredList] = useState([]);
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(false);
  const [submittingId, setSubmittingId] = useState(null);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);

  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 9;

  const currentUser = (() => {
    try {
      return JSON.parse(localStorage.getItem("user")) || {};
    } catch {
      return {};
    }
  })();

  const currentNip =
    currentUser?.NIP_KARYAWAN ||
    currentUser?.nip ||
    currentUser?.NIP ||
    currentUser?.id ||
    "VALIDATOR_PM";

  const fetchData = async () => {
    setLoading(true);
    setError("");

    try {
      const res = await axios.get(`${API_BASE_URL}/evaluasi-rkt`, getAuthConfig());
      const raw = Array.isArray(res.data?.data) ? res.data.data : [];
      setDataList(raw);
      setFilteredList(raw);
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal mengambil data verifikasi evaluasi."
      );
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
        getProgramKerja(item),
        getReferensiPm(item),
        item?.DESKRIPSI_TR_PM,
        item?.NIP_VALIDATOR_PM,
        getStatus(item),
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

  const handleApprove = async (item) => {
    const confirmApprove = window.confirm(
      "Yakin ingin menyetujui evaluasi ini?"
    );
    if (!confirmApprove) return;

    setSubmittingId(item.ID_PM);
    setError("");
    setSuccess("");

    try {
      const payload = {
        NIP_VALIDATOR_PM: currentNip,
      };

      const res = await axios.put(
        `${API_BASE_URL}/evaluasi-rkt/approve/${item.ID_PM}`,
        payload,
        getAuthConfig()
      );

      setSuccess(res.data?.message || "Evaluasi berhasil disetujui.");
      await fetchData();
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menyetujui evaluasi."
      );
    } finally {
      setSubmittingId(null);
    }
  };

  const handleReject = async (item) => {
    const confirmReject = window.confirm(
      "Yakin ingin menolak evaluasi ini?"
    );
    if (!confirmReject) return;

    setSubmittingId(item.ID_PM);
    setError("");
    setSuccess("");

    try {
      const res = await axios.put(
        `${API_BASE_URL}/evaluasi-rkt/reject/${item.ID_PM}`,
        {},
        getAuthConfig()
      );

      setSuccess(res.data?.message || "Evaluasi berhasil ditolak.");
      await fetchData();
    } catch (err) {
      setError(
        err?.response?.data?.message ||
          err?.response?.data?.error ||
          "Gagal menolak evaluasi."
      );
    } finally {
      setSubmittingId(null);
    }
  };

  const downloadExport = async (type) => {
    try {
      setError("");

      const token = localStorage.getItem("token");

      const response = await fetch(
        `${API_BASE_URL}/evaluasi-rkt/export/${type}`,
        {
          method: "GET",
          headers: {
            Authorization: token ? `Bearer ${token}` : "",
            Accept: "application/octet-stream",
          },
        }
      );

      if (!response.ok) {
        throw new Error(`Gagal export ${type.toUpperCase()}`);
      }

      const blob = await response.blob();

      const url = window.URL.createObjectURL(blob);

      const link = document.createElement("a");
      link.href = url;

      if (type === "excel") {
        link.download = "verifikasi_pm.xlsx";
      } else if (type === "csv") {
        link.download = "verifikasi_pm.csv";
      } else {
        link.download = "verifikasi_pm.pdf";
      }

      document.body.appendChild(link);
      link.click();
      link.remove();

      window.URL.revokeObjectURL(url);
    } catch (err) {
      setError(err.message || "Gagal export data.");
    }
  };

  return (
    <div className="verifikasi-pm-container">
      <div className="verifikasi-pm-header">
        <h2>Verifikasi & Evaluasi PM</h2>

        <div className="verifikasi-pm-toolbar">
          <button className="btn-reset" onClick={handleReset}>
            <FaSyncAlt />
            Reset
          </button>

          <input
            type="text"
            className="search-input"
            placeholder="Cari evaluasi PM..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />

          <button className="search-btn" type="button">
            <FaSearch />
            Cari
          </button>
        </div>
      </div>

      {success ? <div className="alert-success">{success}</div> : null}
      {error ? <div className="alert-error">{error}</div> : null}

      <div className="verifikasi-pm-table-wrapper">
        <div className="verifikasi-pm-table-scroll">
          <table className="verifikasi-pm-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Program Kerja</th>
                <th>Referensi PM</th>
                <th>Deskripsi Evaluasi</th>
                <th>Validator</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="8" className="text-center">
                    Memuat data...
                  </td>
                </tr>
              ) : paginatedList.length === 0 ? (
                <tr>
                  <td colSpan="8" className="text-center">
                    Tidak ada data.
                  </td>
                </tr>
              ) : (
                paginatedList.map((item, index) => {
                  const status = getStatus(item);
                  const isProcessing = submittingId === item.ID_PM;

                  return (
                    <tr key={item.ID_PM || index}>
                      <td>{(currentPage - 1) * itemsPerPage + index + 1}</td>
                      <td className="ellipsis-cell">{getProgramKerja(item)}</td>
                      <td className="ellipsis-cell">{getReferensiPm(item)}</td>
                      <td className="ellipsis-cell">
                        {item?.DESKRIPSI_TR_PM || "-"}
                      </td>
                      <td className="ellipsis-cell">
                        {item?.NIP_VALIDATOR_PM &&
                        String(item.NIP_VALIDATOR_PM).toLowerCase() !== "ditolak"
                          ? item.NIP_VALIDATOR_PM
                          : "-"}
                      </td>
                      <td>{formatDate(item?.TGL_PM)}</td>
                      <td className="text-center">
                        <span
                          className={`status-badge ${
                            status === "Disetujui"
                              ? "approved"
                              : status === "Ditolak"
                              ? "rejected"
                              : "pending"
                          }`}
                        >
                          {status}
                        </span>
                      </td>
                      <td>
                        <div className="aksi">
                          <button
                            className="btn-view"
                            onClick={() => setSelectedItem(item)}
                            title="Detail"
                          >
                            <FaEye />
                          </button>

                          <button
                            className="btn-approve"
                            onClick={() => handleApprove(item)}
                            title="Setujui"
                            disabled={isProcessing || status === "Disetujui"}
                          >
                            <FaCheck />
                          </button>

                          <button
                            className="btn-reject"
                            onClick={() => handleReject(item)}
                            title="Tolak"
                            disabled={isProcessing || status === "Ditolak"}
                          >
                            <FaTimes />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        <div className="table-footer">

        <div className="footer-left">
          <div className="export-buttons">

            <button
              className="export-btn excel"
              // onClick={() => downloadExport("excel")}
            >
              Export Excel
            </button>

            <button
              className="export-btn pdf"
              // onClick={() => downloadExport("pdf")}
            >
              Export PDF
            </button>

          </div>
        </div>

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
              className="btn-pagination"
              onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
            >
              Prev
            </button>

            <span className="page-number">
              Halaman {currentPage} / {totalPages || 1}
            </span>

            <button
              className="btn-pagination"
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

      {selectedItem && (
        <div className="modal-overlay">
          <div className="verifikasi-modal-box">
            <h3>Detail Verifikasi Evaluasi PM</h3>

            <div className="detail-grid">
              <div>
                <span className="detail-label">Program Kerja</span>
                <p>{getProgramKerja(selectedItem)}</p>
              </div>

              <div>
                <span className="detail-label">Referensi PM</span>
                <p>{getReferensiPm(selectedItem)}</p>
              </div>

              <div>
                <span className="detail-label">Tanggal</span>
                <p>{formatDate(selectedItem?.TGL_PM)}</p>
              </div>

              <div>
                <span className="detail-label">Status</span>
                <p>{getStatus(selectedItem)}</p>
              </div>

              <div>
                <span className="detail-label">Validator</span>
                <p>
                  {selectedItem?.NIP_VALIDATOR_PM &&
                  String(selectedItem.NIP_VALIDATOR_PM).toLowerCase() !==
                    "ditolak"
                    ? selectedItem.NIP_VALIDATOR_PM
                    : "-"}
                </p>
              </div>

              <div>
                <span className="detail-label">Hasil Verifikasi</span>
                <p>
                  {String(selectedItem?.NIP_VALIDATOR_PM).toLowerCase() ===
                  "ditolak"
                    ? "Ditolak"
                    : selectedItem?.NIP_VALIDATOR_PM
                    ? "Disetujui"
                    : "Belum Diverifikasi"}
                </p>
              </div>

              <div className="full-width">
                <span className="detail-label">Deskripsi Evaluasi</span>
                <p>{selectedItem?.DESKRIPSI_TR_PM || "-"}</p>
              </div>
            </div>

            <div className="modal-actions">
              <button
                className="btn-cancel"
                onClick={() => setSelectedItem(null)}
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
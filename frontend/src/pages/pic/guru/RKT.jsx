import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/SidebarPic.css";
import "../../../styles/pic/guru/RKT.css";
import { Plus, Download } from "lucide-react";
function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value || 0);
}

function formatDate(value) {
  if (!value) return "-";
  return new Date(value).toLocaleDateString("id-ID");
}

function formatLongDate(value) {
  if (!value) return "-";

  return new Date(value).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

function getStatusInfo(item) {
  if (!item) {
    return {
      value: "diajukan",
      label: "Diajukan",
      detailLabel: "Diajukan",
      className: "rkt-status-badge submitted",
    };
  }

  const validator = item?.NIP_VALIDATOR_PROGKER;

  const trPmList = item?.trPm || item?.tr_pm || [];
  const lastNote = trPmList[trPmList.length - 1]?.DESKRIPSI_TR_PM || "";
  const note = lastNote.toLowerCase().trim();

  if (note.startsWith("draft")) {
    return {
      value: "draft",
      label: "Draft",
      detailLabel: "Draft (Belum diajukan)",
      className: "rkt-status-badge draft",
    };
  }

  if (note.startsWith("ditolak")) {
    return {
      value: "ditolak",
      label: "Ditolak",
      detailLabel: "Ditolak",
      className: "rkt-status-badge rejected",
    };
  }

  if (note.startsWith("revisi")) {
    return {
      value: "revisi",
      label: "Revisi",
      detailLabel: "Perlu Revisi",
      className: "rkt-status-badge revision",
    };
  }

  if (validator) {
    return {
      value: "disetujui",
      label: "Disetujui",
      detailLabel: "Disetujui Kepala Sekolah",
      className: "rkt-status-badge approved",
    };
  }

  return {
    value: "diajukan",
    label: "Diajukan",
    detailLabel: "Menunggu Approval Kepala Sekolah",
    className: "rkt-status-badge submitted",
  };
}

export default function RKT() {
  const navigate = useNavigate();

  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [selectedId, setSelectedId] = useState(null);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
  });

  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const selectedItem = useMemo(() => {
    return data.find((item) => item.ID_PROGRAM_KERJA === selectedId) || null;
  }, [data, selectedId]);

  const selectedStatus = getStatusInfo(selectedItem);

  const filteredData = useMemo(() => {
    return data.filter((item) => {
      const derivedStatus = getStatusInfo(item).value;

      const matchStatus = !statusFilter || derivedStatus === statusFilter;

      const keyword = search.trim().toLowerCase();
      const matchSearch =
        !keyword ||
        String(item.PROGRAM_KERJA || "").toLowerCase().includes(keyword) ||
        String(item.INDIKATOR || "").toLowerCase().includes(keyword);

      return matchStatus && matchSearch;
    });
  }, [data, search, statusFilter]);

  const fetchRkt = async (customSearch = search, page = 1) => {
    try {
      setLoading(true);

      const response = await axios.get("http://127.0.0.1:8000/api/rkt", {
        params: {
          search: customSearch,
          per_page: 10,
          page,
        },
      });

      console.log("response.data:", response.data);

      const apiData = response.data?.data ?? response.data;

      let rows = [];
      let currentPage = 1;
      let lastPage = 1;
      let perPage = 10;
      let total = 0;

      if (Array.isArray(apiData)) {
        rows = apiData;
        total = apiData.length;
      } else if (Array.isArray(apiData?.data)) {
        rows = apiData.data;
        currentPage = apiData.current_page || 1;
        lastPage = apiData.last_page || 1;
        perPage = apiData.per_page || 10;
        total = apiData.total || apiData.data.length;
      } else if (Array.isArray(apiData?.data?.data)) {
        rows = apiData.data.data;
        currentPage = apiData.data.current_page || 1;
        lastPage = apiData.data.last_page || 1;
        perPage = apiData.data.per_page || 10;
        total = apiData.data.total || apiData.data.data.length;
      }

      setData(rows);
      setPagination({
        currentPage,
        lastPage,
        perPage,
        total,
      });

      setSelectedId((prev) => {
        if (!rows.length) return null;
        if (prev && rows.some((item) => item.ID_PROGRAM_KERJA === prev)) {
          return prev;
        }
        return rows[0].ID_PROGRAM_KERJA;
      });
    } catch (error) {
      console.error("Gagal ambil data RKT:", error);
    } finally {
      setLoading(false);
    }
  };


  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const nipLogin = String(user.NIP_KARYAWAN || "");

  const isOwner = selectedItem
    ? String(selectedItem.NIP_PENANGGUNG_JAWAB || "") === nipLogin
    : false;

  const selectedStatusValue = selectedStatus.value;

  const canEdit =
    isOwner &&
    (selectedStatusValue === "draft" || selectedStatusValue === "revisi");

  const canDelete =
    isOwner && (selectedStatusValue === "draft");

  const canSubmit =
    isOwner && selectedStatusValue === "draft";

  useEffect(() => {
    window.scrollTo(0, 0);
    fetchRkt("", 1);
  }, []);

  const handleSearch = () => {
    fetchRkt(search, 1);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleReset = () => {
    setSearch("");
    setStatusFilter("");
    fetchRkt("", 1);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleAjukan = async (id) => {
    try {
      await axios.post(`http://127.0.0.1:8000/api/rkt/ajukan/${id}`, {
        NIP_LOGIN: nipLogin,
      });

      await fetchRkt(search, pagination.currentPage);
    } catch (error) {
      alert(error.response?.data?.message || "Gagal mengajukan RKT");
    }
  };

  const handleDelete = async () => {
    if (!deleteTarget) return;

    try {
      setDeleting(true);

      await axios.delete(
        `http://127.0.0.1:8000/api/rkt/delete/${deleteTarget.ID_PROGRAM_KERJA}`,
        {
          data: {
            NIP_LOGIN: nipLogin,
          },
        }
      );

      setDeleteTarget(null);

      const nextPage =
        data.length === 1 && pagination.currentPage > 1
          ? pagination.currentPage - 1
          : pagination.currentPage;

      await fetchRkt(search, nextPage);
    } catch (error) {
      alert(error.response?.data?.message || "Data gagal dihapus");
    } finally {
      setDeleting(false);
    }
  };

  const handleExport = () => {
    const params = new URLSearchParams({ search });
    window.open(
      `http://127.0.0.1:8000/api/rkt/export/excel?${params.toString()}`,
      "_blank"
    );
  };

  const getDisplayValue = (...values) => {
    const found = values.find((value) => {
      if (value === null || value === undefined) return false;
      if (typeof value === "string") return value.trim() !== "";
      return true;
    });

    return found ?? "-";
  };

  return (
    <div className="rkt-shell">
      <SidebarPic />

      <main className="rkt-main">
        <div className="rkt-wrapper">
          <div className="rkt-header-card">
            <div>
              <h1 className="rkt-title">Rencana Kerja Tahunan</h1>
              <p className="rkt-subtitle">Kelola data program kerja PIC/Guru.</p>
            </div>

            <div className="rkt-header-actions">
              <button className="btn-warning-custom" onClick={handleExport}>
                <Download size={16} />
                Export Excel
              </button>

              <button
                className="btn-primary-custom"
                onClick={() => navigate("/pic/guru/rkt/create")}
              >
                <Plus size={16} />
                Tambah RKT
              </button>
            </div>
          </div>

          <div className="rkt-filter-card">
            <div className="rkt-filter-row">
              <div className="rkt-input-group rkt-search-group">
                <label>Search</label>
                <input
                  type="text"
                  placeholder="Cari program kerja atau indikator..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === "Enter") handleSearch();
                  }}
                />
              </div>

              <div className="rkt-input-group rkt-filter-small">
                <label>Status</label>
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                >
                  <option value="">Semua Status</option>
                  <option value="draft">Draft</option>
                  <option value="diajukan">Diajukan</option>
                  <option value="disetujui">Disetujui</option>
                  <option value="ditolak">Ditolak</option>
                  <option value="revisi">Revisi</option>
                </select>
              </div>

              <div className="rkt-filter-actions">
                <button className="btn-primary-custom" onClick={handleSearch}>
                  Cari
                </button>
                <button className="btn-light-custom" onClick={handleReset}>
                  Reset
                </button>
              </div>
            </div>
          </div>

          <div className="rkt-content-section">
            <div className="rkt-table-card">
              {loading ? (
                <div className="rkt-empty">Loading data...</div>
              ) : (
                <>
                  <div className="rkt-table-wrapper">
                    <table className="rkt-table">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Program Kerja</th>
                          <th>Indikator</th>
                          <th>Waktu</th>
                          <th>Anggaran</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        {filteredData.length > 0 ? (
                          filteredData.map((item, index) => {

                            return (
                              <tr
                                key={item.ID_PROGRAM_KERJA}
                                className={
                                  selectedId === item.ID_PROGRAM_KERJA
                                    ? "rkt-row-active"
                                    : ""
                                }
                                onClick={() => setSelectedId(item.ID_PROGRAM_KERJA)}
                              >
                                <td>
                                  {(pagination.currentPage - 1) * pagination.perPage + index + 1}
                                </td>

                                <td className="rkt-program">
                                  {item.PROGRAM_KERJA || "-"}
                                </td>

                                <td>{item.INDIKATOR || "-"}</td>

                                <td>
                                  <div>{formatDate(item.WAKTU_AWAL)}</div>
                                  <div className="rkt-date-sub">
                                    s.d {formatDate(item.WAKTU_AKHIR)}
                                  </div>
                                </td>

                                <td className="rkt-amount">
                                  {formatRupiah(item.NOMINAL)}
                                </td>

                                <td>
                                  <span className={getStatusInfo(item).className}>
                                    {getStatusInfo(item).label}
                                  </span>
                                </td>
                              </tr>
                            );
                          })
                        ) : (
                          <tr>
                            <td colSpan="6" className="rkt-empty">
                              Tidak ada data RKT
                            </td>
                          </tr>
                        )}
                      </tbody>
                    </table>
                  </div>

                  <div className="rkt-pagination">
                    <span className="rkt-pagination-info">
                      Menampilkan{" "}
                      {filteredData.length > 0 ? 1 : 0}
                      {" - "}
                      {filteredData.length}
                      {" dari "}
                      {data.length} data
                    </span>

                    <div className="rkt-pagination-actions">
                      <button
                        className="rkt-page-btn"
                        disabled={pagination.currentPage === 1}
                        onClick={() =>
                          fetchRkt(search, pagination.currentPage - 1)
                        }
                      >
                        ‹
                      </button>

                      <span className="rkt-page-number">
                        {pagination.currentPage}
                      </span>

                      <button
                        className="rkt-page-btn"
                        disabled={
                          pagination.currentPage === pagination.lastPage
                        }
                        onClick={() =>
                          fetchRkt(search, pagination.currentPage + 1)
                        }
                      >
                        ›
                      </button>
                    </div>
                  </div>
                </>
              )}
            </div>

            <aside className="rkt-detail-card">
              {selectedItem ? (
                <>
                  <div className="rkt-detail-header">
                    <div className="rkt-detail-header-top">
                      <div>
                        <h2 className="rkt-detail-title">
                          {selectedItem.PROGRAM_KERJA || "Detail RKT"}
                        </h2>

                        <p className="rkt-detail-subtitle">
                          Klik baris pada tabel untuk melihat detail program kerja.
                        </p>

                        <span className={selectedStatus.className}>
                          {selectedStatus.detailLabel || selectedStatus.label}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div className="rkt-detail-body">
                    <div className="rkt-detail-grid">
                      <div className="rkt-detail-item full">
                        <span className="rkt-detail-label">Indikator</span>
                        <span className="rkt-detail-value">
                          {selectedItem.INDIKATOR || "-"}
                        </span>
                      </div>

                      <div className="rkt-detail-item full">
                        <span className="rkt-detail-label">Sasaran</span>
                        <span className="rkt-detail-value">
                          {selectedItem.SASARAN || "-"}
                        </span>
                      </div>

                      <div className="rkt-detail-item full">
                        <span className="rkt-detail-label">Keluaran Program Kerja</span>
                        <span className="rkt-detail-value">
                          {selectedItem.KELUARAN_PROGKER || "-"}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Waktu Awal</span>
                        <span className="rkt-detail-value">
                          {formatLongDate(selectedItem.WAKTU_AWAL)}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Waktu Akhir</span>
                        <span className="rkt-detail-value">
                          {formatLongDate(selectedItem.WAKTU_AKHIR)}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Nominal</span>
                        <span className="rkt-detail-value strong">
                          {formatRupiah(selectedItem.NOMINAL)}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Validator</span>
                        <span className="rkt-detail-value">
                          {selectedItem.NIP_VALIDATOR_PROGKER
                            ? `${selectedItem.NIP_VALIDATOR_PROGKER} - ${
                                selectedItem.NAMA_VALIDATOR || "Validator"
                              }`
                            : "Belum ada validator"}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Pemberi Catatan</span>
                        <span className="rkt-detail-value">
                          {selectedItem.tr_pm?.[selectedItem.tr_pm.length - 1]?.NIP_VALIDATOR_PM ||
                            selectedItem.trPm?.[selectedItem.trPm.length - 1]?.NIP_VALIDATOR_PM ||
                            "-"}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Penanggung Jawab</span>
                        <span className="rkt-detail-value">
                          {getDisplayValue(
                            selectedItem.penanggung_jawab?.NAMA_KARYAWAN,
                            selectedItem.nama_penanggung_jawab,
                            selectedItem.NAMA_PENANGGUNG_JAWAB,
                            selectedItem.NIP_PENANGGUNG_JAWAB
                          )}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Unit</span>
                        <span className="rkt-detail-value">
                          {getDisplayValue(
                            selectedItem.unit?.NAMA_UNIT,
                            selectedItem.nama_unit
                          )}
                        </span>
                      </div>

                      <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Tahun Anggaran</span>
                        <span className="rkt-detail-value">
                          {getDisplayValue(
                            selectedItem.tahun_anggaran?.DESKRIPSI_TAHUN_ANGGARAN,
                            selectedItem.tahunAnggaran?.DESKRIPSI_TAHUN_ANGGARAN,
                            selectedItem.tahun_anggaran?.TAHUN_ANGGARAN,
                            selectedItem.tahunAnggaran?.TAHUN_ANGGARAN,
                            "Belum tersedia"
                          )}
                        </span>
                      </div>
                    </div>

                    <div className="rkt-note-box">
                      <div className="rkt-note-title">Catatan Revisi / Review</div>
                      <div className="rkt-note-content">
                        {selectedItem.tr_pm?.[selectedItem.tr_pm.length - 1]?.DESKRIPSI_TR_PM ||
                          selectedItem.trPm?.[selectedItem.trPm.length - 1]?.DESKRIPSI_TR_PM ||
                          selectedItem.CATATAN_REVISI ||
                          selectedItem.catatan_revisi ||
                          "Belum ada catatan revisi."}
                      </div>
                    </div>
                  </div>

                  <div className="rkt-detail-actions">
                    {selectedStatusValue === "disetujui" && (
                      <button className="btn-light-custom rkt-detail-btn" disabled>
                        Sudah Disetujui
                      </button>
                    )}

                    {selectedStatusValue === "ditolak" && (
                      <button className="btn-light-custom rkt-detail-btn" disabled>
                        Ditolak
                      </button>
                    )}

                    {selectedStatusValue === "diajukan" && (
                      <>
                        <button
                          className="btn-red-sm rkt-detail-btn"
                          disabled={!canDelete}
                          onClick={() => setDeleteTarget(selectedItem)}
                        >
                          Hapus
                        </button>

                        <button className="btn-light-custom rkt-detail-btn" disabled>
                          Menunggu Approval Kepala Sekolah
                        </button>
                      </>
                    )}

                    {selectedStatusValue === "revisi" && (
                      <>
                        <button
                          className="btn-yellow-sm rkt-detail-btn"
                          disabled={!canEdit}
                          onClick={() =>
                            navigate(`/pic/guru/rkt/edit/${selectedItem.ID_PROGRAM_KERJA}`)
                          }
                        >
                          Perbaiki Revisi
                        </button>
                      </>
                    )}

                    {selectedStatusValue === "draft" && (
                      <>
                        <button
                          className="btn-yellow-sm rkt-detail-btn"
                          disabled={!canEdit}
                          onClick={() =>
                            navigate(`/pic/guru/rkt/edit/${selectedItem.ID_PROGRAM_KERJA}`)
                          }
                        >
                          Edit Draft
                        </button>

                        <button
                          className="btn-primary-custom rkt-detail-btn"
                          disabled={!canSubmit}
                          onClick={() => handleAjukan(selectedItem.ID_PROGRAM_KERJA)}
                        >
                          Ajukan RKT
                        </button>

                        <button
                          className="btn-red-sm rkt-detail-btn"
                          disabled={!canDelete}
                          onClick={() => setDeleteTarget(selectedItem)}
                        >
                          Hapus
                        </button>
                      </>
                    )}
                  </div>
                </>
              ) : (
                <div className="rkt-detail-empty">
                  <div className="rkt-detail-empty-icon">📋</div>
                  <p>Klik baris pada tabel untuk melihat detail program kerja.</p>
                </div>
              )}
            </aside>
          </div>
        </div>

        {deleteTarget && (
          <div className="delete-modal-overlay">
            <div className="delete-modal-box">
              <button
                type="button"
                className="delete-modal-close"
                onClick={() => setDeleteTarget(null)}
                disabled={deleting}
              >
                ×
              </button>

              <div className="delete-modal-icon">!</div>

              <h3>Konfirmasi Hapus</h3>

              <p>
                Yakin ingin menghapus program kerja ini?
                <br />
                Data yang sudah dihapus tidak dapat dikembalikan.
              </p>

              <div className="delete-modal-actions">
                <button
                  type="button"
                  className="delete-cancel-btn"
                  onClick={() => setDeleteTarget(null)}
                  disabled={deleting}
                >
                  Batal
                </button>

                <button
                  type="button"
                  className="delete-confirm-btn"
                  onClick={handleDelete}
                  disabled={deleting}
                >
                  {deleting ? "Menghapus..." : "Ya, Hapus"}
                </button>
              </div>
            </div>
          </div>
        )}


      </main>
    </div>
  );
}
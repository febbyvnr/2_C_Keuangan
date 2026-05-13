import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "../../../styles/bendahara/SidebarBendahara.css";
import "../../../styles/pic/guru/RKT.css";
import { Plus} from "lucide-react";

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

function getLatestReviewNote(item) {
  const trPmList = item?.trPm || item?.tr_pm || [];
  const lastTrPm = trPmList.length > 0 ? trPmList[trPmList.length - 1] : null;

  const rawNote =
    lastTrPm?.DESKRIPSI_TR_PM ||
    lastTrPm?.deskripsi_tr_pm ||
    item?.DESKRIPSI_TR_PM ||
    item?.CATATAN_REVISI ||
    item?.catatan_revisi ||
    "";

  if (!rawNote) return "Belum ada catatan.";

  const parts = String(rawNote)
    .split(/\s:\s(?=Draft|Diajukan|Revisi|Ditolak|Disetujui)/i)
    .map((part) => part.trim())
    .filter(Boolean);

  return parts.length ? parts[parts.length - 1] : rawNote;
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
  const lastTrPm = trPmList.length > 0 ? trPmList[trPmList.length - 1] : null;

  const aksi = String(
    lastTrPm?.AKSI ||
      lastTrPm?.aksi ||
      item?.AKSI ||
      item?.aksi ||
      ""
  )
    .trim()
    .toUpperCase();

  const note = String(
    lastTrPm?.DESKRIPSI_TR_PM ||
      lastTrPm?.deskripsi_tr_pm ||
      item?.DESKRIPSI_TR_PM ||
      ""
  )
    .toLowerCase()
    .trim();

  if (aksi === "DRAFT" || note.startsWith("draft")) {
    return {
      value: "draft",
      label: "Draft",
      detailLabel: "Draft (Belum diajukan)",
      className: "rkt-status-badge draft",
    };
  }

  if (
    aksi === "TOLAK" ||
    aksi === "DITOLAK" ||
    aksi === "REJECT" ||
    note.includes("ditolak") ||
    note.includes("tolak")
  ) {
    return {
      value: "ditolak",
      label: "Ditolak",
      detailLabel: "Ditolak Kepala Sekolah",
      className: "rkt-status-badge rejected",
    };
  }

  if (
    aksi === "REVISI" ||
    aksi === "REVISION" ||
    note.startsWith("revisi") ||
    note.includes(": revisi")
  ) {
    return {
      value: "revisi",
      label: "Revisi",
      detailLabel: "Perlu Revisi",
      className: "rkt-status-badge revision",
    };
  }

  if (
    aksi === "DIAJUKAN" ||
    note.startsWith("diajukan") ||
    note.includes(": diajukan")
  ) {
    return {
      value: "diajukan",
      label: "Diajukan",
      detailLabel: "Menunggu Approval Kepala Sekolah",
      className: "rkt-status-badge submitted",
    };
  }

  if (
    aksi === "SETUJUI" ||
    aksi === "DISETUJUI" ||
    aksi === "APPROVE" ||
    aksi === "APPROVED" ||
    validator
  ) {
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

function getRkaDetails(item) {
  return (
    item?.detailProgramKerja ||
    item?.detail_program_kerja ||
    item?.Rka ||
    item?.rka ||
    []
  );
}

function getTotalRka(item) {
  return getRkaDetails(item).reduce((total, detail) => {
    return total + Number(detail.NOMINAL || 0);
  }, 0);
}

function getRkaValidationInfo(item) {
  const pagu = Number(item?.TOTAL_PROGKER || 0);
  const totalRka = getTotalRka(item);
  const minimalRka = pagu * 0.95;
  if (!item) {
    return {
      valid: false,
      label: "Pilih RKT terlebih dahulu.",
    };
  }

  if (totalRka <= 0) {
    return {
      valid: false,
      label: "Lengkapi rincian RKA terlebih dahulu sebelum mengajukan RKT",
    };
  }

  if (totalRka > pagu) {
    return {
      valid: false,
      label: "Total RKA melebihi pagu",
    };
  }

  if (totalRka < minimalRka) {
    return {
      valid: false,
      label: "Total RKA belum mencapai 95% pagu",
    };
  }

  return {
    valid: true,
    label: "RKA sesuai anggaran",
  };
}

export default function RKT() {
  const navigate = useNavigate();

  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [selectedId, setSelectedId] = useState(null);

  const [statusDropdownOpen, setStatusDropdownOpen] = useState(false);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
  });

  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleting, setDeleting] = useState(false);

  const [toast, setToast] = useState(null);
  const [visible, setVisible] = useState(false);

  const showToast = (type = "success", message = "") => {
    setToast({ type, message });
    setVisible(true);

    setTimeout(() => setVisible(false), 2500);
    setTimeout(() => setToast(null), 3000);
  };

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

  const totalFilteredData = filteredData.length;

  const totalPages = Math.max(
    1,
    Math.ceil(totalFilteredData / pagination.perPage)
  );

  const startIndex = (pagination.currentPage - 1) * pagination.perPage;
  const endIndex = startIndex + pagination.perPage;

  const paginatedData = filteredData.slice(startIndex, endIndex);

  const showingStart = totalFilteredData === 0 ? 0 : startIndex + 1;
  const showingEnd = Math.min(endIndex, totalFilteredData);

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
        currentPage: page,
        lastPage: Math.max(1, Math.ceil(rows.length / perPage)),
        perPage,
        total: rows.length,
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

  const selectedRkaInfo = getRkaValidationInfo(selectedItem);

  const canSubmit =
    isOwner &&
    selectedStatusValue === "draft" &&
    selectedRkaInfo.valid;

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

  const handleChangePage = (nextPage) => {
    if (nextPage < 1 || nextPage > totalPages) return;

    setPagination((current) => ({
      ...current,
      currentPage: nextPage,
      lastPage: totalPages,
      total: totalFilteredData,
    }));

    const nextStartIndex = (nextPage - 1) * pagination.perPage;
    const nextItem = filteredData[nextStartIndex];

    setSelectedId(nextItem?.ID_PROGRAM_KERJA || null);

    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleAjukan = async (id) => {
    try {
      await axios.post(`http://127.0.0.1:8000/api/rkt/ajukan/${id}`, {
        NIP_LOGIN: nipLogin,
      });
      showToast("success", "RKT berhasil diajukan ke Kepala Sekolah.");
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
      showToast("success", "RKT berhasil dihapus.");
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

  const statusOptions = [
    { value: "", label: "Semua Status" },
    { value: "draft", label: "Draft" },
    { value: "diajukan", label: "Diajukan" },
    { value: "disetujui", label: "Disetujui" },
    { value: "ditolak", label: "Ditolak" },
    { value: "revisi", label: "Revisi" },
  ];

  const selectedStatusLabel =
    statusOptions.find((option) => option.value === statusFilter)?.label ||
    "Semua Status";

  const approvedData = data.filter((item) => getStatusInfo(item).value === "disetujui");

  const totalApprovedRkt = approvedData.length;

  const totalApprovedBudget = approvedData.reduce(
    (total, item) => total + Number(item.TOTAL_PROGKER || 0),
    0
  );

  return (
    <div className="rkt-shell">

      <main className="rkt-main">
        <div className="rkt-wrapper">
          <div className="rkt-header-card">
            <div>
              <h1 className="rkt-title">Rencana Kerja Tahunan</h1>
              <p className="rkt-subtitle">Kelola data program kerja PIC/Guru.</p>
            </div>

            <div className="rkt-header-actions">
              <button
                type="button"
                className="rkt-export-btn rkt-export-excel"
                onClick={handleExport}
              >
                <i className="bi bi-filetype-xlsx"></i>
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

          <div className="rkt-summary-grid">
            <div className="rkt-summary-card">
              <span className="rkt-summary-label">RKT Disetujui</span>
              <strong className="rkt-summary-value">{totalApprovedRkt}</strong>
            </div>

            <div className="rkt-summary-card">
              <span className="rkt-summary-label">Total Anggaran Disetujui</span>
              <strong className="rkt-summary-value">
                {formatRupiah(totalApprovedBudget)}
              </strong>
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
                  <div className="rkt-custom-select">
                    <button
                      type="button"
                      className={`rkt-custom-select-btn ${
                        !statusFilter ? "placeholder" : ""
                      }`}
                      onClick={() => setStatusDropdownOpen((prev) => !prev)}
                    >
                      {selectedStatusLabel}
                    </button>

                    {statusDropdownOpen && (
                      <div className="rkt-custom-select-menu">
                        {statusOptions.map((option) => (
                          <button
                            key={option.value || "all"}
                            type="button"
                            onClick={() => {
                              setStatusFilter(option.value);
                              setStatusDropdownOpen(false);
                              setPagination((current) => ({
                                ...current,
                                currentPage: 1,
                              }));
                            }}
                          >
                            {option.label}
                          </button>
                        ))}
                      </div>
                    )}
                  </div>
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
                          <th>Anggaran/Pagu</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        {paginatedData.length > 0 ? (
                          paginatedData.map((item, index) => {

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
                                  {formatRupiah(item.TOTAL_PROGKER)}
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
                      Menampilkan {showingStart} - {showingEnd} dari {totalFilteredData} data
                    </span>

                    <div className="rkt-pagination-actions">
                      <button
                        className="rkt-page-btn"
                        disabled={pagination.currentPage === 1}
                        onClick={() => handleChangePage(pagination.currentPage - 1)}
                      >
                        ‹
                      </button>

                      <span className="rkt-page-number">
                        {pagination.currentPage}
                      </span>

                      <button
                        className="rkt-page-btn"
                        disabled={pagination.currentPage === totalPages}
                        onClick={() => handleChangePage(pagination.currentPage + 1)}
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
                        <span className="rkt-detail-label">Nominal (Pagu)</span>
                        <span className="rkt-detail-value strong">
                          {formatRupiah(selectedItem.TOTAL_PROGKER)}
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

                      {/* <div className="rkt-detail-item">
                        <span className="rkt-detail-label">Unit</span>
                        <span className="rkt-detail-value">
                          {getDisplayValue(
                            selectedItem.unit?.NAMA_UNIT,
                            selectedItem.nama_unit
                          )}
                        </span>
                      </div> */}

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
                      <div className="rkt-note-title">
                        {selectedStatusValue === "draft"
                          ? "Catatan Draft"
                          : selectedStatusValue === "diajukan"
                          ? "Catatan Pengajuan"
                          : selectedStatusValue === "revisi"
                          ? "Catatan Revisi"
                          : selectedStatusValue === "ditolak"
                          ? "Catatan Penolakan"
                          : selectedStatusValue === "disetujui"
                          ? "Catatan Persetujuan"
                          : "Catatan Revisi / Review"}
                      </div>

                      <div className="rkt-note-content">
                        {getLatestReviewNote(selectedItem)}
                      </div>
                    </div>
                    {(selectedStatusValue === "draft" || selectedStatusValue === "revisi") && (
                      <div className="rkt-note-box">
                        <div className="rkt-note-title">Status RKA</div>
                        <div className="rkt-note-content">
                          {selectedRkaInfo.label}
                        </div>
                      </div>
                    )}
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
                          Menunggu Approval
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
                          title={!selectedRkaInfo.valid ? selectedRkaInfo.label : "Ajukan RKT"}
                          onClick={() => handleAjukan(selectedItem.ID_PROGRAM_KERJA)}
                        >
                          Ajukan
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
                  <i className="bi bi-clipboard-check rkt-detail-empty-icon"></i>
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

        {toast && (
          <div className={`toast-container ${visible ? "show" : "hide"}`}>
            <div className={`toast-box ${toast.type}`}>
              <span className="toast-text">{toast.message}</span>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}

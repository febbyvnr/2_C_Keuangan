import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import "../../styles/bendahara/RKA.css";
import { Plus, Pencil, Trash2 } from "lucide-react";

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

function getDetails(item) {
  return item?.details || [];
}

function getAnggaranRkt(item) {
  return Number(item?.rkt?.TOTAL_PROGKER || 0);
}

function getTotalRincian(item) {
  const details = getDetails(item);
  return details.reduce((total, detail) => {
    const nominal = Number(detail.NOMINAL) || 
                    (Number(detail.QTY || 0) * Number(detail.VOLUME || 0) * Number(detail.HARGA_SATUAN || 0));
    return total + nominal;
  }, 0);
}

function getStatusRka(item) {
  const anggaranRkt = getAnggaranRkt(item);
  const totalRincian = getTotalRincian(item);

  if (totalRincian === 0) {
    return {
      label: "Belum Ada Rincian",
      className: "rka-status-badge draft",
    };
  }

  if (totalRincian > anggaranRkt) {
    return {
      label: "Melebihi Anggaran",
      className: "rka-status-badge danger",
    };
  }

  return {
    label: "Sesuai Anggaran",
    className: "rka-status-badge ready",
  };
}

function getSisaAnggaran(item) {
  return getAnggaranRkt(item) - getTotalRincian(item);
}

export default function RKA() {
  const [data, setData] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [editingDetail, setEditingDetail] = useState(null);
  const [openStatus, setOpenStatus] = useState(false);

  const [sumberDanaList, setSumberDanaList] = useState([]);
  const [sumberDanaKeyword, setSumberDanaKeyword] = useState("");
  const [showSumberDanaDropdown, setShowSumberDanaDropdown] = useState(false);

  const [showDetailModal, setShowDetailModal] = useState(false);
  const [savingDetail, setSavingDetail] = useState(false);
  const [detailForm, setDetailForm] = useState({
    ID_REF_DANA: "",
    QTY: "",
    VOLUME: "",
    SATUAN: "",
    HARGA_SATUAN: "",
  });

  const selectedItem = useMemo(() => {
    return data.find((item) => item.ID_PROGRAM_KERJA === selectedId) || null;
  }, [data, selectedId]);

  const filteredData = useMemo(() => {
    const keyword = search.trim().toLowerCase();
    return data.filter((item) => {
            const status = getStatusRka(item).label;
            const matchSearch =
                !keyword ||
                String(item.rkt?.PROGRAM_KERJA || "").toLowerCase().includes(keyword) ||
                String(item.rkt?.INDIKATOR || "").toLowerCase().includes(keyword) ||
                String(item.rkt?.SASARAN || "").toLowerCase().includes(keyword);

            const matchStatus = !statusFilter || status === statusFilter;

            return matchSearch && matchStatus;
        });
  }, [data, search, statusFilter]);

  const filteredSumberDana = useMemo(() => {
    const keyword = sumberDanaKeyword.toLowerCase().trim();

    if (!keyword) return sumberDanaList;

    return sumberDanaList.filter((item) =>
        String(item.DESKRIPSI_SUMBER_DANA || "")
        .toLowerCase()
        .includes(keyword)
    );
  }, [sumberDanaList, sumberDanaKeyword]);

  const fetchRka = async () => {
    try {
      setLoading(true);
      const response = await axios.get("http://127.0.0.1:8000/api/rka");
      const rows = response.data?.data ?? [];
      console.log(rows);
      const formattedData = rows.map((item) => ({
        ...item,
        rkt: item,
        details: item.rka || [],
      }));
      setData(formattedData);
      if (formattedData.length > 0 && !selectedId) {
        setSelectedId(formattedData[0].ID_PROGRAM_KERJA);
      }
    } catch (error) {
      console.error("Gagal ambil data RKA:", error);
      setData([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchSumberDana = async () => {
    try {
        const response = await axios.get("http://127.0.0.1:8000/api/ref-sumber-dana");
        const rows = response.data?.data ?? response.data ?? [];
        setSumberDanaList(Array.isArray(rows) ? rows : []);
    } catch (error) {
        console.error("Gagal ambil sumber dana:", error);
        setSumberDanaList([]);
    }
  };

  useEffect(() => {
    fetchRka();
    fetchSumberDana();
  }, []);

  const handleReset = () => {
    setSearch("");
    setStatusFilter("");
    fetchRka();
  };

  const handleExportExcel = () => {
    window.open("http://127.0.0.1:8000/api/rka/export", "_blank");
  };

  const handleExportPdf = () => {
    window.open("http://127.0.0.1:8000/api/rka/export/pdf", "_blank");
  };

  const detailTotal =
  Number(detailForm.QTY || 0) *
  Number(detailForm.VOLUME || 0) *
  Number(detailForm.HARGA_SATUAN || 0);

const handleOpenDetailModal = () => {
  if (!selectedItem) {
    alert("Pilih program kerja terlebih dahulu.");
    return;
  }

  setEditingDetail(null);

  setDetailForm({
    ID_REF_DANA: "",
    QTY: "",
    VOLUME: "",
    SATUAN: "",
    HARGA_SATUAN: "",
  });

  setSumberDanaKeyword("");
  setShowSumberDanaDropdown(false);
  setShowDetailModal(true);
};

const handleEdit = (detail) => {
  setEditingDetail(detail.ID_DT_PROGKER);

  setDetailForm({
    ID_REF_DANA: detail.ID_REF_DANA || "",
    QTY: detail.QTY || "",
    VOLUME: detail.VOLUME || "",
    SATUAN: detail.SATUAN || "",
    HARGA_SATUAN: detail.HARGA_SATUAN || "",
  });

  const sumberDana = sumberDanaList.find(
    (item) => Number(item.ID_REF_DANA) === Number(detail.ID_REF_DANA)
  );

  setSumberDanaKeyword(
    sumberDana?.DESKRIPSI_SUMBER_DANA || detail.DESKRIPSI_SUMBER_DANA || ""
  );

  setShowSumberDanaDropdown(false);
  setShowDetailModal(true);
};

const handleDelete = async (idDetail) => {
  const confirmDelete = window.confirm(
    "Yakin mau menghapus rincian anggaran ini?"
  );

  if (!confirmDelete) return;

  try {
    await axios.delete(
      `http://127.0.0.1:8000/api/rka/delete/${idDetail}`
    );

    await fetchRka();
  } catch (error) {
    alert(error.response?.data?.message || "Gagal menghapus detail RKA");
  }
};

const handleDetailChange = (e) => {
  const { name, value } = e.target;
  setDetailForm((prev) => ({
    ...prev,
    [name]: value,
  }));
};

const handleSubmitDetail = async (e) => {
  e.preventDefault();

  if (!selectedItem) return;

  const totalSekarang = getTotalRincian(selectedItem);
  const anggaranRkt = getAnggaranRkt(selectedItem);

  const totalLama = editingDetail
    ? Number(
        getDetails(selectedItem).find(
          (detail) => detail.ID_DT_PROGKER === editingDetail
        )?.NOMINAL || 0
      )
    : 0;

  const totalSetelahUpdate = totalSekarang - totalLama + detailTotal;

  if (totalSetelahUpdate > anggaranRkt) {
    alert("Total rincian melebihi anggaran RKT.");
    return;
  }

  const payload = {
    ID_PROGRAM_KERJA: selectedItem.ID_PROGRAM_KERJA,
    ID_REF_DANA: detailForm.ID_REF_DANA || null,
    QTY: Number(detailForm.QTY),
    VOLUME: Number(detailForm.VOLUME),
    SATUAN: detailForm.SATUAN,
    HARGA_SATUAN: Number(detailForm.HARGA_SATUAN),
  };

  try {
    setSavingDetail(true);
    if (editingDetail) {
      await axios.put(`http://127.0.0.1:8000/api/rka/update/${editingDetail}`, payload);
    } else {
      await axios.post("http://127.0.0.1:8000/api/rka/store", payload);
    }
    setDetailForm({ ID_REF_DANA: "", QTY: "", VOLUME: "", SATUAN: "", HARGA_SATUAN: "" });
    setSumberDanaKeyword("");
    setShowDetailModal(false);
    await fetchRka();
  } catch (error) {
    alert(error.response?.data?.message || "Gagal menyimpan detail RKA");
  } finally {
    setSavingDetail(false);
  }
};

  return (
    <div className="rka-shell">
      <main className="rka-main">
        <div className="rka-wrapper">
          <div className="rka-header-card">
            <div>
              <h1 className="rka-title">Rencana Kegiatan dan Anggaran</h1>
              <p className="rka-subtitle">
                Kelola rincian anggaran dari RKT yang sudah disetujui.
              </p>
            </div>

           <div className="rka-header-actions">
              <div className="export-wrapper">
                <a
                  href="http://127.0.0.1:8000/api/rka/export"
                  className="btn btn-outline-success custom-btn"
                  target="_blank"
                  rel="noreferrer"
                >
                  <i className="bi bi-filetype-xlsx"></i>
                  Export Excel
                </a>

                <a
                  href="http://127.0.0.1:8000/api/rka/export/pdf"
                  className="btn btn-outline-danger custom-btn"
                  target="_blank"
                  rel="noreferrer"
                >
                  <i className="bi bi-file-earmark-pdf"></i>
                  Export PDF
                </a>
              </div>

              <button
                className="btn-primary-custom"
                type="button"
                onClick={handleOpenDetailModal}
              >
                <Plus size={16} />
                Tambah Detail RKA
              </button>
            </div>
          </div>

          <div className="rka-filter-card">
            <div className="rka-filter-row">
              <div className="rka-input-group rka-search-group">
                <label>Search</label>
                <input
                  type="text"
                  placeholder="Cari program kerja, indikator, atau sasaran..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>

              {/* <div className="rka-input-group rka-status-filter">
                <label>Status</label>
                <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                >
                    <option value="">Semua Status</option>
                    <option value="Belum Ada Rincian">Belum Ada Rincian</option>
                    <option value="Sesuai Anggaran">Sesuai Anggaran</option>
                    <option value="Melebihi Anggaran">Melebihi Anggaran</option>
                </select>
              </div> */}
              <div className="rka-input-group rka-status-filter">
                <label>Status</label>

                <div className="rka-custom-select">
                  <button
                    type="button"
                    className={`rka-custom-select-btn ${!statusFilter ? "placeholder" : ""}`}
                    onClick={() => setOpenStatus((prev) => !prev)}
                  >
                    {statusFilter || "Semua Status"}
                  </button>

                  {openStatus && (
                    <div className="rka-custom-select-menu">
                      {[
                        "",
                        "Belum Ada Rincian",
                        "Sesuai Anggaran",
                        "Melebihi Anggaran",
                      ].map((status) => (
                        <button
                          type="button"
                          key={status || "Semua Status"}
                          onClick={() => {
                            setStatusFilter(status);
                            setOpenStatus(false);
                          }}
                        >
                          {status || "Semua Status"}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              <div className="rka-filter-actions">
                {/* <button className="btn-primary-custom" type="button">
                  Cari
                </button> */}
                <button className="btn-light-custom" type="button" onClick={handleReset}>
                  Reset
                </button>
              </div>
            </div>
          </div>

          <div className="rka-content-section">
            <div className="rka-table-card">
              {loading ? (
                <div className="rka-empty">Loading data...</div>
              ) : (
                <div className="rka-table-wrapper">
                  <table className="rka-table">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Program Kerja</th>
                        <th>Sumber Dana</th>
                        <th>Total Rincian</th>
                        <th>Anggaran RKT</th>
                        <th>Selisih</th>
                        <th>Status</th>
                      </tr>
                    </thead>

                    <tbody>
                      {filteredData.length > 0 ? (
                        filteredData.map((item, index) => {
                          const status = getStatusRka(item);
                          return (
                            <tr
                              key={item.ID_PROGRAM_KERJA}
                              className={
                                selectedId === item.ID_PROGRAM_KERJA
                                  ? "rka-row-active"
                                  : ""
                              }
                              onClick={() => setSelectedId(item.ID_PROGRAM_KERJA)}
                            >
                              <td>{index + 1}</td>
                              <td className="rka-program">
                                {item.rkt?.PROGRAM_KERJA || "-"}
                              </td>
                              <td>
                                {getDetails(item).length > 0
                                  ? getDetails(item)
                                      .map(
                                        (d) =>
                                          d.DESKRIPSI_SUMBER_DANA ||
                                          d.ref_dana?.DESKRIPSI_SUMBER_DANA
                                      )
                                      .filter(Boolean)
                                      .join(", ")
                                  : "-"}
                              </td>
                              <td className="rka-amount">
                                {formatRupiah(getTotalRincian(item))}
                              </td>
                              <td className="rka-amount">
                                {formatRupiah(getAnggaranRkt(item))}
                              </td>
                              <td
                                className={
                                  getSisaAnggaran(item) < 0
                                    ? "rka-amount danger"
                                    : "rka-amount"
                                }
                              >
                                {formatRupiah(getSisaAnggaran(item))}
                              </td>
                              <td>
                                <span className={status.className}>
                                  {status.label}
                                </span>
                              </td>
                            </tr>
                          );
                        })
                      ) : (
                        <tr>
                          <td colSpan="7" className="rka-empty">
                            Tidak ada data RKA
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            <aside className="rka-detail-card">
              {selectedItem ? (
                <>
                  <div className="rka-detail-header">
                    <h2 className="rka-detail-title">
                      {selectedItem.rkt?.PROGRAM_KERJA || "Detail RKA"}
                    </h2>
                    <p className="rka-detail-subtitle">
                      Klik baris pada tabel untuk melihat detail RKA.
                    </p>

                    <span className={getStatusRka(selectedItem).className}>
                      {getStatusRka(selectedItem).label}
                    </span>
                  </div>

                  <div className="rka-detail-body">
                    <div className="rka-detail-grid">
                      <div className="rka-detail-item full">
                        <span className="rka-detail-label">Program Kerja</span>
                        <span className="rka-detail-value">
                          {selectedItem.rkt?.PROGRAM_KERJA || "-"}
                        </span>
                      </div>
                      <div className="rka-detail-item full">
                        <span className="rka-detail-label">Indikator</span>
                        <span className="rka-detail-value">
                          {selectedItem.rkt?.INDIKATOR || "-"}
                        </span>
                      </div>
                      <div className="rka-detail-item full">
                        <span className="rka-detail-label">Sasaran</span>
                        <span className="rka-detail-value">
                          {selectedItem.rkt?.SASARAN || "-"}
                        </span>
                      </div>
                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Anggaran RKT</span>
                        <span className="rka-detail-value strong">
                          {formatRupiah(getAnggaranRkt(selectedItem))}
                        </span>
                      </div>
                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Total Rincian</span>
                        <span className="rka-detail-value strong">
                          {formatRupiah(getTotalRincian(selectedItem))}
                        </span>
                      </div>
                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Sisa Anggaran</span>
                        <span
                          className={
                            getSisaAnggaran(selectedItem) < 0
                              ? "rka-detail-value strong danger"
                              : "rka-detail-value strong"
                          }
                        >
                          {formatRupiah(getSisaAnggaran(selectedItem))}
                        </span>
                      </div>
                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Jumlah Detail</span>
                        <span className="rka-detail-value">
                          {getDetails(selectedItem).length} item
                        </span>
                      </div>
                    </div>

                    <div className="rka-detail-list">
                      <div className="rka-detail-list-title">Rincian Anggaran</div>

                      {getDetails(selectedItem).map((detail, index) => {
                        const qty = Number(detail.QTY || 0);
                        const volume = Number(detail.VOLUME || 1);
                        const satuan = detail.SATUAN || "Item Rincian";
                        const total = Number(detail.NOMINAL || 0);

                        return (
                          <div className="rka-detail-row" key={detail.ID_DT_PROGKER}>
                            <span className="rka-detail-text">
                              {index + 1}. {qty} {satuan} × {volume} ×{" "}
                              {formatRupiah(detail.HARGA_SATUAN)}
                            </span>

                            <div className="rka-detail-action-icons">
                              <button
                                type="button"
                                className="rka-icon-btn edit"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  handleEdit(detail);
                                }}
                                title="Edit rincian"
                              >
                                <Pencil size={15} />
                              </button>

                              <button
                                type="button"
                                className="rka-icon-btn delete"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  handleDelete(detail.ID_DT_PROGKER);
                                }}
                                title="Hapus rincian"
                              >
                                <Trash2 size={15} />
                              </button>
                            </div>

                            <b className="rka-detail-total">{formatRupiah(total)}</b>
                          </div>
                        );
                      })}
                    </div>
                  </div>

                  <div className="rka-detail-actions">
                    <button className="btn-primary-custom rka-detail-btn" onClick={handleOpenDetailModal}>
                      Tambah Detail
                    </button>
                  </div>
                </>
              ) : (
                <div className="rka-detail-empty-state">
                  <div className="rka-detail-empty-icon">
                    <i className="bi bi-receipt"></i>
                  </div>
                  <p>Klik baris pada tabel untuk melihat detail RKA.</p>
                </div>
              )}
            </aside>
          </div>
        </div>

        {showDetailModal && (
        <div className="rka-modal-overlay">
            <div className="rka-modal-box">
            <div className="rka-modal-header">
                <div>
                  <h3>
                    {editingDetail ? "Edit Detail RKA" : "Tambah Detail RKA"}
                  </h3>
                  <p>{selectedItem?.rkt?.PROGRAM_KERJA}</p>
                  <p>
                      Anggaran RKT: {formatRupiah(getAnggaranRkt(selectedItem))} • Total saat ini:{" "}
                      {formatRupiah(getTotalRincian(selectedItem))}
                  </p>
                </div>

                <button
                type="button"
                className="rka-modal-close"
                onClick={() => setShowDetailModal(false)}
                disabled={savingDetail}
                >
                x
                </button>
            </div>

            <form className="rka-modal-form" onSubmit={handleSubmitDetail}>
                <label className="rka-sumber-dana-field">
                <span>Sumber Dana</span>

                <input
                  type="text"
                  value={detailForm.ID_REF_DANA ? sumberDanaKeyword : ""}
                  onChange={(e) => {
                    setSumberDanaKeyword(e.target.value);
                    setShowSumberDanaDropdown(true);
                    setDetailForm((prev) => ({
                      ...prev,
                      ID_REF_DANA: "",
                    }));
                  }}
                  onFocus={() => setShowSumberDanaDropdown(true)}
                  placeholder="Cari sumber dana..."
                  required
                />

                {showSumberDanaDropdown && (
                    <div className="rka-sumber-dana-dropdown">
                    {filteredSumberDana.length > 0 ? (
                        filteredSumberDana.map((item) => (
                        <button
                            type="button"
                            key={item.ID_REF_DANA}
                            onClick={() => {
                            setDetailForm((prev) => ({
                                ...prev,
                                ID_REF_DANA: item.ID_REF_DANA,
                            }));
                            setSumberDanaKeyword(item.DESKRIPSI_SUMBER_DANA);
                            setShowSumberDanaDropdown(false);
                            }}
                        >
                            {item.DESKRIPSI_SUMBER_DANA}
                        </button>
                        ))
                    ) : (
                        <div className="rka-sumber-dana-empty">
                        Sumber dana tidak ditemukan
                        </div>
                    )}
                    </div>
                )}
                </label>

                <label>
                <span>Qty</span>
                <input
                    type="number"
                    name="QTY"
                    min="1"
                    value={detailForm.QTY}
                    onChange={handleDetailChange}
                    required
                />
                </label>

                <label>
                <span>Volume</span>
                <input
                    type="number"
                    name="VOLUME"
                    min="1"
                    value={detailForm.VOLUME}
                    onChange={handleDetailChange}
                    required
                />
                </label>

                <label>
                <span>Satuan</span>
                <input
                    type="text"
                    name="SATUAN"
                    value={detailForm.SATUAN}
                    onChange={handleDetailChange}
                    placeholder="contoh: box, pcs, paket"
                    required
                />
                </label>

                <label className="full">
                <span>Harga Satuan</span>
                <input
                    type="number"
                    name="HARGA_SATUAN"
                    min="0"
                    value={detailForm.HARGA_SATUAN}
                    onChange={handleDetailChange}
                    required
                />
                </label>

                <div className="rka-modal-total">
                <span>Total Rincian</span>
                <strong>{formatRupiah(detailTotal)}</strong>
                </div>

                <div className="rka-modal-actions">
                <button
                    type="button"
                    className="btn-light-custom"
                    onClick={() => setShowDetailModal(false)}
                    disabled={savingDetail}
                >
                    Batal
                </button>

                <button
                    type="submit"
                    className="btn-primary-custom"
                    disabled={savingDetail}
                >
                    {savingDetail ? "Menyimpan..." : "Simpan Detail"}
                </button>
                </div>
            </form>
            </div>
        </div>
        )}
      </main>
    </div>
  );
}

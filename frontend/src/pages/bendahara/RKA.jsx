import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import "../../styles/bendahara/RKA.css";
import {Plus, FileSpreadsheet, FileText } from "lucide-react";

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
  return item?.details || item?.detail_program_kerja || item?.detailProgramKerja || [];
}

function getTotalRincian(item) {
  const details = getDetails(item);

  return details.reduce((total, detail) => {
    return total + Number(detail.TOTAL_PROGKER || detail.NOMINAL || 0);
  }, 0);
}

function getStatusRka(item) {
  const anggaranRkt = Number(item?.NOMINAL || 0);
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
  return Number(item?.NOMINAL || 0) - getTotalRincian(item);
}

export default function RKA() {
  const [data, setData] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

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
                String(item.PROGRAM_KERJA || "").toLowerCase().includes(keyword) ||
                String(item.INDIKATOR || "").toLowerCase().includes(keyword) ||
                String(item.SASARAN || "").toLowerCase().includes(keyword);

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

      setData(Array.isArray(rows) ? rows : []);

      setSelectedId((prev) => {
        if (!rows.length) return null;
        if (prev && rows.some((item) => item.ID_PROGRAM_KERJA === prev)) {
          return prev;
        }
        return rows[0].ID_PROGRAM_KERJA;
      });
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
  const anggaranRkt = Number(selectedItem.NOMINAL || 0);

  if (totalSekarang + detailTotal > anggaranRkt) {
    alert("Total rincian melebihi anggaran RKT.");
    return;
  }

  try {
    setSavingDetail(true);

    await axios.post("http://127.0.0.1:8000/api/rka/store", {
      ID_PROGRAM_KERJA: selectedItem.ID_PROGRAM_KERJA,
      ID_REF_DANA: detailForm.ID_REF_DANA || null,
      QTY: Number(detailForm.QTY),
      VOLUME: Number(detailForm.VOLUME),
      SATUAN: detailForm.SATUAN,
      HARGA_SATUAN: Number(detailForm.HARGA_SATUAN),
      NOMINAL: detailTotal,
      TOTAL_PROGKER: detailTotal,
    });

    setShowDetailModal(false);
    await fetchRka();
  } catch (error) {
    alert(error.response?.data?.message || "Gagal menambah detail RKA");
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
              <button className="btn-success-custom" onClick={handleExportExcel}>
                <FileSpreadsheet size={16} />
                    Export Excel
              </button>

              <button className="btn-warning-custom" onClick={handleExportPdf}>
                <FileText size={16} />
                    Export PDF
              </button>

              <button className="btn-primary-custom" type="button" onClick={handleOpenDetailModal}>
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

              <div className="rka-input-group rka-status-filter">
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
                        <th>Waktu</th>
                        <th>Anggaran RKT</th>
                        <th>Total Rincian</th>
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
                                {item.PROGRAM_KERJA || "-"}
                              </td>

                              <td>
                                <div>{formatDate(item.WAKTU_AWAL)}</div>
                                <div className="rka-date-sub">
                                  s.d {formatDate(item.WAKTU_AKHIR)}
                                </div>
                              </td>

                              <td className="rka-amount">
                                {formatRupiah(item.NOMINAL)}
                              </td>

                              <td className="rka-amount">
                                {formatRupiah(getTotalRincian(item))}
                              </td>

                              <td className={getSisaAnggaran(item) < 0 ? "rka-amount danger" : "rka-amount"}>
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
                      {selectedItem.PROGRAM_KERJA || "Detail RKA"}
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
                        <span className="rka-detail-label">Indikator</span>
                        <span className="rka-detail-value">
                          {selectedItem.INDIKATOR || "-"}
                        </span>
                      </div>

                      <div className="rka-detail-item full">
                        <span className="rka-detail-label">Sasaran</span>
                        <span className="rka-detail-value">
                          {selectedItem.SASARAN || "-"}
                        </span>
                      </div>

                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Anggaran RKT</span>
                        <span className="rka-detail-value strong">
                          {formatRupiah(selectedItem.NOMINAL)}
                        </span>
                      </div>

                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Total Rincian</span>
                        <span className="rka-detail-value strong">
                          {formatRupiah(getTotalRincian(selectedItem))}
                        </span>
                      </div>

                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Selisih Anggaran</span>
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

                      <div className="rka-detail-item">
                        <span className="rka-detail-label">Validator</span>
                        <span className="rka-detail-value">
                          {selectedItem.NIP_VALIDATOR_PROGKER
                            ? `${selectedItem.NIP_VALIDATOR_PROGKER} - ${
                                selectedItem.NAMA_VALIDATOR || "Validator"
                              }`
                            : "Belum ada validator"}
                        </span>
                      </div>
                    </div>

                    <div className="rka-detail-list">
                      <div className="rka-detail-list-title">Rincian Anggaran</div>

                      {getDetails(selectedItem).length > 0 ? (
                        getDetails(selectedItem).map((detail) => (
                          <div
                            className="rka-detail-row"
                            key={detail.ID_DT_PROGKER}
                          >
                            <div>
                              <strong>
                                {detail.SATUAN || "Item Rincian"}
                              </strong>
                              <span>
                                Qty {detail.QTY || 0} × Volume{" "}
                                {detail.VOLUME || 1}
                              </span>
                            </div>

                            <b>
                              {formatRupiah(
                                detail.TOTAL_PROGKER || detail.NOMINAL
                              )}
                            </b>
                          </div>
                        ))
                      ) : (
                        <div className="rka-detail-empty">
                          Belum ada rincian anggaran.
                        </div>
                      )}
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
                  <div className="rka-detail-empty-icon">💰</div>
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
                <h3>Tambah Detail RKA</h3>
                <p>{selectedItem?.PROGRAM_KERJA}</p>
                <p>
                    Anggaran RKT: {formatRupiah(selectedItem?.NOMINAL)} • Total saat ini:{" "}
                    {formatRupiah(getTotalRincian(selectedItem))}
                </p>
                </div>

                <button
                type="button"
                className="rka-modal-close"
                onClick={() => setShowDetailModal(false)}
                disabled={savingDetail}
                >
                ×
                </button>
            </div>

            <form className="rka-modal-form" onSubmit={handleSubmitDetail}>
                <label className="rka-sumber-dana-field">
                <span>Sumber Dana</span>

                <input
                    type="text"
                    value={sumberDanaKeyword}
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
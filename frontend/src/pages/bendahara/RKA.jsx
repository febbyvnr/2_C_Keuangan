import { useEffect, useMemo, useState } from "react";
import axios from "axios";
import SidebarBendahara from "../../components/SidebarBendahara";
import "../../styles/bendahara/SidebarBendahara.css";
import "../../styles/bendahara/RKA.css";
import { Download, Plus } from "lucide-react";

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
  const details = getDetails(item);

  if (!details.length) {
    return {
      label: "Belum Ada Rincian",
      className: "rka-status-badge draft",
    };
  }

  return {
    label: "Sudah Ada Rincian",
    className: "rka-status-badge ready",
  };
}

export default function RKA() {
  const [data, setData] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");

  const selectedItem = useMemo(() => {
    return data.find((item) => item.ID_PROGRAM_KERJA === selectedId) || null;
  }, [data, selectedId]);

  const filteredData = useMemo(() => {
    const keyword = search.trim().toLowerCase();

    if (!keyword) return data;

    return data.filter((item) => {
      return (
        String(item.PROGRAM_KERJA || "").toLowerCase().includes(keyword) ||
        String(item.INDIKATOR || "").toLowerCase().includes(keyword) ||
        String(item.SASARAN || "").toLowerCase().includes(keyword)
      );
    });
  }, [data, search]);

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

  useEffect(() => {
    fetchRka();
  }, []);

  const handleReset = () => {
    setSearch("");
    fetchRka();
  };

  const handleExport = () => {
    window.open("http://127.0.0.1:8000/api/rka/export/pdf", "_blank");
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
              <button className="btn-warning-custom" onClick={handleExport}>
                <Download size={16} />
                Export PDF
              </button>

              <button className="btn-primary-custom" type="button">
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

              <div className="rka-filter-actions">
                <button className="btn-primary-custom" type="button">
                  Cari
                </button>
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
                          <td colSpan="6" className="rka-empty">
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
                    <button className="btn-primary-custom rka-detail-btn">
                      Tambah Detail
                    </button>

                    <button className="btn-light-custom rka-detail-btn">
                      Lihat Detail
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
      </main>
    </div>
  );
}
import { useEffect, useState } from "react";
import axios from "axios";
import "../../../styles/pic/guru/RKT.css";

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

export default function RKT() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");
  const [tahunAnggaran, setTahunAnggaran] = useState("");
  const [tan, setTan] = useState("");

  const fetchRkt = async (customParams = {}) => {
    try {
      setLoading(true);

      const response = await axios.get("http://127.0.0.1:8000/api/rkt", {
        params: {
          search,
          ID_TA_ANGGARAN: tahunAnggaran,
          ID_TAN: tan,
          per_page: 10,
          ...customParams,
        },
      });

      setData(response.data?.data?.data || []);
    } catch (error) {
      console.error("Gagal ambil data RKT:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRkt();
  }, []);

  const handleSearch = () => {
    fetchRkt();
  };

  const handleReset = () => {
    setSearch("");
    setTahunAnggaran("");
    setTan("");

    fetchRkt({
      search: "",
      ID_TA_ANGGARAN: "",
      ID_TAN: "",
    });
  };

  const handleDelete = async (id) => {
    const confirmDelete = window.confirm("Yakin ingin menghapus data ini?");
    if (!confirmDelete) return;

    try {
      await axios.delete(`http://127.0.0.1:8000/api/rkt/delete/${id}`);
      fetchRkt();
    } catch (error) {
      console.error("Gagal hapus data:", error);
      alert("Data gagal dihapus");
    }
  };

  const handleExport = () => {
    const params = new URLSearchParams({
      search,
      ID_TA_ANGGARAN: tahunAnggaran,
      ID_TAN: tan,
    });

    window.open(
      `http://127.0.0.1:8000/api/rkt/export/excel?${params.toString()}`,
      "_blank"
    );
  };

  return (
    <div className="rkt-page">
      <div className="rkt-card">
        <div className="rkt-header">
          <div>
            <h1 className="rkt-title">Rencana Kerja Tahunan</h1>
            <p className="rkt-subtitle">
              Kelola data program kerja PIC/Guru dengan mudah.
            </p>
          </div>

          <div className="rkt-header-actions">
            <button className="btn-secondary" onClick={() => fetchRkt()}>
              Refresh
            </button>
            <button className="btn-tertiary" onClick={handleExport}>
              Export Excel
            </button>
            <button className="btn-primary">
              Tambah RKT
            </button>
          </div>
        </div>

        <div className="rkt-filter-card">
          <div className="rkt-filter-grid">
            <div className="rkt-field">
              <label>Search</label>
              <input
                type="text"
                placeholder="Cari program kerja, indikator, sasaran..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === "Enter") handleSearch();
                }}
              />
            </div>

            <div className="rkt-field">
              <label>Tahun Anggaran</label>
              <input
                type="text"
                placeholder="ID Tahun Anggaran"
                value={tahunAnggaran}
                onChange={(e) => setTahunAnggaran(e.target.value)}
              />
            </div>

            <div className="rkt-field">
              <label>TAN</label>
              <input
                type="text"
                placeholder="ID TAN"
                value={tan}
                onChange={(e) => setTan(e.target.value)}
              />
            </div>

            <div className="rkt-filter-actions">
              <button className="btn-primary" onClick={handleSearch}>
                Cari
              </button>
              <button className="btn-secondary" onClick={handleReset}>
                Reset
              </button>
            </div>
          </div>
        </div>

        <div className="rkt-table-card">
          {loading ? (
            <div className="rkt-empty">Loading data...</div>
          ) : (
            <div className="rkt-table-wrapper">
              <table className="rkt-table">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Program Kerja</th>
                    <th>Indikator</th>
                    <th>Sasaran</th>
                    <th>Waktu</th>
                    <th>Anggaran</th>
                    <th>Penanggung Jawab</th>
                    <th>Validator</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {data.length > 0 ? (
                    data.map((item, index) => (
                      <tr key={item.ID_PROGRAM_KERJA}>
                        <td>{index + 1}</td>
                        <td className="program-col">{item.PROGRAM_KERJA || "-"}</td>
                        <td>{item.INDIKATOR || "-"}</td>
                        <td>{item.SASARAN || "-"}</td>
                        <td>
                          <div>{formatDate(item.WAKTU_AWAL)}</div>
                          <div className="rkt-date-muted">
                            s/d {formatDate(item.WAKTU_AKHIR)}
                          </div>
                        </td>
                        <td className="anggaran-col">{formatRupiah(item.NOMINAL)}</td>
                        <td>{item.NIP_PENANGGUNG_JAWAB || "-"}</td>
                        <td>{item.NIP_VALIDATOR_PROGKER || "-"}</td>
                        <td>
                          <div className="rkt-action-group">
                            <button className="btn-table-detail">Detail</button>
                            <button className="btn-table-edit">Edit</button>
                            <button
                              className="btn-table-delete"
                              onClick={() => handleDelete(item.ID_PROGRAM_KERJA)}
                            >
                              Hapus
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan="9" className="rkt-empty">
                        Tidak ada data RKT
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
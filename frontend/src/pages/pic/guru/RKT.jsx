import { useEffect, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import SidebarPic from "../../../components/SidebarPic";
import "../../../styles/pic/guru/SidebarPic.css";
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
  const navigate = useNavigate();

  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState("");

  const fetchRkt = async (customSearch = search) => {
    try {
      setLoading(true);

      const response = await axios.get("http://127.0.0.1:8000/api/rkt", {
        params: {
          search: customSearch,
          per_page: 10,
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
    window.scrollTo(0, 0);
    fetchRkt("");
  }, []);

  const handleSearch = () => {
    fetchRkt(search);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleReset = () => {
    setSearch("");
    fetchRkt("");
    window.scrollTo({ top: 0, behavior: "smooth" });
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
    const params = new URLSearchParams({ search });
    window.open(
      `http://127.0.0.1:8000/api/rkt/export/excel?${params.toString()}`,
      "_blank"
    );
  };

  return (
    <div className="rkt-shell">
      <SidebarPic />

      <main className="rkt-main">
        <div className="rkt-wrapper">
          <div className="rkt-header-card">
            <div>
              <h1 className="rkt-title">Rencana Kerja Tahunan</h1>
              <p className="rkt-subtitle">
                Kelola data program kerja PIC/Guru.
              </p>
            </div>

            <div className="rkt-header-actions">
              <button className="btn-light-custom" onClick={() => fetchRkt()}>
                Refresh
              </button>
              <button className="btn-warning-custom" onClick={handleExport}>
                Export Excel
              </button>
              <button
                className="btn-primary-custom"
                onClick={() => navigate("/pic/guru/rkt/create")}
              >
                Tambah RKT
              </button>
            </div>
          </div>

          <div className="rkt-filter-card">
            <div className="rkt-filter-row">
              <div className="rkt-input-group">
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
                      <th>Waktu</th>
                      <th>Anggaran</th>
                      <th>Validator</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {data.length > 0 ? (
                      data.map((item, index) => (
                        <tr key={item.ID_PROGRAM_KERJA}>
                          <td>{index + 1}</td>
                          <td className="rkt-program">
                            {item.PROGRAM_KERJA || "-"}
                          </td>
                          <td>{item.INDIKATOR || "-"}</td>
                          <td>
                            <div>{formatDate(item.WAKTU_AWAL)}</div>
                            <div className="rkt-date-sub">
                              s/d {formatDate(item.WAKTU_AKHIR)}
                            </div>
                          </td>
                          <td className="rkt-amount">
                            {formatRupiah(item.NOMINAL)}
                          </td>
                          <td>{item.NIP_VALIDATOR_PROGKER || "-"}</td>
                          <td>
                            <div className="rkt-action-buttons">
                              <button
                                className="btn-dark-sm"
                                onClick={() =>
                                  navigate(
                                    `/pic/guru/rkt/detail/${item.ID_PROGRAM_KERJA}`
                                  )
                                }
                              >
                                Detail
                              </button>
                              <button
                                className="btn-yellow-sm"
                                onClick={() =>
                                  navigate(
                                    `/pic/guru/rkt/edit/${item.ID_PROGRAM_KERJA}`
                                  )
                                }
                              >
                                Edit
                              </button>
                              <button
                                className="btn-red-sm"
                                onClick={() =>
                                  handleDelete(item.ID_PROGRAM_KERJA)
                                }
                              >
                                Hapus
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="7" className="rkt-empty">
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
      </main>
    </div>
  );
}
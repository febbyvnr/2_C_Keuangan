import { useState, useEffect } from "react";
import "../../styles/kepsek/Laporan.css"; 
import "bootstrap-icons/font/bootstrap-icons.css";

export default function LaporanKepsek() {
  const tabs = ["Penerimaan", "Pengeluaran", "RKAS", "BKU"];
  const [active, setActive] = useState("Penerimaan");
  const [data, setData] = useState([]);
  const [total, setTotal] = useState(0);

  const [start, setStart] = useState("");
  const [end, setEnd] = useState("");
  const [sumberDana, setSumberDana] = useState("");
  const [tahun, setTahun] = useState("2026");
  const [programKerja, setProgramKerja] = useState("");
  const [page, setPage] = useState(1);
  const perPage = 10;
  const [bkuTab, setBkuTab] = useState("bku");

  const loadData = () => {
    let baseUrl = "";
    if (active === "Penerimaan") baseUrl = "http://localhost:8000/api/laporan/penerimaan";
    else if (active === "Pengeluaran") baseUrl = "http://localhost:8000/api/laporan/pengeluaran";
    else if (active === "BKU") baseUrl = "http://localhost:8000/api/laporan/bku";
    else if (active === "RKAS") baseUrl = "http://localhost:8000/api/laporan/rkas"; // Sesuai logika bendahara

    const params = new URLSearchParams();
    const user = JSON.parse(localStorage.getItem("user"));
    if (user?.NIP_KARYAWAN) params.append("nip", user.NIP_KARYAWAN);
    
    if (start) params.append("start", start);
    if (end) params.append("end", end);

    // Logika Filter sesuai Bendahara
    if (active === "Pengeluaran") {
      if (programKerja) params.append("program_kerja", programKerja);
    } else {
      if (sumberDana) params.append("sumber_dana", sumberDana);
    }
    // Selalu kirim tahun untuk Kepsek
    params.append("tahun", tahun);

    fetch(`${baseUrl}?${params.toString()}`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    })
      .then((res) => {
        if (!res.ok) throw new Error("Unauthorized");
        return res.json();
      })
      .then((res) => {
        let selectedData = res.data || [];
        let computedTotal = res.total || 0;

        if (active === "BKU") {
          if (bkuTab === "bku") selectedData = res.bku || [];
          else if (bkuTab === "p1") selectedData = res.tunai || [];
          else if (bkuTab === "p2") selectedData = res.bank || [];
          computedTotal = selectedData.reduce((acc, item) => acc + (item.saldo ?? 0), 0);
        }

        setData(selectedData);
        setTotal(computedTotal);
        setPage(1);
      })
      .catch((err) => {
        console.error("Fetch Error:", err);
        setData([]);
      });
  };

  // LOGIKA EXCEL (Sesuai Bendahara)
  const handleExportExcel = () => {
    let baseUrl = `http://localhost:8000/api/laporan/${active.toLowerCase()}`;
    if (active === "RKAS") baseUrl = "http://localhost:8000/api/laporan/rkas/export";

    const params = new URLSearchParams({
      start, end, sumber_dana: sumberDana, tahun, type: "excel"
    });
    const user = JSON.parse(localStorage.getItem("user"));
    if (user?.NIP_KARYAWAN) params.append("nip", user.NIP_KARYAWAN);
    if (active === "BKU") params.append("jenis", bkuTab);

    window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  // LOGIKA PDF (Sesuai Bendahara)
  const handleExportPDF = () => {
    let baseUrl = `http://localhost:8000/api/laporan/${active.toLowerCase()}`;
    if (active === "RKAS") baseUrl = "http://localhost:8000/api/laporan/rkas/export-pdf";

    const params = new URLSearchParams({
      start, end, sumber_dana: sumberDana, tahun, type: "pdf"
    });
    const user = JSON.parse(localStorage.getItem("user"));
    if (user?.NIP_KARYAWAN) params.append("nip", user.NIP_KARYAWAN);
    if (active === "BKU") params.append("jenis", bkuTab);

    window.open(`${baseUrl}?${params.toString()}`, "_blank");
  };

  useEffect(() => { loadData(); }, [active, tahun]);
  useEffect(() => { if (active === "BKU") loadData(); }, [bkuTab]);

  const startIndex = (page - 1) * perPage;
  const currentData = data.slice(startIndex, startIndex + perPage);
  const totalPage = Math.ceil(data.length / perPage) || 1;

  return (
    <div style={{ padding: "30px" }}>
      <h2>Laporan</h2>
      <div className="laporan-tabs">
        {tabs.map((tab) => (
          <div key={tab} className={`laporan-tab ${active === tab ? "active" : ""}`} onClick={() => setActive(tab)}>
            {tab}
          </div>
        ))}
      </div>

      <div className="laporan-header">
        <div className="laporan-actions">
          <button className="btn-outline excel" onClick={handleExportExcel}><i className="bi bi-file-earmark-excel"></i> Export Excel</button>
          <button className="btn-outline pdf" onClick={handleExportPDF}><i className="bi bi-file-earmark-pdf"></i> Export PDF</button>
        </div>

        <div className="laporan-filter">
          {active !== "RKAS" && (
            <>
              <input type="date" value={start} onChange={(e) => setStart(e.target.value)} />
              <input type="date" value={end} onChange={(e) => setEnd(e.target.value)} />
            </>
          )}
          <select value={sumberDana} onChange={(e) => setSumberDana(e.target.value)}>
            <option value="">Semua Dana</option>
            <option value="1">Dana Pemerintah</option>
            <option value="2">Dana Komite</option>
          </select>
          <button className="btn btn-primary" onClick={loadData}>Filter</button>
        </div>
      </div>

      {active === "BKU" && (
        <div className="bku-tab-container" style={{ marginBottom: "15px" }}>
          {[{ label: "BKU", value: "bku" }, { label: "Tunai", value: "p1" }, { label: "Bank", value: "p2" }].map((t) => (
            <button key={t.value} className={`bku-tab ${bkuTab === t.value ? "active" : ""}`} onClick={() => setBkuTab(t.value)}>{t.label}</button>
          ))}
        </div>
      )}

      <div className="laporan-table">
        <table>
          <thead>
            {active === "RKAS" ? (
              <tr><th>No</th><th>Program</th><th>Sumber Dana</th><th>Anggaran</th></tr>
            ) : active === "BKU" ? (
              <tr><th>No</th><th>Tanggal</th><th>Uraian</th><th>Debit</th><th>Kredit</th><th>Saldo</th></tr>
            ) : (
              <tr><th>No</th><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr>
            )}
          </thead>
          <tbody>
            {currentData.length > 0 ? (
              currentData.map((item, i) => (
                <tr key={i}>
                  <td>{startIndex + i + 1}</td>
                  {active === "RKAS" ? (
                    <>
                      <td>{item.program_kerja || item.program}</td>
                      <td>{item.sumber_dana}</td>
                      <td className="nominal">Rp {Number(item.anggaran_disetujui ?? 0).toLocaleString("id-ID")}</td>
                    </>
                  ) : active === "BKU" ? (
                    <>
                      <td>{new Date(item.tanggal).toLocaleDateString("id-ID")}</td>
                      <td>{item.uraian}</td>
                      <td className="nominal">Rp {Number(item.debit ?? 0).toLocaleString("id-ID")}</td>
                      <td className="nominal">Rp {Number(item.kredit ?? 0).toLocaleString("id-ID")}</td>
                      <td className="nominal">Rp {Number(item.saldo ?? 0).toLocaleString("id-ID")}</td>
                    </>
                  ) : (
                    <>
                      <td>{new Date(item.tanggal).toLocaleDateString("id-ID")}</td>
                      <td>{item.jenis || item.program}</td>
                      <td>{item.uraian}</td>
                      <td className="nominal">Rp {Number(item.jumlah || item.nominal || 0).toLocaleString("id-ID")}</td>
                    </>
                  )}
                </tr>
              ))
            ) : (
              <tr><td colSpan="6" style={{ textAlign: "center" }}>Tidak ada data</td></tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="laporan-footer">
        <div className="laporan-info">Menampilkan {startIndex + 1} - {Math.min(startIndex + perPage, data.length)} dari {data.length} data</div>
        <div className="laporan-pagination">
          <button className="page-btn arrow" onClick={() => setPage(page - 1)} disabled={page === 1}>‹</button>
          {[...Array(totalPage)].map((_, i) => (
            <button key={i} className={`page-btn ${page === i + 1 ? "active" : ""}`} onClick={() => setPage(i + 1)}>{i + 1}</button>
          ))}
          <button className="page-btn arrow" onClick={() => setPage(page + 1)} disabled={page === totalPage}>›</button>
        </div>
        <div className="laporan-total-card"><span>Total</span><strong>Rp {total.toLocaleString("id-ID")}</strong></div>
      </div>
    </div>
  );
}
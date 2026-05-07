import { useEffect, useState } from "react";
import "./PenjaminanMutu.css";

export default function PenjaminanMutu() {
  const [evaluasi, setEvaluasi] = useState([]);
  const [trpm, setTrpm] = useState([]);

  useEffect(() => {
    fetch("http://localhost:8000/api/evaluasi-rkt")
      .then((res) => res.json())
      .then((data) => setEvaluasi(data.data || []));

    fetch("http://localhost:8000/api/tr-pm")
      .then((res) => res.json())
      .then((data) => setTrpm(data.data || []));
  }, []);

  const totalEvaluasi = evaluasi.length;

  const sesuai = trpm.filter((d) => d.TINGKAT_KESESUAIAN === "Sesuai").length;

  const kurang = trpm.filter(
    (d) => d.TINGKAT_KESESUAIAN === "Kurang Sesuai",
  ).length;

  const tidak = trpm.filter(
    (d) => d.TINGKAT_KESESUAIAN === "Tidak Sesuai",
  ).length;

  return (
    <div className="pm-container">
      <h2 className="pm-title">Dashboard Penjaminan Mutu</h2>

      {/* KPI */}
      <div className="pm-kpi">
        <div className="card">
          <p>Total Evaluasi</p>
          <h3>{totalEvaluasi}</h3>
        </div>

        <div className="card success">
          <p>Sesuai</p>
          <h3>{sesuai}</h3>
        </div>

        <div className="card warning">
          <p>Kurang Sesuai</p>
          <h3>{kurang}</h3>
        </div>

        <div className="card danger">
          <p>Tidak Sesuai</p>
          <h3>{tidak}</h3>
        </div>
      </div>

      {/* TABLE EVALUASI */}
      <div className="pm-table">
        <h3>Evaluasi RKT</h3>
        <table>
          <thead>
            <tr>
              <th>Kegiatan</th>
              <th>Tanggal</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            {evaluasi.map((item, index) => (
              <tr key={index}>
                <td>{item.programKerja?.NAMA_PROGRAM || "-"}</td>
                <td>{item.TGL_PM}</td>
                <td>{item.DESKRIPSI_TR_PM}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* TABLE MONITORING PM */}
      <div className="pm-table">
        <h3>Monitoring Mutu</h3>
        <table>
          <thead>
            <tr>
              <th>Deskripsi</th>
              <th>Tanggal</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {trpm.map((item, index) => (
              <tr key={index}>
                <td>{item.DESKRIPSI_TR_PM}</td>
                <td>{item.TGL_PM}</td>
                <td
                  className={
                    item.TINGKAT_KESESUAIAN === "Sesuai"
                      ? "text-success"
                      : item.TINGKAT_KESESUAIAN === "Kurang Sesuai"
                        ? "text-warning"
                        : "text-danger"
                  }
                >
                  {item.TINGKAT_KESESUAIAN}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

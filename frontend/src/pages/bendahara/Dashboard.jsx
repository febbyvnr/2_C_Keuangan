import { useEffect, useState } from "react";
import { apiFetch } from "../../api/api";
import "../../styles/bendahara/Dashboard.css";

const money = (value) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(Number(value || 0));

export default function Dashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const load = async () => {
      setLoading(true);
      try {
        const res = await apiFetch("/dashboard-bendahara");
        if (res.status) {
          setData(res.data);
        }
      } catch (error) {
        console.error("Gagal memuat dashboard bendahara:", error);
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  return (
    <main className="bendahara-dashboard-main">
      {/* Hero Section Sesuai Gambar 1 */}
      <section className="bendahara-hero">
        <h1>Selamat Datang</h1>
        <p className="bendahara-hero-subtitle">Bendahara Sekolah</p>
        <p className="bendahara-hero-date">
          {new Date().toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric",
          })}
        </p>
      </section>

      {loading ? (
        <div className="bendahara-loading-panel">Sedang menyelaraskan data keuangan...</div>
      ) : (
        <>
          {/* KPI Grid */}
          <section className="bendahara-kpi-grid">
            <article className="bendahara-kpi-card accent-blue">
              <span>Pagu Anggaran</span>
              <strong>{money(data?.total_anggaran)}</strong>
              <small>Total alokasi program kerja</small>
            </article>
            <article className="bendahara-kpi-card accent-orange">
              <span>Total Realisasi (FPD)</span>
              <strong>{money(data?.total_realisasi)}</strong>
              <small>Dana yang sudah dikeluarkan</small>
            </article>
            <article className="bendahara-kpi-card accent-green">
              <span>Pembayaran Siswa</span>
              <strong>{money(data?.total_pembayaran_siswa)}</strong>
              <small>Total dana masuk dari siswa</small>
            </article>
            <article className="bendahara-kpi-card accent-gold">
              <span>Sisa Anggaran</span>
              <strong>{money(data?.sisa_anggaran)}</strong>
              <small>Saldo anggaran tersedia</small>
            </article>
          </section>

          {/* Insight Grid */}
          <section className="bendahara-insight-grid">
            <div className="bendahara-panel">
              <div className="bendahara-panel-head">
                <div>
                  <p>Persentase Penyerapan</p>
                  <h3 className="bendahara-progress-value">{data?.persentase_serapan}%</h3>
                </div>
                <span>Target: 100%</span>
              </div>
              <div className="bendahara-progress-track">
                <div
                  className="bendahara-progress-fill"
                  style={{ width: `${data?.persentase_serapan}%` }}
                />
              </div>
              <p className="bendahara-panel-note">
                Rasio antara realisasi belanja terhadap total pagu anggaran yang telah ditetapkan.
              </p>
            </div>

            <div className="bendahara-panel">
              <div className="bendahara-panel-head">
                <h3>Status Keuangan</h3>
              </div>
              <div className="bendahara-status-summary">
                <div className="status-item">
                  <i className="bi bi-arrow-up-circle-fill text-success"></i>
                  <span>Arus Masuk Aman</span>
                </div>
                <div className="status-item">
                  <i className="bi bi-check-circle-fill text-primary"></i>
                  <span>Sinkronisasi Berhasil</span>
                </div>
              </div>
            </div>
          </section>
        </>
      )}
    </main>
  );
}
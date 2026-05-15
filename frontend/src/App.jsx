import { BrowserRouter, Routes, Route, Outlet } from "react-router-dom";
import { Navigate } from "react-router-dom";
import { useEffect, useState } from "react";

import SidebarBendahara from "./components/SidebarBendahara";
import SidebarWaka from "./components/SidebarWaka";
import SidebarPic from "./components/SidebarPic";
import Login from "./pages/Login";

import Dashboard from "./pages/bendahara/Dashboard";
import Dana from "./pages/bendahara/Dana";
// import RKA from "./pages/bendahara/RKA";
import BKU from "./pages/bendahara/BKU";
import BKM from "./pages/bendahara/BKM";
import BKK from "./pages/bendahara/BKK";
import Tagihan from "./pages/bendahara/Tagihan";
import Tarif from "./pages/bendahara/Tarif";
import Laporan from "./pages/bendahara/Laporan";

import Verifikasi from "./pages/bendahara/Verifikasi";
import VerifikasiFPD from "./pages/bendahara/VerifikasiFPD";
import Penerimaan from "./pages/bendahara/Penerimaan";
import Log from "./pages/bendahara/ActivityLogDashboard";

import MasterCOA from "./pages/bendahara/MasterCOA";
import MasterKegiatan from "./pages/bendahara/MasterKegiatan";
import MasterTahunAnggaran from "./pages/bendahara/MasterTahunAnggaran";
import MasterTahunAkademik from "./pages/bendahara/MasterTahunAkademik";
import MasterSumberDana from "./pages/bendahara/MasterSumberDana";
import MasterRefPenerimaan from "./pages/bendahara/MasterRefPenerimaan";
import MasterRefPM from "./pages/bendahara/MasterRefPM";
import MasterTarif from "./pages/bendahara/MasterTarif";
import MasterJenisTarif from "./pages/bendahara/MasterJenisTarif";
import MasterJenisPembayaran from "./pages/bendahara/MasterJenisPembayaran";

import PicGuruFPD from "./pages/pic/guru/FPD";
import WakaRKT from "./pages/waka/RKT";
import WakaRKA from "./pages/waka/RKA";
import WakaFPD from "./pages/waka/FPD";
import WakaEvaluasiRKT from "./pages/waka/EvaluasiRKT";
import WakaApprovalCenter from "./pages/waka/ApprovalCenter";
import WakaMonitoring from "./pages/waka/Monitoring";
import DashboardWaka from "./pages/waka/Dashboard";

import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";
import AccountMonitor from "./pages/admin/AccountMonitor";

import SidebarYayasan from "./components/SidebarYayasan";
import DashboardYayasan from "./pages/yayasan/Dashboard.jsx";
import LaporanYayasan from "./pages/yayasan/Laporan.jsx";
import MonitoringYayasan from "./pages/yayasan/Monitoring.jsx";
import ApprovalYayasan from "./pages/yayasan/ApprovalCenter.jsx";

import RKT from "./pages/pic/guru/RKT.jsx";
import CreateRKT from "./pages/pic/guru/CreateRKT.jsx";

import SidebarKepsek from "./components/SidebarKepsek";
import SidebarPm from "./components/SidebarPm";
import KepsekDashboard from "./pages/kepsek/Dashboard.jsx";
import RKA from "./pages/pic/guru/RKA.jsx";
import KepsekApproval from "./pages/kepsek/ApprovalCenter.jsx";
import DashboardPIC from "./pages/pic/guru/Dashboard";

import DashboardPM from "./pages/pm/Dashboard";
import ReferensiPm from "./pages/pm/ReferensiPm";
import PMRKT from "./pages/pm/RKTPage";
import VerifikasiEvaluasiPm from "./pages/PM/VerifikasiEvaluasiPm";
import EvaluasiRKT_PM from "./pages/pm/EvaluasiRKT_PM.jsx";

import KepsekMonitoring from "./pages/kepsek/Monitoring.jsx";
import KepsekMasterCOA from "./pages/kepsek/master/MasterCOA.jsx";
import KepsekMasterKegiatan from "./pages/kepsek/master/MasterKegiatan.jsx";
import KepsekMasterTahunAnggaran from "./pages/kepsek/master/MasterTahunAnggaran.jsx";
import KepsekMasterSumberDana from "./pages/kepsek/master/MasterSumberDana.jsx";
import KepsekMasterRefPenerimaan from "./pages/kepsek/master/MasterRefPenerimaan.jsx";
import KepsekMasterRefPM from "./pages/kepsek/master/MasterRefPM.jsx";
import KepsekMasterTarif from "./pages/kepsek/master/MasterTarif.jsx";
import "./index.css";
import EvaluasiRKTPage from "./pages/kepsek/approve/EvaluasiRKTPage.jsx";
import LaporanKepsek from "./pages/kepsek/Laporan.jsx";
import axios from "axios";

// --- 1. GLOBAL FETCH OVERRIDE ---
const originalFetch = window.fetch;
window.fetch = async (...args) => {
  let [resource, config] = args;
  config = config || {};
  config.headers = config.headers || {};

  if (typeof resource === 'string' && resource.includes('api')) {
    let token = localStorage.getItem('token');
    if (token) {
      token = token.replace(/^"(.*)"$/, '$1'); // Bersihkan tanda kutip pembungkus
      const newHeaders = new Headers(config.headers);
      newHeaders.set('Authorization', `Bearer ${token}`);
      config.headers = newHeaders;
    }
  }
  return originalFetch(resource, config);
};

// --- 2. GLOBAL AXIOS INTERCEPTOR (PENOLONG LOG DELETE) ---
axios.interceptors.request.use((config) => {
  let token = localStorage.getItem("token");
  if (token && config.url && config.url.includes("api")) {
    token = token.replace(/^"(.*)"$/, '$1'); // Bersihkan tanda kutip pembungkus
    config.headers["Authorization"] = `Bearer ${token}`;
  }
  return config;
}, (error) => Promise.reject(error));

import EvaluasiRKT from "./pages/pic/guru/EvaluasiRKT.jsx";
import { useLocation } from "react-router-dom";

function getHomeByRoles(roles = []) {
  const text = roles.join(" ").toLowerCase();
  if (text.includes("super admin")) return "/admin/account-monitor";
  if (text.includes("siswa") || text.includes("ortu")) return "/siswa-ortu/utama";
  if (text.includes("yayasan")) return "/yayasan/dashboard";
  if (text.includes("penjaminan mutu") || text.includes("pm")) return "/pm/dashboard";
  if (text.includes("bendahara") || text.includes("keuangan")) return "/bendahara/dashboard";
  if (text.includes("kepala sekolah") || text.includes("kepsek")) return "/kepsek/dashboard";
  if (text.includes("waka")) return "/waka/dashboard";
  if (text.includes("guru") || text.includes("pic")) return "/pic/guru/dashboard";
  return "/login";
}

function RequireRole({ allow, children }) {
  const location = useLocation();
  const token = localStorage.getItem("token");
  const roles = JSON.parse(localStorage.getItem("roles") || "[]");
  const roleText = roles.join(" ").toLowerCase();
  const canAccess = allow.some((r) => roleText.includes(r));

  if (!token) {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  if (!canAccess) {
    return <AccessDeniedRedirect target={getHomeByRoles(roles)} />;
  }

  return children;
}

function AccessDeniedRedirect({ target }) {
  const [seconds, setSeconds] = useState(4);
  const [goNow, setGoNow] = useState(false);
  const location = useLocation();

  useEffect(() => {
    if (seconds <= 0) {
      setGoNow(true);
      return;
    }

    const timer = setTimeout(() => setSeconds((s) => s - 1), 1000);
    return () => clearTimeout(timer);
  }, [seconds]);

  if (goNow) {
    return <Navigate to={target} replace />;
  }

  return (
    <div style={{ minHeight: "100vh", display: "flex", alignItems: "center", justifyContent: "center", padding: "24px", background: "#f6f7f9" }}>
      <div style={{ maxWidth: "520px", width: "100%", background: "#fff", border: "1px solid #e5e7eb", borderRadius: "12px", padding: "24px", boxShadow: "0 8px 24px rgba(0,0,0,0.08)" }}>
        <h2 style={{ marginTop: 0, marginBottom: "8px", color: "#1f2937" }}>Akses Ditolak</h2>
        <p style={{ marginTop: 0, marginBottom: "14px", color: "#374151" }}>
          Kamu tidak punya akses ke halaman <strong>{location.pathname}</strong>.
        </p>
        <p style={{ marginTop: 0, marginBottom: "18px", color: "#6b7280" }}>
          Dialihkan ke halaman yang sesuai dalam <strong>{seconds}</strong> detik.
        </p>
        <button
          type="button"
          onClick={() => setGoNow(true)}
          style={{ padding: "10px 14px", border: "none", borderRadius: "8px", background: "#2563eb", color: "#fff", cursor: "pointer", fontWeight: 600 }}
        >
          Kembali Sekarang
        </button>
      </div>
    </div>
  );
}

function BendaharaLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarBendahara />
      <div className="content-wrapper" style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
}

function WakaLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarWaka />
      <div className="content-wrapper" style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
}

function YayasanLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarYayasan />
      <div className="content-wrapper" style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
}

function KepsekLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarKepsek />
      <div className="content-wrapper" style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
}

function PmLayout() {
  return (
    <div className="layout" style={{ display: "flex", minHeight: "100vh" }}>
      <SidebarPm />
      <div
        className="content-wrapper"
        style={{
          flex: 1,
          minHeight: "100vh",
          background: "#f6f7f9",
          overflowX: "auto",
        }}
      >
        <Outlet />
      </div>
    </div>
  );
}

function PicGuruLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarPic />
      <div className="content-wrapper" style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/" element={<Navigate to="/login" />} />

        {/* BENDHARA LAYOUT */}
        <Route path="/bendahara" element={<RequireRole allow={["bendahara", "keuangan"]}><BendaharaLayout /></RequireRole>}>
          <Route index element={<Dashboard />} />
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="dana" element={<Dana />} />
          {/* <Route path="rka" element={<RKA />} /> */}
          <Route path="bku" element={<BKU />} />
          <Route path="bkm" element={<BKM />} />
          <Route path="bkk" element={<BKK />} />
          <Route path="tagihan" element={<Tagihan />} />
          <Route path="tarif" element={<Tarif />} />
          <Route path="laporan" element={<Laporan />} />
          <Route path="verifikasi" element={<Verifikasi />} />
          <Route path="verifikasi-fpd" element={<VerifikasiFPD />} />
          <Route path="penerimaan" element={<Penerimaan />} />
          <Route path="log" element={<Log />} />

          {/* BENDAHARA MASTER DATA */}
          <Route path="master/coa" element={<MasterCOA />} />
          <Route path="master/kegiatan" element={<MasterKegiatan />} />
          <Route
            path="master/tahun-anggaran"
            element={<MasterTahunAnggaran />}
          />
          <Route
            path="master/tahun-akademik"
            element={<MasterTahunAkademik />}
          />
          <Route path="master/sumber-dana" element={<MasterSumberDana />} />
          <Route
            path="master/ref-penerimaan"
            element={<MasterRefPenerimaan />}
          />
          <Route path="master/ref-pm" element={<MasterRefPM />} />
          <Route path="master/tarif" element={<MasterTarif />} />
          <Route path="master/jenis-tarif" element={<MasterJenisTarif />} />
          <Route
            path="master/jenis-pembayaran"
            element={<MasterJenisPembayaran />}
          />
        </Route>

        {/* SISWA ORTU TANPA SIDEBAR */}
        {/* <Route path="/siswa-ortu/utama/:id" element={<UtamaSiswaOrtu />} />
        <Route
          path="/siswa-ortu/pembayaran/:id"
          element={<PembayaranTagihanSiswaOrtu />}
        />
        <Route path="/siswa-ortu/profile/:id" element={<ProfileSiswaOrtu />} /> */}
        {/* SEBELUMNYA: path="/siswa-ortu/utama/:id" */}
        <Route path="/siswa-ortu/utama" element={<RequireRole allow={["siswa", "ortu"]}><UtamaSiswaOrtu /></RequireRole>} />

        {/* SEBELUMNYA: path="/siswa-ortu/profile/:id" */}
        <Route path="/siswa-ortu/profile" element={<RequireRole allow={["siswa", "ortu"]}><ProfileSiswaOrtu /></RequireRole>} />

        {/* TETAP DIPERTAHANKAN: :id di bawah ini adalah ID Tagihan spesifik */}
        <Route path="/siswa-ortu/pembayaran/:id" element={<RequireRole allow={["siswa", "ortu"]}><PembayaranTagihanSiswaOrtu /></RequireRole>} />
        <Route path="/admin/account-monitor" element={<RequireRole allow={["super admin"]}><AccountMonitor /></RequireRole>} />

        {/* PIC GURU */}

        <Route path="/pic/guru" element={<RequireRole allow={["guru", "pic"]}><PicGuruLayout /></RequireRole>}>
          <Route index element={<DashboardPIC />} />
          <Route path="dashboard" element={<DashboardPIC />} />
          <Route path="fpd" element={<PicGuruFPD />} />
          <Route path="rkt" element={<RKT />} />
          <Route path="rka" element={<RKA />} />
          <Route path="rkt/create" element={<CreateRKT />} />
          <Route path="rkt/edit/:id" element={<CreateRKT />} />
        </Route>

        <Route path="/pic/guru/evaluasi-rkt" element={<EvaluasiRKT />} />

        {/* KEPSEK */}
        <Route path="/kepsek" element={<RequireRole allow={["kepala sekolah", "kepsek"]}><KepsekLayout /></RequireRole>}>
          <Route index element={<Navigate to="dashboard" />} />
          <Route path="dashboard" element={<KepsekDashboard />} />
          <Route path="monitoring" element={<KepsekMonitoring />} />
          <Route path="approval-center" element={<KepsekApproval />} />
          <Route path="master/coa" element={<KepsekMasterCOA />} />
          <Route path="master/kegiatan" element={<KepsekMasterKegiatan />} />
          <Route
            path="master/tahun-anggaran"
            element={<KepsekMasterTahunAnggaran />}
          />
          <Route
            path="master/sumber-dana"
            element={<KepsekMasterSumberDana />}
          />
          <Route
            path="master/ref-penerimaan"
            element={<KepsekMasterRefPenerimaan />}
          />
          <Route path="master/ref-pm" element={<KepsekMasterRefPM />} />
          <Route path="master/tarif" element={<KepsekMasterTarif />} />
          <Route path="laporan" element={<LaporanKepsek />} />
        </Route>

        {/* WAKA */}
        <Route path="/waka" element={<RequireRole allow={["waka"]}><WakaLayout /></RequireRole>}>
          <Route path="" element={<DashboardWaka />} />
          <Route path="dashboard" element={<DashboardWaka />} />
          <Route path="rkt" element={<WakaRKT />} />
          <Route path="rka" element={<WakaRKA />} />
          <Route path="fpd" element={<WakaFPD />} />
          <Route path="evaluasi" element={<WakaEvaluasiRKT />} />
          <Route path="evaluasi-rkt" element={<WakaEvaluasiRKT />} />
          <Route path="approval-center" element={<WakaApprovalCenter />} />
          <Route path="monitoring" element={<WakaMonitoring />} />
        </Route>

        {/* YAYASAN */}
        <Route path="/yayasan" element={<RequireRole allow={["yayasan"]}><YayasanLayout /></RequireRole>}>
          <Route path="dashboard" element={<DashboardYayasan />} />
          <Route path="approval" element={<ApprovalYayasan />} />
          <Route path="laporan" element={<LaporanYayasan />} />
          <Route path="monitoring" element={<MonitoringYayasan />} />
        </Route>

        {/* PM */}
        <Route path="/pm" element={<RequireRole allow={["penjaminan mutu", "pm"]}><PmLayout /></RequireRole>}>
          <Route index element={<Navigate to="dashboard" />} />
          <Route path="dashboard" element={<DashboardPM />} />
          <Route path="referensi" element={<ReferensiPm />} />
          <Route
            path="monitoring-mutu"
            element={
              <div>
                <h1>Monitoring Mutu</h1>
              </div>
            }
          />
          <Route path="kegiatan" element={<KepsekMasterKegiatan />} />
          <Route path="rkt" element={<PMRKT />} />
          <Route
            path="evaluasi-rkt"
            element={<EvaluasiRKT_PM />  }
          />
          <Route
            path="verifikasi-evaluasi"
            element={<VerifikasiEvaluasiPm />}
          />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

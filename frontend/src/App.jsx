import { BrowserRouter, Routes, Route, Outlet, Navigate } from "react-router-dom";

// COMPONENTS & LAYOUTS
import SidebarBendahara from "./components/SidebarBendahara";
import SidebarWaka from "./components/SidebarWaka";
import SidebarYayasan from "./components/SidebarYayasan";
import SidebarKepsek from "./components/SidebarKepsek";
import SidebarPm from "./components/SidebarPm";
import Login from "./pages/Login";

// BENDAHARA PAGES
import Dashboard from "./pages/bendahara/Dashboard";
import Dana from "./pages/bendahara/Dana";
import RKA from "./pages/bendahara/RKA";
import BKU from "./pages/bendahara/BKU";
import BKM from "./pages/bendahara/BKM";
import BKK from "./pages/bendahara/BKK";
import Tagihan from "./pages/bendahara/Tagihan";
import Tarif from "./pages/bendahara/Tarif";
import Laporan from "./pages/bendahara/Laporan";
import Verifikasi from "./pages/bendahara/Verifikasi";
import Penerimaan from "./pages/bendahara/Penerimaan";
import Log from "./pages/bendahara/ActivityLogDashboard";

// BENDAHARA MASTER
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

// PIC GURU PAGES
import DashboardPIC from "./pages/pic/guru/Dashboard";
import PicGuruFPD from "./pages/pic/guru/FPD";
import RKT from "./pages/pic/guru/RKT";
import CreateRKT from "./pages/pic/guru/CreateRKT";
import StatusPengajuan from "./pages/pic/guru/StatusPengajuan";

// WAKA PAGES
import DashboardWaka from "./pages/waka/Dashboard";
import WakaRKT from "./pages/waka/RKT";
import WakaRKA from "./pages/waka/RKA";
import WakaFPD from "./pages/waka/FPD";
import WakaEvaluasiRKT from "./pages/waka/EvaluasiRKT";
import WakaApprovalCenter from "./pages/waka/ApprovalCenter";
import MonitoringWaka from "./pages/waka/Monitoring";

// KEPSEK & PM PAGES
import KepsekDashboard from "./pages/kepsek/Dashboard.jsx";
import KepsekApproval from "./pages/kepsek/ApprovalCenter.jsx";
import KepsekMonitoring from "./pages/kepsek/Monitoring.jsx";
import DashboardPM from "./pages/pm/Dashboard";
import ReferensiPm from "./pages/pm/ReferensiPm";
import MonitoringMutu from "./pages/pm/MonitoringMutu";

// SISWA & OTHERS
import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";
import DashboardYayasan from "./pages/yayasan/Dashboard.jsx";
import LaporanYayasan from "./pages/yayasan/Laporan.jsx";
import MonitoringYayasan from "./pages/yayasan/Monitoring.jsx";
import ApprovalYayasan from "./pages/yayasan/ApprovalCenter.jsx";

import "./index.css";

// ------------------ LAYOUT DEFINITIONS ------------------

function BendaharaLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarBendahara />
      <div className="content-wrapper" style={{ flex: 1 }}><Outlet /></div>
    </div>
  );
}

function YayasanLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarYayasan />
      <div className="content-wrapper" style={{ flex: 1 }}><Outlet /></div>
    </div>
  );
}

function KepsekLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarKepsek />
      <div className="content-wrapper" style={{ flex: 1 }}><Outlet /></div>
    </div>
  );
}

function WakaLayout() {
  return (
    <div className="layout" style={{ display: "flex" }}>
      <SidebarWaka />
      <div className="content-wrapper" style={{ flex: 1 }}><Outlet /></div>
    </div>
  );
}

function PmLayout() {
  return (
    <div className="layout" style={{ display: "flex", minHeight: "100vh" }}>
      <SidebarPm />
      <div className="content-wrapper" style={{ flex: 1, minHeight: "100vh", background: "#f6f7f9", overflowX: "auto" }}>
        <Outlet />
      </div>
    </div>
  );
}

// ------------------ MAIN APP ------------------

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/" element={<Navigate to="/login" />} />

        {/* BENDAHARA */}
        <Route path="/bendahara" element={<BendaharaLayout />}>
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="dana" element={<Dana />} />
          <Route path="rka" element={<RKA />} />
          <Route path="bku" element={<BKU />} />
          <Route path="bkm" element={<BKM />} />
          <Route path="bkk" element={<BKK />} />
          <Route path="tagihan" element={<Tagihan />} />
          <Route path="tarif" element={<Tarif />} />
          <Route path="laporan" element={<Laporan />} />
          <Route path="verifikasi" element={<Verifikasi />} />
          <Route path="penerimaan" element={<Penerimaan />} />
          <Route path="log" element={<Log />} />
          <Route path="master/coa" element={<MasterCOA />} />
          <Route path="master/kegiatan" element={<MasterKegiatan />} />
          <Route path="master/tahun-anggaran" element={<MasterTahunAnggaran />} />
          <Route path="master/tahun-akademik" element={<MasterTahunAkademik />} />
          <Route path="master/sumber-dana" element={<MasterSumberDana />} />
          <Route path="master/ref-penerimaan" element={<MasterRefPenerimaan />} />
          <Route path="master/ref-pm" element={<MasterRefPM />} />
          <Route path="master/tarif" element={<MasterTarif />} />
          <Route path="master/jenis-tarif" element={<MasterJenisTarif />} />
          <Route path="master/jenis-pembayaran" element={<MasterJenisPembayaran />} />
        </Route>

        {/* SISWA ORTU */}
        <Route path="/siswa-ortu/utama/:id" element={<UtamaSiswaOrtu />} />
        <Route path="/siswa-ortu/pembayaran/:id" element={<PembayaranTagihanSiswaOrtu />} />
        <Route path="/siswa-ortu/profile/:id" element={<ProfileSiswaOrtu />} />

        {/* LEGACY / ALIAS ROUTE - supaya link lama tetap masuk */}
        <Route path="/pic/status-pengajuan" element={<Navigate to="/pic/guru/status-pengajuan" replace />} />
        <Route path="/guru/status-pengajuan" element={<Navigate to="/pic/guru/status-pengajuan" replace />} />

        {/* PIC GURU */}
        <Route path="/pic/guru">
          <Route index element={<DashboardPIC />} />
          <Route path="dashboard" element={<DashboardPIC />} />
          <Route path="fpd" element={<PicGuruFPD />} />
          <Route path="rkt" element={<RKT />} />
          <Route path="rkt/create" element={<CreateRKT />} />
          <Route path="rkt/edit/:id" element={<CreateRKT />} />
          <Route path="rka" element={<RKA />} />
          <Route path="status-pengajuan" element={<StatusPengajuan />} />
        </Route>

        {/* WAKA */}
        <Route path="/waka" element={<WakaLayout />}>
          <Route index element={<DashboardWaka />} />
          <Route path="dashboard" element={<DashboardWaka />} />
          <Route path="rkt" element={<WakaRKT />} />
          <Route path="rka" element={<WakaRKA />} />
          <Route path="fpd" element={<WakaFPD />} />
          <Route path="evaluasi" element={<WakaEvaluasiRKT />} />
          <Route path="monitoring" element={<MonitoringWaka />} />
        </Route>

        {/* KEPSEK */}
        <Route path="/kepsek" element={<KepsekLayout />}>
          <Route index element={<Navigate to="dashboard" />} />
          <Route path="dashboard" element={<KepsekDashboard />} />
          <Route path="monitoring" element={<KepsekMonitoring />} />
          <Route path="approval-center" element={<KepsekApproval />} />
        </Route>

        {/* YAYASAN */}
        <Route path="/yayasan" element={<YayasanLayout />}>
          <Route path="dashboard" element={<DashboardYayasan />} />
          <Route path="approval" element={<ApprovalYayasan />} />
          <Route path="laporan" element={<LaporanYayasan />} />
          <Route path="monitoring" element={<MonitoringYayasan />} />
        </Route>

        {/* PM */}
        <Route path="/pm" element={<PmLayout />}>
          <Route index element={<Navigate to="dashboard" />} />
          <Route path="dashboard" element={<DashboardPM />} />
          <Route path="referensi" element={<ReferensiPm />} />
          <Route path="monitoring-mutu" element={<MonitoringMutu />} />
          <Route path="evaluasi-rkt" element={<div><h1>Evaluasi RKT</h1></div>} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
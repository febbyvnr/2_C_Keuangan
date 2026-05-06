import { BrowserRouter, Routes, Route, Outlet } from "react-router-dom";
import { Navigate } from "react-router-dom";

import SidebarBendahara from "./components/SidebarBendahara";
import SidebarWaka from "./components/SidebarWaka";
import Login from "./pages/Login";

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
import RKT from "./pages/pic/guru/RKT";
import CreateRKT from "./pages/pic/guru/CreateRKT";

// ✅ BARU: Status Pengajuan (Guru/PIC)
import StatusPengajuan from "./pages/pic/guru/StatusPengajuan";

import WakaRKT from "./pages/waka/RKT";
import WakaEvaluasiRKT from "./pages/waka/EvaluasiRKT";
import WakaApprovalCenter from "./pages/waka/ApprovalCenter";
import DashboardWaka from "./pages/waka/Dashboard";

// ✅ BARU: Monitoring Waka (ganti yang lama)
import MonitoringWaka from "./pages/waka/Monitoring";

import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";

import SidebarYayasan from "./components/SidebarYayasan";
import DashboardYayasan from "./pages/yayasan/Dashboard.jsx";
import LaporanYayasan from "./pages/yayasan/Laporan.jsx";
import MonitoringYayasan from "./pages/yayasan/Monitoring.jsx";
import ApprovalYayasan from "./pages/yayasan/ApprovalCenter.jsx";

import KepsekMonitoring from "./pages/kepsek/Monitoring.jsx";

// ✅ BARU: Tim PM Monitoring Mutu
import MonitoringMutu from "./pages/pm/MonitoringMutu";

import "./index.css";

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

        {/* PIC GURU */}
        <Route path="/pic/guru" element={<PicGuruFPD />} />
        <Route path="/pic/guru/fpd" element={<PicGuruFPD />} />
        <Route path="/pic/guru/rkt" element={<RKT />} />
        <Route path="/pic/guru/rkt/create" element={<CreateRKT />} />
        <Route path="/pic/guru/rkt/edit/:id" element={<CreateRKT />} />

        {/* Status Pengajuan */}
        <Route path="/pic/guru/status-pengajuan" element={<StatusPengajuan />} />
        <Route path="/pic/guru/StatusPengajuan" element={<StatusPengajuan />} />
        <Route path="/pic/guru/statuspengajuan" element={<StatusPengajuan />} />

        {/* WAKA */}
        <Route path="/waka" element={<DashboardWaka />} />
        <Route path="/waka/rkt" element={<WakaRKT />} />
        <Route path="/waka/evaluasi" element={<WakaEvaluasiRKT />} />
        <Route path="/waka/evaluasi-rkt" element={<WakaEvaluasiRKT />} />
        <Route path="/waka/approval-center" element={<WakaApprovalCenter />} />

        {/* Monitoring Waka  */}
        <Route path="/waka/monitoring" element={<MonitoringWaka />} />

        {/* YAYASAN */}
        <Route path="/yayasan" element={<YayasanLayout />}>
          <Route path="dashboard" element={<DashboardYayasan />} />
          <Route path="approval" element={<ApprovalYayasan />} />
          <Route path="laporan" element={<LaporanYayasan />} />
          <Route path="monitoring" element={<MonitoringYayasan />} />
        </Route>

        {/*  Tim PM */}
        <Route path="/pm/monitoring-mutu" element={<MonitoringMutu />} />
      </Routes>
    </BrowserRouter>
  );
}
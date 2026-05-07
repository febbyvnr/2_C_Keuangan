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
// import RKAPicGuru from "./pages/pic/guru/RKA.jsx";
import KepsekApproval from "./pages/kepsek/ApprovalCenter.jsx";
//import RKAPicGuru from "./pages/pic/guru/RKA.jsx";
import DashboardPIC from "./pages/pic/guru/Dashboard";

import DashboardPM from "./pages/pm/Dashboard";
import ReferensiPm from "./pages/pm/ReferensiPm";
import LaporanPM from "./pages/PM/Laporan";

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

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/" element={<Navigate to="/login" />} />

        {/* BENDHARA LAYOUT */}
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
        <Route path="/siswa-ortu/utama/:id" element={<UtamaSiswaOrtu />} />
        <Route
          path="/siswa-ortu/pembayaran/:id"
          element={<PembayaranTagihanSiswaOrtu />}
        />
        <Route path="/siswa-ortu/profile/:id" element={<ProfileSiswaOrtu />} />

        {/* PIC GURU */}
        <Route path="/pic/guru" element={<DashboardPIC />} />
        <Route path="/pic/guru/fpd" element={<PicGuruFPD />} />
        <Route path="/pic/guru/rkt" element={<RKT />} />
        <Route path="/pic/guru/rka" element={<RKA />} />
        <Route path="/pic/guru/rkt/create" element={<CreateRKT />} />
        <Route path="/pic/guru/rkt/edit/:id" element={<CreateRKT />} />
        {/* <Route path="/pic/guru/status-pengajuan" element={<StatusPengajuan />} /> */}
        {/* <Route path="/pic/guru/evaluasi-rkt" element={<EvaluasiRKTPage />} /> */}

        {/* KEPSEK */}
        <Route path="/kepsek" element={<KepsekLayout />}>
          <Route index element={<Navigate to="dashboard" />} />
          <Route path="dashboard" element={<KepsekDashboard />} />
          <Route path="monitoring" element={<KepsekMonitoring />} />
          <Route path="approval-center" element={<KepsekApproval />} />
          <Route path="master/coa" element={<KepsekMasterCOA />} />
          <Route path="master/kegiatan" element={<KepsekMasterKegiatan />} />
          <Route path="master/tahun-anggaran" element={<KepsekMasterTahunAnggaran />} />
          <Route path="master/sumber-dana" element={<KepsekMasterSumberDana />} />
          <Route path="master/ref-penerimaan" element={<KepsekMasterRefPenerimaan />} />
          <Route path="master/ref-pm" element={<KepsekMasterRefPM />} />
          <Route path="master/tarif" element={<KepsekMasterTarif />} />
        </Route>

        {/* WAKA */}
        <Route path="/waka" element={<WakaLayout />}>
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
          <Route path="laporan" element={<LaporanPM />} />
          <Route
            path="monitoring-mutu"
            element={
              <div>
                <h1>Monitoring Mutu</h1>
              </div>
            }
          />
          <Route
            path="evaluasi-rkt"
            element={
              <div>
                <h1>Evaluasi RKT</h1>
              </div>
            }
          />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
import { BrowserRouter, Routes, Route, Outlet } from "react-router-dom"

// ==========================================
// 1. IMPORT PUNYA BENDAHARA (JANGAN DIHAPUS)
// ==========================================
import SidebarBendahara from "./components/SidebarBendahara"
import Dashboard from "./pages/bendahara/Dashboard"
import Verifikasi from "./pages/bendahara/Verifikasi"
import Penerimaan from "./pages/bendahara/Penerimaan"
import Tagihan from "./pages/bendahara/Tagihan"
import Laporan from "./pages/bendahara/Laporan"
import MasterCOA from "./pages/bendahara/MasterCOA"
import MasterKegiatan from "./pages/bendahara/MasterKegiatan"
import MasterTahunAnggaran from "./pages/bendahara/MasterTahunAnggaran"
import MasterTahunAkademik from "./pages/bendahara/MasterTahunAkademik"
import MasterSumberDana from "./pages/bendahara/MasterSumberDana"
import MasterRefPenerimaan from "./pages/bendahara/MasterRefPenerimaan"
import MasterTarif from "./pages/bendahara/MasterTarif"
import MasterJenisTarif from "./pages/bendahara/MasterJenisTarif"
import MasterJenisPembayaran from "./pages/bendahara/MasterJenisPembayaran"
import Log from "./pages/bendahara/LogAktivitas"

// ==========================================
// 2. IMPORT PUNYA TUGAS LU (PORTAL BARU)
// ==========================================
import StatusPengajuanGuru from "./pages/guru/StatusPengajuanGuru"
import MonitoringWaka from "./pages/waka/MonitoringWaka"
import MonitoringMutu from "./pages/tim-pm/MonitoringMutu"


// ==========================================
// 3. LAYOUT KHUSUS BENDAHARA (BIAR GABOCOR)
// ==========================================
const BendaharaLayout = () => {
    return (
        <div style={{ display: "flex" }}>
            <SidebarBendahara />
            <div className="content-wrapper">
                {/* Outlet ini tempat nampilin halaman-halaman bendahara */}
                <Outlet /> 
            </div>
        </div>
    )
}

// ==========================================
// 4. MAIN ROUTER
// ==========================================
export default function App() {
    return (
        <BrowserRouter>
            <Routes>
                
                {/* --------------------------------------------------- */}
                {/* ROUTE PORTAL BARU LU (DESAIN FIGMA - TANPA TABRAKAN) */}
                {/* --------------------------------------------------- */}
                <Route path="/guru" element={<StatusPengajuanGuru />} />
                <Route path="/waka" element={<MonitoringWaka />} />
                <Route path="/pm" element={<MonitoringMutu />} />


                {/* --------------------------------------------------- */}
                {/* ROUTE LAMA BENDAHARA (DIBUNGKUS DALAM BENDAHARA LAYOUT) */}
                {/* --------------------------------------------------- */}
                <Route element={<BendaharaLayout />}>
                    <Route path="/bendahara/" element={<Dashboard />} />
                    <Route path="/bendahara/dashboard" element={<Dashboard />} />
                    <Route path="/bendahara/master/coa" element={<MasterCOA />} />
                    <Route path="/bendahara/master/kegiatan" element={<MasterKegiatan />} />
                    <Route path="/bendahara/master/tahun-anggaran" element={<MasterTahunAnggaran />} />
                    <Route path="/bendahara/master/tahun-akademik" element={<MasterTahunAkademik />} />
                    <Route path="/bendahara/master/sumber-dana" element={<MasterSumberDana />} />
                    <Route path="/bendahara/master/ref-penerimaan" element={<MasterRefPenerimaan />} />
                    <Route path="/bendahara/master/tarif" element={<MasterTarif />} />
                    <Route path="/bendahara/master/jenis-tarif" element={<MasterJenisTarif />} />
                    <Route path="/bendahara/master/jenis-pembayaran" element={<MasterJenisPembayaran />} />
                    <Route path="/bendahara/penerimaan" element={<Penerimaan />} />
                    <Route path="/bendahara/tagihan" element={<Tagihan />} />
                    <Route path="/bendahara/verifikasi" element={<Verifikasi />} />
                    <Route path="/bendahara/laporan" element={<Laporan />} />
                    <Route path="/bendahara/log" element={<Log />} />
                </Route>

            </Routes>
        </BrowserRouter>
    )
}
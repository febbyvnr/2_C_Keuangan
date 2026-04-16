import { BrowserRouter, Routes, Route, Outlet } from "react-router-dom";

import SidebarBendahara from "./components/SidebarBendahara";

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
import Log from "./pages/bendahara/LogAktivitas";

import MasterCOA from "./pages/bendahara/MasterCOA";
import MasterKegiatan from "./pages/bendahara/MasterKegiatan";
import MasterTahunAnggaran from "./pages/bendahara/MasterTahunAnggaran";
import MasterTahunAkademik from "./pages/bendahara/MasterTahunAkademik";
import MasterSumberDana from "./pages/bendahara/MasterSumberDana";
import MasterRefPenerimaan from "./pages/bendahara/MasterRefPenerimaan";
import MasterTarif from "./pages/bendahara/MasterTarif";
import MasterJenisTarif from "./pages/bendahara/MasterJenisTarif";
import MasterJenisPembayaran from "./pages/bendahara/MasterJenisPembayaran";

import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";

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

export default function App() {
    return (
        <BrowserRouter>
            <Routes>
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
                    <Route path="penerimaan" element={<Penerimaan />} />
                    <Route path="log" element={<Log />} />

                    <Route path="master/coa" element={<MasterCOA />} />
                    <Route path="master/kegiatan" element={<MasterKegiatan />} />
                    <Route path="master/tahun-anggaran" element={<MasterTahunAnggaran />} />
                    <Route path="master/tahun-akademik" element={<MasterTahunAkademik />} />
                    <Route path="master/sumber-dana" element={<MasterSumberDana />} />
                    <Route path="master/ref-penerimaan" element={<MasterRefPenerimaan />} />
                    <Route path="master/tarif" element={<MasterTarif />} />
                    <Route path="master/jenis-tarif" element={<MasterJenisTarif />} />
                    <Route path="master/jenis-pembayaran" element={<MasterJenisPembayaran />} />
                </Route>

                {/* SISWA ORTU */}
                <Route path="/siswa-ortu/utama" element={<UtamaSiswaOrtu />} />
                <Route path="/siswa-ortu/pembayaran/:id" element={<PembayaranTagihanSiswaOrtu />} />
                <Route path="/siswa-ortu/profile" element={<ProfileSiswaOrtu />} />

            </Routes>
        </BrowserRouter>
    );
}
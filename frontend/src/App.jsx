import { BrowserRouter, Routes, Route } from "react-router-dom";
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
import MasterCOA from "./pages/bendahara/MasterCOA";
import MasterTahunAnggaran from "./pages/bendahara/MasterTahunAnggaran";
import MasterTahunAkademik from "./pages/bendahara/MasterTahunAkademik";
import MasterSumberDana from "./pages/bendahara/MasterSumberDana";
import MasterRefPenerimaan from "./pages/bendahara/MasterRefPenerimaan";
import MasterTarif from "./pages/bendahara/MasterTarif";
import MasterJenisTarif from "./pages/bendahara/MasterJenisTarif";
import MasterJenisPembayaran from "./pages/bendahara/MasterJenisPembayaran";
import Log from "./pages/bendahara/LogAktivitas";

import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";

import "./index.css";

function BendaharaPage({ children }) {
  return (
    <div style={{ display: "flex" }}>
      <SidebarBendahara />
      <div className="content-wrapper">{children}</div>
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/bendahara/" element={<BendaharaPage><Dashboard /></BendaharaPage>} />
        <Route path="/bendahara/dashboard" element={<BendaharaPage><Dashboard /></BendaharaPage>} />
        <Route path="/bendahara/dana" element={<BendaharaPage><Dana /></BendaharaPage>} />
        <Route path="/bendahara/rka" element={<BendaharaPage><RKA /></BendaharaPage>} />
        <Route path="/bendahara/bku" element={<BendaharaPage><BKU /></BendaharaPage>} />
        <Route path="/bendahara/bkm" element={<BendaharaPage><BKM /></BendaharaPage>} />
        <Route path="/bendahara/bkk" element={<BendaharaPage><BKK /></BendaharaPage>} />
        <Route path="/bendahara/tagihan" element={<BendaharaPage><Tagihan /></BendaharaPage>} />
        <Route path="/bendahara/tarif" element={<BendaharaPage><Tarif /></BendaharaPage>} />
        <Route path="/bendahara/laporan" element={<BendaharaPage><Laporan /></BendaharaPage>} />
        <Route path="/bendahara/master/coa" element={<BendaharaPage><MasterCOA /></BendaharaPage>} />
        <Route path="/bendahara/master/tahun-anggaran" element={<BendaharaPage><MasterTahunAnggaran /></BendaharaPage>} />
        <Route path="/bendahara/master/tahun-akademik" element={<BendaharaPage><MasterTahunAkademik /></BendaharaPage>} />
        <Route path="/bendahara/master/sumber-dana" element={<BendaharaPage><MasterSumberDana /></BendaharaPage>} />
        <Route path="/bendahara/master/ref-penerimaan" element={<BendaharaPage><MasterRefPenerimaan /></BendaharaPage>} />
        <Route path="/bendahara/master/tarif" element={<BendaharaPage><MasterTarif /></BendaharaPage>} />
        <Route path="/bendahara/master/jenis-tarif" element={<BendaharaPage><MasterJenisTarif /></BendaharaPage>} />
        <Route path="/bendahara/master/jenis-pembayaran" element={<BendaharaPage><MasterJenisPembayaran /></BendaharaPage>} />
        <Route path="/bendahara/penerimaan" element={<BendaharaPage><Penerimaan /></BendaharaPage>} />
        <Route path="/bendahara/verifikasi" element={<BendaharaPage><Verifikasi /></BendaharaPage>} />
        <Route path="/bendahara/log" element={<BendaharaPage><Log /></BendaharaPage>} />

        <Route path="/siswa-ortu/utama" element={<UtamaSiswaOrtu />} />
        <Route path="/siswa-ortu/pembayaran/:id" element={<PembayaranTagihanSiswaOrtu />} />
        <Route path="/siswa-ortu/profile" element={<ProfileSiswaOrtu />} />
      </Routes>
    </BrowserRouter>
  );
}
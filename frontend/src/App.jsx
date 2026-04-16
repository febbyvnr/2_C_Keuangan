import { BrowserRouter, Routes, Route } from "react-router-dom";

import SidebarBendahara from "./components/SidebarBendahara";

import Dashboard from "./pages/bendahara/Dashboard";
// import Persetujuan from "./pages/bendahara/Persetujuan";
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
import MasterKegiatan from "./pages/bendahara/MasterKegiatan";
import MasterTahunAnggaran from "./pages/bendahara/MasterTahunAnggaran";
import MasterTahunAkademik from "./pages/bendahara/MasterTahunAkademik";
import MasterSumberDana from "./pages/bendahara/MasterSumberDana";
import MasterRefPenerimaan from "./pages/bendahara/MasterRefPenerimaan";
import MasterTarif from "./pages/bendahara/MasterTarif";
import MasterJenisTarif from "./pages/bendahara/MasterJenisTarif";
import MasterJenisPembayaran from "./pages/bendahara/MasterJenisPembayaran";
import Log from "./pages/bendahara/LogAktivitas";
import "./index.css";

export default function App() {
  return (
    <BrowserRouter>
      <div style={{ display: "flex" }}>
        <SidebarBendahara />
        <div className="content-wrapper">
          <Routes>
            <Route path="/bendahara/" element={<Dashboard />} />
            <Route path="/bendahara/dashboard" element={<Dashboard />} />
            {/* <Route path="/bendahara/persetujuan" element={<Persetujuan />} /> */}
            <Route path="/bendahara/dana" element={<Dana />} />
            <Route path="/bendahara/rka" element={<RKA />} />
            <Route path="/bendahara/bku" element={<BKU />} />
            <Route path="/bendahara/bkm" element={<BKM />} />
            <Route path="/bendahara/bkk" element={<BKK />} />
            <Route path="/bendahara/tagihan" element={<Tagihan />} />
            <Route path="/bendahara/tarif" element={<Tarif />} />
            <Route path="/bendahara/laporan" element={<Laporan />} />
            <Route path="/bendahara/master/coa" element={<MasterCOA />} />
            <Route
              path="/bendahara/master/kegiatan"
              element={<MasterKegiatan />}
            />
            <Route
              path="/bendahara/master/tahun-anggaran"
              element={<MasterTahunAnggaran />}
            />
            <Route
              path="/bendahara/master/tahun-akademik"
              element={<MasterTahunAkademik />}
            />
            <Route
              path="/bendahara/master/sumber-dana"
              element={<MasterSumberDana />}
            />
            <Route
              path="/bendahara/master/ref-penerimaan"
              element={<MasterRefPenerimaan />}
            />
            <Route path="/bendahara/master/tarif" element={<MasterTarif />} />
            <Route
              path="/bendahara/master/jenis-tarif"
              element={<MasterJenisTarif />}
            />
            <Route
              path="/bendahara/master/jenis-pembayaran"
              element={<MasterJenisPembayaran />}
            />
            <Route path="/bendahara/penerimaan" element={<Penerimaan />} />
            <Route path="/bendahara/verifikasi" element={<Verifikasi />} />
            <Route path="/bendahara/log" element={<Log />} />
          </Routes>
        </div>
      </div>
    </BrowserRouter>
  );
}

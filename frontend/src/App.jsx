// import { BrowserRouter, Routes, Route } from "react-router-dom"

// import SidebarBendahara from "./components/SidebarBendahara"

// import Dashboard from "./pages/bendahara/Dashboard"
// import Persetujuan from "./pages/bendahara/Persetujuan"
// import Dana from "./pages/bendahara/Dana"
// import RKA from "./pages/bendahara/RKA"
// import BKU from "./pages/bendahara/BKU"
// import BKM from "./pages/bendahara/BKM"
// import BKK from "./pages/bendahara/BKK"
// import Tagihan from "./pages/bendahara/Tagihan"
// import Tarif from "./pages/bendahara/Tarif"
// import Laporan from "./pages/bendahara/Laporan"

// export default function App() {
//     return (
//         <BrowserRouter>
//             <div style={{display:"flex"}}>
//                 <SidebarBendahara />
//                 <div className="content-wrapper">
//                     <Routes>
//                         <Route path="/bendahara/" element={<Dashboard />} />
//                         <Route path="/bendahara/dashboard" element={<Dashboard />} />
//                         <Route path="/bendahara/persetujuan" element={<Persetujuan />} />
//                         <Route path="/bendahara/dana" element={<Dana />} />
//                         <Route path="/bendahara/rka" element={<RKA />} />
//                         <Route path="/bendahara/bku" element={<BKU />} />
//                         <Route path="/bendahara/bkm" element={<BKM />} />
//                         <Route path="/bendahara/bkk" element={<BKK />} />
//                         <Route path="/bendahara/tagihan" element={<Tagihan />} />
//                         <Route path="/bendahara/tarif" element={<Tarif />} />
//                         <Route path="/bendahara/laporan" element={<Laporan />} />
//                     </Routes>
//                 </div>
//             </div>
//         </BrowserRouter>
//     )
// }

import { BrowserRouter, Routes, Route } from "react-router-dom";

import SidebarBendahara from "./components/SidebarBendahara";

import Dashboard from "./pages/bendahara/Dashboard";
import Persetujuan from "./pages/bendahara/Persetujuan";
import Dana from "./pages/bendahara/Dana";
import RKA from "./pages/bendahara/RKA";
import BKU from "./pages/bendahara/BKU";
import BKM from "./pages/bendahara/BKM";
import BKK from "./pages/bendahara/BKK";
import Tagihan from "./pages/bendahara/Tagihan";
import Tarif from "./pages/bendahara/Tarif";
import Laporan from "./pages/bendahara/Laporan";

import UtamaSiswaOrtu from "./pages/siswaOrtu/UtamaSiswaOrtu";
import PembayaranTagihanSiswaOrtu from "./pages/siswaOrtu/PembayaranTagihanSiswaOrtu";
import ProfileSiswaOrtu from "./pages/siswaOrtu/ProfileSiswaOrtu";

function BendaharaPage({ children }) {
  return (
    <div style={{ display: "flex" }}>
      <SidebarBendahara />
      <div className="content-wrapper">
        {children}
      </div>
    </div>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route
          path="/bendahara/"
          element={
            <BendaharaPage>
              <Dashboard />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/dashboard"
          element={
            <BendaharaPage>
              <Dashboard />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/persetujuan"
          element={
            <BendaharaPage>
              <Persetujuan />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/dana"
          element={
            <BendaharaPage>
              <Dana />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/rka"
          element={
            <BendaharaPage>
              <RKA />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/bku"
          element={
            <BendaharaPage>
              <BKU />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/bkm"
          element={
            <BendaharaPage>
              <BKM />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/bkk"
          element={
            <BendaharaPage>
              <BKK />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/tagihan"
          element={
            <BendaharaPage>
              <Tagihan />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/tarif"
          element={
            <BendaharaPage>
              <Tarif />
            </BendaharaPage>
          }
        />
        <Route
          path="/bendahara/laporan"
          element={
            <BendaharaPage>
              <Laporan />
            </BendaharaPage>
          }
        />

        <Route path="/siswa-ortu/utama" element={<UtamaSiswaOrtu />} />
        <Route
          path="/siswa-ortu/pembayaran/:id"
          element={<PembayaranTagihanSiswaOrtu />}
        />
        <Route path="/siswa-ortu/profile" element={<ProfileSiswaOrtu />} />
      </Routes>
    </BrowserRouter>
  );
}
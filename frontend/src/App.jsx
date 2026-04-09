import { BrowserRouter, Routes, Route } from "react-router-dom"

import Sidebar from "./components/Sidebar"

import Dashboard from "./pages/Dashboard"
import Persetujuan from "./pages/Persetujuan"
import Dana from "./pages/Dana"
import RKA from "./pages/RKA"
import BKU from "./pages/BKU"
import BKM from "./pages/BKM"
import BKK from "./pages/BKK"
import Tagihan from "./pages/Tagihan"
import Tarif from "./pages/Tarif"
import Laporan from "./pages/Laporan"

export default function App() {
    return (
        <BrowserRouter>
            <div style={{display:"flex"}}>
                <Sidebar />
                <div style={{flex:1}}>
                    <Routes>
                        <Route path="/" element={<Dashboard />} />
                        <Route path="/dashboard" element={<Dashboard />} />
                        <Route path="/persetujuan" element={<Persetujuan />} />
                        <Route path="/dana" element={<Dana />} />
                        <Route path="/rka" element={<RKA />} />
                        <Route path="/bku" element={<BKU />} />
                        <Route path="/bkm" element={<BKM />} />
                        <Route path="/bkk" element={<BKK />} />
                        <Route path="/tagihan" element={<Tagihan />} />
                        <Route path="/tarif" element={<Tarif />} />
                        <Route path="/laporan" element={<Laporan />} />
                    </Routes>
                </div>
            </div>
        </BrowserRouter>
    )
}
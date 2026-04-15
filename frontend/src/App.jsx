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
import './index.css'

export default function App() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-black">
      <h1 className="text-5xl font-bold text-red-500 bg-yellow-300 p-6 rounded-2xl shadow-2xl">
        TAILWIND AKTIF
      </h1>
    </div>
  );
}
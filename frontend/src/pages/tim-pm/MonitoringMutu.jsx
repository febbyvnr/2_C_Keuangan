import React, { useState, useEffect } from 'react';
import api from '../../services/api';
import SidebarPortal from '../../components/SidebarPortal';

const MonitoringMutu = () => {
  const [dataPm, setDataPm] = useState([]);

  const pmMenus = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'standar', label: 'Standar Mutu' },
    { id: 'evaluasi', label: 'Evaluasi Program' },
    { id: 'laporan', label: 'Laporan Penjaminan' },
  ];

  useEffect(() => {
    api.get('/tr-pm')
      .then(response => setDataPm(response.data.data || []))
      .catch(error => console.error("Error fetching PM:", error));
  }, []);

  return (
    <div className="flex min-h-screen bg-[#F6F7F9] font-sans">
      <SidebarPortal 
        roleTitle="Tim Penjamin Mutu" 
        roleSubtitle="SMK BOPKRI 2" 
        userName="Admin PM" 
        userRole="Evaluator Mutu" 
        activeMenu="evaluasi" 
        menus={pmMenus} 
      />
      
      <div className="flex-1 ml-64 p-8 overflow-y-auto">
        <div className="mb-6">
          <h1 className="text-xl font-bold text-gray-900 tracking-tight">Evaluasi Mutu Program</h1>
          <p className="text-gray-500 mt-1 text-xs font-medium">Pantau hasil evaluasi dan kesesuaian Visi Misi.</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div className="bg-white rounded-lg p-5 border border-gray-200 border-t-[3px] border-t-[#8b5cf6] shadow-sm">
            <p className="text-[11px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Total Evaluasi Masuk</p>
            <h3 className="text-lg font-bold text-gray-900">{dataPm.length} Dokumen</h3>
          </div>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
          <h2 className="text-sm font-bold text-gray-900 mb-4">Riwayat Penilaian Terkini</h2>
          
          <div className="space-y-2.5">
            {dataPm.map((item) => (
              <div key={item.ID_TR_PM} className="border border-gray-200 rounded-md p-3.5 flex justify-between items-center bg-white hover:border-purple-200 transition-colors">
                <div>
                  <h3 className="font-bold text-gray-900 text-[13px]">
                    Evaluasi Program ID: {item.ID_PROGRAM_KERJA}
                  </h3>
                  <p className="text-[11px] text-gray-500 mt-0.5 font-medium">
                    Tanggal Evaluasi: {item.TGL_PM} | Catatan: {item.DESKRIPSI_TR_PM || '-'}
                  </p>
                </div>
                <div>
                  <span className={`px-2.5 py-1 rounded-md text-[10px] font-bold 
                    ${item.TINGKAT_KESESUAIAN === 'Sesuai' ? 'bg-[#dcfce7] text-[#166534]' : 
                      item.TINGKAT_KESESUAIAN === 'Kurang Sesuai' ? 'bg-[#fef08a] text-[#854d0e]' : 'bg-[#fee2e2] text-[#991b1b]'}`}>
                    {item.TINGKAT_KESESUAIAN || 'Belum Dinilai'}
                  </span>
                </div>
              </div>
            ))}
            {dataPm.length === 0 && <p className="text-xs text-gray-500 italic">Belum ada data evaluasi.</p>}
          </div>
        </div>
      </div>
    </div>
  );
};

export default MonitoringMutu;
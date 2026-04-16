import React, { useState, useEffect } from 'react';
import api from '../../services/api';
import SidebarPortal from '../../components/SidebarPortal';

const MonitoringWaka = () => {
  const [dataRkt, setDataRkt] = useState([]);

  const wakaMenus = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'rkt', label: 'Perencanaan RKT' },
    { id: 'approval', label: 'Persetujuan (Approval)' },
    { id: 'laporan', label: 'Laporan Keuangan' },
  ];

  useEffect(() => {
    api.get('/rkt')
      .then(response => setDataRkt(response.data.data || []))
      .catch(error => console.error("Error fetching RKT:", error));
  }, []);

  return (
    <div className="flex min-h-screen bg-[#F6F7F9] font-sans">
      <SidebarPortal 
        roleTitle="Ruang Pimpinan" 
        roleSubtitle="SMK BOPKRI 2" 
        userName="Bpk. Waka" 
        userRole="Wakil Kepala Sekolah" 
        activeMenu="rkt" 
        menus={wakaMenus} 
      />
      
      <div className="flex-1 ml-64 p-8 overflow-y-auto">
        <div className="mb-6">
          <h1 className="text-xl font-bold text-gray-900 tracking-tight">Dashboard Eksekutif RKT</h1>
          <p className="text-gray-500 mt-1 text-xs font-medium">Pantau serapan anggaran dan program kerja sekolah Anda.</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div className="bg-white rounded-lg p-5 border border-gray-200 border-t-[3px] border-t-[#2563eb] shadow-sm">
            <p className="text-[11px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Total RKT Aktif</p>
            <h3 className="text-lg font-bold text-gray-900">{dataRkt.length} Kegiatan</h3>
          </div>
          <div className="bg-white rounded-lg p-5 border border-gray-200 border-t-[3px] border-t-[#16a34a] shadow-sm">
            <p className="text-[11px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Total Nominal Anggaran</p>
            <h3 className="text-lg font-bold text-[#16a34a]">
              Rp {dataRkt.reduce((sum, item) => sum + Number(item.NOMINAL || 0), 0).toLocaleString('id-ID')}
            </h3>
          </div>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
          <h2 className="text-sm font-bold text-gray-900 mb-4">Daftar Program Kerja (RKT)</h2>
          
          <div className="space-y-2.5">
            {dataRkt.map((item) => (
              <div key={item.ID_PROGRAM_KERJA} className="border border-gray-200 rounded-md p-3.5 flex justify-between items-center bg-white hover:border-blue-200 transition-colors">
                <div>
                  <h3 className="font-bold text-gray-900 text-[13px]">{item.PROGRAM_KERJA}</h3>
                  <p className="text-[11px] text-gray-500 mt-0.5 font-medium">
                    Waktu: {item.WAKTU_AWAL} s/d {item.WAKTU_AKHIR} | PIC: {item.NIP_PENANGGUNG_JAWAB}
                  </p>
                </div>
                <div>
                  <span className="bg-[#f8fafc] text-gray-800 border border-gray-200 px-2.5 py-1 rounded-md text-[11px] font-bold">
                    Rp {Number(item.NOMINAL).toLocaleString('id-ID')}
                  </span>
                </div>
              </div>
            ))}
            {dataRkt.length === 0 && <p className="text-xs text-gray-500 italic">Belum ada data RKT.</p>}
          </div>
        </div>
      </div>
    </div>
  );
};

export default MonitoringWaka;
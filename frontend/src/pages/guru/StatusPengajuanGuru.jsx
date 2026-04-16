import React, { useState, useEffect } from 'react';
import api from '../../services/api';
import SidebarPortal from '../../components/SidebarPortal';

const StatusPengajuanGuru = () => {
  const [dataFpd, setDataFpd] = useState([]);

  const guruMenus = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'rkt', label: 'Page RKT' },
    { id: 'realisasi', label: 'Page Realisasi RKT' },
    { id: 'bridging', label: 'Page Bridging RKT' },
    { id: 'pengajuan', label: 'Page Pengajuan Dana' },
    { id: 'lpj', label: 'Page LPJ' },
    { id: 'evaluasi', label: 'Page Evaluasi RKT' },
  ];

  useEffect(() => {
    api.get('/fpd-anggaran')
      .then(res => setDataFpd(res.data.data || []))
      .catch(err => console.error(err));
  }, []);

  return (
    <div className="flex bg-[#F6F7F9] min-h-screen">

      <SidebarPortal 
        roleTitle="Portal Guru"
        roleSubtitle="SMK BOPKRI 2"
        userName="Antonius Budiarto"
        userRole="Guru Kejuruan RPL"
        activeMenu="pengajuan"
        menus={guruMenus}
      />

      {/* CONTENT */}
      <div className="ml-60 w-full max-w-[900px] px-6 py-6">

        {/* HEADER */}
        <div className="mb-5">
          <h1 className="text-lg font-semibold text-gray-900">
            Status Pengajuan Dana
          </h1>
          <p className="text-[11px] text-gray-500 mt-1">
            Pantau status pengajuan dana dan dokumen Anda di sini.
          </p>
        </div>

        {/* 🔥 CARD 3 SEJAJAR */}
        <div className="grid grid-cols-3 gap-3 mb-5">

          <div className="bg-white border border-gray-200 rounded-md px-3 py-2">
            <p className="text-[9px] text-gray-400 uppercase">Total Dokumen</p>
            <h2 className="text-base font-semibold mt-[2px]">
              {dataFpd.length}
            </h2>
          </div>

          <div className="bg-white border border-gray-200 rounded-md px-3 py-2">
            <p className="text-[9px] text-gray-400 uppercase">Menunggu</p>
            <h2 className="text-base font-semibold mt-[2px]">
              {dataFpd.filter(d => !d.NIP_VALIDATOR_FPD).length}
            </h2>
          </div>

          <div className="bg-white border border-gray-200 rounded-md px-3 py-2">
            <p className="text-[9px] text-gray-400 uppercase">Disetujui</p>
            <h2 className="text-base font-semibold mt-[2px]">
              {dataFpd.filter(d => d.NIP_VALIDATOR_FPD).length}
            </h2>
          </div>

        </div>

        {/* LIST */}
        <div className="bg-white border border-gray-200 rounded-md p-3">

          <h2 className="text-[12px] font-semibold mb-2">
            Riwayat Pengajuan
          </h2>

          <div className="space-y-2">

            {dataFpd.map(item => (
              <div 
                key={item.ID_FPD}
                className="flex justify-between items-center border border-gray-200 rounded px-3 py-2"
              >

                <div>
                  <p className="text-[12px] font-medium">
                    {item.programKerja?.PROGRAM_KERJA || `Program ID ${item.ID_PROGRAM_KERJA}`}
                  </p>

                  <p className="text-[10px] text-gray-500">
                    {item.TGL_FPD} • Rp {(item.NOMINAL_ANGGARAN || 0).toLocaleString('id-ID')}
                  </p>
                </div>

                <span className={`text-[9px] px-2 py-[2px] rounded ${
                  item.NIP_VALIDATOR_FPD
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-600'
                }`}>
                  {item.NIP_VALIDATOR_FPD ? 'Disetujui' : 'Menunggu'}
                </span>

              </div>
            ))}

          </div>

        </div>

      </div>
    </div>
  );
};

export default StatusPengajuanGuru;
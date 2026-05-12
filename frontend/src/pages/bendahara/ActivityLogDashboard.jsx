import React, { useState, useEffect } from 'react';
import '../../styles/bendahara/ActivityLogDashboard.css'; 

const ActivityLogDashboard = () => {
    // State Tab
    const [activeTab, setActiveTab] = useState('activity'); // 'activity' atau 'access'

    // State Data
    const [activityLogs, setActivityLogs] = useState([]);
    const [accessLogs, setAccessLogs] = useState([]);
    
    // State Controls
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState('All');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (activeTab === 'activity') {
            fetchActivityLogs();
        } else {
            fetchAccessLogs();
        }
    }, [activeTab]); // Fetch ulang tiap kali pindah tab

    const fetchActivityLogs = async () => {
        setLoading(true);
        try {
            const response = await fetch('http://localhost:8000/api/activity-logs');
            const result = await response.json();
            if (result.success) setActivityLogs(result.data);
        } catch (error) {
            console.error("Gagal mengambil activity log:", error);
        } finally {
            setLoading(false);
        }
    };

    const fetchAccessLogs = async () => {
        setLoading(true);
        try {
            const response = await fetch('http://localhost:8000/api/access-logs');
            const result = await response.json();
            if (result.success) setAccessLogs(result.data);
        } catch (error) {
            console.error("Gagal mengambil access log:", error);
        } finally {
            setLoading(false);
        }
    };

    const handleReset = () => {
        setSearchTerm('');
        setRoleFilter('All');
    };

    // Filter Logic untuk Tab Aktif (Mendukung pencarian teks & potongan waktu lengkap)
    const getFilteredData = () => {
        const sourceData = activeTab === 'activity' ? activityLogs : accessLogs;
        
        return sourceData.filter(log => {
            const strAktivitas = log.aktivitas || '';
            const strNama = log.username || log.nama_asli || '';
            const strNip = log.nip_nis || '';
            
            // Atribut waktu untuk mendukung pencarian Year, Month, Date, Hours, Minutes, Seconds
            const strWaktu = log.waktu || '';
            const strStartLogin = log.start_login || '';
            const strEndLogin = log.end_login || '';

            const matchSearch = 
                strAktivitas.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strNip.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strNama.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strWaktu.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strStartLogin.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strEndLogin.toLowerCase().includes(searchTerm.toLowerCase());
                
            const matchRole = roleFilter === 'All' || log.role === roleFilter;

            return matchSearch && matchRole;
        });
    };

    const filteredData = getFilteredData();

    // EKSTRAKSI ROLE OTOMATIS (Menggunakan new Set secara dinamis dari tab yang aktif)
    const currentSourceData = activeTab === 'activity' ? activityLogs : accessLogs;
    const uniqueRoles = ['All', ...new Set(currentSourceData.map(item => item.role).filter(Boolean))];

    const getBadgeClass = (aktivitas) => {
        if (!aktivitas) return 'badge';
        const act = aktivitas.toUpperCase();
        if (act.includes('INSERT') || act.includes('CREATE')) return 'badge badge-created';
        if (act.includes('UPDATE')) return 'badge badge-updated';
        if (act.includes('DELETE')) return 'badge badge-deleted';
        return 'badge badge-default';
    };

    return (
        <div className="log-page-wrapper">
            
            {/* Bagian Header */}
            <div className="log-header-row">
                <h2 className="log-title">Dashboard Log Sistem</h2>
                
                <div className="log-actions">
                    <button className="log-btn-reset" onClick={handleReset}>Reset</button>
                    
                    {/* Dropdown Role Dinamis */}
                    <select 
                        className="log-select" 
                        value={roleFilter} 
                        onChange={(e) => setRoleFilter(e.target.value)}
                    >
                        {uniqueRoles.map((role, index) => (
                            <option key={index} value={role}>
                                {role === 'All' ? 'Semua Role' : role}
                            </option>
                        ))}
                    </select>

                    {/* Searchbar tunggal yang mendukung pencarian universal */}
                    <input 
                        type="text" 
                        placeholder="Cari NIP, Nama, Aktivitas, Waktu..." 
                        className="log-input"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* TAB NAVIGATION */}
            <div className="log-tabs-container">
                <button 
                    className={`log-tab-btn ${activeTab === 'activity' ? 'active' : ''}`}
                    onClick={() => { setActiveTab('activity'); setRoleFilter('All'); }}
                >
                    Log Aktivitas Data
                </button>
                <button 
                    className={`log-tab-btn ${activeTab === 'access' ? 'active' : ''}`}
                    onClick={() => { setActiveTab('access'); setRoleFilter('All'); }}
                >
                    Log Session Login
                </button>
            </div>

            {/* Box Putih Utama */}
            <div className="log-content-box">
                <div className="log-table-wrapper">
                    <table className="log-table">
                        <thead>
                            {activeTab === 'activity' ? (
                                <tr>
                                    <th>ID</th>
                                    <th>WAKTU</th>
                                    <th>USERNAME</th>
                                    <th>NIP/NIS</th>
                                    <th>ROLE</th>
                                    <th>AKTIVITAS</th>
                                    <th>DESKRIPSI</th>
                                </tr>
                            ) : (
                                <tr>
                                    <th style={{ width: '5%' }}>ID LOG</th>
                                    <th style={{ width: '20%' }}>WAKTU LOGIN</th>
                                    <th style={{ width: '20%' }}>WAKTU LOGOUT</th>
                                    <th style={{ width: '25%' }}>USERNAME</th>
                                    <th style={{ width: '15%' }}>NIP/NIS</th>
                                    <th style={{ width: '15%' }}>ROLE</th>
                                </tr>
                            )}
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan={activeTab === 'activity' ? 7 : 6} className="empty-state" style={{ textAlign: 'center' }}>
                                        Memuat data log...
                                    </td>
                                </tr>
                            ) : filteredData.length === 0 ? (
                                <tr>
                                    <td colSpan={activeTab === 'activity' ? 7 : 6} className="empty-state" style={{ fontWeight: '500', color: '#4b5563', textAlign: 'center' }}>
                                        Data log masih kosong atau tidak ditemukan.
                                    </td>
                                </tr>
                            ) : (
                                filteredData.map((log) => (
                                    activeTab === 'activity' ? (
                                        // Baris Tabel Log Aktivitas
                                        <tr key={log.id}>
                                            <td>{log.id}</td>
                                            <td>{log.waktu}</td>
                                            <td className="username-cell">{log.username}</td>
                                            <td>{log.nip_nis}</td>
                                            <td>{log.role || '-'}</td>
                                            <td><span className={getBadgeClass(log.aktivitas)}>{log.aktivitas}</span></td>
                                            <td style={{ fontSize: '13px', color: '#6b7280' }}>{log.deskripsi || '-'}</td>
                                        </tr>
                                    ) : (
                                        // Baris Tabel Log Access
                                        <tr key={log.id}>
                                            <td>{log.id}</td>
                                            <td>{log.start_login}</td>
                                            <td>{log.end_login}</td>
                                            <td className="username-cell">{log.username}</td>
                                            <td>{log.nip_nis}</td>
                                            <td style={{ whiteSpace: 'nowrap' }}>{log.role || '-'}</td>
                                        </tr>
                                    )
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    );
};

export default ActivityLogDashboard;
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

    // Filter Logic untuk Tab Aktif
    const getFilteredData = () => {
        const sourceData = activeTab === 'activity' ? activityLogs : accessLogs;
        
        return sourceData.filter(log => {
            const strAktivitas = log.aktivitas || '';
            const strNama = log.username || log.nama_asli || '';
            const strNip = log.nip_nis || '';

            const matchSearch = 
                strAktivitas.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strNip.toLowerCase().includes(searchTerm.toLowerCase()) ||
                strNama.toLowerCase().includes(searchTerm.toLowerCase());
                
            const matchRole = roleFilter === 'All' || log.role === roleFilter;

            return matchSearch && matchRole;
        });
    };

    const filteredData = getFilteredData();

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
                    <select 
                        className="log-select" 
                        value={roleFilter} 
                        onChange={(e) => setRoleFilter(e.target.value)}
                    >
                        <option value="All">Semua Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Bendahara">Bendahara</option>
                        <option value="Kepala Sekolah">Kepala Sekolah</option>
                        <option value="Waka">Waka</option>
                        <option value="PIC / Guru">PIC / Guru</option>
                        <option value="TPM / Tim Penjaminan Mutu">TPM / Tim Penjaminan Mutu</option>
                        <option value="Yayasan">Yayasan</option>
                        <option value="Siswa / Orang Tua">Siswa / Orang Tua</option>
                    </select>
                    <input 
                        type="text" 
                        placeholder="Cari NIP, Nama, Aktivitas..." 
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
                    onClick={() => setActiveTab('activity')}
                >
                    Log Aktivitas Data
                </button>
                <button 
                    className={`log-tab-btn ${activeTab === 'access' ? 'active' : ''}`}
                    onClick={() => setActiveTab('access')}
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
                                            {/* Asumsi API activity log me-return field 'deskripsi' */}
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
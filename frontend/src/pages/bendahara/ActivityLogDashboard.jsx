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

    // STATE PAGINASI: Sekarang pakai 7 data dengan aman!
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 7; 

    useEffect(() => {
        if (activeTab === 'activity') {
            fetchActivityLogs();
        } else {
            fetchAccessLogs();
        }
    }, [activeTab]);

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
        setCurrentPage(1);
    };

    // Helper memecah waktu string jadi 2 baris agar hemat tempat
    const renderWaktuDuaBaris = (waktuStr) => {
        if (!waktuStr || waktuStr === '-') return '-';
        const parts = waktuStr.split(' ');
        if (parts.length < 2) return waktuStr; 
        
        return (
            <div style={{ display: 'inline-block', textAlign: 'center', lineHeight: '1.15' }}>
                <div style={{ fontWeight: '500', whiteSpace: 'nowrap' }}>{parts[0]}</div>
                <div style={{ fontSize: '11px', color: '#6b7280', whiteSpace: 'nowrap' }}>{parts[1]}</div>
            </div>
        );
    };

    // Filter Logic Universal
    const getFilteredData = () => {
        const sourceData = activeTab === 'activity' ? activityLogs : accessLogs;
        
        return sourceData.filter(log => {
            const strAktivitas = log.aktivitas || '';
            const strNama = log.username || log.nama_asli || '';
            const strNip = log.nip_nis || '';
            
            const strWaktu = log.waktu || log.event_time || '';
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

    // Ekstraksi Role Dinamis
    const currentSourceData = activeTab === 'activity' ? activityLogs : accessLogs;
    const uniqueRoles = ['All', ...new Set(currentSourceData.map(item => item.role).filter(Boolean))];

    // Logika Paginasi
    const totalData = filteredData.length;
    const totalPages = Math.ceil(totalData / itemsPerPage);
    const indexOfLast = currentPage * itemsPerPage;
    const indexOfFirst = indexOfLast - itemsPerPage;
    const currentRows = filteredData.slice(indexOfFirst, indexOfLast);
    const startData = totalData === 0 ? 0 : indexOfFirst + 1;
    const endData = Math.min(indexOfLast, totalData);

    const changePage = (page) => {
        setCurrentPage(page);
    };

    const getBadgeClass = (aktivitas) => {
        if (!aktivitas) return 'badge';
        const act = aktivitas.toUpperCase();
        if (act.includes('INSERT') || act.includes('CREATE')) return 'badge badge-created';
        if (act.includes('UPDATE')) return 'badge badge-updated';
        if (act.includes('DELETE')) return 'badge badge-deleted';
        return 'badge badge-default';
    };

    return (
        <div className="log-page-wrapper" style={{ maxWidth: '100%', boxSizing: 'border-box' }}>
            
            {/* Bagian Header */}
            <div className="log-header-row">
                <h2 className="log-title">Dashboard Log Sistem</h2>
                
                <div className="log-actions">
                    <button className="log-btn-reset" onClick={handleReset}>Reset</button>
                    
                    <select 
                        className="log-select" 
                        value={roleFilter} 
                        onChange={(e) => {
                            setRoleFilter(e.target.value);
                            setCurrentPage(1);
                        }}
                    >
                        {uniqueRoles.map((role, index) => (
                            <option key={index} value={role}>
                                {role === 'All' ? 'Semua Role' : role}
                            </option>
                        ))}
                    </select>

                    <input 
                        type="text" 
                        placeholder="Cari NIP, Nama, Aktivitas, Waktu..." 
                        className="log-input"
                        value={searchTerm}
                        onChange={(e) => {
                            setSearchTerm(e.target.value);
                            setCurrentPage(1);
                        }}
                    />
                </div>
            </div>

            {/* TAB NAVIGATION */}
            <div className="log-tabs-container">
                <button 
                    className={`log-tab-btn ${activeTab === 'activity' ? 'active' : ''}`}
                    onClick={() => { 
                        setActiveTab('activity'); 
                        setRoleFilter('All'); 
                        setCurrentPage(1); 
                    }}
                >
                    Log Aktivitas Data
                </button>
                <button 
                    className={`log-tab-btn ${activeTab === 'access' ? 'active' : ''}`}
                    onClick={() => { 
                        setActiveTab('access'); 
                        setRoleFilter('All'); 
                        setCurrentPage(1); 
                    }}
                >
                    Log Session Login
                </button>
            </div>

            {/* Box Putih Utama: Margin bawah & spasi pembungkus diperketat sedikit */}
            <div className="log-content-box" style={{ width: '100%', overflow: 'hidden', marginBottom: '5px' }}>
                <div className="log-table-wrapper" style={{ overflowX: 'auto', width: '100%' }}>
                    <table className="log-table" style={{ width: '100%' }}>
                        <thead>
                            {activeTab === 'activity' ? (
                                <tr>
                                    <th style={{ width: '5%', textAlign: 'center', padding: '8px 10px' }}>ID</th>
                                    <th style={{ width: '15%', textAlign: 'center', padding: '8px 10px' }}>WAKTU</th>
                                    <th style={{ width: '15%', padding: '8px 10px' }}>USERNAME</th>
                                    <th style={{ width: '15%', padding: '8px 10px' }}>NIP/NIS</th>
                                    <th style={{ width: '12%', padding: '8px 10px' }}>ROLE</th>
                                    <th style={{ width: '13%', padding: '8px 10px' }}>AKTIVITAS</th>
                                    <th style={{ width: '25%', padding: '8px 10px' }}>DESKRIPSI</th>
                                </tr>
                            ) : (
                                <tr>
                                    <th style={{ width: '5%', textAlign: 'center', padding: '8px 10px' }}>ID LOG</th>
                                    <th style={{ width: '20%', textAlign: 'center', padding: '8px 10px' }}>WAKTU LOGIN</th>
                                    <th style={{ width: '20%', textAlign: 'center', padding: '8px 10px' }}>WAKTU LOGOUT</th>
                                    <th style={{ width: '25%', padding: '8px 10px' }}>USERNAME</th>
                                    <th style={{ width: '15%', padding: '8px 10px' }}>NIP/NIS</th>
                                    <th style={{ width: '15%', padding: '8px 10px' }}>ROLE</th>
                                </tr>
                            )}
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan={activeTab === 'activity' ? 7 : 6} className="empty-state" style={{ textAlign: 'center', padding: '15px' }}>
                                        Memuat data log...
                                    </td>
                                </tr>
                            ) : currentRows.length === 0 ? (
                                <tr>
                                    <td colSpan={activeTab === 'activity' ? 7 : 6} className="empty-state" style={{ fontWeight: '500', color: '#4b5563', textAlign: 'center', padding: '15px' }}>
                                        Data log masih kosong atau tidak ditemukan.
                                    </td>
                                </tr>
                            ) : (
                                currentRows.map((log) => (
                                    activeTab === 'activity' ? (
                                        // Baris Tabel Log Aktivitas (padding diset sedikit lebih padat)
                                        <tr key={log.id}>
                                            <td style={{ textAlign: 'center', padding: '6px 10px' }}>{log.id}</td>
                                            <td style={{ textAlign: 'center', padding: '6px 10px' }}>
                                                {renderWaktuDuaBaris(log.waktu || log.event_time)}
                                            </td>
                                            <td className="username-cell" style={{ padding: '6px 10px' }}>{log.username}</td>
                                            <td style={{ padding: '6px 10px' }}>{log.nip_nis}</td>
                                            <td style={{ whiteSpace: 'nowrap', padding: '6px 10px' }}>{log.role || '-'}</td>
                                            <td style={{ padding: '6px 10px' }}>
                                                <span className={getBadgeClass(log.aktivitas)} style={{ whiteSpace: 'nowrap' }}>
                                                    {log.aktivitas}
                                                </span>
                                            </td>
                                            <td style={{ fontSize: '13px', color: '#4b5563', padding: '6px 10px' }}>{log.deskripsi || '-'}</td>
                                        </tr>
                                    ) : (
                                        // Baris Tabel Log Access
                                        <tr key={log.id}>
                                            <td style={{ textAlign: 'center', padding: '6px 10px' }}>{log.id}</td>
                                            <td style={{ textAlign: 'center', padding: '6px 10px' }}>{renderWaktuDuaBaris(log.start_login)}</td>
                                            <td style={{ textAlign: 'center', padding: '6px 10px' }}>{renderWaktuDuaBaris(log.end_login)}</td>
                                            <td className="username-cell" style={{ padding: '6px 10px' }}>{log.username}</td>
                                            <td style={{ padding: '6px 10px' }}>{log.nip_nis}</td>
                                            <td style={{ whiteSpace: 'nowrap', padding: '6px 10px' }}>{log.role || '-'}</td>
                                        </tr>
                                    )
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* KONTROL PAGINASI: marginTop ditarik naik dari 15px jadi 8px biar pas masuk viewport */}
            <div 
                className="pagination-wrapper" 
                style={{ 
                    marginTop: "8px", 
                    display: "grid", 
                    gridTemplateColumns: "1fr auto 1fr", 
                    alignItems: "center" 
                }}
            >
                <div className="pagination-info" style={{ fontSize: "14px", color: "#6b7280" }}>
                    Menampilkan {startData} - {endData} dari {totalData} data
                </div>
                
                <div className="pagination" style={{ display: "flex", gap: "5px", justifyContent: "center" }}>
                    <button
                        className="page-btn"
                        onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                        disabled={currentPage === 1}
                    >
                        <i className="bi bi-chevron-left"></i>
                    </button>
                    
                    {Array.from({ length: totalPages }, (_, i) => (
                        <button
                            key={i + 1}
                            onClick={() => changePage(i + 1)}
                            className={`page-btn ${currentPage === i + 1 ? "active" : ""}`}
                        >
                            {i + 1}
                        </button>
                    ))}

                    <button
                        className="page-btn"
                        onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                        disabled={currentPage === totalPages || totalPages === 0}
                    >
                        <i className="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div></div>
            </div>

        </div>
    );
};

export default ActivityLogDashboard;
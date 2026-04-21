import React, { useState, useEffect } from 'react';
import '../../styles/bendahara/ActivityLogDashboard.css'; 

const ActivityLogDashboard = () => {
    const [logs, setLogs] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState('All');
    const [loading, setLoading] = useState(true);

    // 1. Ambil data dari API Laravel
    useEffect(() => {
        fetchLogs();
    }, []);

    const fetchLogs = async () => {
        setLoading(true);
        try {
            // Sesuaikan URL dengan API backend-mu
            const response = await fetch('http://localhost:8000/api/activity-logs');
            const result = await response.json();
            
            if (result.success) {
                setLogs(result.data);
            }
        } catch (error) {
            console.error("Gagal mengambil data log:", error);
        } finally {
            setLoading(false);
        }
    };

    // 2. Fungsi Reset Filter
    const handleReset = () => {
        setSearchTerm('');
        setRoleFilter('All');
    };

    // 3. Logika Filter (Search & Role)
    const filteredLogs = logs.filter(log => {
        const matchSearch = 
            (log.aktivitas && log.aktivitas.toLowerCase().includes(searchTerm.toLowerCase())) ||
            (log.nip_nis && log.nip_nis.toLowerCase().includes(searchTerm.toLowerCase())) ||
            (log.username && log.username.toLowerCase().includes(searchTerm.toLowerCase()));
            
        const matchRole = roleFilter === 'All' || log.role === roleFilter;

        return matchSearch && matchRole;
    });

    // 4. Helper warna badge aktivitas
    const getBadgeClass = (aktivitas) => {
        if (!aktivitas) return 'badge';
        const act = aktivitas.toUpperCase();
        if (act.includes('INSERT') || act.includes('CREATE')) return 'badge badge-created';
        if (act.includes('UPDATE')) return 'badge badge-updated';
        if (act.includes('DELETE')) return 'badge badge-deleted';
        return 'badge';
    };

    return (
        <div className="log-page-wrapper">
            
            {/* Bagian Header & Controls (Di luar box putih) */}
            <div className="log-header-row">
                <h2 className="log-title">Dashboard Log Aktifitas</h2>
                
                <div className="log-actions">
                    <button className="log-btn-reset" onClick={handleReset}>
                        Reset
                    </button>
                    
                    <select 
                        className="log-select"
                        value={roleFilter}
                        onChange={(e) => setRoleFilter(e.target.value)}
                    >
                        <option value="All">Semua Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Guru">Guru</option>
                        <option value="Kepsek">Kepala Sekolah</option>
                        <option value="Bendahara">Bendahara</option>
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

            {/* Box Putih Utama (Hanya membungkus tabel) */}
            <div className="log-content-box">
                <div className="log-table-wrapper">
                    <table className="log-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>WAKTU</th>
                                <th>USERNAME</th>
                                <th>NIP/NIS</th>
                                <th>ROLE</th>
                                <th>AKTIVITAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="6" className="empty-state">Memuat data log...</td>
                                </tr>
                            ) : filteredLogs.length > 0 ? (
                                filteredLogs.map((log) => (
                                    <tr key={log.id}>
                                        <td>{log.id}</td>
                                        <td>{log.waktu}</td>
                                        <td className="username-cell">{log.username}</td>
                                        <td>{log.nip_nis}</td>
                                        <td>{log.role || '-'}</td>
                                        <td>
                                            <span className={getBadgeClass(log.aktivitas)}>
                                                {log.aktivitas}
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="6" className="empty-state">Tidak ada aktivitas ditemukan</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    );
};

export default ActivityLogDashboard;
// src/pages/ActivityLogDashboard.jsx
import React, { useState, useEffect } from 'react';
import '../../style/ActivityLogDashboard.css'; // Mengarah ke folder style di level yang sama

const ActivityLogDashboard = () => {
    const [logs, setLogs] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [roleFilter, setRoleFilter] = useState('All');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchLogs();
    }, []);

    const fetchLogs = async () => {
        try {
            // Ganti URL ini dengan endpoint API localhost kamu
            const response = await fetch('http://localhost:8000/api/activity-logs');
            const result = await response.json();
            
            // Asumsi API mengembalikan { success: true, data: [...] }
            if (result.success) {
                setLogs(result.data);
            }
        } catch (error) {
            console.error("Gagal mengambil data log:", error);
        } finally {
            setLoading(false);
        }
    };

    const filteredLogs = logs.filter(log => {
        const matchSearch = 
            (log.aktivitas && log.aktivitas.toLowerCase().includes(searchTerm.toLowerCase())) ||
            (log.nip_nis && log.nip_nis.toLowerCase().includes(searchTerm.toLowerCase())) ||
            (log.username && log.username.toLowerCase().includes(searchTerm.toLowerCase()));
            
        const matchRole = roleFilter === 'All' || log.role === roleFilter;

        return matchSearch && matchRole;
    });

    // Helper untuk mapping class CSS dari status aktivitas
    const getBadgeClass = (aktivitas) => {
        if (!aktivitas) return 'badge badge-default';
        if (aktivitas.toUpperCase().startsWith('CREATED')) return 'badge badge-created';
        if (aktivitas.toUpperCase().startsWith('UPDATED')) return 'badge badge-updated';
        if (aktivitas.toUpperCase().startsWith('DELETED')) return 'badge badge-deleted';
        return 'badge badge-default';
    };

    return (
        <div className="log-dashboard-container">
            <div className="log-dashboard-header">
                <h2>Activity Log System</h2>
                
                <div className="log-controls">
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

            <div className="log-table-wrapper">
                <table className="log-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Waktu</th>
                            <th>Username</th>
                            <th>NIP/NIS</th>
                            <th>Role</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr><td colSpan="6" className="empty-state">Memuat data log...</td></tr>
                        ) : filteredLogs.length > 0 ? (
                            filteredLogs.map((log) => (
                                <tr key={log.id}>
                                    <td>{log.id}</td>
                                    <td>{log.waktu}</td>
                                    <td className="username-cell">{log.username}</td>
                                    <td>{log.nip_nis}</td>
                                    <td>{log.role}</td>
                                    <td>
                                        <span className={getBadgeClass(log.aktivitas)}>
                                            {log.aktivitas}
                                        </span>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr><td colSpan="6" className="empty-state">Tidak ada log ditemukan</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default ActivityLogDashboard;
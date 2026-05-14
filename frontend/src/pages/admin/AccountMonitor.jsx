import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../../api/api';

const AccountMonitor = () => {
    const navigate = useNavigate();

    const [activeTab, setActiveTab] = useState('karyawan');
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');

    const [searchKaryawan, setSearchKaryawan] = useState('');
    const [karyawanList, setKaryawanList] = useState([]);
    const [selectedNip, setSelectedNip] = useState(null);
    const [newKaryawanPassword, setNewKaryawanPassword] = useState('');

    const [searchSiswa, setSearchSiswa] = useState('');
    const [siswaList, setSiswaList] = useState([]);
    const [selectedSiswaId, setSelectedSiswaId] = useState(null);
    const [newSiswaPassword, setNewSiswaPassword] = useState('');

    const fetchKaryawan = async () => {
        setLoading(true);
        setMessage('');
        try {
            const data = await apiFetch('/admin/account/karyawan');
            if (data.success) {
                setKaryawanList(data.data || []);
            }
        } catch (err) {
            setMessage(err.message || 'Gagal memuat data karyawan.');
        } finally {
            setLoading(false);
        }
    };

    const fetchSiswa = async () => {
        setLoading(true);
        setMessage('');
        try {
            const data = await apiFetch(`/tagihan-siswa/siswa-options?search=${encodeURIComponent(searchSiswa)}`);
            if (data.success) {
                setSiswaList(data.data || []);
            }
        } catch (err) {
            setMessage(err.message || 'Gagal memuat data siswa.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (activeTab === 'karyawan') {
            fetchKaryawan();
        }
    }, [activeTab]);

    useEffect(() => {
        if (activeTab === 'siswa') {
            fetchSiswa();
        }
    }, [activeTab, searchSiswa]);

    const handleLogout = async () => {
        try {
            await apiFetch('/logout', { method: 'POST' });
        } catch (_) {
            // Tetap bersihkan sesi lokal meskipun API logout gagal
        } finally {
            localStorage.clear();
            navigate('/login');
        }
    };

    const handleResetKaryawanPassword = async (e) => {
        e.preventDefault();
        if (!selectedNip || !newKaryawanPassword) return;

        try {
            const data = await apiFetch(`/admin/account/karyawan/${selectedNip}/password`, {
                method: 'PUT',
                body: JSON.stringify({ new_password: newKaryawanPassword }),
            });

            if (data.success) {
                alert('Password karyawan berhasil diperbarui.');
                setSelectedNip(null);
                setNewKaryawanPassword('');
            } else {
                alert(data.message || 'Gagal mengubah password karyawan.');
            }
        } catch (err) {
            alert(err.message || 'Kesalahan jaringan.');
        }
    };

    const handleResetSiswaPassword = async (e) => {
        e.preventDefault();
        if (!selectedSiswaId || !newSiswaPassword) return;

        try {
            const data = await apiFetch(`/admin/account/siswa/${selectedSiswaId}/password`, {
                method: 'PUT',
                body: JSON.stringify({ new_password: newSiswaPassword }),
            });

            if (data.success) {
                alert('Password siswa berhasil diperbarui.');
                setSelectedSiswaId(null);
                setNewSiswaPassword('');
            } else {
                alert(data.message || 'Gagal mengubah password siswa.');
            }
        } catch (err) {
            alert(err.message || 'Kesalahan jaringan.');
        }
    };

    const filteredKaryawan = karyawanList.filter((k) => {
        const text = `${k.NIP_KARYAWAN || ''} ${k.NAMA_KARYAWAN || ''} ${k.EMAIL_KARYAWAN || ''}`.toLowerCase();
        return text.includes(searchKaryawan.toLowerCase());
    });

    return (
        <div style={{ padding: '20px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }}>
                <h1 style={{ margin: 0 }}>Pusat Kendali Otentikasi (Super Admin Monitor)</h1>
                <button
                    onClick={handleLogout}
                    style={{ padding: '8px 14px', backgroundColor: '#dc3545', color: '#fff', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                >
                    Logout
                </button>
            </div>

            {message && (
                <div style={{ padding: '10px', backgroundColor: '#f8d7da', color: '#842029', marginBottom: '15px', borderRadius: '4px' }}>
                    {message}
                </div>
            )}

            <div style={{ display: 'flex', gap: '10px', marginBottom: '20px', borderBottom: '2px solid #ccc' }}>
                <button
                    onClick={() => setActiveTab('karyawan')}
                    style={{ padding: '10px 20px', fontWeight: activeTab === 'karyawan' ? 'bold' : 'normal', border: 'none', borderBottom: activeTab === 'karyawan' ? '3px solid #007bff' : 'none', background: 'none', cursor: 'pointer' }}
                >
                    Kendali Akun Karyawan
                </button>
                <button
                    onClick={() => setActiveTab('siswa')}
                    style={{ padding: '10px 20px', fontWeight: activeTab === 'siswa' ? 'bold' : 'normal', border: 'none', borderBottom: activeTab === 'siswa' ? '3px solid #007bff' : 'none', background: 'none', cursor: 'pointer' }}
                >
                    Kendali Akun Siswa/Ortu
                </button>
            </div>

            {activeTab === 'karyawan' && (
                <div>
                    <input
                        type="text"
                        placeholder="Cari NIP / Nama / Email Karyawan..."
                        value={searchKaryawan}
                        onChange={(e) => setSearchKaryawan(e.target.value)}
                        style={{ padding: '8px', width: '360px', borderRadius: '4px', border: '1px solid #ccc', marginBottom: '12px' }}
                    />

                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ backgroundColor: '#f4f4f4', textAlign: 'left' }}>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>NIP</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>Nama</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>Email</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredKaryawan.map((k) => (
                                <tr key={k.NIP_KARYAWAN} style={{ borderBottom: '1px solid #eee' }}>
                                    <td style={{ padding: '10px' }}>{k.NIP_KARYAWAN}</td>
                                    <td style={{ padding: '10px' }}>{k.NAMA_KARYAWAN || '-'}</td>
                                    <td style={{ padding: '10px' }}>{k.EMAIL_KARYAWAN || '-'}</td>
                                    <td style={{ padding: '10px' }}>
                                        {selectedNip === k.NIP_KARYAWAN ? (
                                            <form onSubmit={handleResetKaryawanPassword} style={{ display: 'flex', gap: '6px' }}>
                                                <input
                                                    type="text"
                                                    placeholder="Password baru..."
                                                    value={newKaryawanPassword}
                                                    onChange={(e) => setNewKaryawanPassword(e.target.value)}
                                                    required
                                                    style={{ padding: '4px' }}
                                                />
                                                <button type="submit" style={{ background: '#007bff', color: '#fff', border: 'none', padding: '4px 8px' }}>Simpan</button>
                                                <button type="button" onClick={() => setSelectedNip(null)} style={{ background: '#6c757d', color: '#fff', border: 'none', padding: '4px 8px' }}>Batal</button>
                                            </form>
                                        ) : (
                                            <button
                                                onClick={() => { setSelectedNip(k.NIP_KARYAWAN); setNewKaryawanPassword(''); }}
                                                style={{ padding: '5px 10px', backgroundColor: '#ffc107', border: 'none', borderRadius: '3px', cursor: 'pointer', fontSize: '0.85em' }}
                                            >
                                                Reset Password
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {loading && <p style={{ marginTop: '10px' }}>Memuat data...</p>}
                </div>
            )}

            {activeTab === 'siswa' && (
                <div>
                    <input
                        type="text"
                        placeholder="Cari Nama / NISN Siswa..."
                        value={searchSiswa}
                        onChange={(e) => setSearchSiswa(e.target.value)}
                        style={{ padding: '8px', width: '300px', borderRadius: '4px', border: '1px solid #ccc', marginBottom: '12px' }}
                    />

                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ backgroundColor: '#f4f4f4', textAlign: 'left' }}>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>ID Siswa</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>NISN</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>Nama Lengkap</th>
                                <th style={{ padding: '10px', borderBottom: '1px solid #ddd' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {siswaList.map((s) => (
                                <tr key={s.ID_SISWA_TETAP} style={{ borderBottom: '1px solid #eee' }}>
                                    <td style={{ padding: '10px' }}>{s.ID_SISWA_TETAP}</td>
                                    <td style={{ padding: '10px' }}>{s.NISN_SISWA || '-'}</td>
                                    <td style={{ padding: '10px' }}>{s.NAMA_SISWA_TETAP}</td>
                                    <td style={{ padding: '10px' }}>
                                        {selectedSiswaId === s.ID_SISWA_TETAP ? (
                                            <form onSubmit={handleResetSiswaPassword} style={{ display: 'flex', gap: '6px' }}>
                                                <input
                                                    type="text"
                                                    placeholder="Password baru..."
                                                    value={newSiswaPassword}
                                                    onChange={(e) => setNewSiswaPassword(e.target.value)}
                                                    required
                                                    style={{ padding: '4px' }}
                                                />
                                                <button type="submit" style={{ background: '#007bff', color: '#fff', border: 'none', padding: '4px 8px' }}>Simpan</button>
                                                <button type="button" onClick={() => setSelectedSiswaId(null)} style={{ background: '#6c757d', color: '#fff', border: 'none', padding: '4px 8px' }}>Batal</button>
                                            </form>
                                        ) : (
                                            <button
                                                onClick={() => { setSelectedSiswaId(s.ID_SISWA_TETAP); setNewSiswaPassword(''); }}
                                                style={{ padding: '5px 10px', backgroundColor: '#ffc107', border: 'none', borderRadius: '3px', cursor: 'pointer', fontSize: '0.85em' }}
                                            >
                                                Reset Password
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {loading && <p style={{ marginTop: '10px' }}>Memuat data...</p>}
                </div>
            )}
        </div>
    );
};

export default AccountMonitor;


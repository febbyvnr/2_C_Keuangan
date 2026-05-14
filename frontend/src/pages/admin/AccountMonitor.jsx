import React, { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../../api/api";
import "../../styles/admin/AccountMonitor.css";

const AccountMonitor = () => {
  const navigate = useNavigate();

  const [activeTab, setActiveTab] = useState("karyawan");
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);
  const [toast, setToast] = useState({ show: false, type: "success", text: "" });

  const [searchKaryawan, setSearchKaryawan] = useState("");
  const [karyawanList, setKaryawanList] = useState([]);
  const [roleOptions, setRoleOptions] = useState([]);
  const [roleDraftByNip, setRoleDraftByNip] = useState({});
  const [selectedNip, setSelectedNip] = useState(null);
  const [newKaryawanPassword, setNewKaryawanPassword] = useState("");

  const [searchSiswa, setSearchSiswa] = useState("");
  const [siswaList, setSiswaList] = useState([]);
  const [selectedSiswaId, setSelectedSiswaId] = useState(null);
  const [newSiswaPassword, setNewSiswaPassword] = useState("");

  const showToast = (text, type = "success") => {
    setToast({ show: true, type, text });
    setTimeout(() => {
      setToast({ show: false, type: "success", text: "" });
    }, 2600);
  };

  const fetchKaryawan = async () => {
    setLoading(true);
    setMessage("");
    try {
      const [karyawanRes, roleRes] = await Promise.all([
        apiFetch("/admin/account/karyawan"),
        apiFetch("/admin/account/roles"),
      ]);

      if (karyawanRes.success) {
        setKaryawanList(karyawanRes.data || []);
      }
      if (roleRes.success) {
        setRoleOptions(roleRes.data || []);
      }
    } catch (err) {
      setMessage(err.message || "Gagal memuat data karyawan.");
    } finally {
      setLoading(false);
    }
  };

  const fetchSiswa = async () => {
    setLoading(true);
    setMessage("");
    try {
      const data = await apiFetch(
        `/tagihan-siswa/siswa-options?search=${encodeURIComponent(searchSiswa)}`
      );
      if (data.success) {
        setSiswaList(data.data || []);
      }
    } catch (err) {
      setMessage(err.message || "Gagal memuat data siswa.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (activeTab === "karyawan") {
      fetchKaryawan();
    }
  }, [activeTab]);

  useEffect(() => {
    if (activeTab === "siswa") {
      fetchSiswa();
    }
  }, [activeTab, searchSiswa]);

  const handleLogout = async () => {
    try {
      await apiFetch("/logout", { method: "POST" });
    } catch (_) {
      // ignore
    } finally {
      localStorage.clear();
      navigate("/login");
    }
  };

  const handleAssignRole = async (nip) => {
    const roleId = roleDraftByNip[nip];
    if (!roleId) return;
    const selectedRole = roleOptions.find((r) => String(r.ID_JABATAN) === String(roleId));

    try {
      const data = await apiFetch(`/admin/account/karyawan/${nip}/role`, {
        method: "PUT",
        body: JSON.stringify({
          ID_JABATAN: Number(roleId),
          DESKRIPSI_JABATAN: selectedRole?.DESKRIPSI_JABATAN || "",
        }),
      });

      if (data.success) {
        showToast("Role karyawan berhasil diperbarui.", "success");
        fetchKaryawan();
      } else {
        showToast(data.message || "Gagal assign role.", "error");
      }
    } catch (err) {
      showToast(err.message || "Gagal assign role.", "error");
    }
  };

  const handleRevokeRole = async (nip) => {
    const roleId = roleDraftByNip[nip];
    if (!roleId) {
      setMessage("Pilih role dulu untuk dicabut.");
      showToast("Pilih role dulu untuk dicabut.", "error");
      return;
    }

    const target = karyawanList.find((k) => k.NIP_KARYAWAN === nip);
    if (!target || !target.SYSTEM_ROLE || target.SYSTEM_ROLE.toLowerCase().includes("belum punya role")) {
      setMessage("Karyawan ini belum punya role, jadi tidak ada role yang bisa dicabut.");
      return;
    }

    try {
      const data = await apiFetch(`/admin/account/karyawan/${nip}/role`, {
        method: "DELETE",
        body: JSON.stringify({ ID_JABATAN: Number(roleId) }),
      });

      if (data.success) {
        showToast("Role karyawan berhasil dicabut.", "success");
        fetchKaryawan();
      } else {
        showToast(data.message || "Gagal cabut role.", "error");
      }
    } catch (err) {
      showToast(err.message || "Gagal cabut role.", "error");
    }
  };

  const handleResetKaryawanPassword = async (e) => {
    e.preventDefault();
    if (!selectedNip || !newKaryawanPassword) return;

    try {
      const data = await apiFetch(`/admin/account/karyawan/${selectedNip}/password`, {
        method: "PUT",
        body: JSON.stringify({ new_password: newKaryawanPassword }),
      });
      if (data.success) {
        showToast("Password karyawan berhasil diperbarui.", "success");
        setSelectedNip(null);
        setNewKaryawanPassword("");
      } else {
        showToast(data.message || "Gagal mengubah password karyawan.", "error");
      }
    } catch (err) {
      showToast(err.message || "Kesalahan jaringan.", "error");
    }
  };

  const handleResetSiswaPassword = async (e) => {
    e.preventDefault();
    if (!selectedSiswaId || !newSiswaPassword) return;

    try {
      const data = await apiFetch(`/admin/account/siswa/${selectedSiswaId}/password`, {
        method: "PUT",
        body: JSON.stringify({ new_password: newSiswaPassword }),
      });
      if (data.success) {
        showToast("Password siswa berhasil diperbarui.", "success");
        setSelectedSiswaId(null);
        setNewSiswaPassword("");
      } else {
        showToast(data.message || "Gagal mengubah password siswa.", "error");
      }
    } catch (err) {
      showToast(err.message || "Kesalahan jaringan.", "error");
    }
  };

  const filteredKaryawan = useMemo(() => {
    return karyawanList
      .filter((k) => {
      const text = `${k.NIP_KARYAWAN || ""} ${k.NAMA_KARYAWAN || ""} ${
        k.EMAIL_KARYAWAN || ""
      } ${k.SYSTEM_ROLE || ""}`.toLowerCase();
      return text.includes(searchKaryawan.toLowerCase());
      })
      .sort((a, b) => String(a.NIP_KARYAWAN || "").localeCompare(String(b.NIP_KARYAWAN || "")));
  }, [karyawanList, searchKaryawan]);

  const roleBadgeClass = (roleText = "") => {
    if (!roleText || roleText.toLowerCase().includes("belum")) return "am-badge am-badge-none";
    if (roleText.toLowerCase().includes("bendahara")) return "am-badge am-badge-bend";
    if (roleText.toLowerCase().includes("kepala sekolah")) return "am-badge am-badge-kepsek";
    return "am-badge am-badge-default";
  };

  return (
    <div className="am-page">
      <div className="am-header-row">
        <h2 className="am-title">Account Control</h2>
        <button className="am-btn-danger" onClick={() => setShowLogoutConfirm(true)}>
          Logout
        </button>
      </div>

      {message && <div className="am-alert">{message}</div>}

      <div className="am-tabs">
        <button
          className={`am-tab ${activeTab === "karyawan" ? "active" : ""}`}
          onClick={() => setActiveTab("karyawan")}
        >
          Karyawan
        </button>
        <button
          className={`am-tab ${activeTab === "siswa" ? "active" : ""}`}
          onClick={() => setActiveTab("siswa")}
        >
          Siswa
        </button>
      </div>

      <div className="am-content">
        {activeTab === "karyawan" && (
          <>
            <div className="am-controls">
              <input
                className="am-input"
                type="text"
                placeholder="Cari NIP / Nama / Email / Role..."
                value={searchKaryawan}
                onChange={(e) => setSearchKaryawan(e.target.value)}
              />
            </div>

            <div className="am-table-wrap">
              <table className="am-table">
                <thead>
                  <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role Sistem</th>
                    <th>Assign Role</th>
                    <th>Aksi Password</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredKaryawan.map((k) => (
                    <tr key={k.NIP_KARYAWAN}>
                      <td>{k.NIP_KARYAWAN}</td>
                      <td>{k.NAMA_KARYAWAN || "-"}</td>
                      <td>{k.EMAIL_KARYAWAN || "-"}</td>
                      <td>
                        <span className={roleBadgeClass(k.SYSTEM_ROLE)}>{k.SYSTEM_ROLE || "Belum Punya Role"}</span>
                      </td>
                      <td>
                        <div className="am-inline">
                          <select
                            className="am-select"
                            value={roleDraftByNip[k.NIP_KARYAWAN] || ""}
                            onChange={(e) =>
                              setRoleDraftByNip((prev) => ({
                                ...prev,
                                [k.NIP_KARYAWAN]: e.target.value,
                              }))
                            }
                          >
                            <option value="">Pilih role...</option>
                            {roleOptions.map((r) => (
                              <option key={r.ID_JABATAN} value={r.ID_JABATAN}>
                                {r.DESKRIPSI_JABATAN}
                              </option>
                            ))}
                          </select>
                          <button
                            className="am-btn-primary"
                            onClick={() => handleAssignRole(k.NIP_KARYAWAN)}
                          >
                            Assign
                          </button>
                          <button
                            className="am-btn-danger-soft"
                            onClick={() => handleRevokeRole(k.NIP_KARYAWAN)}
                          >
                            Cabut
                          </button>
                        </div>
                      </td>
                      <td>
                        {selectedNip === k.NIP_KARYAWAN ? (
                          <form onSubmit={handleResetKaryawanPassword} className="am-inline">
                            <input
                              className="am-input-sm"
                              type="text"
                              placeholder="Masukkan password baru"
                              value={newKaryawanPassword}
                              onChange={(e) => setNewKaryawanPassword(e.target.value)}
                              required
                            />
                            <button className="am-btn-primary" type="submit">
                              Simpan Password
                            </button>
                            <button className="am-btn-muted" type="button" onClick={() => setSelectedNip(null)}>
                              Batal
                            </button>
                          </form>
                        ) : (
                          <button
                            className="am-btn-warning-strong"
                            onClick={() => {
                              setSelectedNip(k.NIP_KARYAWAN);
                              setNewKaryawanPassword("");
                            }}
                          >
                            Ganti Password
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </>
        )}

        {activeTab === "siswa" && (
          <>
            <div className="am-controls">
              <input
                className="am-input"
                type="text"
                placeholder="Cari Nama / NISN siswa..."
                value={searchSiswa}
                onChange={(e) => setSearchSiswa(e.target.value)}
              />
            </div>

            <div className="am-table-wrap">
              <table className="am-table">
                <thead>
                  <tr>
                    <th>ID Siswa</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Aksi Password</th>
                  </tr>
                </thead>
                <tbody>
                  {siswaList.map((s) => (
                    <tr key={s.ID_SISWA_TETAP}>
                      <td>{s.ID_SISWA_TETAP}</td>
                      <td>{s.NISN_SISWA || "-"}</td>
                      <td>{s.NAMA_SISWA_TETAP}</td>
                      <td>
                        {selectedSiswaId === s.ID_SISWA_TETAP ? (
                          <form onSubmit={handleResetSiswaPassword} className="am-inline">
                            <input
                              className="am-input-sm"
                              type="text"
                              placeholder="Password baru..."
                              value={newSiswaPassword}
                              onChange={(e) => setNewSiswaPassword(e.target.value)}
                              required
                            />
                            <button className="am-btn-primary" type="submit">
                              Simpan
                            </button>
                            <button className="am-btn-muted" type="button" onClick={() => setSelectedSiswaId(null)}>
                              Batal
                            </button>
                          </form>
                        ) : (
                          <button
                            className="am-btn-warning"
                            onClick={() => {
                              setSelectedSiswaId(s.ID_SISWA_TETAP);
                              setNewSiswaPassword("");
                            }}
                          >
                            Reset Password
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </>
        )}
        {loading && <div className="am-loading">Memuat data...</div>}
      </div>

      {showLogoutConfirm && (
        <div className="am-modal-backdrop">
          <div className="am-modal">
            <h3>Konfirmasi Logout</h3>
            <p>Yakin mau keluar dari sesi Super Admin?</p>
            <div className="am-modal-actions">
              <button className="am-btn-muted" onClick={() => setShowLogoutConfirm(false)}>
                No
              </button>
              <button className="am-btn-danger" onClick={handleLogout}>
                Yes
              </button>
            </div>
          </div>
        </div>
      )}

      {toast.show && (
        <div className={`am-toast ${toast.type === "error" ? "error" : "success"}`}>
          {toast.text}
        </div>
      )}
    </div>
  );
};

export default AccountMonitor;

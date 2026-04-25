import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../api/api'; 
import logoSekolah from '../assets/logo.png'; 

export default function Login() {
    const [nip, setNip] = useState('');
    const [password, setPassword] = useState('');
    const [errorMsg, setErrorMsg] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false); 
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setErrorMsg('');

        try {
            const response = await apiFetch('/login', {
                method: 'POST',
                body: JSON.stringify({ nip, password })
            });

            localStorage.setItem('token', response.data.access_token);
            localStorage.setItem('roles', JSON.stringify(response.data.roles));
            localStorage.setItem('user', JSON.stringify(response.data.user));

            const roles = response.data.roles || [];
            const user = response.data.user || {};

            const roleText = [
                ...roles,
                user.JABATAN_FUNGSIONAL,
                user.GOLONGAN_KARYAWAN,
                user.NAMA_KARYAWAN
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();

            console.log("ROLES:", roles);
            console.log("USER:", user);
            console.log("ROLE TEXT:", roleText);

            if (roleText.includes("bendahara") || roleText.includes("keuangan")) {
                navigate("/bendahara/dashboard");
            } else if (roleText.includes("guru")) {
                navigate("/pic/guru");
            } else if (roleText.includes("waka")) {
                navigate("/waka");
            } else if (roleText.includes("yayasan")) {
                navigate("/yayasan/dashboard");
            } else {
                console.log("Role tidak dikenali:", roleText);
                setErrorMsg("Role akun belum dikenali.");
            }
        } catch (error) {
            setErrorMsg(error.status === 401 ? 'NIP atau Password salah!' : (error.message || 'Gagal terhubung ke server.'));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div style={styles.container}>
            <div style={styles.card}>
                <div style={styles.header}>
                    <div style={{ textAlign: 'center', marginBottom: '15px' }}>
                        <img 
                            src={logoSekolah} 
                            alt="Logo SMK Bopkri Dua"
                            style={{ width: '80px', height: 'auto' }}
                        />
                    </div>
                    <div style={{ textAlign: 'center' }}>
                        <h2 style={styles.title}>Selamat Datang di SIBOKU</h2>
                        <p style={styles.subtitle}>Sistem Informasi Keuangan SMK 2 BOPKRI</p>
                    </div>
                </div>
                
                <div style={styles.formContainer}>
                    {errorMsg && <div style={styles.errorAlert}>{errorMsg}</div>}
                    
                    <form onSubmit={handleLogin}>
                        <div style={styles.inputGroup}>
                            <label style={styles.label}>NIP</label>
                            <input 
                                type="text" 
                                value={nip} 
                                onChange={(e) => setNip(e.target.value)} 
                                required 
                                style={styles.input}
                                placeholder="Masukkan NIP Anda"
                            />
                        </div>
                        
                        <div style={styles.inputGroup}>
                            <label style={styles.label}>Password</label>
                            <div style={styles.passwordWrapper}>
                                <input 
                                    type={showPassword ? "text" : "password"} 
                                    value={password} 
                                    onChange={(e) => setPassword(e.target.value)} 
                                    required 
                                    style={styles.inputPassword}
                                    placeholder="Masukkan Password"
                                />
                                <button 
                                    type="button" 
                                    onClick={() => setShowPassword(!showPassword)}
                                    style={styles.eyeButton}
                                >
                                    <i className={`fa-solid ${showPassword ? "fa-eye-slash" : "fa-eye"}`}></i>
                                </button>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            disabled={isLoading}
                            style={{
                                ...styles.submitButton,
                                opacity: isLoading ? 0.7 : 1,
                                cursor: isLoading ? 'not-allowed' : 'pointer'
                            }}
                        >
                            {isLoading ? 'Memproses...' : 'Login'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}

// --- CSS IN JS (Supaya styling langsung nempel dan kebal Dark Mode Chrome) ---
const styles = {
    container: {
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        minHeight: '100vh',
        backgroundColor: '#e9ecef', 
        fontFamily: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif'
    },
    card: {
        background: '#ffffff',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
        width: '100%',
        maxWidth: '450px',
        overflow: 'hidden',
        color: '#333' 
    },
    header: {
        backgroundColor: '#f8f9fa',
        padding: '20px',
        borderBottom: '1px solid #e0e0e0',
        textAlign: 'left'
    },
    title: {
        margin: 0,
        fontSize: '1.25rem',
        fontWeight: 'bold',
        color: '#212529' 
    },
    subtitle: {
        margin: '5px 0 0 0',
        fontSize: '0.85rem',
        color: '#6c757d'
    },
    formContainer: {
        padding: '24px 30px'
    },
    inputGroup: {
        marginBottom: '20px'
    },
    label: {
        display: 'block',
        marginBottom: '8px',
        fontWeight: '600',
        fontSize: '0.9rem',
        color: '#495057' 
    },
    input: {
        width: '100%',
        padding: '10px 12px',
        fontSize: '1rem',
        border: '1px solid #ced4da',
        borderRadius: '4px',
        boxSizing: 'border-box',
        backgroundColor: '#ffffff', 
        color: '#212529', 
        colorScheme: 'light' 
    },
    passwordWrapper: {
        position: 'relative',
        display: 'flex',
        alignItems: 'center'
    },
    inputPassword: {
        width: '100%',
        padding: '10px 40px 10px 12px', 
        fontSize: '1rem',
        border: '1px solid #ced4da',
        borderRadius: '4px',
        boxSizing: 'border-box',
        backgroundColor: '#ffffff', 
        color: '#212529',
        colorScheme: 'light'
    },
    eyeButton: {
        position: 'absolute',
        right: '10px',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        fontSize: '1.1rem',
        color: '#6c757d',
        padding: 0,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
    },
    submitButton: {
        width: '100%',
        padding: '12px',
        backgroundColor: '#0d6efd', 
        color: '#ffffff',
        border: 'none',
        borderRadius: '4px',
        fontSize: '1rem',
        fontWeight: 'bold',
        marginTop: '10px',
        transition: 'background-color 0.2s'
    },
    errorAlert: {
        backgroundColor: '#f8d7da',
        color: '#842029',
        padding: '10px',
        borderRadius: '4px',
        marginBottom: '20px',
        fontSize: '0.9rem',
        border: '1px solid #f5c2c7'
    }
};

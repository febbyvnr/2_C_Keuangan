import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiFetch } from '../api/api';
import './Login.css';

export default function Login() {
    const [nip, setNip] = useState('');
    const [password, setPassword] = useState('');
    const [errorMsg, setErrorMsg] = useState('');
    const [isLoading, setIsLoading] = useState(false);
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

            // Simpan token ke localStorage
            localStorage.setItem('token', response.data.access_token);
            localStorage.setItem('roles', JSON.stringify(response.data.roles));
            localStorage.setItem('user', JSON.stringify(response.data.user));

            // Lempar ke dashboard
            navigate('/dashboard');
        } catch (error) {
            setErrorMsg(error.status === 401 ? 'NIP atau Password salah!' : error.message);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="login-container">
            <div className="login-card">
                <h2>Login Sistem Bopkri</h2>
                {errorMsg && <div className="error-alert">{errorMsg}</div>}
                
                <form onSubmit={handleLogin}>
                    <div className="input-group">
                        <label>NIP Karyawan</label>
                        <input type="text" value={nip} onChange={(e) => setNip(e.target.value)} required />
                    </div>
                    <div className="input-group">
                        <label>Password</label>
                        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
                    </div>
                    <button type="submit" disabled={isLoading}>
                        {isLoading ? 'Loading...' : 'Masuk'}
                    </button>
                </form>
            </div>
        </div>
    );
}
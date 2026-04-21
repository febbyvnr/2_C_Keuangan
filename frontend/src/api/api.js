const BASE_URL = 'http://localhost:8000/api'; // Sesuaikan port backend laravelmu

export const apiFetch = async (endpoint, options = {}) => {
    const token = localStorage.getItem('token');
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers,
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${BASE_URL}${endpoint}`, {
        ...options,
        headers: headers,
    });

    const data = await response.json();

    if (!response.ok) {
        const error = new Error(data.message || 'Terjadi kesalahan pada server');
        error.status = response.status;
        throw error;
    }
    return data;
};
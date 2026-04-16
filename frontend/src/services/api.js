import axios from 'axios';

const api = axios.create({
    // Sesuaikan port 8000 dengan port Laravel lu (php artisan serve)
    baseURL: 'http://localhost:8000/api', 
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

export default api;
# 2_C_Keuangan
Project Setup Guide
1. Clone Repository ke Local
Kalau pakai GitHub Desktop:
<ul><li>Klik Add → pilih Clone repository.</li></ul>
<ul><li>Pilih URL, lalu paste URL repository.</li></ul>
<ul><li>Pilih lokasi project di laptop.</li></ul>
<ul><li>Klik Clone.</li></ul>

2. Install Dependencies
Backend (Laravel)
<ul>Buka terminal di folder backend.</ul>
<ul>Install dependencies Laravel : "composer install"</ul>
<ul>Jika perlu, buat file .env dari .env.example dan generate key: "cp .env.example .env" : "php artisan key:generate"</ul>

3. Menjalankan Project
Frontend (Vite / npm)
<ul>Buka terminal di folder frontend : "npm install"</ul>
<ul>Terminal akan menampilkan link, biasanya http://localhost:5173/.</ul>
<ul>Ctrl+Click link atau copy-paste ke browser untuk menjalankan project frontend.</ul>

4. Catatan
<ul><li>Pastikan PHP, Composer, Node.js, dan npm sudah terinstall di laptop.</li></ul>
<ul><li>Untuk pertama kali, backend dan frontend harus diinstall dependencies-nya masing-masing.</li></ul>
<ul><li>Setelah dijalankan, backend Laravel dan frontend Vite dapat berjalan secara bersamaan.</li></ul>

# 2_C_Keuangan
## Project Setup Guide

---

### 1. Clone Repository ke Local

Kalau pakai **GitHub Desktop**:

<ul>
  <li>Klik <strong>Add</strong> → pilih <strong>Clone repository</strong>.</li>
  <li>Pilih <strong>URL</strong>, lalu paste URL repository.</li>
  <li>Pilih lokasi project di laptop.</li>
  <li>Klik <strong>Clone</strong>.</li>
</ul>

---

### 2. Install Dependencies

#### Backend (Laravel)

<ul>
  <li>Buka terminal di folder <strong>backend</strong>.</li>
  <li>Install dependencies Laravel:
    <pre><code>composer install</code></pre>
  </li>
  <li>Jika perlu, buat file <code>.env</code> dari <code>.env.example</code> dan generate key:
    <pre><code>cp .env.example .env
php artisan key:generate</code></pre>
  </li>
</ul>

#### Frontend (Vite / npm)

<ul>
  <li>Buka terminal di folder <strong>frontend</strong>.</li>
  <li>Install dependencies:
    <pre><code>npm install</code></pre>
  </li>
</ul>

---

### 3. Menjalankan Project

#### Backend (Laravel)

<ul>
  <li>Buka terminal di folder <strong>backend</strong>.</li>
  <li>Jalankan Laravel:
    <pre><code>php artisan serve</code></pre>
  </li>
  <li>Laravel biasanya berjalan di <code>http://localhost:8000</code>.</li>
</ul>

#### Frontend (Vite)

<ul>
  <li>Buka terminal di folder <strong>frontend</strong>.</li>
  <li>Jalankan project frontend:
    <pre><code>npm run dev</code></pre>
  </li>
  <li>Terminal akan menampilkan link, biasanya <code>http://localhost:5173/</code>.</li>
  <li>Ctrl+Click link atau copy-paste ke browser untuk menjalankan frontend.</li>
</ul>

---

### 4. Catatan

<ul>
  <li>Pastikan <strong>PHP</strong>, <strong>Composer</strong>, <strong>Node.js</strong>, dan <strong>npm</strong> sudah terinstall di laptop.</li>
  <li>Pada pertama kali clone, backend dan frontend harus diinstall dependencies-nya masing-masing.</li>
  <li>Setelah dijalankan, backend Laravel dan frontend Vite dapat berjalan secara bersamaan.</li>
</ul>

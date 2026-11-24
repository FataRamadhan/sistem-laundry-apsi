# Sistem Pengelolaan Laundry (Mobile-First Web App)
**Mata Kuliah:** Analisis Perancangan Sistem Informasi (APSI)  
**Dosen Pengampu:** Misna Asqia, S.Kom., M.Kom.  
**Asisten Dosen:** Muhammad Mush'ab Al Mubarok  
**Kelompok 2 - 3SI01 (STT Terpadu Nurul Fikri)**

---

## 📌 Ringkasan Sistem Sesuai DFD Revisi

Aplikasi ini dibangun secara ketat hanya berdasarkan kebutuhan pada Data Flow Diagram (DFD) revisi dan Activity Diagram:
1. **Pemilik (A-01)**: Akses penuh ke Proses 1.0, 2.0, 3.0, dan 5.0 (Login via D6).
2. **Petugas Operasional (A-02)**: Hanya berhak mengakses daftar pesanan untuk Proses 3.0 (mengubah status pengerjaan & antar-jemput). Akses ke proses 1.0, 2.0, 5.0 ditolak secara ketat.
3. **Pelanggan (A-03)**: **Tanpa Login**. Hanya mengakses halaman web publik (`cek_status.php`) dengan memasukkan Nomor Pesanan & Nomor HP.

---

## 🗄️ 7 Data Store (Tabel MySQL)

| Data Store | Nama Tabel | Kolom Utama & Deskripsi |
|---|---|---|
| **D1** | `pelanggan` | `id`, `nama`, `no_hp`, `alamat`, `created_at` (diinput oleh Pemilik) |
| **D2** | `pesanan` | `id`, `no_pesanan`, `pelanggan_id`, `tanggal_titip`, `berat`, `jenis_layanan`, `lama_pengerjaan`, `total_harga`, `status_pengerjaan`, `waktu_pembaruan` |
| **D3** | `rincian_item` | `id`, `pesanan_id`, `jenis`, `jumlah`, `ciri_barang` (warna/merek) |
| **D4** | `cucian_tanpa_pemilik` | `id`, `deskripsi_barang`, `foto`, `status` (`Ditemukan`/`Diserahkan`), `waktu_ditemukan`, `waktu_diserahkan` |
| **D5** | `antar_jemput` | `id`, `pesanan_id`, `jenis_layanan`, `alamat`, `berat_cucian`, `status` (`Dijadwalkan`/`Dijemput`/`Diantar`/`Selesai`), `waktu_pembaruan` |
| **D6** | `akun_pengguna` | `id`, `username`, `password` (bcrypt), `peran` (`Pemilik`/`Petugas Operasional`) |
| **D7** | `riwayat_status` | `id`, `pesanan_id`, `status`, `waktu_pembaruan`, `diubah_oleh` |

---

## ⚙️ 5 Proses Utama yang Diimplementasikan

### 1.0 Catat Pesanan (Khusus Pemilik)
- Pengecekan otomatis pelanggan lama dari D1 berdasarkan nomor HP atau nama.
- Hitung total harga otomatis berdasarkan berat (kg), jenis layanan, dan durasi pengerjaan.
- Input opsional rincian item cucian ke D3 (jenis, jumlah, ciri barang/merek/warna).
- Input opsional layanan antar-jemput ke D5.
- Dialog konfirmasi sebelum menyimpan pesanan. Status awal otomatis **"Diterima"** tercatat di D2 dan D7.
- Menghasilkan **Nota Digital** dengan QR Code mandiri yang langsung mengarah ke halaman pelacakan publik pelanggan.

### 2.0 Cari Pesanan (Khusus Pemilik)
- Multi-kriteria pencarian: nama pelanggan, tanggal titip (`YYYY-MM-DD`), ciri barang, atau nomor pesanan.
- Menampilkan pesan baku **"Pesanan tidak ditemukan"** jika hasil pencarian kosong.
- Klik pesanan untuk melihat detail lengkap (D1, D2, D3, D5, dan D7).

### 3.0 Kelola Status Pengerjaan & Antar-Jemput (Pemilik & Petugas)
- Verifikasi hak akses aktor melalui D6 (akses ditolak jika tidak berwenang).
- Perubahan status baku pengerjaan: `Diterima`, `Dicuci`, `Disetrika`, `Siap Diambil/Diantar`, `Selesai`.
- Perubahan status antar-jemput: `Dijadwalkan`, `Dijemput`, `Diantar`, `Selesai`.
- Setiap perubahan status otomatis memperbarui waktu pembaruan di D2/D5 serta mencatat log audit ke D7 (status, waktu, dan identitas pengubah).

### 4.0 Lihat Status Pesanan (Pelanggan - Publik Tanpa Login)
- Dapat diakses langsung melalui file `cek_status.php`.
- Menerima parameter URL dari QR Code nota (`?no_pesanan=...&no_hp=...`) untuk auto-fill dan auto-track.
- Memverifikasi kecocokan nomor pesanan dan nomor HP ke D1 dan D2; menampilkan pesan baku **"Data tidak tersedia"** jika tidak cocok.
- Auto-refresh / polling otomatis setiap ± 30 detik untuk tampilan real-time.

### 5.0 Kelola Cucian Tanpa Pemilik (Khusus Pemilik)
- Pencatatan deskripsi barang dan unggah foto ke folder `uploads/` (disimpan di D4).
- Pembaruan status barang dari **"Ditemukan"** menjadi **"Diserahkan"** disertai dialog konfirmasi.

---

## 🔑 Akun Default untuk Pengujian (D6)

| Aktor | Username | Password | Hak Akses |
|---|---|---|---|
| **Pemilik (A-01)** | `pemilik` | `password123` | Proses 1.0, 2.0, 3.0, 5.0 (Akses Penuh) |
| **Petugas Operasional (A-02)** | `petugas` | `password123` | Hanya Proses 3.0 (Ubah Status Pengerjaan & Antar-Jemput) |
| **Pelanggan (A-03)** | *(Tanpa Login)* | *(Tanpa Login)* | Hanya Proses 4.0 (`cek_status.php`) |

---

## 🚀 Panduan Menjalankan di XAMPP

1. **Jalankan Apache & MySQL** pada XAMPP Control Panel.
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Import file `database.sql` ke MySQL:
   - Klik tab **Import** -> pilih file `database.sql` -> klik **Go**.
   - Database `db_laundry_apsi` dan 7 tabel D1–D7 akan dibuat otomatis beserta data contoh.
4. Salin / arahkan folder proyek ini ke `C:\xampp\htdocs\laundry_apsi` (atau akses langsung sesuai virtual host / direktori Anda).
5. Buka peramban (browser):
   - **Aplikasi Staf (Pemilik & Petugas):** `http://localhost/laundry_apsi/index.php`
   - **Pelacakan Publik Pelanggan:** `http://localhost/laundry_apsi/cek_status.php`

---

## 📱 Panduan Membungkus Menjadi File APK (.apk) Android

Aplikasi ini menggunakan tata letak **Mobile-First** dengan *viewport mobile container*. Anda dapat dengan mudah membungkusnya menjadi aplikasi Android APK:

### Opsi 1: Menggunakan Capacitor (Rekomendasi)
```bash
npm install -g @capacitor/cli @capacitor/core
npx cap init "LaundryCare" "id.ac.nurulfikri.laundryapsi" --web-dir "."
npm install @capacitor/android
npx cap add android
# Edit capacitor.config.json untuk mengarahkan server url ke IP lokal XAMPP Anda:
# "server": { "url": "http://192.168.1.xxx/laundry_apsi" }
npx cap open android
# Build APK melalui Android Studio -> Build -> Build Bundle(s) / APK(s) -> Build APK(s)
```

### Opsi 2: Menggunakan Android Studio WebView Native
1. Buka Android Studio -> Buat New Project (Empty Views Activity).
2. Buka `activity_main.xml`, ganti komponen utama dengan `<WebView android:id="@+id/webView" android:layout_width="match_parent" android:layout_height="match_parent" />`.
3. Di `MainActivity.java`:
   ```java
   WebView webView = findViewById(R.id.webView);
   webView.getSettings().setJavaScriptEnabled(true);
   webView.getSettings().setDomStorageEnabled(true);
   webView.setWebViewClient(new WebViewClient());
   webView.loadUrl("http://IP_KOMPUTER_XAMPP/laundry_apsi/index.php");
   ```
4. Tambahkan izin internet pada `AndroidManifest.xml`: `<uses-permission android:name="android.permission.INTERNET" />`.
5. Klik **Build APK**.

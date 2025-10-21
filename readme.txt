Proyek Absensi Wajah dengan Geolokasi (Backend Python)

Selamat datang di dokumentasi backend untuk sistem absensi wajah. Proyek ini dibangun menggunakan Python, Flask, dan InsightFace untuk menyediakan API yang andal untuk pendaftaran dan verifikasi wajah, lengkap dengan validasi geolokasi.

Dokumen ini ditujukan untuk developer (khususnya rekan yang akan mengintegrasikannya dengan Laravel) untuk memahami arsitektur, cara instalasi, dan cara menghubungkan frontend ke backend API ini.

Arsitektur Proyek

Sistem ini terbagi menjadi dua bagian utama:

Backend (Python & Flask): Bertugas sebagai "otak" dari sistem.

face_api.py: Server API utama yang menerima permintaan HTTP dari Laravel.

face_register_service.py: Menangani semua logika kompleks untuk pendaftaran wajah, termasuk validasi pose, kualitas gambar, dan cek duplikasi.

face_recognize_service.py: Menangani logika absensi, membandingkan wajah dengan dataset, dan memvalidasi lokasi.

main.py: Script yang dijalankan secara otomatis untuk melatih ulang (menghitung ulang embeddings) model setelah ada pendaftaran baru.

geolocation_service.py: Mengatur lokasi target absensi dan menghitung jarak.

attendance_logger.py: Menyimpan data absensi yang berhasil (saat ini ke CSV, dengan opsi untuk beralih ke MySQL).

Frontend (Laravel & JavaScript): Bertugas sebagai antarmuka pengguna (UI).

Menampilkan preview kamera.

Mengirim gambar dan data (nama, lokasi) ke backend API Python.

Menampilkan respon (sukses/gagal) dari API.

Alur Data Absensi

[Gambar alur data sederhana dari frontend ke backend]

Laravel (Browser) -> Mengambil Foto & Geolokasi -> Kirim POST Request -> Flask (face_api.py) -> Panggil face_recognize_service.py -> Validasi Wajah & Lokasi -> Panggil attendance_logger.py -> Simpan ke CSV/DB -> Kirim Respon JSON -> Laravel (Tampilkan Pesan)


Fitur Utama

Pendaftaran Wajah Terpandu: Sistem memandu pengguna untuk mengambil 5 foto dari sudut berbeda (depan, kiri, kanan, atas, bawah).

Validasi Kualitas & Pose: Setiap foto yang didaftarkan harus memenuhi ambang batas kualitas gambar (85%) dan pose yang benar (dapat diaktifkan/dinonaktifkan).

Anti-Duplikasi: Mencegah pendaftaran nama atau wajah yang sudah ada di dataset.

Absensi Akurasi Tinggi: Membutuhkan tingkat kemiripan wajah 98% untuk absensi diterima.

Validasi Geolokasi: Absensi hanya diterima jika pengguna berada dalam radius yang ditentukan dari lokasi target.

Retrain Otomatis: Model secara otomatis diperbarui setelah setiap pendaftaran berhasil, tanpa perlu me-restart server.

Logging Modular: Data absensi dapat disimpan ke file CSV atau database MySQL dengan mengubah satu baris konfigurasi.

Instalasi & Konfigurasi Backend (Python)

Langkah-langkah ini harus dilakukan di lingkungan pengembangan backend.

Prasyarat

Python (versi 3.8 atau lebih baru direkomendasikan).

Git.

Langkah 1: Clone Repositori

Buka terminal dan clone repositori ini ke mesin lokal Anda.

git clone [https://github.com/aufatsaqief/TA-facerecog-Qash.git](https://github.com/aufatsaqief/TA-facerecog-Qash.git)
cd TA-facerecog-Qash


Langkah 2: Setup Virtual Environment

Sangat penting untuk menggunakan virtual environment (.venv) agar dependensi proyek tidak tercampur dengan sistem Python Anda.

# Buat virtual environment
python -m venv .venv

# Aktifkan virtual environment
# Di Windows (PowerShell):
.\.venv\Scripts\Activate.ps1
# Di macOS/Linux:
# source .venv/bin/activate


Setelah aktif, Anda akan melihat (.venv) di awal prompt terminal Anda.

Langkah 3: Instal Dependensi

Instal semua pustaka Python yang diperlukan dari file requirements.txt.

pip install -r requirements.txt


Langkah 4: Konfigurasi Lokasi Absensi

Lokasi target untuk absensi diatur langsung di dalam kode.

Dapatkan Koordinat: Jalankan script get_coords.py untuk mendapatkan perkiraan latitude dan longitude lokasi Anda saat ini.

python get_coords.py


Update geolocation_service.py: Buka file geolocation_service.py dan ganti nilai TARGET_LATITUDE, TARGET_LONGITUDE, dan ACCEPTABLE_RADIUS_METERS dengan koordinat dan radius toleransi yang Anda inginkan.

Langkah 5: Konfigurasi Database (Opsional)

Secara default, sistem menyimpan log absensi ke absensi.csv. Jika Anda ingin beralih ke MySQL:

Pastikan Anda sudah membuat database qash_demo.

Buka file attendance_logger.py.

Ubah LOGGING_MODE = 'CSV' menjadi LOGGING_MODE = 'MYSQL'.

Isi placeholder insertuserhere dan insertpasswordhere di dalam DB_CONFIG dengan username dan password MySQL Anda.

Langkah 6: Jalankan Server API

Setelah semua konfigurasi selesai, jalankan server Flask.

python face_api.py


Server akan berjalan di http://127.0.0.1:5000. Biarkan terminal ini tetap berjalan selama aplikasi digunakan.

Integrasi dengan Laravel (Frontend)

Berikut adalah panduan untuk menghubungkan aplikasi Laravel ke API Python.

URL API

Semua permintaan dikirim ke base URL: http://127.0.0.1:5000.

Endpoint API

1. Pendaftaran Wajah (/register)

Method: POST

Tipe Data: multipart/form-data

Body:

name (string): Nama lengkap karyawan.

image (file): File gambar wajah dari kamera.

frame_index (integer): Urutan frame (0 sampai 4).

total_frames (integer): Total frame (selalu 5).

required_pose (integer): Indeks pose yang diminta (0=lurus, 1=kiri, dst.).

2. Absensi Wajah (/recognize)

Method: POST

Tipe Data: multipart/form-data

Body:

image (file): File gambar wajah dari kamera.

latitude (string): Latitude lokasi pengguna saat ini.

longitude (string): Longitude lokasi pengguna saat ini.

Contoh Kode JavaScript (Fetch API untuk Absensi)

Ini adalah contoh bagaimana JavaScript di sisi Laravel harus mengirim data ke endpoint /recognize.

// URL API yang dituju
const API_URL_RECOGNIZE = '[http://127.0.0.1:5000/recognize](http://127.0.0.1:5000/recognize)';

async function sendToRecognize() {
    statusMessage.innerText = 'Mendeteksi wajah & lokasi...';
    
    // 1. Ambil gambar dari <video>
    const imageBlob = await captureImage(); // Fungsi untuk mengambil blob dari canvas
    const formData = new FormData();
    formData.append('image', imageBlob, 'photo.jpg');

    // 2. Ambil geolokasi dari browser
    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 });
        });
        formData.append('latitude', position.coords.latitude);
        formData.append('longitude', position.coords.longitude);
    } catch (geoError) {
        statusMessage.innerText = '❌ Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.';
        return;
    }

    // 3. Kirim data ke API Flask
    try {
        const response = await fetch(API_URL_RECOGNIZE, { method: 'POST', body: formData });
        const result = await response.json();

        // 4. Tampilkan pesan dari server
        if (result.status === 'ok') {
            statusMessage.innerText = `✅ ${result.message}`;
            statusMessage.className = 'text-success';
        } else {
            statusMessage.innerText = `❌ ${result.message}`;
            statusMessage.className = 'text-danger';
        }
    } catch (error) {
        statusMessage.innerText = 'Terjadi kesalahan saat terhubung ke server Flask.';
    }
}


Penanganan Respon JSON

API akan selalu mengembalikan objek JSON dengan properti status dan message.

status: 'success' atau 'finished': Operasi berhasil.

status: 'skip': Pendaftaran gagal (misalnya, kualitas gambar rendah), minta pengguna mencoba lagi.

status: 'error': Terjadi kesalahan fatal atau validasi gagal (misalnya, wajah duplikat).

Troubleshooting

"Connection Refused": Pastikan server face_api.py sedang berjalan di terminal terpisah.

"ModuleNotFoundError": Pastikan Anda sudah mengaktifkan virtual environment (.venv) sebelum menjalankan pip install atau python face_api.py.

Error Geolokasi: Pastikan pengguna memberikan izin lokasi di browser.

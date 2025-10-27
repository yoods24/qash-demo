Sistem Presensi Karyawan Berbasis Pengenalan Wajah dan Geolokasi

Proyek ini merupakan implementasi sistem presensi karyawan modern yang memanfaatkan teknologi pengenalan wajah real-time dan validasi geolokasi. Sistem ini dibangun dengan arsitektur modular, memisahkan backend API (Python/Flask) dari frontend (Laravel).

Fitur Utama ✨

Pengenalan Wajah Akurat: Menggunakan model state-of-the-art InsightFace (ArcFace) untuk ekstraksi fitur wajah yang diskriminatif.

Verifikasi 1:1: Saat absensi, sistem hanya membandingkan wajah yang di-scan dengan data wajah milik pengguna yang sedang login, meningkatkan efisiensi dan keamanan.

Validasi Geolokasi: Memastikan absensi hanya dapat dilakukan jika pengguna berada dalam radius lokasi yang telah ditentukan (kantor/tempat kerja). Konfigurasi lokasi mudah diubah.

Pendaftaran Wajah Terpandu (Multi-Pose): Proses pendaftaran yang interaktif meminta pengguna untuk mengambil 5 foto wajah dari sudut berbeda (Lurus, Kiri, Kanan, Atas, Bawah) untuk meningkatkan robustness model.

Validasi Kualitas Gambar: Setiap foto pendaftaran divalidasi untuk memastikan kualitas deteksi minimal (misalnya 85%) sebelum disimpan.

Pencegahan Duplikasi: Sistem mengecek duplikasi berdasarkan nama dan kemiripan wajah saat pendaftaran.

Pembaruan Model Otomatis: Setiap kali pendaftaran wajah baru berhasil, database embeddings secara otomatis dihitung ulang dan dimuat oleh server API tanpa perlu restart.

Pencatatan Fleksibel: Hasil absensi dapat dicatat ke file CSV atau database MySQL (mudah dikonfigurasi).

Arsitektur Modular: Kode Python dipecah menjadi services terpisah (register, recognize, logger) untuk kemudahan pemeliharaan.

Integrasi Mudah: API Flask dirancang untuk mudah diintegrasikan dengan frontend web seperti Laravel.

Arsitektur Sistem 🏛️

Sistem ini terdiri dari dua bagian utama:

Backend API (Python/Flask):

Bertanggung jawab atas semua pemrosesan AI (deteksi wajah, ekstraksi embedding, perbandingan).

Menangani validasi geolokasi.

Mengelola database embeddings wajah.

Menyediakan endpoint API untuk pendaftaran, pembatalan, dan absensi.

File Utama:

face_api.py: Server Flask utama, menangani routing API.

face_register_service.py: Logika untuk proses pendaftaran 5-pose, validasi kualitas/duplikasi, dan memicu retrain.

face_recognize_service.py: Logika untuk proses absensi 1:1 dan validasi geolokasi.

attendance_logger.py: Mengelola penyimpanan data absensi (CSV atau MySQL).

geolocation_service.py: Menangani validasi jarak dari lokasi target.

main.py: Skrip untuk menghitung ulang embeddings dari dataset.

config.json (Opsional jika menggunakan file config): Menyimpan koordinat geolokasi.

requirements.txt: Daftar pustaka Python yang dibutuhkan.

Frontend (Laravel - Tidak Termasuk di Repositori Ini):

Menyediakan antarmuka pengguna (UI) untuk login, pendaftaran wajah, dan absensi.

Mengakses kamera pengguna melalui browser.

Mengirim request (gambar wajah, username, koordinat GPS) ke backend API Flask.

Menampilkan feedback dari API kepada pengguna.

Instalasi Backend Python 🐍

Ikuti langkah-langkah ini untuk menjalankan server API Python di mesin development Anda (misalnya, laptop rekan Anda).

Prasyarat:

Python 3.8+ terinstal.

Git terinstal.

Langkah Instalasi:

Clone Repositori:

git clone [https://github.com/aufatsaqief/TA-facerecog-Qash.git](https://github.com/aufatsaqief/TA-facerecog-Qash.git)
cd TA-facerecog-Qash


Buat dan Aktifkan Virtual Environment:
Sangat disarankan untuk menggunakan virtual environment agar dependensi tidak bentrok.

# Buat environment (hanya sekali)
python -m venv .venv

# Aktifkan environment
# Windows (PowerShell):
.\.venv\Scripts\Activate.ps1
# Windows (CMD):
.\.venv\Scripts\activate
# Linux/macOS:
source .venv/bin/activate


Anda akan melihat (.venv) di awal prompt terminal jika aktivasi berhasil.

Instal Dependensi:
Instal semua pustaka Python yang diperlukan dari file requirements.txt.

pip install -r requirements.txt


(Proses ini mungkin memerlukan waktu, terutama saat mengunduh model InsightFace).

Konfigurasi (Opsional - Jika diperlukan):

Geolokasi: Buka geolocation_service.py dan perbarui variabel TARGET_LATITUDE, TARGET_LONGITUDE, dan ACCEPTABLE_RADIUS_METERS sesuai lokasi target yang diinginkan. Atau, jika Anda menggunakan config.json, edit file tersebut.

Logging Absensi: Buka attendance_logger.py.

Secara default, logging menggunakan CSV. Path file CSV adalah D:/Semester 8/Capstong/TA2/absensi.csv (sesuaikan jika perlu).

Untuk beralih ke MySQL, ubah LOGGING_MODE = 'CSV' menjadi LOGGING_MODE = 'MYSQL', pastikan mysql-connector-python terinstal (pip install mysql-connector-python), dan isi detail koneksi (DB_CONFIG) dengan benar. Pastikan tabel table_absensi (atau nama tabel Anda) sudah ada di database qash_demo.

Threshold: Anda dapat menyesuaikan DETECTION_QUALITY_THRESHOLD_REGISTER (kualitas pendaftaran, disarankan >= 0.85) dan RECOGNITION_THRESHOLD_ABSENSI (ambang batas absensi, misal 0.40 untuk 60%) di face_register_service.py dan face_recognize_service.py.

Jalankan Server API:
Pastikan virtual environment Anda aktif, lalu jalankan server Flask.

python face_api.py


Server API akan berjalan di http://0.0.0.0:5000. 0.0.0.0 berarti server dapat diakses dari alamat IP lokal mesin Anda (misalnya, http://192.168.1.10:5000).

Integrasi dengan Frontend Laravel 💻

Rekan Anda perlu memodifikasi frontend Laravel untuk berkomunikasi dengan API Flask yang berjalan.

Poin Penting:

URL API: Pastikan JavaScript di Laravel menggunakan URL API Flask yang benar, termasuk alamat IP lokal mesin tempat Flask berjalan, bukan 127.0.0.1 (kecuali Laravel dan Flask berjalan di mesin yang sama).
Contoh di JavaScript:

const API_BASE_URL = '[http://192.168.1.10:5000](http://192.168.1.10:5000)'; // Ganti dengan IP mesin Flask
const API_URL_REGISTER = `${API_BASE_URL}/register`;
const API_URL_RECOGNIZE = `${API_BASE_URL}/recognize`;
const API_URL_CANCEL = `${API_BASE_URL}/cancel_register`;


Akses Kamera: Gunakan navigator.mediaDevices.getUserMedia({ video: true }) untuk mengakses kamera browser.

Endpoint /register:

Kirim data menggunakan FormData via POST.

Sertakan field berikut:

name: Nama karyawan (dari input form).

image: File gambar wajah (dari <canvas>.toBlob()).

frame_index: Indeks frame saat ini (0-4).

total_frames: Jumlah total frame (selalu 5).

required_pose: Indeks pose yang diminta (0-4).

Contoh JavaScript (fetch): (Lihat kode register.blade.php di proyek ini sebagai referensi detail).

Endpoint /recognize:

Kirim data menggunakan FormData via POST.

Sertakan field berikut:

username: Nama pengguna atau ID unik pengguna yang sedang login (diambil dari sesi Laravel, misalnya auth()->user()->name). Ini wajib.

image: File gambar wajah (dari <canvas>.toBlob()).

latitude: Koordinat lintang pengguna (dari navigator.geolocation).

longitude: Koordinat bujur pengguna (dari navigator.geolocation).

Contoh JavaScript (fetch): (Lihat kode layouts.scripts.blade.php di proyek ini sebagai referensi detail, terutama bagian pengambilan username dan geolokasi).

Endpoint /cancel_register:

Kirim data menggunakan FormData via POST.

Sertakan field name: Nama karyawan yang pendaftarannya dibatalkan.

Penanganan Respon:

Proses respon JSON dari API Flask.

Tampilkan pesan message kepada pengguna (statusMsg, poseInstruction).

Gunakan status (status: 'success', 'skip', 'error', 'finished') untuk mengontrol alur UI.

Pada pendaftaran, gunakan next_pose untuk tahu pose berikutnya yang harus ditampilkan.

CORS: API Flask sudah dikonfigurasi dengan flask_cors untuk mengizinkan permintaan dari origin manapun. Jika ada masalah CORS, pastikan tidak ada firewall atau konfigurasi jaringan lain yang memblokir komunikasi antar mesin.

Struktur Folder Python (Contoh)

TA-facerecog-Qash/
├── .venv/                  # Virtual environment
├── Dataset/                # (Dibuat otomatis, di-.gitignore) Folder berisi gambar wajah terdaftar
├── embeddings/             # (Dibuat otomatis, di-.gitignore) File model .pkl
├── face_api.py             # Server Flask utama
├── face_register_service.py # Logika Pendaftaran
├── face_recognize_service.py # Logika Absensi
├── attendance_logger.py    # Logika Logging (CSV/MySQL)
├── geolocation_service.py  # Logika Geolokasi
├── main.py                 # Skrip Retrain Embeddings
├── config.json             # (Opsional) Konfigurasi Geolokasi
├── requirements.txt        # Dependensi Python
├── README.md               # File ini
└── .gitignore              # File/folder yang diabaikan Git



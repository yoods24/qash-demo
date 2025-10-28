README - Konfigurasi Proyek Presensi Wajah Python

Dokumen ini menjelaskan cara mengkonfigurasi backend Python untuk sistem presensi wajah melalui file config.py. Semua pengaturan penting terpusat di file ini agar mudah disesuaikan.

Lokasi File Konfigurasi

File konfigurasi utama adalah config.py yang terletak di root direktori proyek Python (D:\Semester 8\Capstong\TA2 atau lokasi setara di mesin Anda/rekan Anda).

Pengaturan Utama

Buka file config.py dengan editor teks. Berikut adalah penjelasan untuk setiap bagian konfigurasi:

1. Path (Lokasi Folder)

Bagian ini sangat penting untuk menghubungkan backend Python dengan lokasi data (Dataset) di proyek Laravel.

# --- PATH KONFIGURASI UTAMA ---

# ❗ UBAH PATH INI SESUAI LOKASI PROYEK LARAVEL ANDA ❗
LARAVEL_PROJECT_PATH = r"D:\laragon\www\test03"
DATASET_SUBDIR = "storage/Dataset" # Subdirektori di dalam proyek Laravel

# Path absolut ke folder Dataset (Otomatis dibuat berdasarkan di atas)
DATASET_PATH = os.path.join(LARAVEL_PROJECT_PATH, DATASET_SUBDIR)

# Path ke folder embeddings (Direkomendasikan tetap di proyek Python)
EMB_DIR = "embeddings"

# Path ke file absensi CSV (Jika LOGGING_MODE = 'CSV')
CSV_LOG_PATH = r"D:/Semester 8/Capstong/TA2/absensi.csv" # Sesuaikan path ini jika perlu


LARAVEL_PROJECT_PATH: WAJIB DIUBAH. Ganti path ini dengan lokasi absolut root folder proyek Laravel rekan Anda di komputernya. Gunakan r"..." (raw string) untuk Windows agar backslash terbaca benar.

DATASET_SUBDIR: Subdirektori tempat menyimpan gambar wajah di dalam proyek Laravel. Standarnya adalah storage/Dataset. Pastikan folder ini ada di dalam proyek Laravel. Python akan membaca dan menulis gambar ke lokasi ini.

EMB_DIR: Lokasi penyimpanan file model embeddings (.pkl). Sebaiknya biarkan di dalam folder proyek Python.

CSV_LOG_PATH: Lokasi file log absensi jika Anda menggunakan mode CSV. Sesuaikan path ini jika perlu.

2. Konfigurasi Geolokasi

Pengaturan ini digunakan jika fitur validasi lokasi diaktifkan.

# --- Konfigurasi Geolokasi ---
TARGET_LATITUDE = -6.9926
TARGET_LONGITUDE = 110.4208
ACCEPTABLE_RADIUS_METERS = 50


TARGET_LATITUDE, TARGET_LONGITUDE: Ganti dengan koordinat (Lintang, Bujur) lokasi absensi yang sah. Gunakan alat seperti Google Maps atau script get_coords.py untuk mendapatkannya.

ACCEPTABLE_RADIUS_METERS: Jarak toleransi (dalam meter) dari titik target agar absensi dianggap valid.

3. Konfigurasi Database (jika menggunakan MySQL)

Pengaturan ini hanya relevan jika LOGGING_MODE diatur ke 'MYSQL'.

# --- Konfigurasi Database (jika digunakan) ---
DB_NAME = "qash_demo"
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root', # Ganti dengan username MySQL
    'password': '', # Ganti dengan password MySQL
    'database': DB_NAME
}


DB_NAME: Nama database MySQL yang digunakan.

DB_CONFIG: Detail koneksi ke server MySQL (host, user, password). Ganti user dan password sesuai pengaturan MySQL rekan Anda. Pastikan user tersebut memiliki izin INSERT pada tabel absensi di database DB_NAME.

4. Mode Logging

Pilih cara penyimpanan log absensi.

# --- Konfigurasi Lainnya ---
LOGGING_MODE = 'CSV' # Ganti ke 'MYSQL' jika database siap


LOGGING_MODE: Atur ke 'CSV' untuk menyimpan ke file CSV (sesuai CSV_LOG_PATH) atau 'MYSQL' untuk menyimpan ke database MySQL (sesuai DB_CONFIG).

5. Threshold (Ambang Batas)

Pengaturan ini mengontrol sensitivitas pengenalan wajah dan validasi.

RECOGNITION_THRESHOLD_ABSENSI = 0.40 # 60% kemiripan untuk Absensi 1:1
RECOGNITION_THRESHOLD_REGISTER = 0.30 # 70% kemiripan untuk Cek Duplikasi Wajah
DETECTION_QUALITY_THRESHOLD_REGISTER = 0.70 # Kualitas gambar min 70% saat daftar
VALIDATE_POSE_ENABLED = False # Aktifkan/nonaktifkan validasi pose
POSE_THRESHOLD_SIDE = 30
POSE_THRESHOLD_UP_DOWN = 30


RECOGNITION_THRESHOLD_ABSENSI: Jarak Cosine maksimal agar wajah dianggap cocok saat absensi (1:1). Nilai 0.40 berarti kemiripan minimal (1 - 0.40) * 100 = 60%. Naikkan nilai ini (misal 0.30 untuk 70%) jika terlalu longgar, turunkan jika terlalu ketat.

RECOGNITION_THRESHOLD_REGISTER: Jarak Cosine maksimal untuk mendeteksi wajah duplikat saat pendaftaran. 0.10 (90% kemiripan) cukup ketat.

DETECTION_QUALITY_THRESHOLD_REGISTER: Skor minimal (0-1) dari detektor wajah agar gambar diterima saat pendaftaran. 0.85 adalah nilai yang baik.

VALIDATE_POSE_ENABLED: True untuk mengaktifkan validasi sudut wajah (Lurus, Kiri, dll.) saat pendaftaran, False untuk menonaktifkan.

POSE_THRESHOLD_SIDE, POSE_THRESHOLD_UP_DOWN: Toleransi (dalam piksel relatif) untuk validasi pose jika diaktifkan. Nilai 30 cukup user-friendly.

Langkah Setelah Konfigurasi

Simpan file config.py setelah melakukan perubahan.

Restart Server Flask: Hentikan (Ctrl+C) dan jalankan kembali face_api.py. Server akan otomatis menggunakan pengaturan baru saat dimulai.

Pastikan Folder Ada: Pastikan folder yang ditentukan di DATASET_PATH benar-benar ada dan Python memiliki izin baca/tulis di sana.

Dengan mengikuti panduan ini, rekan Anda seharusnya dapat dengan mudah menyesuaikan backend Python agar terhubung dengan benar ke proyek Laravel dan data dataset-nya.
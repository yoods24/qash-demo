# config.py
# File konfigurasi utama untuk path

import os

# --- PATH KONFIGURASI UTAMA ---

# ❗ UBAH PATH INI SESUAI LOKASI FOLDER DATASET DI PROYEK LARAVEL ANDA ❗
# Pastikan path ini benar dan folder 'Dataset' sudah ada di dalamnya.
# Gunakan Raw String (r"...") untuk menghindari masalah backslash di Windows.
LARAVEL_PROJECT_PATH = r"C:\Users\nadhi\Herd\Qash"
DATASET_SUBDIR = "storage" # Basis folder storage Laravel (tanpa trailing slash)

# Path absolut ke folder Dataset
DATASET_PATH = os.path.join(LARAVEL_PROJECT_PATH, DATASET_SUBDIR)

# Path ke folder embeddings (bisa tetap di proyek Python atau dipindah juga)
# Untuk saat ini, kita biarkan di proyek Python
EMB_DIR = "embeddings"

# Nama subfolder untuk foto wajah per user
# Sesuaikan agar cocok dengan struktur yang sudah ada di Laravel
FACIAL_SUBDIR_NAME = "facial-recog"

# --- Multi‑tenant helpers (Stancl Tenancy) ---
# Arahkan dataset & embeddings per tenant agar tidak bentrok antar penyewa.
# Struktur yang diminta:
#   {LARAVEL_PROJECT_PATH}/storage/{tenant_id}/app/public/users/{user}/{FACIAL_SUBDIR_NAME}/...
def tenant_storage_root(tenant_id: str) -> str:
    """Root penyimpanan publik khusus tenant dalam folder storage Laravel.

    Contoh hasil: C:\\...\\laravel\\storage\\{tenant_id}\\app\\public
    """
    return os.path.join(DATASET_PATH, str(tenant_id), "app", "public")


def tenant_users_root(tenant_id: str) -> str:
    """Folder dasar untuk menyimpan dataset wajah per tenant.

    Hasil akhir folder orang akan menjadi:
      {tenant_users_root}/{nama}/{FACIAL_SUBDIR_NAME}
    """
    return os.path.join(tenant_storage_root(tenant_id), "users")


def tenant_emb_dir(tenant_id: str) -> str:
    """Folder embeddings per tenant agar model tidak saling timpa."""
    return os.path.join(EMB_DIR, "tenants", str(tenant_id))

# Path ke file absensi (CSV atau bisa dihapus jika logging via DB)
CSV_LOG_PATH = r"D:/Semester 8/Capstong/TA2/absensi.csv"

# --- Konfigurasi API Server ---
# Host dan port untuk Flask API. Ubah API_PORT jika bentrok dengan Laravel.
# Bisa juga dioverride lewat env var FACE_API_PORT saat menjalankan.
API_HOST = "0.0.0.0"
API_PORT = 5001

# --- Konfigurasi Geolokasi ---
# Ganti dengan koordinat target Anda
#TARGET_LATITUDE = -6.9926
#TARGET_LONGITUDE = 110.4208
#ACCEPTABLE_RADIUS_METERS = 50

# --- Konfigurasi Database (jika digunakan) ---
DB_NAME = "qash_demo"
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root', # Ganti 'insertuserhere'
    'password': 'Miscrits24!', # Ganti 'insertpasswordhere'
    'database': DB_NAME
}

# --- Konfigurasi Lainnya ---
# Mode Logger ('CSV' atau 'MYSQL')
LOGGING_MODE = 'MYSQL' # Ganti ke 'MYSQL' jika database siap

# Thresholds
RECOGNITION_THRESHOLD_ABSENSI = 0.40 # 60% kemiripan untuk Absensi 1:1
RECOGNITION_THRESHOLD_REGISTER = 0.30 # 70% kemiripan untuk Cek Duplikasi Wajah
DETECTION_QUALITY_THRESHOLD_REGISTER = 0.85 # Kualitas gambar min 85% saat daftar
VALIDATE_POSE_ENABLED = False # Aktifkan/nonaktifkan validasi pose
POSE_THRESHOLD_SIDE = 30
POSE_THRESHOLD_UP_DOWN = 30

# --- Jangan ubah di bawah ini ---
MODEL_PATH = os.path.join(EMB_DIR, "knn_model.pkl")
NAMES_PATH = os.path.join(EMB_DIR, "names.pkl")

# Pastikan folder embeddings ada di proyek Python
os.makedirs(EMB_DIR, exist_ok=True)
# Pastikan folder Dataset target ada
try:
    os.makedirs(DATASET_PATH, exist_ok=True)
    print(f"✅ Memastikan folder Dataset ada di: {DATASET_PATH}")
except OSError as e:
    print(f"❌ GAGAL membuat/mengakses folder Dataset di: {DATASET_PATH}. Error: {e}")
    # Anda mungkin ingin keluar jika path dataset krusial tidak bisa diakses
    # import sys
    # sys.exit(1)

print(f"💡 Konfigurasi dimuat: Dataset di '{DATASET_PATH}', Embeddings di '{EMB_DIR}'")

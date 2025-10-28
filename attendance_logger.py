import os
import csv
from datetime import datetime
# 🌟 Impor konfigurasi
import config

# === KONFIGURASI (diambil dari config.py) ===
DB_NAME = config.DB_NAME
CSV_PATH = config.CSV_LOG_PATH # Gunakan path dari config
DB_CONFIG = config.DB_CONFIG
LOGGING_MODE = config.LOGGING_MODE

# Impor pustaka MySQL (akan digunakan nanti)
try:
    import mysql.connector
except ImportError:
    pass # Biarkan error terjadi di MySQLLogger jika mode MYSQL dipilih tanpa instalasi

# === KELAS LOGGER MYSQL (Tidak berubah) ===
class MySQLLogger:
    def __init__(self):
        # ... (Sama seperti sebelumnya) ...
        self.is_connected = False
        if 'mysql' not in sys.modules:
             print("❌ Logger: Gagal koneksi MySQL. Pustaka 'mysql-connector-python' belum terinstal.")
             return
        try:
            self.db = mysql.connector.connect(**DB_CONFIG)
            self.cursor = self.db.cursor()
            print(f"✅ Logger: Koneksi MySQL ke '{DB_NAME}' berhasil.")
            self.is_connected = True
        except Exception as e:
            print(f"❌ Logger: Gagal koneksi ke MySQL. Error: {e}")

    def log_attendance(self, name):
        # ... (Sama seperti sebelumnya) ...
        if not self.is_connected: return False
        now = datetime.now(); tanggal = now.strftime("%Y-%m-%d"); jam = now.strftime("%H:%M:%S"); status = "hadir"
        query = "INSERT INTO table_absensi (tanggal, jam, nama, status) VALUES (%s, %s, %s, %s)" # Ganti nama tabel jika perlu
        values = (tanggal, jam, name, status)
        try:
            self.cursor.execute(query, values); self.db.commit(); return True
        except Exception as e:
            print(f"❌ Logger: Gagal INSERT ke MySQL. Error: {e}"); self.db.rollback(); return False

# === KELAS LOGGER CSV (Tidak berubah) ===
class CSVLogger:
    def __init__(self):
        self._initialize_csv()

    def _initialize_csv(self):
        # ... (Sama seperti sebelumnya) ...
        csv_dir = os.path.dirname(CSV_PATH)
        os.makedirs(csv_dir, exist_ok=True) # Pastikan direktori ada
        if not os.path.exists(CSV_PATH) or os.path.getsize(CSV_PATH) == 0:
            try:
                with open(CSV_PATH, mode='w', newline='') as file:
                    writer = csv.writer(file); writer.writerow(["tanggal", "jam", "nama", "status"])
                print(f"✅ Logger: File CSV dibuat di {CSV_PATH}")
            except Exception as e: print(f"❌ Logger: Gagal inisialisasi CSV. Error: {e}")

    def log_attendance(self, name):
        # ... (Sama seperti sebelumnya) ...
        now = datetime.now(); tanggal = now.strftime("%Y-%m-%d"); jam = now.strftime("%H:%M:%S"); status = "hadir"; row = [tanggal, jam, name, status]
        try:
            with open(CSV_PATH, mode='a', newline='') as file: writer = csv.writer(file); writer.writerow(row)
            return True
        except Exception as e: print(f"❌ Logger: Gagal menulis ke CSV. Error: {e}"); return False

# === FUNGSI UTAMA (Interface) ===
# Inisialisasi Logger berdasarkan mode di config.py
import sys # Perlu sys untuk cek modul mysql
if LOGGING_MODE == 'MYSQL':
    ATTENDANCE_LOGGER = MySQLLogger()
elif LOGGING_MODE == 'CSV':
    ATTENDANCE_LOGGER = CSVLogger()
else:
    # Fallback ke CSV jika mode tidak valid
    print(f"⚠️ Mode logging '{LOGGING_MODE}' tidak valid. Menggunakan CSV.")
    ATTENDANCE_LOGGER = CSVLogger()


def log_attendance_to_csv(name): # Nama fungsi tetap sama
    """Interface untuk mencatat absensi menggunakan logger yang dipilih."""
    return ATTENDANCE_LOGGER.log_attendance(name)

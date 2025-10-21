# attendance_logger.py (KODE LENGKAP - SIAP UNTUK MYSQL)

import os
import csv
from datetime import datetime

# Impor pustaka MySQL (akan digunakan nanti)
try:
    import mysql.connector
except ImportError:
    # Ini akan mencegah error jika pustaka belum diinstal.
    pass 

# === KONFIGURASI DATABASE/CSV ===
DB_NAME = "qash_demo"
CSV_PATH = r"D:/Semester 8/Capstong/TA2/absensi.csv"

# Konfigurasi MySQL (Placeholder)
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root', # Ganti dengan username MySQL Anda
    'password': 'Aptx4869', # Ganti dengan password MySQL Anda
    'database': DB_NAME
}


# ============================================================
# KELAS LOGGER DATABASE (MySQL - Placeholder)
# ============================================================
class MySQLLogger:
    def __init__(self):
        try:
            # 🌟 PERBAIKAN: Gunakan 'mysql.connector' (nama yang diimpor)
            self.db = mysql.connector.connect(**DB_CONFIG)
            self.cursor = self.db.cursor()
            print(f"✅ Logger: Koneksi MySQL ke '{DB_NAME}' berhasil.")
            self.is_connected = True
        except NameError:
            # This error happens if 'mysql-connector-python' is not installed
            print("❌ Logger: Gagal koneksi ke MySQL. Pustaka 'mysql-connector-python' belum terinstal.")
            print("Jalankan: pip install mysql-connector-python")
            self.is_connected = False
        except Exception as e:
            print(f"❌ Logger: Gagal koneksi ke MySQL. Error: {e}")
            self.is_connected = False

    def log_attendance(self, name):
        if not self.is_connected:
            print("⚠️ Logger: Gagal mencatat. Database tidak terhubung.")
            return False

        now = datetime.now()
        tanggal = now.strftime("%Y-%m-%d")
        jam = now.strftime("%H:%M:%S")
        status = "hadir"
        
        # 🌟 Ganti 'table_absensi' dengan nama tabel Anda yang sebenarnya
        query = """
            INSERT INTO table_absensi (tanggal, jam, nama, status)
            VALUES (%s, %s, %s, %s)
        """
        values = (tanggal, jam, name, status)

        try:
            self.cursor.execute(query, values)
            self.db.commit()
            return True
        except Exception as e:
            print(f"❌ Logger: Gagal INSERT data ke MySQL. Error: {e}")
            self.db.rollback()
            return False

# ============================================================
# KELAS LOGGER CSV (CSV - Default)
# ============================================================
class CSVLogger:
    def __init__(self):
        self._initialize_csv()

    def _initialize_csv(self):
        """Memastikan file CSV ada dan memiliki header."""
        if not os.path.exists(CSV_PATH) or os.path.getsize(CSV_PATH) == 0:
            try:
                with open(CSV_PATH, mode='w', newline='') as file:
                    writer = csv.writer(file)
                    writer.writerow(["tanggal", "jam", "nama", "status"])
                print(f"✅ Logger: File CSV dibuat di {CSV_PATH}")
            except Exception as e:
                print(f"❌ Logger: Gagal inisialisasi CSV. Error: {e}")

    def log_attendance(self, name):
        now = datetime.now()
        tanggal = now.strftime("%Y-%m-%d")
        jam = now.strftime("%H:%M:%S")
        status = "hadir"
        row = [tanggal, jam, name, status]

        try:
            with open(CSV_PATH, mode='a', newline='') as file:
                writer = csv.writer(file)
                writer.writerow(row)
            return True
        except Exception as e:
            print(f"❌ Logger: Gagal menulis ke CSV. Error: {e}")
            return False

# ============================================================
# FUNGSI UTAMA (Interface yang Dipanggil oleh face_recognize_service.py)
# ============================================================

# 🌟 PENGATURAN MODE: Ganti 'CSV' menjadi 'MYSQL' jika Anda ingin beralih
LOGGING_MODE = 'MYSQL' 
'' 

# Inisialisasi Logger
if LOGGING_MODE == 'MYSQL':
    ATTENDANCE_LOGGER = MySQLLogger()
elif LOGGING_MODE == 'CSV':
    ATTENDANCE_LOGGER = CSVLogger()
else:
    raise ValueError("LOGGING_MODE tidak valid. Pilih 'CSV' atau 'MYSQL'.")


def log_attendance_to_csv(name):
    """
    Fungsi ini adalah interface yang dipanggil oleh service.
    Nama fungsi dipertahankan sebagai 'log_attendance_to_csv' untuk menghindari
    perubahan pada face_recognize_service.py.
    """
    # Panggil metode log_attendance dari objek logger yang sudah dipilih
    return ATTENDANCE_LOGGER.log_attendance(name)
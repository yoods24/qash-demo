# face_api.py (KODE LENGKAP - FINAL)

import os
import cv2
import shutil
import pickle
import numpy as np
import time  # Pastikan 'time' diimpor
import traceback # Untuk logging error detail
import csv # Untuk pengecekan file CSV di main block
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
from insightface.app import FaceAnalysis
from flask_cors import CORS
# Impor layanan
import face_register_service
import face_recognize_service
# Impor path CSV dari logger untuk pengecekan awal
from attendance_logger import CSV_PATH as ATTENDANCE_CSV_PATH

# === KONFIGURASI DASAR ===
app = Flask(__name__)
CORS(app) # Mengizinkan akses dari origin berbeda (Laravel)

# Ambil path dari service agar konsisten
# Diasumsikan path ini didefinisikan di face_register_service.py
DATASET_PATH = face_register_service.DATASET_PATH
EMB_DIR = face_register_service.EMB_DIR
MODEL_PATH = os.path.join(EMB_DIR, "knn_model.pkl")
NAMES_PATH = os.path.join(EMB_DIR, "names.pkl")

# Pastikan direktori ada
os.makedirs(DATASET_PATH, exist_ok=True)
os.makedirs(EMB_DIR, exist_ok=True)

# Inisialisasi InsightFace (Diperlukan oleh kedua layanan dan diteruskan sebagai argumen)
print("🚀 Inisialisasi InsightFace...")
try:
    app_insight = FaceAnalysis(providers=['CPUExecutionProvider'])
    # Menggunakan konfigurasi optimal untuk akurasi yang lebih tinggi
    app_insight.prepare(ctx_id=0, det_size=(640, 640))
    print("✅ InsightFace siap dengan konfigurasi optimal.")
except Exception as e:
    print(f"❌ FATAL: Gagal inisialisasi InsightFace. Error: {e}")
    # Jika InsightFace gagal, API tidak bisa berfungsi
    exit()


# Variabel global untuk menyimpan embeddings di memori
LOADED_EMBEDDINGS = np.array([])
LOADED_NAMES = np.array([])


# === Fungsi: Muat Model Embedding ===
def load_embeddings():
    """Memuat data embeddings wajah dan nama dari file pickle."""
    global LOADED_EMBEDDINGS, LOADED_NAMES
    print("🧠 Memuat model embedding...")

    if os.path.exists(MODEL_PATH) and os.path.exists(NAMES_PATH):
        try:
            # Jeda singkat (0.5s) untuk memastikan file selesai ditulis ke disk
            # oleh proses retrain sebelum mencoba membacanya.
            time.sleep(0.5)

            with open(MODEL_PATH, "rb") as f:
                LOADED_EMBEDDINGS = pickle.load(f)
            with open(NAMES_PATH, "rb") as f:
                LOADED_NAMES = pickle.load(f)

            # Validasi tipe data setelah loading
            if not isinstance(LOADED_EMBEDDINGS, np.ndarray):
                print("⚠️ Tipe data embeddings tidak valid setelah dimuat. Resetting.")
                LOADED_EMBEDDINGS = np.array([])
                LOADED_NAMES = np.array([])
            elif LOADED_EMBEDDINGS.size > 0:
                print(f"✅ Model berhasil dimuat: {len(LOADED_EMBEDDINGS)} total embeddings dari {len(set(LOADED_NAMES))} orang.")
            else:
                print("⚠️ File embedding kosong. Model siap digunakan.")
                LOADED_EMBEDDINGS = np.array([])
                LOADED_NAMES = np.array([])

        except (pickle.UnpicklingError, EOFError, FileNotFoundError) as e:
            print(f"❌ Gagal memuat file embedding (mungkin rusak atau belum ada): {e}")
            LOADED_EMBEDDINGS = np.array([])
            LOADED_NAMES = np.array([])
        except Exception as e:
            print(f"❌ Gagal memuat file embedding karena error tak terduga: {e}")
            LOADED_EMBEDDINGS = np.array([])
            LOADED_NAMES = np.array([])
    else:
        print("⚠️ File model embedding tidak ditemukan. Jalankan main.py atau lakukan pendaftaran pertama.")
        LOADED_EMBEDDINGS = np.array([])
        LOADED_NAMES = np.array([])


# Panggil load_embeddings saat server pertama kali dijalankan
load_embeddings()


# --- Endpoint: Register wajah (Hanya Routing) ---
@app.route('/register', methods=['POST'])
def register():
    """Endpoint untuk menangani permintaan pendaftaran wajah."""
    name = request.form.get("name")

    if not name:
        return jsonify({"message": "Nama tidak boleh kosong.", "status": "error"}), 400

    image_file = request.files.get("image")
    if not image_file:
        return jsonify({"message": "Tidak ada gambar diterima.", "status": "error"}), 400

    try:
        # Panggil fungsi dari layanan registrasi (Blocking Retrain di dalamnya)
        result = face_register_service.handle_register_frame(
            app_insight,
            LOADED_EMBEDDINGS,
            LOADED_NAMES,
            request.form,
            image_file
        )

        # MUAT ULANG EMBEDDINGS JIKA REGISTRASI SELESAI DAN SUKSES
        # 'finished' menandakan retrain dipanggil dan berhasil
        if result.get('status') == 'finished':
            # Tambahkan jeda 1 detik setelah retrain selesai sebelum memuat ulang
            print("⏳ Retrain selesai. Menunggu 1 detik sebelum memuat ulang model...")
            time.sleep(1)
            load_embeddings() # Muat data baru ke memori server

        return jsonify(result)

    except Exception as e:
        print(f"❌ INTERNAL SERVER ERROR during registration: {e}")
        traceback.print_exc() # Cetak traceback untuk debugging
        return jsonify({"message": "❌ Terjadi kesalahan internal server saat memproses registrasi.", "status": "error", "fatalError": True}), 500


# --- Endpoint: Batalkan pendaftaran ---
@app.route('/cancel_register', methods=['POST'])
def cancel_register():
    """Endpoint untuk membatalkan pendaftaran dan menghapus data sementara."""
    name = request.form.get("name")
    if not name:
        return jsonify({"message": "Nama tidak boleh kosong.", "status": "error"}), 400

    # Pastikan secure_filename digunakan untuk keamanan
    person_dir = os.path.join(DATASET_PATH, secure_filename(name))
    if os.path.exists(person_dir):
        try:
            shutil.rmtree(person_dir)
            return jsonify({"message": "Data pendaftaran dihapus.", "status": "success"})
        except Exception as e:
            print(f"❌ Error saat menghapus folder {person_dir}: {e}")
            return jsonify({"message": "Gagal menghapus folder data.", "status": "error"}), 500
    else:
        # Jika folder tidak ada, anggap pembatalan berhasil
        return jsonify({"message": "Proses pembatalan selesai (folder tidak ditemukan).", "status": "success"})


# --- Endpoint: Recognize wajah (Hanya Routing) ---
@app.route('/recognize', methods=['POST'])
def recognize():
    """Endpoint untuk menangani permintaan absensi (pengenalan wajah)."""
    global LOADED_EMBEDDINGS, LOADED_NAMES

    # Coba muat ulang jika kosong (diperlukan jika server dijalankan tanpa data)
    if LOADED_EMBEDDINGS.size == 0:
        load_embeddings()
        # Jika masih kosong setelah mencoba muat ulang, kirim error
        if LOADED_EMBEDDINGS.size == 0:
             return jsonify({"message": "❌ Model wajah belum siap (data kosong). Daftarkan wajah terlebih dahulu.", "status": "error"}), 503


    image_file = request.files.get("image")
    if not image_file:
        return jsonify({"message": "Tidak ada gambar diterima.", "status": "error"}), 400

    # Ambil data geolokasi dan username
    latitude = request.form.get("latitude")
    longitude = request.form.get("longitude")
    username = request.form.get("username")

    if not username:
        return jsonify({"message": "❌ Username pengguna tidak diterima dari frontend.", "status": "error"}), 400


    try:
        # Panggil fungsi dari layanan recognition
        result = face_recognize_service.handle_recognition_frame(
            app_insight,
            LOADED_EMBEDDINGS,
            LOADED_NAMES,
            image_file,
            latitude,
            longitude,
            username
        )
        return jsonify(result)

    except Exception as e:
        print(f"❌ INTERNAL SERVER ERROR during recognition: {e}")
        traceback.print_exc() # Cetak traceback untuk debugging
        return jsonify({"message": "❌ Terjadi kesalahan internal server saat memproses absensi.", "status": "error"}), 500


# === Jalankan API ===
if __name__ == '__main__':
    # Logika pembuatan file CSV absensi awal saat server dimulai
    if not os.path.exists(ATTENDANCE_CSV_PATH):
        try:
            with open(ATTENDANCE_CSV_PATH, mode='w', newline='') as file:
                writer = csv.writer(file)
                writer.writerow(["tanggal", "jam", "nama", "status"])
            print(f"⚠️ Membuat file CSV absensi kosong dengan header di: {ATTENDANCE_CSV_PATH}")
        except Exception as e:
            print(f"❌ Gagal membuat file CSV absensi: {e}")

    # Jalankan server Flask
    # host='0.0.0.0' agar bisa diakses dari jaringan lokal (termasuk HP)
    # debug=True untuk development, ganti ke False saat production
    print("🚀 Menjalankan server Flask...")
    app.run(host="0.0.0.0", port=5000, debug=True)

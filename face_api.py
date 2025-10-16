# face_api.py (KODE LENGKAP - FINAL)

import os
import cv2
import shutil
import pickle
import numpy as np
import time  # 🌟 Pastikan 'time' diimpor
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
from insightface.app import FaceAnalysis
from flask_cors import CORS
# Impor layanan
import face_register_service
import face_recognize_service

# === KONFIGURASI DASAR ===
app = Flask(__name__)
CORS(app)

# Ambil path dari service agar konsisten
DATASET_PATH = face_register_service.DATASET_PATH
EMB_DIR = face_register_service.EMB_DIR
MODEL_PATH = os.path.join(EMB_DIR, "knn_model.pkl")
NAMES_PATH = os.path.join(EMB_DIR, "names.pkl")

os.makedirs(DATASET_PATH, exist_ok=True)
os.makedirs(EMB_DIR, exist_ok=True)

# Inisialisasi InsightFace (Diperlukan oleh kedua layanan)
print("🚀 Inisialisasi InsightFace...")
app_insight = FaceAnalysis(providers=['CPUExecutionProvider'])
app_insight.prepare(ctx_id=0, det_size=(640, 640))
print("✅ InsightFace siap dengan konfigurasi optimal.")

LOADED_EMBEDDINGS = np.array([])
LOADED_NAMES = np.array([])


# === Fungsi: Muat Model Embedding ===
def load_embeddings():
    global LOADED_EMBEDDINGS, LOADED_NAMES
    print("🧠 Memuat model embedding...")

    if os.path.exists(MODEL_PATH) and os.path.exists(NAMES_PATH):
        try:
            # 🌟 Jeda singkat (0.5s) untuk memastikan file selesai ditulis ke disk
            time.sleep(0.5)

            with open(MODEL_PATH, "rb") as f:
                LOADED_EMBEDDINGS = pickle.load(f)
            with open(NAMES_PATH, "rb") as f:
                LOADED_NAMES = pickle.load(f)

            if LOADED_EMBEDDINGS.size > 0 and isinstance(LOADED_EMBEDDINGS, np.ndarray):
                print(f"✅ Model berhasil dimuat: {len(LOADED_EMBEDDINGS)} total embeddings dari {len(set(LOADED_NAMES))} orang.")
            else:
                print("⚠️ File embedding kosong. Model siap digunakan.")
                LOADED_EMBEDDINGS = np.array([])
                LOADED_NAMES = np.array([])

        except Exception as e:
            print(f"❌ Gagal memuat file embedding: {e}")
            LOADED_EMBEDDINGS = np.array([])
            LOADED_NAMES = np.array([])
    else:
        print("⚠️ Model embedding tidak ditemukan. Jalankan main.py terlebih dahulu.")
        LOADED_EMBEDDINGS = np.array([])
        LOADED_NAMES = np.array([])


load_embeddings()


# --- Endpoint: Register wajah (Hanya Routing) ---
@app.route('/register', methods=['POST'])
def register():
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

        # MUAT ULANG EMBEDDINGS JIKA REGISTRASI SELESAI
        if result.get('status') == 'finished':
            # 🌟 Tambahkan jeda 1 detik setelah retrain selesai sebelum memuat ulang
            print("⏳ Retrain selesai. Menunggu 1 detik sebelum memuat ulang model...")
            time.sleep(1)
            load_embeddings()

        return jsonify(result)

    except Exception as e:
        print(f"❌ INTERNAL SERVER ERROR during registration: {e}")
        return jsonify({"message": "❌ Terjadi kesalahan internal server saat memproses registrasi.", "status": "error", "fatalError": True}), 500


# --- Endpoint: Batalkan pendaftaran ---
@app.route('/cancel_register', methods=['POST'])
def cancel_register():
    name = request.form.get("name")
    if not name:
        return jsonify({"message": "Nama tidak boleh kosong.", "status": "error"}), 400

    person_dir = os.path.join(DATASET_PATH, secure_filename(name))
    if os.path.exists(person_dir):
        shutil.rmtree(person_dir)
        return jsonify({"message": "Data pendaftaran dihapus.", "status": "success"})
    else:
        return jsonify({"message": "Proses pembatalan selesai.", "status": "success"})


# --- Endpoint: Recognize wajah (Hanya Routing) ---
@app.route('/recognize', methods=['POST'])
def recognize():
    global LOADED_EMBEDDINGS, LOADED_NAMES

    if LOADED_EMBEDDINGS.size == 0:
        load_embeddings()

    image_file = request.files.get("image")
    if not image_file:
        return jsonify({"message": "Tidak ada gambar diterima.", "status": "error"}), 400

    try:
        # Panggil fungsi dari layanan recognition
        result = face_recognize_service.handle_recognition_frame(
            app_insight,
            LOADED_EMBEDDINGS,
            LOADED_NAMES,
            image_file
        )
        return jsonify(result)

    except Exception as e:
        print(f"❌ INTERNAL SERVER ERROR during recognition: {e}")
        return jsonify({"message": "❌ Terjadi kesalahan internal server saat memproses absensi.", "status": "error"}), 500


# === Jalankan API ===
if __name__ == '__main__':
    # Logika pembuatan CSV awal saat server dimulai
    import csv
    # Pastikan path ini sesuai
    CSV_PATH_MAIN = r"D:/Semester 8/Capstong/TA2/absensi.csv"
    if not os.path.exists(CSV_PATH_MAIN):
        try:
            with open(CSV_PATH_MAIN, mode='w', newline='') as file:
                writer = csv.writer(file)
                writer.writerow(["tanggal", "jam", "nama", "status"])
            print(f"⚠️ Membuat file CSV kosong dengan header di: {CSV_PATH_MAIN}")
        except Exception as e:
            print(f"❌ Gagal membuat file CSV: {e}")

    app.run(host="0.0.0.0", port=5000, debug=True)
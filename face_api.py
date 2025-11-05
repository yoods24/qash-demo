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
import config
# Impor layanan
import face_register_service
import face_recognize_service
# Impor path CSV dari logger untuk pengecekan awal
from attendance_logger import CSV_PATH as ATTENDANCE_CSV_PATH

# === KONFIGURASI DASAR ===
app = Flask(__name__)
CORS(app) # Mengizinkan akses dari origin berbeda (Laravel)

# Ambil path default dari service (legacy, non-tenant)
DATASET_PATH = face_register_service.DATASET_PATH
EMB_DIR = face_register_service.EMB_DIR

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


def _load_embeddings_from_dir(emb_dir: str):
    """Memuat embeddings & names dari direktori embeddings tertentu."""
    model_path = os.path.join(emb_dir, "knn_model.pkl")
    names_path = os.path.join(emb_dir, "names.pkl")
    if os.path.exists(model_path) and os.path.exists(names_path):
        try:
            time.sleep(0.2)
            with open(model_path, "rb") as f:
                embs = pickle.load(f)
            with open(names_path, "rb") as f:
                names = pickle.load(f)
            if not isinstance(embs, np.ndarray):
                return np.array([]), np.array([])
            return embs, names
        except Exception as e:
            print(f"❌ Gagal memuat embeddings di {emb_dir}: {e}")
            return np.array([]), np.array([])
    return np.array([]), np.array([])

def _require_tenant_id():
    tenant_id = request.headers.get("X-Tenant-Id") or request.form.get("tenant_id") or request.args.get("tenant_id")
    if not tenant_id:
        return None, (jsonify({"message": "❌ tenant_id tidak diterima. Sertakan header X-Tenant-Id atau field tenant_id.", "status": "error"}), 400)
    return tenant_id, None


# --- Endpoint: Register wajah (Hanya Routing) ---
@app.route('/register', methods=['POST'])
def register():
    """Endpoint untuk menangani permintaan pendaftaran wajah."""
    # Tentukan tenant lebih dulu
    tenant_id, err = _require_tenant_id()
    if err:
        return err

    name = request.form.get("name")

    if not name:
        return jsonify({"message": "Nama tidak boleh kosong.", "status": "error"}), 400

    image_file = request.files.get("image")
    if not image_file:
        return jsonify({"message": "Tidak ada gambar diterima.", "status": "error"}), 400

    try:
        # Tentukan path dataset & embeddings khusus tenant
        dataset_base = config.tenant_users_root(tenant_id)
        emb_dir = config.tenant_emb_dir(tenant_id)
        os.makedirs(dataset_base, exist_ok=True)
        os.makedirs(emb_dir, exist_ok=True)

        # Muat embeddings milik tenant ini untuk cek duplikasi
        LOADED_EMBEDDINGS, LOADED_NAMES = _load_embeddings_from_dir(emb_dir)
        # Panggil fungsi dari layanan registrasi (Blocking Retrain di dalamnya)
        result = face_register_service.handle_register_frame(
            app_insight,
            LOADED_EMBEDDINGS,
            LOADED_NAMES,
            request.form,
            image_file,
            dataset_base_dir=dataset_base,
            emb_dir=emb_dir
        )

        # MUAT ULANG EMBEDDINGS JIKA REGISTRASI SELESAI DAN SUKSES
        if result.get('status') == 'finished':
            print("⏳ Retrain selesai untuk tenant {tenant_id}. Memuat ulang model tenant...")
            time.sleep(0.5)
            _load_embeddings_from_dir(emb_dir)

        return jsonify(result)

    except Exception as e:
        print(f"❌ INTERNAL SERVER ERROR during registration: {e}")
        traceback.print_exc() # Cetak traceback untuk debugging
        return jsonify({"message": "❌ Terjadi kesalahan internal server saat memproses registrasi.", "status": "error", "fatalError": True}), 500


# --- Endpoint: Batalkan pendaftaran ---
@app.route('/cancel_register', methods=['POST'])
def cancel_register():
    """Endpoint untuk membatalkan pendaftaran dan menghapus data sementara."""
    # Tenant diperlukan untuk menemukan lokasi data yang benar
    tenant_id, err = _require_tenant_id()
    if err:
        return err

    name = request.form.get("name")
    if not name:
        return jsonify({"message": "Nama tidak boleh kosong.", "status": "error"}), 400

    # Pastikan secure_filename digunakan untuk keamanan
    dataset_base = config.tenant_users_root(tenant_id)
    person_dir = os.path.join(dataset_base, secure_filename(name), config.FACIAL_SUBDIR_NAME)
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
    # Tenant wajib
    tenant_id, err = _require_tenant_id()
    if err:
        return err


    image_file = request.files.get("image")
    if not image_file:
        return jsonify({"message": "Tidak ada gambar diterima.", "status": "error"}), 400

    # Muat embeddings khusus tenant ini
    emb_dir = config.tenant_emb_dir(tenant_id)
    LOADED_EMBEDDINGS, LOADED_NAMES = _load_embeddings_from_dir(emb_dir)
    if LOADED_EMBEDDINGS.size == 0:
        return jsonify({"message": "❌ Model wajah tenant belum siap (data kosong). Daftarkan wajah terlebih dahulu.", "status": "error"}), 503

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
    host = getattr(config, "API_HOST", "0.0.0.0")
    port = int(os.getenv("FACE_API_PORT", getattr(config, "API_PORT", 5001)))
    print(f"🌐 Listening on {host}:{port}")
    app.run(host=host, port=port, debug=True)

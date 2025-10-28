import os
import cv2
import pickle
import numpy as np
from insightface.app import FaceAnalysis
# 🌟 PERBAIKAN: Impor konfigurasi terpusat
import config

# === KONFIGURASI DASAR (diambil dari config.py) ===
DATASET_PATH = config.DATASET_PATH
EMB_DIR = config.EMB_DIR
MODEL_PATH = config.MODEL_PATH
NAMES_PATH = config.NAMES_PATH

# === INISIALISASI INSIGHTFACE ===
# Script ini perlu inisialisasi sendiri
print("(main.py) 🚀 Inisialisasi InsightFace...")
app_main = FaceAnalysis(providers=['CPUExecutionProvider'])
app_main.prepare(ctx_id=0, det_size=(640, 640))
print("(main.py) ✅ InsightFace siap.")

# === FUNGSI MUAT DATASET & EKSTRAKSI EMBEDDING ===
def load_dataset_and_extract_embeddings():
    embeddings = []
    labels = []

    print(f"(main.py) 📂 Memindai dataset di: {DATASET_PATH}...") # Gunakan path dari config
    # Iterasi melalui setiap folder nama di dalam Dataset
    for person_name in os.listdir(DATASET_PATH):
        person_dir = os.path.join(DATASET_PATH, person_name)
        if not os.path.isdir(person_dir) or len(os.listdir(person_dir)) == 0:
            continue

        print(f"(main.py)   Memproses gambar untuk: {person_name}")
        image_count = 0
        for img_file in os.listdir(person_dir):
            img_path = os.path.join(person_dir, img_file)
            if not os.path.isfile(img_path):
                continue

            img = cv2.imread(img_path)
            if img is None:
                print(f"(main.py)     ⚠️ Gagal membaca {img_file}, dilewati.")
                continue

            faces = app_main.get(img)
            if not faces:
                print(f"(main.py)     ⚠️ Tidak ada wajah terdeteksi di {img_file}, dilewati.")
                continue
            if len(faces) > 1:
                 print(f"(main.py)     ⚠️ Lebih dari satu wajah di {img_file}, hanya gunakan yang pertama.")

            emb = faces[0].embedding
            emb_norm = emb / (np.linalg.norm(emb) + 1e-12)
            embeddings.append(emb_norm)
            labels.append(person_name)
            image_count += 1

        print(f"(main.py)     {image_count} gambar diproses untuk {person_name}.")

    embeddings_np = np.array(embeddings, dtype=np.float32)
    labels_np = np.array(labels)

    print(f"(main.py) ✅ Dataset selesai diproses: {len(labels_np)} total embeddings dari {len(set(labels_np))} orang.")
    return embeddings_np, labels_np

# === FUNGSI SIMPAN EMBEDDING ===
def save_embeddings(embeddings, labels):
    try:
        # Gunakan path dari config
        with open(MODEL_PATH, "wb") as f:
            pickle.dump(embeddings, f)
        with open(NAMES_PATH, "wb") as f:
            pickle.dump(labels, f)
        print(f"(main.py) 💾 Embedding berhasil disimpan ke: {EMB_DIR}/")
        return True
    except Exception as e:
        print(f"(main.py) ❌ Gagal menyimpan file embedding: {e}")
        return False

# === FUNGSI UTAMA RETRAIN ===
def retrain_model():
    print("\n(main.py) === MEMULAI PROSES RETRAIN MODEL ===")
    embeddings, labels = load_dataset_and_extract_embeddings()

    if embeddings.size == 0:
        embeddings = np.array([], dtype=np.float32)
        labels = np.array([], dtype=np.str_)
        print("(main.py) ⚠️ Tidak ada data wajah ditemukan di Dataset. Menyimpan model kosong.")

    save_success = save_embeddings(embeddings, labels)
    if save_success:
        print("(main.py) === PROSES RETRAIN SELESAI ===\n")
    else:
        print("(main.py) === PROSES RETRAIN GAGAL ===\n")
    return save_success # Mengembalikan status sukses/gagal

# === BLOK EKSEKUSI JIKA DIJALANKAN SEBAGAI SCRIPT ===
if __name__ == "__main__":
    # Pastikan folder ada sebelum menjalankan (meskipun config.py juga mengecek)
    os.makedirs(DATASET_PATH, exist_ok=True)
    os.makedirs(EMB_DIR, exist_ok=True)
    retrain_model()


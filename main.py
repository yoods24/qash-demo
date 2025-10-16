# main.py (KODE LENGKAP)

import os
import cv2
import pickle
import numpy as np
from insightface.app import FaceAnalysis

# ============================================================
# 🔧 KONFIGURASI DASAR
# ============================================================
DATASET_DIR = r"D:/Semester 8/Capstong/TA2/Dataset"
EMB_DIR = "embeddings"
MODEL_PATH = os.path.join(EMB_DIR, "knn_model.pkl") 
NAMES_PATH = os.path.join(EMB_DIR, "names.pkl")

os.makedirs(DATASET_DIR, exist_ok=True)
os.makedirs(EMB_DIR, exist_ok=True)

# ============================================================
# 🤖 INISIALISASI INSIGHTFACE
# ============================================================
print("🚀 Inisialisasi InsightFace...")
app = FaceAnalysis(providers=['CPUExecutionProvider'])
app.prepare(ctx_id=0, det_size=(640, 640)) # Menggunakan konfigurasi optimal
print("✅ InsightFace siap dengan konfigurasi optimal.")

# ============================================================
# 📦 FUNGSI MUAT DATASET & EMBEDDING
# ============================================================
def load_dataset():
    embeddings = []
    labels = []

    print("📂 Memindai dataset...")
    for person_name in os.listdir(DATASET_DIR):
        person_dir = os.path.join(DATASET_DIR, person_name)
        if not os.path.isdir(person_dir) or len(os.listdir(person_dir)) == 0:
            continue

        for img_file in os.listdir(person_dir):
            img_path = os.path.join(person_dir, img_file)
            img = cv2.imread(img_path)
            if img is None:
                continue

            faces = app.get(img)
            if not faces:
                continue

            emb = faces[0].embedding
            emb = emb / (np.linalg.norm(emb) + 1e-12)
            embeddings.append(emb)
            labels.append(person_name)

    embeddings = np.array(embeddings, dtype=np.float32)
    labels = np.array(labels)

    print(f"✅ Dataset selesai dimuat: {len(labels)} wajah dari {len(set(labels))} orang.")
    return embeddings, labels

# ============================================================
# 💾 SIMPAN MODEL EMBEDDING
# ============================================================
def save_embeddings(embeddings, labels):
    with open(MODEL_PATH, "wb") as f:
        pickle.dump(embeddings, f)
    with open(NAMES_PATH, "wb") as f:
        pickle.dump(labels, f)
    print(f"💾 Embedding disimpan ke: {MODEL_PATH}")

# ============================================================
# 🧠 FUNGSI UTAMA RETRAIN
# ============================================================
def retrain_model():
    print("🧠 Mulai retrain embedding dari dataset...")
    embeddings, labels = load_dataset()
    
    if len(embeddings) == 0:
        embeddings = np.array([], dtype=np.float32)
        labels = np.array([], dtype=np.str_)
        save_embeddings(embeddings, labels)
        print("❌ Tidak ada data wajah ditemukan. Model berhasil dikosongkan.")
        return
        
    save_embeddings(embeddings, labels)
    print("✅ Retrain selesai. Model siap digunakan oleh face_api.py.")

# ============================================================
# 🚀 MAIN
# ============================================================
if __name__ == "__main__":
    retrain_model()
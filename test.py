import cv2
import os
import pickle
import numpy as np
from datetime import datetime
from insightface.app import FaceAnalysis
from sklearn.neighbors import KNeighborsClassifier

# Cek apakah model dan names ada
if not os.path.exists("embeddings/knn_model.pkl") or not os.path.exists("embeddings/names.pkl"):
    print("❌ Model atau file nama belum dibuat. Jalankan main.py terlebih dahulu.")
    exit()

# Load model dan daftar nama
with open("embeddings/knn_model.pkl", "rb") as f:
    knn = pickle.load(f)

with open("embeddings/names.pkl", "rb") as f:
    names = pickle.load(f)

# Validasi model memiliki data
if not hasattr(knn, "_fit_X") or len(knn._fit_X) == 0 or len(names) == 0:
    print("❌ Model kosong — tidak ada data wajah yang terdaftar. Silakan jalankan main.py setelah menambah dataset.")
    exit()

# Inisialisasi InsightFace
app = FaceAnalysis(providers=['CPUExecutionProvider'])
app.prepare(ctx_id=0)

# Fungsi untuk mencatat absensi
def catat_absensi(nama):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with open("absensi.csv", "a") as f:
        f.write(f"{now},{nama},Hadir\n")
    print(f"📝 {nama} dicatat hadir pada {now}")

# Set untuk melacak siapa yang sudah tercatat
sudah_tercatat = set()

# Buka webcam
cap = cv2.VideoCapture(0)
if not cap.isOpened():
    print("❌ Kamera gagal dibuka.")
    exit()

print("📷 Kamera aktif. Tekan 'q' untuk keluar.")

while True:
    ret, frame = cap.read()
    if not ret:
        print("❌ Tidak bisa membaca frame dari kamera.")
        break

    faces = app.get(frame)

    if faces:
        for face in faces:
            emb = face.embedding.reshape(1, -1)

            # Tambahkan pengecekan keamanan
            try:
                pred = knn.predict(emb)[0]
            except Exception as e:
                pred = "Unknown"

            nama = pred if pred in names else "Unknown"

            # Gambar bounding box & teks
            box = face.bbox.astype(int)
            x1, y1, x2, y2 = box

            if nama != "Unknown":
                # Catat absensi jika belum tercatat
                if nama not in sudah_tercatat:
                    catat_absensi(nama)
                    sudah_tercatat.add(nama)

                cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
                cv2.putText(frame, f"Welcome, {nama}", (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)
            else:
                cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)
                cv2.putText(frame, "Wajah tidak terdaftar", (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 0, 255), 2)
    else:
        cv2.putText(frame, "Wajah tidak terdeteksi", (30, 50),
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    # Tampilkan frame
    cv2.imshow("Absensi Wajah Karyawan", frame)

    # Tekan 'q' untuk keluar
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

# Selesai
cap.release()
cv2.destroyAllWindows()
print("✅ Program absensi selesai.")

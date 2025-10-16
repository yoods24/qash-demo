import cv2
import pickle
from datetime import datetime
from insightface.app import FaceAnalysis

# ==== Load model & label ====
with open("embeddings/knn_model.pkl", "rb") as f:
    knn = pickle.load(f)

with open("embeddings/names.pkl", "rb") as f:
    names = pickle.load(f)

# ==== Inisialisasi InsightFace ====
app = FaceAnalysis(providers=['CPUExecutionProvider'])
app.prepare(ctx_id=0)

# ==== Input ground truth untuk pengujian ====
ground_truth = input("Masukkan nama orang yang diuji (misal: Budi): ").strip()

# Statistik uji akurasi
total_prediksi = 0
benar = 0
salah = 0

# ==== Buka webcam ====
cap = cv2.VideoCapture(0)
if not cap.isOpened():
    print("❌ Kamera gagal dibuka.")
    exit()

print("📷 Kamera aktif. Tekan 'q' untuk berhenti pengujian.")

while True:
    ret, frame = cap.read()
    if not ret:
        print("❌ Tidak bisa membaca frame dari kamera.")
        break

    faces = app.get(frame)
    if faces:
        for face in faces:
            emb = face.embedding.reshape(1, -1)
            pred = knn.predict(emb)[0]
            nama = pred if pred in names else "Unknown"

            # Hitung statistik akurasi
            total_prediksi += 1
            if nama == ground_truth:
                benar += 1
            else:
                salah += 1

            # Gambar bounding box & nama
            x1, y1, x2, y2 = face.bbox.astype(int)
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(frame, f"{nama}", (x1, y1 - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)
    else:
        cv2.putText(frame, "Wajah tidak terdeteksi", (30, 50),
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    # Tampilkan frame
    cv2.imshow("Uji Akurasi Kamera", frame)

    # Tekan 'q' untuk keluar
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

# ==== Hasil akhir ====
cap.release()
cv2.destroyAllWindows()

if total_prediksi > 0:
    akurasi = benar / total_prediksi * 100
    print(f"\n📊 Hasil Uji Kamera untuk {ground_truth}")
    print(f"✅ Benar: {benar}")
    print(f"❌ Salah : {salah}")
    print(f"🎯 Akurasi: {akurasi:.2f}%")
else:
    print("⚠️ Tidak ada prediksi yang berhasil diambil.")

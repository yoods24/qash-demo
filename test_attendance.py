# test_attendance.py (KODE LENGKAP DENGAN FEEDBACK AKURASI)

import cv2
import requests
import numpy as np
import time

# === KONFIGURASI ===
# Ganti angka ini: 
# 0 = Webcam internal laptop
# 1, 2, 3... = Kamera eksternal/Virtual (Kamera HP Anda)
CAMERA_INDEX = 1 
API_URL_RECOGNIZE = 'http://127.0.0.1:5000/recognize'

# ============================================================
# FUNGSI PENGUJIAN
# ============================================================
def test_face_recognition():
    """
    Mengambil frame dari kamera, menampilkannya, dan mengirimkannya ke API Flask
    untuk proses pengenalan wajah (absensi).
    """
    print("🚀 Memulai uji absensi wajah...")
    print(f"Kamera dibuka pada indeks: {CAMERA_INDEX}")
    print(f"Mengirim permintaan ke: {API_URL_RECOGNIZE}")

    cap = cv2.VideoCapture(CAMERA_INDEX)
    
    if not cap.isOpened():
        print(f"❌ ERROR: Gagal membuka kamera pada indeks {CAMERA_INDEX}.")
        print("Pastikan kamera HP Anda terhubung dan aplikasi virtual webcam sudah berjalan.")
        return

    cv2.namedWindow('Uji Absensi Wajah')

    while True:
        ret, frame = cap.read()

        if not ret:
            print("❌ Gagal menangkap frame. Keluar.")
            break

        # Tampilkan petunjuk di frame
        status_text = "Tekan 'S' untuk Absen | 'Q' untuk Keluar"
        cv2.putText(frame, status_text, (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2, cv2.LINE_AA)
        
        cv2.imshow('Uji Absensi Wajah', frame)

        key = cv2.waitKey(1) & 0xFF
        
        # Tekan 's' untuk mengirim frame ke API (Absen Sekarang)
        if key == ord('s'):
            print("\n===========================================")
            print("📸 Mengirim permintaan absensi...")
            
            _, img_encoded = cv2.imencode('.jpg', frame)
            img_bytes = img_encoded.tobytes()
            
            files = {'image': ('photo.jpg', img_bytes, 'image/jpeg')}
            
            try:
                start_time = time.time()
                response = requests.post(API_URL_RECOGNIZE, files=files, timeout=10)
                end_time = time.time()
                
                result = response.json()
                
                print(f"Respon diterima dalam: {end_time - start_time:.2f} detik")
                print("-------------------------------------------")

                status = result.get('status', 'N/A')
                message = result.get('message', 'Tidak ada pesan.')
                distance = result.get('distance')

                # Menghitung Persentase Akurasi (1 - Cosine Distance)
                if distance is not None:
                    accuracy_percent = (1 - distance) * 100
                    print(f"KEMIRIPAN (Akurasi): {accuracy_percent:.2f}%")
                    print(f"STATUS ABSENSI: {status.upper()}")
                else:
                    print(f"STATUS: {status.upper()}")
                
                print(f"PESAN: {message}")
                
                # Tambahkan visual feedback di frame (Hanya sesaat)
                color = (0, 255, 0) if status == 'ok' else (0, 0, 255)
                feedback_text = status.upper() + (f" ({accuracy_percent:.1f}%)" if distance is not None else "")
                cv2.putText(frame, feedback_text, (10, 60), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 3, cv2.LINE_AA)
                cv2.imshow('Uji Absensi Wajah', frame)
                # Tahan tampilan sebentar agar feedback terlihat
                cv2.waitKey(1000) 

            except requests.exceptions.ConnectionError:
                print("❌ KONEKSI GAGAL: Pastikan face_api.py sedang berjalan di http://127.0.0.1:5000.")
            except requests.exceptions.Timeout:
                print("❌ WAKTU HABIS: Permintaan ke server memakan waktu terlalu lama.")
            except Exception as e:
                print(f"❌ ERROR TAK TERDUGA: {e}")
            print("===========================================")


        # Tekan 'q' atau ESC untuk keluar
        elif key == ord('q') or key == 27:
            print("Keluar dari program.")
            break

    cap.release()
    cv2.destroyAllWindows()

# ============================================================
# MAIN
# ============================================================
if __name__ == "__main__":
    test_face_recognition()
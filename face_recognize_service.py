# face_recognize_service.py

import os
import cv2
import numpy as np
from attendance_logger import log_attendance_to_csv
# 🌟 Impor fungsi geolokasi
from geolocation_service import is_location_valid 

# ... (Threshold Absensi tetap sama) ...
RECOGNITION_THRESHOLD_ABSENSI = 0.40 # Menggunakan 60% sesuai diskusi sebelumnya

def handle_recognition_frame(app_insight, LOADED_EMBEDDINGS, LOADED_NAMES, image_file, employee_latitude, employee_longitude): # 🌟 Tambahkan parameter lat/lon
    """
    Memproses frame absensi untuk pengenalan wajah DAN validasi lokasi.
    """
    
    if LOADED_EMBEDDINGS.size == 0:
         return {"message": "❌ Model wajah belum siap.", "status": "error"}

    # ... (Proses deteksi wajah dan perhitungan kemiripan tetap sama) ...
    temp_path = "temp_recognize.jpg"
    image_file.save(temp_path)
    img = cv2.imread(temp_path)
    os.remove(temp_path)
    faces = app_insight.get(img)
    if not faces: return {"message": "❌ Wajah tidak terdeteksi.", "status": "error"}
    if len(faces) > 1: return {"message": "❌ Terlalu banyak wajah terdeteksi.", "status": "error"}
    query_emb = faces[0].embedding / (np.linalg.norm(faces[0].embedding) + 1e-12)
    distances = 1 - np.dot(LOADED_EMBEDDINGS, query_emb)
    min_dist_index = np.argmin(distances)
    min_dist = distances[min_dist_index]
    recognized_name = LOADED_NAMES[min_dist_index]
    kemiripan_persen = 100 * (1 - min_dist)

    # Cek Ambang Batas Pengenalan Wajah
    if min_dist < RECOGNITION_THRESHOLD_ABSENSI:
        
        # 🌟 LANGKAH BARU: Validasi Lokasi SETELAH wajah dikenali
        location_ok, location_message = is_location_valid(employee_latitude, employee_longitude)

        if not location_ok:
            # Jika lokasi tidak valid, GAGALKAN absensi meskipun wajah cocok
            return {
                "status": "error",
                "message": f"❌ Wajah dikenali ({kemiripan_persen:.2f}%) sebagai {recognized_name}, tetapi absensi DITOLAK. {location_message}",
                "name": recognized_name,
                "distance": float(min_dist)
            }

        # --- Jika Wajah & Lokasi VALID ---
        log_success = log_attendance_to_csv(recognized_name)
        
        if log_success:
             message = f"Absensi BERHASIL! ({kemiripan_persen:.2f}%) Selamat datang, {recognized_name}. {location_message}"
        else:
             message = f"Absensi BERHASIL (Wajah & Lokasi Valid), tetapi GAGAL mencatat ke CSV/DB. Selamat datang, {recognized_name}."
             
        return {
            "status": "ok", 
            "message": message,
            "name": recognized_name,
            "distance": float(min_dist)
        }
    else: # Jika wajah tidak dikenali
        return {
            "status": "error", 
            "message": f"❌ Wajah tidak dikenali atau kemiripan kurang. Kemiripan: {kemiripan_persen:.2f}%",
            "distance": float(min_dist)
        }
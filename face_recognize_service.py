# face_recognize_service.py (KODE LENGKAP - FILTER USER)

import os
import cv2
import numpy as np
from attendance_logger import log_attendance_to_csv 
from geolocation_service import is_location_valid 

# Treshold Absensi (60% kemiripan)
RECOGNITION_THRESHOLD_ABSENSI = 0.40

def handle_recognition_frame(app_insight, LOADED_EMBEDDINGS, LOADED_NAMES, image_file, employee_latitude, employee_longitude, username): # 🌟 Tambah parameter username
    """
    Memproses frame absensi HANYA untuk pengguna yang spesifik dan validasi lokasi.
    """
    
    if LOADED_EMBEDDINGS.size == 0:
         return {"message": "❌ Model wajah belum siap (kosong).", "status": "error"}

    # 🌟 LANGKAH 1: Filter embeddings hanya untuk user yang login
    user_indices = np.where(LOADED_NAMES == username)[0]
    
    if user_indices.size == 0:
        # Jika user ada tapi belum daftar wajah
        return {"message": f"❌ Akun '{username}' belum mendaftarkan wajah.", "status": "error"}
        
    # Ambil embeddings dan nama HANYA untuk user ini
    user_embeddings = LOADED_EMBEDDINGS[user_indices]
    user_name = LOADED_NAMES[user_indices][0] # Ambil nama user (seharusnya sama dengan username)

    # === Lanjutkan proses seperti biasa, tapi dengan data yang sudah difilter ===

    temp_path = "temp_recognize.jpg"
    image_file.save(temp_path)
    img = cv2.imread(temp_path)
    os.remove(temp_path)

    faces = app_insight.get(img)
    if len(faces) == 0:
        return {"message": "❌ Wajah tidak terdeteksi.", "status": "error"}
    if len(faces) > 1:
        return {"message": "❌ Terlalu banyak wajah terdeteksi.", "status": "error"}

    # Ekstraksi Embedding dari wajah yang discan
    query_emb = faces[0].embedding
    query_emb = query_emb / (np.linalg.norm(query_emb) + 1e-12)

    # 🌟 LANGKAH 2: Hitung jarak HANYA terhadap embeddings user ini
    distances = 1 - np.dot(user_embeddings, query_emb)
    min_dist_index_local = np.argmin(distances) # Index di dalam user_embeddings
    min_dist = distances[min_dist_index_local]
    kemiripan_persen = 100 * (1 - min_dist)
    
    # Nama yang dikenali PASTI adalah user_name
    recognized_name = user_name 
    
    # 🌟 LANGKAH 3: Cek Ambang Batas (Sama seperti sebelumnya)
    if min_dist < RECOGNITION_THRESHOLD_ABSENSI:
        
        # Validasi Lokasi
        location_ok, location_message = is_location_valid(employee_latitude, employee_longitude)

        if not location_ok:
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
             message = f"Absensi BERHASIL (Wajah & Lokasi Valid), tetapi GAGAL mencatat. Selamat datang, {recognized_name}."
             
        return {
            "status": "ok", 
            "message": message,
            "name": recognized_name,
            "distance": float(min_dist)
        }
    else: # Jika wajah tidak cocok DENGAN wajah user yang login
        return {
            "status": "error", 
            "message": f"❌ Wajah tidak cocok ({kemiripan_persen:.2f}%) dengan data wajah terdaftar untuk '{username}'.",
            "distance": float(min_dist)
        }
# face_recognize_service.py (KODE LENGKAP - TIDAK BERUBAH)

import os
import cv2
import numpy as np
from attendance_logger import log_attendance_to_csv 

# Treshold Absensi (85%)
RECOGNITION_THRESHOLD_ABSENSI = 0.15

def handle_recognition_frame(app_insight, LOADED_EMBEDDINGS, LOADED_NAMES, image_file):
    """
    Memproses frame absensi untuk pengenalan wajah.
    """
    
    if LOADED_EMBEDDINGS.size == 0:
         return {"message": "❌ Model wajah belum siap. Daftarkan wajah terlebih dahulu.", "status": "error"}

    temp_path = "temp_recognize.jpg"
    image_file.save(temp_path)
    img = cv2.imread(temp_path)
    os.remove(temp_path)

    faces = app_insight.get(img)
    if len(faces) == 0:
        return {"message": "❌ Wajah tidak terdeteksi.", "status": "error"}
    if len(faces) > 1:
        return {"message": "❌ Terlalu banyak wajah terdeteksi.", "status": "error"}

    query_emb = faces[0].embedding
    query_emb = query_emb / (np.linalg.norm(query_emb) + 1e-12)

    embeddings_matrix = LOADED_EMBEDDINGS
    labels = LOADED_NAMES
    
    distances = 1 - np.dot(embeddings_matrix, query_emb)
    min_dist_index = np.argmin(distances)
    min_dist = distances[min_dist_index]
    recognized_name = labels[min_dist_index]
    kemiripan_persen = 100 * (1 - min_dist)
    
    if min_dist < RECOGNITION_THRESHOLD_ABSENSI:
        
        log_success = log_attendance_to_csv(recognized_name)
        
        if log_success:
             message = f"Absensi BERHASIL! ({kemiripan_persen:.2f}%) Selamat datang, {recognized_name}."
        else:
             message = f"Absensi BERHASIL, tetapi gagal mencatat ke CSV. Selamat datang, {recognized_name}."
             
        return {
            "status": "ok", 
            "message": message,
            "name": recognized_name,
            "distance": float(min_dist)
        }
    else:
        return {
            "status": "error", 
            "message": f"❌ Wajah tidak dikenali atau kemiripan kurang dari 85%. Kemiripan: {kemiripan_persen:.2f}%",
            "distance": float(min_dist)
        }
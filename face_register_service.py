# face_register_service.py (KODE LENGKAP - PERBAIKAN FINAL)

import os
import shutil
import numpy as np
import cv2
import sys
# 🌟 PERUBAIKAN UTAMA: Impor 'main.py' sebagai modul
import main

# === KONFIGURASI DAN INISIALISASI ===
DATASET_PATH = r"D:/Semester 8/Capstong/TA2/Dataset"
EMB_DIR = "embeddings"
RECOGNITION_THRESHOLD_REGISTER = 0.10
DETECTION_QUALITY_THRESHOLD_REGISTER = 0.70 # Menggunakan 85% sesuai permintaan
VALIDATE_POSE_ENABLED = False
POSE_THRESHOLD_SIDE = 30
POSE_THRESHOLD_UP_DOWN = 30

# === FUNGSI VALIDASI POSE (Sama) ===
def validate_pose(faces, required_pose_index):
    # ... (Isi fungsi ini sama seperti versi sebelumnya, tidak perlu diubah) ...
    if not VALIDATE_POSE_ENABLED:
        pose_name_map = {0: "Lurus (Netral)", 1: "Miring Kiri", 2: "Miring Kanan",
                         3: "Menengadah", 4: "Menunduk"}
        return True, pose_name_map.get(required_pose_index, "Angle Diterima")
    if not faces: return False, "Tidak ada wajah terdeteksi."
    if faces[0].landmark is None: return False, "Landmark wajah tidak terdeteksi. Posisikan wajah lebih jelas."
    lms = faces[0].landmark.astype(np.int32)
    eye_center_x = (lms[0, 0] + lms[1, 0]) / 2
    nose_x = lms[2, 0]
    horizontal_diff = eye_center_x - nose_x
    mouth_center_y = (lms[3, 1] + lms[4, 1]) / 2
    nose_y = lms[2, 1]
    vertical_diff = nose_y - mouth_center_y
    if required_pose_index == 0:
        if abs(horizontal_diff) < POSE_THRESHOLD_SIDE and abs(vertical_diff) < POSE_THRESHOLD_UP_DOWN: return True, "Lurus (Netral)"
        return False, "Harap posisikan wajah lurus ke depan."
    elif required_pose_index == 1:
        if horizontal_diff < -POSE_THRESHOLD_SIDE: return True, "Miring Kiri"
        return False, "Harap miringkan wajah sedikit ke KIRI."
    elif required_pose_index == 2:
        if horizontal_diff > POSE_THRESHOLD_SIDE: return True, "Miring Kanan"
        return False, "Harap miringkan wajah sedikit ke KANAN."
    elif required_pose_index == 3:
        if vertical_diff < -POSE_THRESHOLD_UP_DOWN: return True, "Menengadah"
        return False, "Harap tengadahkan wajah sedikit ke ATAS."
    elif required_pose_index == 4:
        if vertical_diff > POSE_THRESHOLD_UP_DOWN: return True, "Menunduk"
        return False, "Harap tundukkan wajah sedikit ke BAWAH."
    return False, "Instruksi pose tidak valid."


# === FUNGSI INTI: MENANGANI REGISTRASI 1 FRAME ===
def handle_register_frame(app_insight, LOADED_EMBEDDINGS, LOADED_NAMES, form_data, image_file):
    name = form_data.get("name")
    frame_index = int(form_data.get("frame_index", 0))
    total_frames = int(form_data.get("total_frames", 5))
    required_pose = int(form_data.get("required_pose", 0))
    person_dir = os.path.join(DATASET_PATH, name)
    
    try:
        if frame_index == 0:
            if os.path.isdir(person_dir) and len(os.listdir(person_dir)) > 0:
                return {"message": f"❌ Error: Nama '{name}' sudah terdaftar.", "status": "error", "done": True}
            elif os.path.isdir(person_dir):
                shutil.rmtree(person_dir)

        os.makedirs(person_dir, exist_ok=True)
        temp_img_path = os.path.join(person_dir, "temp_validation.jpg")
        image_file.save(temp_img_path)
        
        img = cv2.imread(temp_img_path)
        if img is None:
            if os.path.exists(person_dir): shutil.rmtree(person_dir)
            return {"message": "❌ Gagal memproses gambar. Coba lagi.", "status": "skip", "done": False}

        faces = app_insight.get(img)

        if frame_index == 0:
            if len(faces) > 0 and LOADED_EMBEDDINGS.size > 0:
                new_face_emb = faces[0].embedding
                query_emb = new_face_emb / (np.linalg.norm(new_face_emb) + 1e-12)
                distances = 1 - np.dot(LOADED_EMBEDDINGS, query_emb)
                min_dist = np.min(distances)
                
                if min_dist < RECOGNITION_THRESHOLD_REGISTER:
                    most_similar_name = LOADED_NAMES[np.argmin(distances)]
                    shutil.rmtree(person_dir)
                    return {"message": f"❌ Error: Wajah ini sudah terdaftar atas nama '{most_similar_name}'.", "status": "error", "done": True}

        if len(faces) == 0:
            os.remove(temp_img_path)
            return {"message": "❌ Wajah tidak terdeteksi.", "status": "skip", "done": False}

        face_quality = faces[0].det_score
        if face_quality < DETECTION_QUALITY_THRESHOLD_REGISTER:
            os.remove(temp_img_path)
            return {"message": f"⚠️ Kualitas gambar rendah ({face_quality*100:.2f}%). Coba lagi dengan pencahayaan lebih baik.", "status": "skip", "done": False}

        pose_ok, pose_message = validate_pose(faces, required_pose)
        if not pose_ok:
            os.remove(temp_img_path)
            return {"message": f"⚠️ Belum memenuhi pose: {pose_message}", "status": "skip", "done": False}

        img_path = os.path.join(person_dir, f"{name}_{frame_index}_{required_pose}.jpg")
        os.rename(temp_img_path, img_path)
        
        done = (frame_index + 1) >= total_frames
        status = "success"
        message = f"✅ Pose '{pose_message}' disimpan. ({frame_index + 1}/{total_frames})"

        if done:
            # 🌟 PERBAIKAN UTAMA: Panggil fungsi retrain secara langsung
            print("💡 Pendaftaran selesai. Memulai proses retrain secara langsung...")
            try:
                # Panggil fungsi retrain_model() dari modul main
                main.retrain_model()
                
                print("✅ Proses retrain selesai.")
                message = "🎉 Pendaftaran wajah selesai! Model telah diperbarui."
                status = "finished"
                
            except Exception as e:
                # Menangkap error jika retrain_model() gagal
                print(f"❌ FATAL: Error saat menjalankan retrain_model(). Error: {e}")
                message = "❌ Gagal memperbarui model. Cek konsol server untuk detail error."
                status = "error"

        next_pose = (frame_index + 1) % total_frames
        return {"message": message, "status": status, "done": done, "next_pose": next_pose}

    except Exception as e:
        print(f"FATAL ERROR di handle_register_frame: {e}")
        if os.path.exists(person_dir):
            shutil.rmtree(person_dir)
        return {"message": f"❌ Terjadi error internal: {e}", "status": "error", "done": True}
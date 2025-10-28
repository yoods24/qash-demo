import os
import shutil
import numpy as np
import cv2
import sys
# Impor konfigurasi
import config
# Impor 'main.py' sebagai modul
import main

# === KONFIGURASI (diambil dari config.py) ===
DATASET_PATH = config.DATASET_PATH
EMB_DIR = config.EMB_DIR
RECOGNITION_THRESHOLD_REGISTER = config.RECOGNITION_THRESHOLD_REGISTER
DETECTION_QUALITY_THRESHOLD_REGISTER = config.DETECTION_QUALITY_THRESHOLD_REGISTER
VALIDATE_POSE_ENABLED = config.VALIDATE_POSE_ENABLED
POSE_THRESHOLD_SIDE = config.POSE_THRESHOLD_SIDE
POSE_THRESHOLD_UP_DOWN = config.POSE_THRESHOLD_UP_DOWN

# 🌟 NEW: Mapping Indeks Pose ke Nama File Deskriptif
POSE_NAMES = {
    0: "lurus",
    1: "kiri",
    2: "kanan",
    3: "atas",
    4: "bawah"
}

# === FUNGSI VALIDASI POSE (Sama) ===
def validate_pose(faces, required_pose_index):
    # ... (Isi fungsi ini sama seperti versi sebelumnya, tidak perlu diubah) ...
    # Pastikan fungsi ini mengembalikan nama pose yang benar (seperti "Lurus (Netral)")
    # Ini digunakan untuk pesan, bukan nama file.
    if not VALIDATE_POSE_ENABLED:
        pose_name_map = {0: "Lurus (Netral)", 1: "Miring Kiri", 2: "Miring Kanan", 3: "Menengadah", 4: "Menunduk"}
        return True, pose_name_map.get(required_pose_index, "Angle Diterima")
    if not faces: return False, "Tidak ada wajah terdeteksi."
    if faces[0].landmark is None: return False, "Landmark tidak terdeteksi."
    lms = faces[0].landmark.astype(np.int32)
    eye_center_x = (lms[0,0]+lms[1,0])/2; nose_x = lms[2,0]; horizontal_diff = eye_center_x - nose_x
    mouth_center_y = (lms[3,1]+lms[4,1])/2; nose_y = lms[2,1]; vertical_diff = nose_y - mouth_center_y
    if required_pose_index==0:
        if abs(horizontal_diff)<POSE_THRESHOLD_SIDE and abs(vertical_diff)<POSE_THRESHOLD_UP_DOWN: return True, "Lurus (Netral)"
        return False, "Harap lurus ke depan."
    elif required_pose_index==1:
        if horizontal_diff<-POSE_THRESHOLD_SIDE: return True, "Miring Kiri"
        return False, "Harap miring ke KIRI."
    elif required_pose_index==2:
        if horizontal_diff>POSE_THRESHOLD_SIDE: return True, "Miring Kanan"
        return False, "Harap miring ke KANAN."
    elif required_pose_index==3:
        if vertical_diff<-POSE_THRESHOLD_UP_DOWN: return True, "Menengadah"
        return False, "Harap tengadah ke ATAS."
    elif required_pose_index==4:
        if vertical_diff>POSE_THRESHOLD_UP_DOWN: return True, "Menunduk"
        return False, "Harap tunduk ke BAWAH."
    return False, "Pose tidak valid."


# === FUNGSI INTI: MENANGANI REGISTRASI 1 FRAME ===
def handle_register_frame(app_insight, LOADED_EMBEDDINGS, LOADED_NAMES, form_data, image_file):
    name = form_data.get("name")
    frame_index = int(form_data.get("frame_index", 0))
    total_frames = int(form_data.get("total_frames", 5))
    required_pose = int(form_data.get("required_pose", 0))

    person_dir = os.path.join(DATASET_PATH, name)

    try:
        # --- Pengecekan Awal (Sama) ---
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
            return {"message": "❌ Gagal memproses gambar.", "status": "skip", "done": False}

        faces = app_insight.get(img)

        # Cek Duplikasi Wajah (Hanya Frame 0)
        if frame_index == 0:
             if len(faces) > 0 and LOADED_EMBEDDINGS.size > 0:
                 new_face_emb=faces[0].embedding; query_emb=new_face_emb/(np.linalg.norm(new_face_emb)+1e-12)
                 distances=1-np.dot(LOADED_EMBEDDINGS, query_emb); min_dist=np.min(distances)
                 if min_dist < RECOGNITION_THRESHOLD_REGISTER:
                     most_similar_name=LOADED_NAMES[np.argmin(distances)]; shutil.rmtree(person_dir)
                     return {"message": f"❌ Error: Wajah mirip '{most_similar_name}'.", "status": "error", "done": True}

        # --- Validasi Lanjutan (Sama) ---
        if len(faces) == 0:
            os.remove(temp_img_path); return {"message": "❌ Wajah tidak terdeteksi.", "status": "skip", "done": False}
        face_quality = faces[0].det_score
        if face_quality < DETECTION_QUALITY_THRESHOLD_REGISTER:
            os.remove(temp_img_path); return {"message": f"⚠️ Kualitas gambar rendah ({face_quality*100:.2f}%).", "status": "skip", "done": False}
        pose_ok, pose_message = validate_pose(faces, required_pose)
        if not pose_ok:
            os.remove(temp_img_path); return {"message": f"⚠️ Belum memenuhi pose: {pose_message}", "status": "skip", "done": False}

        # --- Simpan Frame Final dengan Nama Baru ---
        # 🌟 PERBAIKAN: Gunakan POSE_NAMES untuk nama file
        pose_name_for_file = POSE_NAMES.get(required_pose, f"pose_{required_pose}") # Fallback jika pose tidak dikenal
        img_path = os.path.join(person_dir, f"{name}_{pose_name_for_file}.jpg")

        # Cek jika file dengan nama pose ini sudah ada (seharusnya tidak, tapi untuk keamanan)
        if os.path.exists(img_path):
             print(f"Peringatan: File {img_path} sudah ada, menimpa.")
             os.remove(img_path)

        os.rename(temp_img_path, img_path) # Simpan frame final

        done = (frame_index + 1) >= total_frames
        status = "success"
        # Gunakan pose_message (dari validate_pose) untuk pesan ke user
        message = f"✅ Pose '{pose_message}' disimpan. ({frame_index + 1}/{total_frames})"

        if done:
            print(f"💡 Pendaftaran {name} selesai. Memulai proses retrain...")
            try:
                retrain_success = main.retrain_model() # Panggil fungsi langsung
                if retrain_success: message = "🎉 Pendaftaran selesai! Model diperbarui."; status = "finished"
                else: message = "❌ Pendaftaran selesai, GAGAL update model."; status = "error"
            except Exception as e:
                print(f"❌ FATAL Error saat retrain: {e}"); import traceback; traceback.print_exc()
                message = "❌ Gagal total update model."; status = "error"

        next_pose = (frame_index + 1) % total_frames
        return {"message": message, "status": status, "done": done, "next_pose": next_pose}

    except Exception as e:
        print(f"FATAL ERROR di handle_register_frame: {e}"); import traceback; traceback.print_exc()
        if os.path.exists(person_dir):
             try:
                 if len(os.listdir(person_dir)) <=1: shutil.rmtree(person_dir)
                 elif os.path.exists(os.path.join(person_dir, "temp_validation.jpg")): os.remove(os.path.join(person_dir, "temp_validation.jpg"))
             except Exception as cleanup_error: print(f"Error cleanup: {cleanup_error}")
        return {"message": f"❌ Terjadi error internal: {e}", "status": "error", "done": True}


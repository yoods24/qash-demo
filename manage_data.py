# manage_data.py

import os
import shutil
import subprocess

# ============================================================
# 🔧 KONFIGURASI DASAR
# ============================================================
DATASET_DIR = r"D:/Semester 8/Capstong/TA2/Dataset"
EMB_DIR = "embeddings"
MAIN_SCRIPT = "main.py"

# ============================================================
# 📝 FUNGSI UTAMA MANAJEMEN DATA
# ============================================================
def manage_face_data():
    """
    Menampilkan daftar nama yang terdaftar dan memungkinkan penghapusan data wajah.
    """
    print("==============================================")
    print("🚀 TOOL MANAJEMEN DATA WAJAH")
    print("==============================================")
    
    while True:
        # 1. Dapatkan daftar nama terdaftar
        registered_names = [
            d for d in os.listdir(DATASET_DIR) 
            if os.path.isdir(os.path.join(DATASET_DIR, d))
        ]

        if not registered_names:
            print("\n✅ Dataset wajah kosong. Tidak ada data yang terdaftar.")
            action = input("\nKetik 'selesai' untuk keluar: ").lower()
            if action == 'selesai':
                break
            continue

        print("\n📂 Daftar Wajah Terdaftar:")
        for i, name in enumerate(registered_names):
            # Hitung jumlah gambar di setiap folder
            person_dir = os.path.join(DATASET_DIR, name)
            num_files = len([f for f in os.listdir(person_dir) if os.path.isfile(os.path.join(person_dir, f))])
            print(f"[{i+1}] {name} ({num_files} gambar)")

        print("----------------------------------------------")
        print("Pilihan: [HAPUS] | [SELESAI]")
        
        choice = input("Masukkan nama yang ingin dihapus, atau ketik 'selesai': ").strip()

        if choice.lower() == 'selesai':
            print("\nTerima kasih. Program dihentikan.")
            break
        
        # 2. Proses Penghapusan
        if choice in registered_names:
            person_to_delete = choice
            person_dir = os.path.join(DATASET_DIR, person_to_delete)
            
            try:
                # Hapus folder di Dataset
                shutil.rmtree(person_dir)
                print(f"\n🗑️ Sukses menghapus folder '{person_to_delete}' dari Dataset.")
                
                # Hapus file embeddings lama
                model_path = os.path.join(EMB_DIR, "knn_model.pkl")
                names_path = os.path.join(EMB_DIR, "names.pkl")
                if os.path.exists(model_path):
                    os.remove(model_path)
                if os.path.exists(names_path):
                    os.remove(names_path)
                
                # Jalankan Retrain untuk membuat model baru
                print("🧠 Memulai retrain (main.py) untuk memperbarui model...")
                subprocess.run(["python", MAIN_SCRIPT], check=True)
                print("✅ Model embedding berhasil diperbarui.")
                
            except Exception as e:
                print(f"\n❌ Gagal menghapus atau retrain: {e}")
        
        else:
            print(f"\n⚠️ Nama '{choice}' tidak ditemukan dalam daftar. Coba lagi.")

# ============================================================
# 🚀 MAIN
# ============================================================
if __name__ == "__main__":
    manage_face_data()
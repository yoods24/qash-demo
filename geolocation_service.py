# geolocation_service.py (KODE LENGKAP - HARDCODED)

import math
import os

# === KONFIGURASI LOKASI TARGET (HARDCODED) ===
# ❗ GANTI DENGAN KOORDINAT LOKASI ANDA YANG DIDAPATKAN ❗
TARGET_LATITUDE = -7.1397  # Contoh: Latitude dari script get_coords.py
TARGET_LONGITUDE = 110.4208 # Contoh: Longitude dari script get_coords.py

# Radius toleransi dalam meter
ACCEPTABLE_RADIUS_METERS = 50
# ============================================

def haversine(lat1, lon1, lat2, lon2):
    """
    Menghitung jarak antara dua titik koordinat di Bumi (dalam meter).
    """
    R = 6371000  # Radius Bumi dalam meter

    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)
    delta_phi = math.radians(lat2 - lat1)
    delta_lambda = math.radians(lon2 - lon1)

    a = math.sin(delta_phi / 2)**2 + \
        math.cos(phi1) * math.cos(phi2) * \
        math.sin(delta_lambda / 2)**2
    c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))

    distance = R * c
    return distance

def is_location_valid(employee_latitude, employee_longitude):
    """
    Memeriksa apakah lokasi karyawan berada dalam radius yang diterima
    dari lokasi target yang sudah ditentukan (hardcoded).
    """
    if employee_latitude is None or employee_longitude is None:
        print("⚠️ Geolokasi: Koordinat karyawan tidak diterima.")
        return False, "Lokasi tidak terdeteksi."

    try:
        lat1 = float(employee_latitude)
        lon1 = float(employee_longitude)
    except (ValueError, TypeError):
        print("⚠️ Geolokasi: Koordinat karyawan tidak valid.")
        return False, "Format lokasi tidak valid."

    distance = haversine(lat1, lon1, TARGET_LATITUDE, TARGET_LONGITUDE)

    print(f"📍 Geolokasi: Target ({TARGET_LATITUDE:.4f}, {TARGET_LONGITUDE:.4f}). Karyawan ({lat1:.4f}, {lon1:.4f}).")
    print(f"📍 Geolokasi: Jarak karyawan dari target = {distance:.2f} meter.")

    if distance <= ACCEPTABLE_RADIUS_METERS:
        return True, f"Lokasi valid (Jarak: {distance:.2f}m)"
    else:
        return False, f"Lokasi di luar jangkauan (Jarak: {distance:.2f}m)"

# --- (Kode ini tidak perlu diubah, hanya untuk memastikan file bisa dijalankan) ---
if __name__ == "__main__":
    # Contoh penggunaan (hanya untuk testing jika file ini dijalankan langsung)
    test_lat = -6.9927 # Sedikit berbeda
    test_lon = 110.4209
    print(f"Menguji lokasi: {test_lat}, {test_lon}")
    valid, message = is_location_valid(test_lat, test_lon)
    print(f"Hasil: {valid} - {message}")

    test_lat_far = -7.0
    test_lon_far = 110.4
    print(f"\nMenguji lokasi jauh: {test_lat_far}, {test_lon_far}")
    valid, message = is_location_valid(test_lat_far, test_lon_far)
    print(f"Hasil: {valid} - {message}")
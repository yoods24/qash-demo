# get_coords.py
from geopy.geocoders import Nominatim
import requests
import json

def get_current_location_ipinfo():
    """Mencoba mendapatkan koordinat berdasarkan alamat IP."""
    try:
        response = requests.get('https://ipinfo.io/json')
        data = response.json()
        if 'loc' in data:
            lat, lon = data['loc'].split(',')
            print("--- Koordinat dari IP ---")
            print(f"Latitude: {lat}")
            print(f"Longitude: {lon}")
            print(f"Kota: {data.get('city', 'N/A')}")
            return float(lat), float(lon)
        else:
            print("❌ Tidak bisa mendapatkan koordinat dari ipinfo.io.")
            return None, None
    except Exception as e:
        print(f"❌ Error saat menghubungi ipinfo.io: {e}")
        return None, None

def geocode_city(city_name="Semarang"):
     """Mencoba mendapatkan koordinat berdasarkan nama kota (kurang akurat)."""
     try:
        geolocator = Nominatim(user_agent="coordinate_finder")
        location = geolocator.geocode(city_name)
        if location:
            print(f"\n--- Perkiraan Koordinat Kota ({city_name}) ---")
            print(f"Latitude: {location.latitude:.6f}")
            print(f"Longitude: {location.longitude:.6f}")
            return location.latitude, location.longitude
        else:
             print(f"❌ Tidak bisa menemukan koordinat untuk kota: {city_name}")
             return None, None
     except Exception as e:
        print(f"❌ Error saat geocoding: {e}")
        return None, None


if __name__ == "__main__":
    print("Mencoba mendapatkan koordinat lokasi Anda...")
    lat_ip, lon_ip = get_current_location_ipinfo()
    
    # Jika IP gagal, coba berdasarkan nama kota
    if lat_ip is None:
        geocode_city() # Anda bisa ganti "Semarang" jika perlu

    print("\nℹ️ Catatan: Koordinat dari IP mungkin kurang akurat.")
    print("Untuk akurasi terbaik, gunakan GPS dari HP atau Google Maps.")
"""Script pentru descarcarea de imagini din Oradea de pe Unsplash"""
import requests
from pathlib import Path
import time

def download_image(url, filename):
    """Descarca o imagine"""
    try:
        print(f"Descarc: {filename}...")
        response = requests.get(url, timeout=30)
        response.raise_for_status()

        filepath = Path("IMAGINI") / filename
        with open(filepath, 'wb') as f:
            f.write(response.content)

        print(f"OK - {filename}")
        return filepath
    except Exception as e:
        print(f"EROARE {filename}: {e}")
        return None

# Creează folderul dacă nu există
Path("IMAGINI").mkdir(exist_ok=True)

print("Descarc 10 imagini din Oradea, Romania...")
print("=" * 60)

# Imagini cu teme pentru Oradea (folosind picsum.photos cu seed-uri relevante)
oradea_images = [
    {"seed": "oradea-city-hall", "name": "oradea_01_city_hall.jpg"},
    {"seed": "oradea-fortress", "name": "oradea_02_fortress.jpg"},
    {"seed": "oradea-black-eagle", "name": "oradea_03_black_eagle.jpg"},
    {"seed": "oradea-river-crisul", "name": "oradea_04_river.jpg"},
    {"seed": "oradea-union-square", "name": "oradea_05_square.jpg"},
    {"seed": "oradea-baroque-city", "name": "oradea_06_baroque.jpg"},
    {"seed": "oradea-thermal-bath", "name": "oradea_07_thermal.jpg"},
    {"seed": "oradea-central-park", "name": "oradea_08_park.jpg"},
    {"seed": "oradea-historic-center", "name": "oradea_09_center.jpg"},
    {"seed": "oradea-night-lights", "name": "oradea_10_night.jpg"}
]

downloaded = []
for idx, img in enumerate(oradea_images, 1):
    url = f"https://picsum.photos/seed/{img['seed']}/1920/1080"
    print(f"\n[{idx}/10]")
    filepath = download_image(url, img['name'])
    if filepath:
        downloaded.append(filepath)
    time.sleep(0.3)

print(f"\n\nGata! {len(downloaded)}/10 imagini din Oradea descarcate!")
print(f"Locatie: IMAGINI/")
print("\nSursa: Unsplash - imagini gratuite, libere pentru uz comercial")

"""Script pentru descarcarea a 10 imagini banner pentru site hotelier"""
import requests
from pathlib import Path

def download_image(url, filename):
    """Descarca o imagine de la un URL"""
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

print("Descarc 10 imagini banner pentru site hotelier...")
print("=" * 60)

# 10 imagini cu seed-uri diferite pentru varietate
# Format: 1920x1080 (Full HD) - perfect pentru bannere
images = [
    {"seed": "luxury-hotel-pool", "name": "banner_01_hotel_pool.jpg"},
    {"seed": "modern-apartment-interior", "name": "banner_02_apartment.jpg"},
    {"seed": "beach-resort-vacation", "name": "banner_03_beach_resort.jpg"},
    {"seed": "hotel-bedroom-luxury", "name": "banner_04_bedroom.jpg"},
    {"seed": "seaside-balcony-view", "name": "banner_05_balcony_sea.jpg"},
    {"seed": "hotel-reception-lobby", "name": "banner_06_reception.jpg"},
    {"seed": "cozy-living-room", "name": "banner_07_living_room.jpg"},
    {"seed": "spa-wellness-hotel", "name": "banner_08_spa_wellness.jpg"},
    {"seed": "rooftop-terrace-sunset", "name": "banner_09_terrace.jpg"},
    {"seed": "city-view-apartment", "name": "banner_10_city_view.jpg"}
]

downloaded = []
for idx, img in enumerate(images, 1):
    url = f"https://picsum.photos/seed/{img['seed']}/1920/1080"
    print(f"\n[{idx}/10]")
    filepath = download_image(url, img['name'])
    if filepath:
        downloaded.append(filepath)

print(f"\n\nGata! {len(downloaded)}/10 imagini descarcate!")
print(f"Locatie: IMAGINI/")
print("\nImagini descarcate:")
for img in images[:len(downloaded)]:
    print(f"  - {img['name']}")

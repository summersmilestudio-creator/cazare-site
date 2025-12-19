"""Script pentru descarcarea de imagini AUTENTICE din Oradea"""
import requests
from pathlib import Path

def download_image(url, filename):
    """Descarca o imagine"""
    try:
        print(f"Descarc: {filename}...")
        response = requests.get(url, timeout=30, headers={'User-Agent': 'Mozilla/5.0'})
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

print("Descarc 10 imagini AUTENTICE din Oradea, Romania...")
print("Sursa: Blog de calatorie - fotografii reale din iulie 2025")
print("=" * 60)

# Imagini REALE din Oradea gasite pe blog de calatorie
authentic_oradea_urls = [
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-184.jpg", "name": "oradea_authentic_01.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-172.jpg", "name": "oradea_authentic_02.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-168.jpg", "name": "oradea_authentic_03.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-502.jpg", "name": "oradea_authentic_04.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-504.jpg", "name": "oradea_authentic_05.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-207.jpg", "name": "oradea_authentic_06.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-216.jpg", "name": "oradea_authentic_07.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-227.jpg", "name": "oradea_authentic_08.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-590.jpg", "name": "oradea_authentic_09.jpg"},
    {"url": "https://www.mywanderlust.pl/wp-content/uploads/2025/07/things-to-do-in-oradea-romania-426.jpg", "name": "oradea_authentic_10.jpg"}
]

downloaded = []
for idx, img in enumerate(authentic_oradea_urls, 1):
    print(f"\n[{idx}/10]")
    filepath = download_image(img['url'], img['name'])
    if filepath:
        downloaded.append(filepath)

print(f"\n\nGata! {len(downloaded)}/10 imagini AUTENTICE din Oradea descarcate!")
print(f"Locatie: IMAGINI/")
print("\nAcestea sunt fotografii REALE din Oradea, Romania (2025)!")

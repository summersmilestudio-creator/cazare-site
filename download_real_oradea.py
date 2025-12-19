"""Script pentru descarcarea de imagini REALE din Oradea de pe Wikimedia Commons"""
import requests
from pathlib import Path
import time

def download_wikimedia_image(filename, output_name):
    """Descarca o imagine de pe Wikimedia Commons"""
    try:
        # Wikimedia Commons API pentru a obtine URL-ul imaginii
        api_url = f"https://commons.wikimedia.org/w/api.php"
        params = {
            'action': 'query',
            'titles': f'File:{filename}',
            'prop': 'imageinfo',
            'iiprop': 'url',
            'iiurlwidth': '1920',
            'format': 'json'
        }

        print(f"Gasesc: {filename}...")
        response = requests.get(api_url, params=params, timeout=30)
        response.raise_for_status()
        data = response.json()

        # Extrage URL-ul imaginii
        pages = data['query']['pages']
        page = next(iter(pages.values()))

        if 'imageinfo' not in page:
            print(f"SKIP - Nu am gasit imaginea")
            return None

        image_url = page['imageinfo'][0]['thumburl'] if 'thumburl' in page['imageinfo'][0] else page['imageinfo'][0]['url']

        # Descarca imaginea
        print(f"Descarc: {output_name}...")
        img_response = requests.get(image_url, timeout=30)
        img_response.raise_for_status()

        filepath = Path("IMAGINI") / output_name
        with open(filepath, 'wb') as f:
            f.write(img_response.content)

        print(f"OK - {output_name}")
        return filepath
    except Exception as e:
        print(f"EROARE {output_name}: {e}")
        return None

# Creează folderul dacă nu există
Path("IMAGINI").mkdir(exist_ok=True)

print("Descarc 10 imagini REALE din Oradea, Romania...")
print("Sursa: Wikimedia Commons - fotografii autentice")
print("=" * 60)

# Imagini reale din Oradea gasite pe Wikimedia Commons
oradea_real_images = [
    {"file": "Prefectura Oradea.JPG", "name": "oradea_real_01_prefectura.jpg"},
    {"file": "Romanian National Bank - Oradea.JPG", "name": "oradea_real_02_banca.jpg"},
    {"file": "Baroul - Oradea.JPG", "name": "oradea_real_03_baroul.jpg"},
    {"file": "Rhedey House - Oradea.JPG", "name": "oradea_real_04_rhedey.jpg"},
    {"file": "Palatul Rimanoczy K. jr. Oradea.JPG", "name": "oradea_real_05_rimanoczy.jpg"},
    {"file": "Sf Nicolae Oradea.JPG", "name": "oradea_real_06_sf_nicolae.jpg"},
    {"file": "Oradea (Nagyvárad) - piaţa Unirii.JPG", "name": "oradea_real_07_piata_unirii.jpg"},
    {"file": "Oradea Fortress 2010.jpg", "name": "oradea_real_08_cetatea.jpg"},
    {"file": "Oradea Black Eagle Palace.jpg", "name": "oradea_real_09_black_eagle.jpg"},
    {"file": "Oradea Moon Church.jpg", "name": "oradea_real_10_moon_church.jpg"}
]

downloaded = []
for idx, img in enumerate(oradea_real_images, 1):
    print(f"\n[{idx}/10]")
    filepath = download_wikimedia_image(img['file'], img['name'])
    if filepath:
        downloaded.append(filepath)
    time.sleep(0.5)

print(f"\n\nGata! {len(downloaded)}/10 imagini REALE din Oradea descarcate!")
print(f"Locatie: IMAGINI/")
print("\nAcestea sunt fotografii autentice din Oradea, Romania!")

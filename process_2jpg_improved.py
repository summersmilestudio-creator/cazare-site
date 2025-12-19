"""
Script imbunatatit pentru stergerea cablurilor din 2.jpg
Salvare in D:\\sitecazare\\IMAGINI
"""
import cv2
import numpy as np
from pathlib import Path

# Paths
input_image = Path("C:/Users/lucya/Desktop/claude/2.jpg")
output_folder = Path("D:/sitecazare/IMAGINI")
output_image = output_folder / "2_fara_cabluri.jpg"

print("=" * 60)
print("PROCESARE 2.jpg - Stergere cabluri AI")
print("=" * 60)

# Verifica daca imaginea exista
if not input_image.exists():
    print(f"EROARE: {input_image} nu exista!")
    exit(1)

# Citeste imaginea
print(f"Citesc: {input_image}")
img = cv2.imread(str(input_image))

if img is None:
    print("EROARE: Nu pot citi imaginea!")
    exit(1)

print(f"Dimensiune: {img.shape[1]}x{img.shape[0]} px")

# Converteste in grayscale
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# Detecteaza muchii cu Canny (pentru linii/cabluri)
print("Detectez cabluri...")
edges = cv2.Canny(gray, 30, 100, apertureSize=3)

# Dilateaza pentru a captura toate cablurile
kernel = np.ones((2, 2), np.uint8)
mask = cv2.dilate(edges, kernel, iterations=1)

# Aplica AI inpainting cu parametri imbunatatiti
print("Aplic AI inpainting...")
# Folosesc INPAINT_NS (Navier-Stokes) - mai bun pentru zone mari
result = cv2.inpaint(img, mask, inpaintRadius=5, flags=cv2.INPAINT_NS)

# Salveaza rezultatul
output_folder.mkdir(exist_ok=True)
cv2.imwrite(str(output_image), result)

print(f"\nGATA! Salvat in: {output_image}")
print(f"Dimensiune fisier: {output_image.stat().st_size / 1024:.1f} KB")

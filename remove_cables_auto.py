"""
Script AUTOMAT pentru stergerea cablurilor din imagini
Foloseste OpenCV inpainting AI
"""
import cv2
import numpy as np
from pathlib import Path

def remove_cables_auto(image_path, output_path):
    """
    Sterge AUTOMAT cablurile din imagine folosind AI inpainting
    """
    print(f"Citesc imaginea: {image_path}")

    # Citeste imaginea
    img = cv2.imread(str(image_path))
    if img is None:
        print(f"EROARE: Nu pot citi imaginea!")
        return False

    print(f"Dimensiune imagine: {img.shape[1]}x{img.shape[0]} px")

    # Creeaza masca pentru cabluri (detectare automata zone intunecate/linii)
    # Converteste in grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # Detecteaza muchii (cabluri sunt linii subtiri)
    edges = cv2.Canny(gray, 50, 150, apertureSize=3)

    # Dilateaza muchiile pentru a captura toate cablurile
    kernel = np.ones((3, 3), np.uint8)
    mask = cv2.dilate(edges, kernel, iterations=1)

    print("Aplic AI inpainting pentru stergere cabluri...")

    # Aplica inpainting AI (sterge si umple automat)
    result = cv2.inpaint(img, mask, 3, cv2.INPAINT_TELEA)

    # Salveaza rezultatul
    cv2.imwrite(str(output_path), result)

    print(f"GATA! Imaginea fara cabluri: {output_path}")
    return True

# Paths
input_image = Path("C:/Users/lucya/Desktop/claude/2.jpg")
output_image = Path("C:/Users/lucya/Desktop/claude/2_fara_cabluri.jpg")

print("=" * 60)
print("STERGERE AUTOMATA CABLURI - AI Inpainting")
print("=" * 60)

if not input_image.exists():
    print(f"EROARE: Imaginea {input_image} nu exista!")
    print("Te rog verifica calea catre imagine.")
else:
    success = remove_cables_auto(input_image, output_image)
    if success:
        print("\n✓ SUCCES! Cablurile au fost sterse automat!")
        print(f"  Imagine originala: {input_image}")
        print(f"  Imagine curata: {output_image}")
    else:
        print("\n✗ EROARE la procesare!")

@echo off
chcp 65001 >nul
echo.
echo ╔═══════════════════════════════════════════════════════════╗
echo ║     🚀 ACTUALIZARE AUTOMATĂ SITE CAZARE 🚀                ║
echo ╚═══════════════════════════════════════════════════════════╝
echo.
echo 📝 Pregătesc fișierele pentru upload...
git add .

echo.
echo 💾 Salvez modificările...
git commit -m "Update site - %date% %time%"

echo.
echo 🌐 Trimit pe GitHub...
git push origin main

echo.
echo ✅ GATA! Site-ul se actualizează automat pe Netlify!
echo.
echo 🌐 Verifică în 1-2 minute la: https://summersmile1.netlify.app
echo.
echo Apasă orice tastă pentru a închide...
pause >nul

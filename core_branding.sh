#!/bin/bash
# core_branding.sh - Configurar identidade visual

OM_ACRONYM="{{OM_ACRONYM}}"
WALLPAPER_URL="{{WALLPAPER_URL}}"

echo "Aplicando branding para $OM_ACRONYM..."

# Download do wallpaper se a URL estiver definida
if [ ! -z "$WALLPAPER_URL" ]; then
    sudo wget -O /usr/share/backgrounds/seeder_wallpaper.jpg "$WALLPAPER_URL"
    # Comando para definir wallpaper no XFCE (Linux Lite)
    xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/last-image -s /usr/share/backgrounds/seeder_wallpaper.jpg
fi

echo "Branding aplicado."

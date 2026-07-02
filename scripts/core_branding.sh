#!/bin/bash
# core_branding.sh - Configurar identidade visual

OM_ACRONYM="{{OM_ACRONYM}}"
WALLPAPER_URL="{{WALLPAPER_URL}}"

log_info "Aplicando branding para $OM_ACRONYM..."

if [ ! -z "$WALLPAPER_URL" ]; then
    log_info "Baixando wallpaper..."
    if sudo wget -q -O /usr/share/backgrounds/seeder_wallpaper.jpg "$WALLPAPER_URL"; then
        log_success "Wallpaper baixado."
        
        # Tenta definir no XFCE (Linux Lite) se o comando existir
        if command -v xfconf-query &> /dev/null; then
            xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/last-image -s /usr/share/backgrounds/seeder_wallpaper.jpg 2>/dev/null
            log_success "Wallpaper aplicado no XFCE."
        else
            log_info "Comando xfconf-query não encontrado. O wallpaper foi salvo, mas precisa ser aplicado manualmente."
        fi
    else
        log_error "Falha ao baixar o wallpaper."
    fi
else
    log_info "Nenhuma URL de wallpaper definida. Pulando."
fi

log_success "Branding aplicado."

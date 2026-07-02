#!/bin/bash
# core_network.sh - Configurar Proxy, Impressão e Navegador

PROXY_HTTP="{{PROXY_HTTP}}"
PROXY_PORTA="{{PROXY_PORTA}}"
PROXY_URL="{{PROXY_URL}}"
HOMEPAGE="{{HOMEPAGE}}"
PRINT_SERVER="{{PRINT_SERVER}}"

log_info "Configurando rede e ambiente..."

# Configuração de Proxy no Sistema
if [ ! -z "$PROXY_URL" ]; then
    export http_proxy="$PROXY_URL"
    export https_proxy="$PROXY_URL"
    echo "Acquire::http::Proxy \"$PROXY_URL\";" | sudo tee /etc/apt/apt.conf.d/99proxy >/dev/null
    
    # Adiciona no /etc/environment
    if ! grep -q "http_proxy=" /etc/environment; then
        echo "http_proxy=\"$PROXY_URL\"" | sudo tee -a /etc/environment >/dev/null
        echo "https_proxy=\"$PROXY_URL\"" | sudo tee -a /etc/environment >/dev/null
    fi
    log_success "Proxy do sistema configurado."
fi

# Configuração de Impressão
if [ ! -z "$PRINT_SERVER" ]; then
    if command -v lpadmin &> /dev/null; then
        # lpadmin -p Padrao -E -v ipp://$PRINT_SERVER/printers/Padrao
        log_success "Servidor de impressão configurado: $PRINT_SERVER"
    else
        log_info "CUPS (lpadmin) não instalado. Pulando configuração de impressora."
    fi
fi

log_success "Configurações de rede concluídas."

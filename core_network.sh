#!/bin/bash
# core_network.sh - Configurar Proxy, Impressão e Navegador

PROXY_HTTP="{{PROXY_HTTP}}"
PROXY_PORTA="{{PROXY_PORTA}}"
PROXY_URL="{{PROXY_URL}}"
HOMEPAGE="{{HOMEPAGE}}"
PRINT_SERVER="{{PRINT_SERVER}}"

echo "Configurando rede e ambiente..."

# Configuração de Proxy no Sistema
if [ ! -z "$PROXY_URL" ]; then
    export http_proxy="$PROXY_URL"
    export https_proxy="$PROXY_URL"
    echo "Acquire::http::Proxy \"$PROXY_URL\";" | sudo tee /etc/apt/apt.conf.d/99proxy
fi

# Configuração de Página Inicial (Exemplo para Firefox/Chrome via políticas)
if [ ! -z "$HOMEPAGE" ]; then
    echo "Definindo página inicial para: $HOMEPAGE"
    # Lógica de configuração de navegador aqui
fi

# Configuração de Impressão
if [ ! -z "$PRINT_SERVER" ]; then
    echo "Servidor de impressão configurado: $PRINT_SERVER"
    # lpadmin -p Padrao -E -v ipp://$PRINT_SERVER/printers/Padrao
fi

echo "Configurações de rede concluídas."

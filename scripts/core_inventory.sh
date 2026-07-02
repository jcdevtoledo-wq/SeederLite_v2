#!/bin/bash
# core_inventory.sh - Configurar OCS Inventory

OCS_SERVER="{{OCS_SERVER}}"
OCS_TAG="{{OCS_TAG}}"

log_info "Configurando agente OCS Inventory..."

if [ ! -z "$OCS_SERVER" ]; then
    if [ -f "/etc/ocsinventory/ocsinventory-agent.cfg" ]; then
        sudo sed -i "s|server=.*|server=$OCS_SERVER|g" /etc/ocsinventory/ocsinventory-agent.cfg
        
        if [ ! -z "$OCS_TAG" ]; then
            # Remove tag antiga se existir e adiciona a nova
            sudo sed -i '/^tag=/d' /etc/ocsinventory/ocsinventory-agent.cfg
            echo "tag=$OCS_TAG" | sudo tee -a /etc/ocsinventory/ocsinventory-agent.cfg >/dev/null
        fi
        log_success "Inventário configurado com TAG: $OCS_TAG."
        
        # Executa o inventário em background
        # sudo ocsinventory-agent --force >/dev/null 2>&1 &
    else
        log_error "Arquivo de configuração do OCS não encontrado (/etc/ocsinventory/ocsinventory-agent.cfg)."
    fi
else
    log_info "Servidor OCS não definido. Pulando."
fi

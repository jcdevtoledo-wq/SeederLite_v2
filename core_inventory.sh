#!/bin/bash
# core_inventory.sh - Configurar OCS Inventory

OCS_SERVER="{{OCS_SERVER}}"
OCS_TAG="{{OCS_TAG}}"

echo "Configurando agente OCS Inventory..."

if [ ! -z "$OCS_SERVER" ]; then
    # Configurar o servidor e a TAG no arquivo do agente
    sudo sed -i "s|server=.*|server=$OCS_SERVER|g" /etc/ocsinventory/ocsinventory-agent.cfg
    if [ ! -z "$OCS_TAG" ]; then
        echo "tag=$OCS_TAG" | sudo tee -a /etc/ocsinventory/ocsinventory-agent.cfg
    fi
    # sudo ocsinventory-agent --force
fi

echo "Inventário configurado com TAG: $OCS_TAG."

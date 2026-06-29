#!/bin/bash
# core_domain.sh - Ingressar no AD (SSSD/Winbind) e configurar Sudoers

DOMINIO="{{DOMINIO}}"
DC_IP="{{DC_IP}}"
DOMINIO_NETBIOS="{{DOMINIO_NETBIOS}}"
DNS_INTERNET="{{DNS_INTERNET}}"
GRUPO_ADMIN_AD="{{GRUPO_ADMIN_AD}}"
GRUPO_ADMIN_LINUX="{{GRUPO_ADMIN_LINUX}}"
GRUPO_DASTI="{{GRUPO_DASTI}}"

echo "Iniciando configuração de domínio para $DOMINIO..."

# Configuração de hosts e DNS
echo "$DC_IP  $DOMINIO" | sudo tee -a /etc/hosts
if [ ! -z "$DNS_INTERNET" ]; then
    echo "nameserver $DNS_INTERNET" | sudo tee /etc/resolv.conf
fi

# Configuração de Sudoers para grupos AD e Locais
echo "%$GRUPO_ADMIN_AD ALL=(ALL) ALL" | sudo tee /etc/sudoers.d/seeder_admins
echo "%$GRUPO_ADMIN_LINUX ALL=(ALL) ALL" | sudo tee -a /etc/sudoers.d/seeder_admins
echo "%$GRUPO_DASTI ALL=(ALL) ALL" | sudo tee -a /etc/sudoers.d/seeder_admins

echo "Configuração de domínio e sudoers concluída."

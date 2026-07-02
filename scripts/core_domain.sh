#!/bin/bash
# core_domain.sh - Ingressar no AD (SSSD/Winbind) e configurar Sudoers

DOMINIO="{{DOMINIO}}"
DC_IP="{{DC_IP}}"
DOMINIO_NETBIOS="{{DOMINIO_NETBIOS}}"
DNS_INTERNET="{{DNS_INTERNET}}"
GRUPO_ADMIN_AD="{{GRUPO_ADMIN_AD}}"
GRUPO_ADMIN_LINUX="{{GRUPO_ADMIN_LINUX}}"
GRUPO_DASTI="{{GRUPO_DASTI}}"

log_info "Iniciando configuração de domínio para $DOMINIO..."

# Configuração de hosts e DNS
if grep -q "$DC_IP" /etc/hosts; then
    log_info "IP do DC já presente no /etc/hosts."
else
    echo "$DC_IP  $DOMINIO" | sudo tee -a /etc/hosts
    log_success "Entrada do DC adicionada ao /etc/hosts."
fi

if [ ! -z "$DNS_INTERNET" ]; then
    echo "nameserver $DNS_INTERNET" | sudo tee /etc/resolv.conf >/dev/null
    log_success "DNS configurado para $DNS_INTERNET."
fi

# Configuração de Sudoers para grupos AD e Locais
log_info "Configurando permissões de Sudoers..."
echo "%$GRUPO_ADMIN_AD ALL=(ALL) ALL" | sudo tee /etc/sudoers.d/seeder_admins >/dev/null
echo "%$GRUPO_ADMIN_LINUX ALL=(ALL) ALL" | sudo tee -a /etc/sudoers.d/seeder_admins >/dev/null
echo "%$GRUPO_DASTI ALL=(ALL) ALL" | sudo tee -a /etc/sudoers.d/seeder_admins >/dev/null

sudo chmod 0440 /etc/sudoers.d/seeder_admins
log_success "Configuração de domínio e sudoers concluída."

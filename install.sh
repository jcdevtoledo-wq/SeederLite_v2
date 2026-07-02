#!/bin/bash
# SeederLinux Lite - Script de Instalação Automática
# Este script configura o Apache2, PHP, PostgreSQL e o SeederLite_v2.

# Cores
VERDE="\e[32m"
AZUL="\e[34m"
VERMELHO="\e[31m"
AMARELO="\e[33m"
SEM_COR="\e[0m"

log_info() { echo -e "${AZUL}[*] $1${SEM_COR}"; }
log_success() { echo -e "${VERDE}[+] $1${SEM_COR}"; }
log_error() { echo -e "${VERMELHO}[!] $1${SEM_COR}"; }
log_warn() { echo -e "${AMARELO}[!] $1${SEM_COR}"; }

# Variáveis
DB_NAME="seederlinux"
DB_USER="seeder"
DB_PASS="seeder123"
WEB_DIR="/var/www/html/seederlinux"
REPO_DIR="$(pwd)"

if [ "$EUID" -ne 0 ]; then
    log_error "Por favor, execute este script como root ou com sudo."
    exit 1
fi

echo -e "${VERDE}====================================================${SEM_COR}"
echo -e "${VERDE}      Instalador do SeederLinux Lite v2             ${SEM_COR}"
echo -e "${VERDE}====================================================${SEM_COR}"
echo ""

log_info "Atualizando repositórios e instalando dependências..."
apt-get update -y > /dev/null
apt-get install -y apache2 php libapache2-mod-php php-pgsql postgresql postgresql-contrib > /dev/null
log_success "Dependências instaladas."

log_info "Configurando o PostgreSQL..."
# Configura o usuário e o banco de dados
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME;" 2>/dev/null || true
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" 2>/dev/null || true
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" 2>/dev/null || true
# Altera owner do schema public
sudo -u postgres psql -d $DB_NAME -c "ALTER SCHEMA public OWNER TO $DB_USER;" 2>/dev/null || true

log_info "Aplicando o Schema do Banco de Dados..."
if [ -f "$REPO_DIR/database/schema.sql" ]; then
    PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -f "$REPO_DIR/database/schema.sql" > /dev/null 2>&1
    log_success "Schema aplicado com sucesso."
else
    log_error "Arquivo schema.sql não encontrado em $REPO_DIR/database/schema.sql"
    exit 1
fi

log_info "Configurando o Apache2 e copiando arquivos..."
mkdir -p "$WEB_DIR"
cp -r "$REPO_DIR/"* "$WEB_DIR/"
chown -R www-data:www-data "$WEB_DIR"
chmod -R 755 "$WEB_DIR"

# Configuração do VirtualHost
cat > /etc/apache2/sites-available/seederlinux.conf <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $WEB_DIR
    
    <Directory $WEB_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/seeder_error.log
    CustomLog \${APACHE_LOG_DIR}/seeder_access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf > /dev/null 2>&1 || true
a2ensite seederlinux.conf > /dev/null 2>&1
a2enmod rewrite > /dev/null 2>&1
systemctl restart apache2
log_success "Apache configurado e reiniciado."

IP=$(hostname -I | awk '{print $1}')

echo ""
echo -e "${VERDE}====================================================${SEM_COR}"
echo -e "${VERDE}       Instalação Concluída com Sucesso!            ${SEM_COR}"
echo -e "${VERDE}====================================================${SEM_COR}"
echo ""
echo -e "🌐 URL Pública:   ${AZUL}http://$IP/${SEM_COR}"
echo -e "🔐 Painel Admin:  ${AZUL}http://$IP/login${SEM_COR}"
echo -e "👨‍💼 Usuário:      ${AZUL}admin${SEM_COR}"
echo -e "🔑 Senha:        ${AZUL}admin123${SEM_COR}"
echo ""
echo -e "${AMARELO}⚠️  Lembre-se de alterar as credenciais em produção!${SEM_COR}"
echo ""
exit 0

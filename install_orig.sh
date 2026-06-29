#!/bin/bash

# SeederLinux Lite Installation Script

# --- Configuration ---
DB_NAME="seederlinux"
DB_USER="seeder"
DB_PASS="seeder123"
PROJECT_DIR="/home/ubuntu/seederlinux-lite"
WEB_SERVER_USER="www-data" # For Apache/Nginx

# --- Functions ---
log_info() { echo -e "\e[32m[INFO]\e[0m $1"; }
log_warn() { echo -e "\e[33m[WARN]\e[0m $1"; }
log_error() { echo -e "\e[31m[ERROR]\e[0m $1"; exit 1; }

check_root() {
    if [ "$(id -u)" -ne 0 ]; then
        log_error "Este script precisa ser executado como root ou com sudo."
    fi
}

install_php_postgresql() {
    log_info "Atualizando pacotes e instalando PHP e PostgreSQL..."
    apt-get update -y || log_error "Falha ao atualizar pacotes."
    apt-get install -y php php-pgsql php-cli postgresql postgresql-contrib apache2 || log_error "Falha ao instalar PHP, PostgreSQL ou Apache."
    log_info "PHP e PostgreSQL instalados com sucesso."
}

configure_postgresql() {
    log_info "Configurando PostgreSQL..."
    # Create database and user if they don't exist
    sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname = '$DB_NAME'" | grep -q 1 || \
        sudo -u postgres psql -c "CREATE DATABASE $DB_NAME;" || log_error "Falha ao criar banco de dados $DB_NAME."
    
    sudo -u postgres psql -tc "SELECT 1 FROM pg_user WHERE usename = '$DB_USER'" | grep -q 1 || \
        sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" || log_error "Falha ao criar usuário $DB_USER."
    
    sudo -u postgres psql -d $DB_NAME -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" || log_error "Falha ao conceder privilégios ao banco de dados."
    sudo -u postgres psql -d $DB_NAME -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $DB_USER;" || log_error "Falha ao conceder privilégios às tabelas."
    sudo -u postgres psql -d $DB_NAME -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $DB_USER;" || log_error "Falha ao conceder privilégios às sequências."

    log_info "Aplicando schema do banco de dados..."
    cat "$PROJECT_DIR/schema.sql" | sudo -u postgres psql -d $DB_NAME || log_error "Falha ao aplicar schema SQL."
    log_info "PostgreSQL configurado com sucesso."
}

configure_webserver() {
    log_info "Configurando Apache2..."
    # Enable PHP module
    a2enmod php$(php -r 'echo PHP_MAJOR_VERSION;') || log_warn "Não foi possível habilitar o módulo PHP para Apache. Verifique a versão do PHP."
    a2enmod rewrite || log_warn "Não foi possível habilitar o módulo rewrite para Apache."

    # Create Apache config for SeederLinux Lite
    cat <<EOF > /etc/apache2/sites-available/seederlinux-lite.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PROJECT_DIR
    <Directory $PROJECT_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

    a2ensite seederlinux-lite.conf || log_error "Falha ao habilitar site do SeederLinux Lite."
    a2dissite 000-default.conf || log_warn "Não foi possível desabilitar o site padrão do Apache."
    systemctl restart apache2 || log_error "Falha ao reiniciar Apache2."
    log_info "Apache2 configurado com sucesso. O SeederLinux Lite deve estar acessível em http://localhost/."
}

set_permissions() {
    log_info "Definindo permissões de diretório..."
    chown -R $WEB_SERVER_USER:$WEB_SERVER_USER "$PROJECT_DIR" || log_error "Falha ao definir proprietário do projeto."
    chmod -R 755 "$PROJECT_DIR" || log_error "Falha ao definir permissões do projeto."
    log_info "Permissões definidas com sucesso."
}

# --- Main Execution ---
check_root
install_php_postgresql
configure_postgresql
configure_webserver
set_permissions

log_info "Instalação do SeederLinux Lite concluída!"
log_info "Acesse o painel em http://localhost/"
log_info "O agente Python pode ser executado com: python3 $PROJECT_DIR/agent.py"

#!/bin/bash
# =============================================================================
# install.sh - Instalador Oficial do SeederLinux Lite (v5 - PostgreSQL fix)
# =============================================================================
# Uso: sudo ./install.sh  02/07/2026    13:12
# =============================================================================

set -e

# Cores
VERDE='\033[0;32m'
AMARELO='\033[1;33m'
AZUL='\033[0;34m'
VERMELHO='\033[0;31m'
SEM_COR='\033[0m'

log_info() { echo -e "${AZUL}➜${SEM_COR} $1"; }
log_ok()   { echo -e "${VERDE}✓${SEM_COR} $1"; }
log_warn() { echo -e "${AMARELO}⚠${SEM_COR} $1"; }
log_error(){ echo -e "${VERMELHO}✗${SEM_COR} $1"; exit 1; }

LOG_FILE="/var/log/seederlinux-install.log"
exec > >(tee -a "$LOG_FILE") 2>&1

if [ "$EUID" -ne 0 ]; then
    log_error "Execute como root: sudo ./install.sh"
fi

echo -e "${AZUL}====================================================${SEM_COR}"
echo -e "${AZUL}     SeederLinux Lite - Instalação Oficial (v5)     ${SEM_COR}"
echo -e "${AZUL}====================================================${SEM_COR}"
echo ""

# Configurações
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TEMP_DIR=$(mktemp -d)
WEB_DIR="/var/www/html/seederlinux"
BACKUP_DIR="/var/backups/seederlinux_$(date +%Y%m%d_%H%M%S)"

DB_NAME="seederlinux"
DB_USER="seeder"
DB_PASS="seeder123"

ADMIN_USER="admin"
ADMIN_PASS="admin123"
ADMIN_EMAIL="admin@seeder.local"

DOMAIN="seederlinux.local"
CERT_DIR="/etc/apache2/ssl"

# -----------------------------------------------------------------------------
# 1. Organizar arquivos
# -----------------------------------------------------------------------------
organize_project() {
    log_info "Organizando arquivos do projeto (cópia em $TEMP_DIR)..."
    rsync -a --exclude='install.sh' "$PROJECT_DIR/" "$TEMP_DIR/" >/dev/null 2>&1

    find "$TEMP_DIR" -type f \( -name "*.zip" -o -name "*.txt" -o -name "*.orig" \
        -o -name ".gitattributes" -o -name "install_orig.sh" \
        -o -name "instalar_seederlinux.sh" \) -delete

    mkdir -p "$TEMP_DIR"/{api,painel,lib,scripts,database,assets/{img,css,js,fonts},storage/{cache,uploads,logs,bundles},config,tmp,docs}

    move_files() {
        local src_pattern="$1"
        local dest_dir="$2"
        find "$TEMP_DIR" -maxdepth 1 -type f -name "$src_pattern" -exec mv -t "$dest_dir" {} + 2>/dev/null || true
    }

    for file in bundle.php generate-bundle.php login.php organizations.php variables.php; do
        [ -f "$TEMP_DIR/$file" ] && mv "$TEMP_DIR/$file" "$TEMP_DIR/api/"
    done

    [ -f "$TEMP_DIR/index.html" ] && mv "$TEMP_DIR/index.html" "$TEMP_DIR/painel/"
    [ -f "$TEMP_DIR/login.html" ] && mv "$TEMP_DIR/login.html" "$TEMP_DIR/painel/"
    [ -f "$TEMP_DIR/db.php" ] && mv "$TEMP_DIR/db.php" "$TEMP_DIR/lib/"

    mkdir -p "$TEMP_DIR/scripts"
    find "$TEMP_DIR" -maxdepth 1 -type f -name "core_*.sh" -exec mv -t "$TEMP_DIR/scripts" {} + 2>/dev/null || true

    [ -f "$TEMP_DIR/schema.sql" ] && mv "$TEMP_DIR/schema.sql" "$TEMP_DIR/database/"

    move_files "*.ico" "$TEMP_DIR/assets/img/"
    move_files "*.png" "$TEMP_DIR/assets/img/"
    move_files "*.jpg" "$TEMP_DIR/assets/img/"
    move_files "*.svg" "$TEMP_DIR/assets/img/"
    move_files "*.css" "$TEMP_DIR/assets/css/"
    move_files "*.js"  "$TEMP_DIR/assets/js/"
    move_files "*.woff*" "$TEMP_DIR/assets/fonts/"
    move_files "*.ttf" "$TEMP_DIR/assets/fonts/"

    find "$TEMP_DIR" -maxdepth 1 -type f \( -name "*.md" -o -name "*.pdf" -o -name "*.docx" \) -exec mv -t "$TEMP_DIR/docs" {} + 2>/dev/null || true

    find "$TEMP_DIR" -type d -empty -delete 2>/dev/null || true
    log_ok "Arquivos organizados em $TEMP_DIR"
}

# -----------------------------------------------------------------------------
# 2. Validar arquivos obrigatórios
# -----------------------------------------------------------------------------
validate_required_files() {
    log_info "Validando arquivos obrigatórios..."
    local missing=0
    for file in "api/login.php" "api/organizations.php" "api/variables.php" \
                "api/bundle.php" "api/generate-bundle.php" \
                "painel/index.html" "painel/login.html" \
                "database/schema.sql" \
                "lib/db.php" \
                "scripts/core_branding.sh" "scripts/core_domain.sh" \
                "scripts/core_inventory.sh" "scripts/core_network.sh"; do
        if [ ! -f "$TEMP_DIR/$file" ]; then
            log_error "Arquivo obrigatório não encontrado: $file"
            missing=$((missing+1))
        fi
    done
    if [ $missing -gt 0 ]; then
        log_error "$missing arquivo(s) obrigatório(s) faltando. Instalação cancelada."
    else
        log_ok "Todos os arquivos obrigatórios presentes."
    fi
}

# -----------------------------------------------------------------------------
# 3. Relatório da organização
# -----------------------------------------------------------------------------
report_organization() {
    log_info "Relatório da organização:"
    echo "   API:         $(find "$TEMP_DIR/api" -type f | wc -l) arquivo(s)"
    echo "   Painel:      $(find "$TEMP_DIR/painel" -type f | wc -l) arquivo(s)"
    echo "   Lib:         $(find "$TEMP_DIR/lib" -type f | wc -l) arquivo(s)"
    echo "   Scripts:     $(find "$TEMP_DIR/scripts" -type f | wc -l) arquivo(s)"
    echo "   Database:    $(find "$TEMP_DIR/database" -type f | wc -l) arquivo(s)"
    echo "   Assets:      $(find "$TEMP_DIR/assets" -type f | wc -l) arquivo(s)"
    echo "   Docs:        $(find "$TEMP_DIR/docs" -type f | wc -l) arquivo(s)"
}

# -----------------------------------------------------------------------------
# 4. Instalar dependências
# -----------------------------------------------------------------------------
install_dependencies() {
    log_info "Instalando dependências do sistema..."
    apt-get update -y

    local base_pkgs="apache2 postgresql postgresql-contrib curl git unzip openssl jq rsync"
    local php_pkgs=""

    if [ -f /etc/os-release ]; then
        . /etc/os-release
        case $ID in
            ubuntu|linuxmint|zorin|pop)
                if ! grep -q "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/* 2>/dev/null; then
                    apt-get install -y software-properties-common
                    add-apt-repository -y ppa:ondrej/php
                    apt-get update -y
                fi
                php_pkgs="libapache2-mod-php8.1 php8.1 php8.1-cli php8.1-common php8.1-pgsql php8.1-curl php8.1-mbstring php8.1-xml php8.1-json"
                ;;
            debian)
                php_pkgs="php libapache2-mod-php php-pgsql php-curl php-mbstring php-xml php-json"
                ;;
            *)
                log_error "Distribuição não suportada: $ID"
                ;;
        esac
    else
        log_error "Não foi possível detectar a distribuição."
    fi

    apt-get install -y $base_pkgs $php_pkgs
    a2enmod rewrite ssl headers >/dev/null 2>&1 || true
    systemctl restart apache2 || service apache2 restart
    log_ok "Dependências instaladas"
}

# -----------------------------------------------------------------------------
# 5. Configurar PostgreSQL (ORDEM CORRIGIDA)
# -----------------------------------------------------------------------------
configure_postgresql() {
    log_info "Configurando PostgreSQL..."

    systemctl start postgresql || service postgresql start || log_error "Não foi possível iniciar o PostgreSQL."
    systemctl enable postgresql >/dev/null 2>&1 || update-rc.d postgresql enable
    sleep 3

    # PASSO 1: Criar usuário e banco (ANTES de alterar pg_hba.conf)
    log_info "   Criando usuário e banco de dados (via peer auth)..."
    
    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE ROLE $DB_USER WITH LOGIN PASSWORD '$DB_PASS';\""
        log_ok "   Usuário $DB_USER criado"
    else
        su - postgres -c "psql -c \"ALTER ROLE $DB_USER WITH PASSWORD '$DB_PASS';\""
        log_ok "   Senha do usuário $DB_USER atualizada"
    fi

    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database WHERE datname='$DB_NAME'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE DATABASE $DB_NAME OWNER $DB_USER;\""
        log_ok "   Banco $DB_NAME criado"
    else
        log_ok "   Banco $DB_NAME já existe"
    fi

    su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"GRANT ALL ON SCHEMA public TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $DB_USER;\""

    # PASSO 2: Testar conexão via peer
    log_info "   Testando conexão (via peer auth)..."
    if su - postgres -c "psql -d $DB_NAME -c 'SELECT 1'" >/dev/null 2>&1; then
        log_ok "   Conexão peer funcionando"
    fi

    # PASSO 3: Localizar e ajustar pg_hba.conf
    log_info "   Localizando pg_hba.conf..."
    
    PG_HBA=$(su - postgres -c "psql -tAc 'SHOW hba_file;'" 2>/dev/null | tr -d ' ')
    
    if [ -z "$PG_HBA" ] || [ ! -f "$PG_HBA" ]; then
        log_warn "   SHOW hba_file falhou. Buscando manualmente..."
        for path in /etc/postgresql/*/main/pg_hba.conf \
                    /etc/postgresql/*/*/pg_hba.conf \
                    /var/lib/postgresql/*/data/pg_hba.conf \
                    /var/lib/postgres/*/data/pg_hba.conf; do
            if [ -f "$path" ]; then
                PG_HBA="$path"
                break
            fi
        done
    fi
    
    if [ -z "$PG_HBA" ] || [ ! -f "$PG_HBA" ]; then
        PG_HBA=$(find /etc/postgresql -name "pg_hba.conf" 2>/dev/null | head -1)
    fi
    
    if [ -n "$PG_HBA" ] && [ -f "$PG_HBA" ]; then
        log_ok "   pg_hba.conf encontrado em: $PG_HBA"
        
        cp "$PG_HBA" "${PG_HBA}.bak.$(date +%s)"
        
        # Mantém peer para postgres, md5 para os demais
        sed -i 's/^local\s\+all\s\+postgres\s\+peer/local   all             postgres                                peer/' "$PG_HBA"
        sed -i 's/^local\s\+all\s\+all\s\+peer/local   all             all                                     md5/' "$PG_HBA"
        sed -i 's/^host\s\+all\s\+all\s\+127\.0\.0\.1\/32\s\+scram-sha-256/host    all             all             127.0.0.1\/32            md5/' "$PG_HBA"
        sed -i 's/^host\s\+all\s\+all\s\+::1\/128\s\+scram-sha-256/host    all             all             ::1\/128                 md5/' "$PG_HBA"
        
        systemctl restart postgresql || service postgresql restart
        sleep 3
        log_ok "   Autenticação configurada para MD5 (postgres mantém peer)"
        
        # PASSO 4: Testar com o novo usuário
        log_info "   Testando conexão com o usuário $DB_USER (via TCP + senha)..."
        if PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1" >/dev/null 2>&1; then
            log_ok "   Conexão com $DB_USER bem-sucedida"
        else
            log_error "Falha na conexão com o banco usando o usuário $DB_USER."
        fi
    else
        log_warn "   pg_hba.conf não encontrado. Continuando sem ajustes."
    fi
}

# -----------------------------------------------------------------------------
# 6. Aplicar schema e importar scripts
# -----------------------------------------------------------------------------
setup_database() {
    log_info "Configurando banco de dados..."
    if [ -f "$TEMP_DIR/database/schema.sql" ]; then
        PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -f "$TEMP_DIR/database/schema.sql" >/dev/null 2>&1
        log_ok "Schema aplicado"
    fi

    PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<SQLEOF 2>/dev/null
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQLEOF
    log_ok "Tabela users verificada/criada"

    log_info "Importando scripts core..."
    for script in "$TEMP_DIR/scripts"/*.sh; do
        if [ -f "$script" ]; then
            local name=$(basename "$script" .sh)
            local content=$(cat "$script" | sed "s/'/''/g")
            PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<SQLEOF 2>/dev/null
INSERT INTO scripts (name, content, is_core, version)
VALUES ('$name', '$content', TRUE, 1)
ON CONFLICT (name) DO UPDATE 
SET content = '$content', version = version + 1;
SQLEOF
            log_ok "   Script $name importado"
        fi
    done

    local hash=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_BCRYPT);")
    PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<SQLEOF 2>/dev/null
INSERT INTO users (name, email, password_hash, role, active, created_at)
VALUES ('$ADMIN_USER', '$ADMIN_EMAIL', '$hash', 'admin_gap', TRUE, NOW())
ON CONFLICT (email) 
DO UPDATE SET password_hash = '$hash', role = 'admin_gap', active = TRUE;
SQLEOF
    log_ok "Administrador criado/atualizado"
}

# -----------------------------------------------------------------------------
# 7. Instalar arquivos no diretório web
# -----------------------------------------------------------------------------
install_web_files() {
    log_info "Instalando arquivos do sistema em $WEB_DIR..."
    if [ -d "$WEB_DIR" ]; then
        log_warn "Diretório $WEB_DIR já existe. Criando backup em $BACKUP_DIR"
        mkdir -p "$BACKUP_DIR"
        [ -d "$WEB_DIR/storage" ] && mv "$WEB_DIR/storage" "$BACKUP_DIR/storage"
        [ -d "$WEB_DIR/config" ] && mv "$WEB_DIR/config" "$BACKUP_DIR/config"
        rm -rf "$WEB_DIR"
        mkdir -p "$WEB_DIR"
        [ -d "$BACKUP_DIR/storage" ] && mv "$BACKUP_DIR/storage" "$WEB_DIR/storage"
        [ -d "$BACKUP_DIR/config" ] && mv "$BACKUP_DIR/config" "$WEB_DIR/config"
    else
        mkdir -p "$WEB_DIR"
    fi

    rsync -a --exclude='storage' --exclude='config' "$TEMP_DIR/" "$WEB_DIR/" >/dev/null 2>&1

    mkdir -p "$WEB_DIR/storage"/{cache,uploads,logs,bundles}
    chmod -R 775 "$WEB_DIR/storage"

    mkdir -p "$WEB_DIR/config"
    cat > "$WEB_DIR/config/database.php" <<PHPEOF
<?php
return [
    'host' => 'localhost',
    'port' => 5432,
    'dbname' => '$DB_NAME',
    'user' => '$DB_USER',
    'password' => '$DB_PASS',
];
PHPEOF

    cat > "$WEB_DIR/lib/db.php" <<PHPEOF
<?php
\$config = require __DIR__ . '/../config/database.php';

try {
    \$pdo = new PDO(
        "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
        \$config['user'],
        \$config['password']
    );
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Falha na conexão com o banco: ' . \$e->getMessage()]);
    exit;
}

function jsonResponse(\$data, \$code = 200) {
    header('Content-Type: application/json', true, \$code);
    echo json_encode(\$data);
    exit;
}
PHPEOF

    chown -R www-data:www-data "$WEB_DIR"
    chmod -R 755 "$WEB_DIR"
    log_ok "Arquivos instalados"
}

# -----------------------------------------------------------------------------
# 8. Configurar Apache com HTTPS
# -----------------------------------------------------------------------------
configure_apache() {
    log_info "Configurando Apache com HTTPS..."
    mkdir -p "$CERT_DIR"
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$CERT_DIR/seederlinux.key" \
        -out "$CERT_DIR/seederlinux.crt" \
        -subj "/C=BR/ST=PA/L=Belem/O=SeederLinux/OU=TI/CN=$DOMAIN" 2>/dev/null

    cat > /etc/apache2/sites-available/seederlinux.conf <<APACHEEOF
<VirtualHost *:80>
    ServerName $DOMAIN
    Redirect permanent / https://$DOMAIN/
</VirtualHost>

<VirtualHost *:443>
    ServerName $DOMAIN
    DocumentRoot $WEB_DIR

    SSLEngine on
    SSLCertificateFile $CERT_DIR/seederlinux.crt
    SSLCertificateKeyFile $CERT_DIR/seederlinux.key

    <Directory $WEB_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    Alias /api $WEB_DIR/api
    <Directory $WEB_DIR/api>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/seeder_error.log
    CustomLog \${APACHE_LOG_DIR}/seeder_access.log combined
</VirtualHost>
APACHEEOF

    a2dissite 000-default.conf >/dev/null 2>&1 || true
    a2ensite seederlinux.conf >/dev/null 2>&1
    systemctl restart apache2 || service apache2 restart
    log_ok "Apache configurado com HTTPS (certificado autoassinado)"
}

# -----------------------------------------------------------------------------
# 9. Verificação final
# -----------------------------------------------------------------------------
final_verification() {
    log_info "Verificando instalação..."
    sleep 2
    local http_code=$(curl -s -k -o /dev/null -w "%{http_code}" https://localhost/api/organizations 2>/dev/null || echo "000")
    if [ "$http_code" = "200" ] || [ "$http_code" = "401" ] || [ "$http_code" = "405" ]; then
        log_ok "API respondeu (HTTPS) - HTTP $http_code"
    else
        log_warn "API não respondeu como esperado. Código: $http_code"
    fi
}

# -----------------------------------------------------------------------------
# 10. Rollback
# -----------------------------------------------------------------------------
rollback() {
    log_error "Falha na instalação. Executando rollback..."
    if [ -d "$BACKUP_DIR" ]; then
        rm -rf "$WEB_DIR"
        mv "$BACKUP_DIR" "$WEB_DIR"
        log_ok "Rollback concluído."
    else
        log_warn "Nenhum backup encontrado."
    fi
    exit 1
}

# -----------------------------------------------------------------------------
# Execução principal
# -----------------------------------------------------------------------------
trap rollback ERR

organize_project
validate_required_files
report_organization

install_dependencies
configure_postgresql
setup_database
install_web_files
configure_apache
final_verification

# Resumo
IP=$(hostname -I | awk '{print $1}')
echo ""
echo -e "${VERDE}====================================================${SEM_COR}"
echo -e "${VERDE}       Instalação Concluída com Sucesso!            ${SEM_COR}"
echo -e "${VERDE}====================================================${SEM_COR}"
echo ""
echo -e "🌐 URL:           ${AZUL}https://$DOMAIN/${SEM_COR}  (ou https://$IP/)"
echo -e "🔐 Painel:        ${AZUL}https://$DOMAIN/painel/${SEM_COR}"
echo -e "🔌 API:           ${AZUL}https://$DOMAIN/api/${SEM_COR}"
echo -e "🗄️  Banco:        ${AZUL}$DB_NAME ($DB_USER / $DB_PASS)${SEM_COR}"
echo -e "👨‍💼 Admin:        ${AZUL}$ADMIN_EMAIL / $ADMIN_PASS${SEM_COR}"
echo ""
echo -e "${AMARELO}⚠️  Certificado autoassinado - aceite o aviso no navegador.${SEM_COR}"
echo -e "${AMARELO}💡 Adicione ao /etc/hosts: echo '$IP $DOMAIN' | sudo tee -a /etc/hosts${SEM_COR}"
echo -e "${AMARELO}🔑 Altere as senhas padrão em produção!${SEM_COR}"
echo -e "📁 Arquivos: $WEB_DIR"
echo -e "📝 Log: $LOG_FILE"
echo ""

rm -rf "$TEMP_DIR"
exit 0

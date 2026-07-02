#!/bin/bash
# =============================================================================
# install.sh - Instalador Oficial do SeederLinux Lite
# =============================================================================
# Uso: sudo ./install.sh 02/07/2026 11:44
# =============================================================================

set -e

# Cores
VERDE='\033[0;32m'
AMARELO='\033[1;33m'
AZUL='\033[0;34m'#!/bin/bash
# =============================================================================
# install.sh - Instalador Oficial do SeederLinux Lite
# =============================================================================
# Uso: sudo ./install.sh
# =============================================================================

set -e

# Cores
VERDE='\033[0;32m'
AMARELO='\033[1;33m'
AZUL='\033[0;34m'
VERMELHO='\033[0;31m'
SEM_COR='\033[0m'

# -----------------------------------------------------------------------------
# Funções de logging e utilitários
# -----------------------------------------------------------------------------
log_info() { echo -e "${AZUL}➜${SEM_COR} $1"; }
log_ok()   { echo -e "${VERDE}✓${SEM_COR} $1"; }
log_warn() { echo -e "${AMARELO}⚠${SEM_COR} $1"; }
log_error(){ echo -e "${VERMELHO}✗${SEM_COR} $1"; exit 1; }

LOG_FILE="/var/log/seederlinux-install.log"
exec > >(tee -a "$LOG_FILE") 2>&1

# -----------------------------------------------------------------------------
# Verificação inicial
# -----------------------------------------------------------------------------
if [ "$EUID" -ne 0 ]; then
    log_error "Execute como root: sudo ./install.sh"
fi

echo -e "${AZUL}====================================================${SEM_COR}"
echo -e "${AZUL}     SeederLinux Lite - Instalação Oficial (v2)     ${SEM_COR}"
echo -e "${AZUL}====================================================${SEM_COR}"
echo ""

# -----------------------------------------------------------------------------
# Configurações
# -----------------------------------------------------------------------------
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
# 1. Função para organizar arquivos no diretório temporário
# -----------------------------------------------------------------------------
organize_project() {
    log_info "Organizando arquivos do projeto (cópia em $TEMP_DIR)..."

    # Copia todo o conteúdo do projeto para o temporário
    rsync -a --exclude='install.sh' "$PROJECT_DIR/" "$TEMP_DIR/" >/dev/null 2>&1

    # Remove arquivos indesejados
    find "$TEMP_DIR" -type f \( -name "*.zip" -o -name "*.txt" -o -name "*.orig" \
        -o -name ".gitattributes" -o -name "install_orig.sh" \
        -o -name "instalar_seederlinux.sh" \) -delete

    # Cria diretórios necessários
    mkdir -p "$TEMP_DIR"/{api,painel,lib,scripts,database,assets/{img,css,js,fonts},storage/{cache,uploads,logs,bundles},config,tmp,docs}

    # Move arquivos por tipo
    move_files() {
        local src_pattern="$1"
        local dest_dir="$2"
        find "$TEMP_DIR" -maxdepth 1 -type f -name "$src_pattern" -exec mv -t "$dest_dir" {} + 2>/dev/null || true
    }

    # PHP da API
    for file in bundle.php generate-bundle.php login.php organizations.php variables.php; do
        [ -f "$TEMP_DIR/$file" ] && mv "$TEMP_DIR/$file" "$TEMP_DIR/api/"
    done

    # Painel (HTML)
    [ -f "$TEMP_DIR/index.html" ] && mv "$TEMP_DIR/index.html" "$TEMP_DIR/painel/"
    [ -f "$TEMP_DIR/login.html" ] && mv "$TEMP_DIR/login.html" "$TEMP_DIR/painel/"

    # Lib
    [ -f "$TEMP_DIR/db.php" ] && mv "$TEMP_DIR/db.php" "$TEMP_DIR/lib/"

    # Scripts core
    mkdir -p "$TEMP_DIR/scripts"
    find "$TEMP_DIR" -maxdepth 1 -type f -name "core_*.sh" -exec mv -t "$TEMP_DIR/scripts" {} + 2>/dev/null || true

    # Banco de dados
    [ -f "$TEMP_DIR/schema.sql" ] && mv "$TEMP_DIR/schema.sql" "$TEMP_DIR/database/"

    # Imagens (favicon, logos)
    move_files "*.ico" "$TEMP_DIR/assets/img/"
    move_files "*.png" "$TEMP_DIR/assets/img/"
    move_files "*.jpg" "$TEMP_DIR/assets/img/"
    move_files "*.svg" "$TEMP_DIR/assets/img/"

    # CSS, JS, Fontes
    move_files "*.css" "$TEMP_DIR/assets/css/"
    move_files "*.js"  "$TEMP_DIR/assets/js/"
    move_files "*.woff*" "$TEMP_DIR/assets/fonts/"
    move_files "*.ttf" "$TEMP_DIR/assets/fonts/"

    # Documentação
    find "$TEMP_DIR" -maxdepth 1 -type f \( -name "*.md" -o -name "*.pdf" -o -name "*.docx" \) -exec mv -t "$TEMP_DIR/docs" {} + 2>/dev/null || true

    # Remove diretórios vazios (opcional)
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
# 4. Instalar dependências (modular)
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
                php_pkgs="php libapache2-mod-php"
                for ext in pgsql curl mbstring xml json; do
                    if apt-cache show "php-${ext}" &>/dev/null; then
                        php_pkgs="$php_pkgs php-${ext}"
                    fi
                done
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
# 5. Configurar PostgreSQL (com rollback)
# -----------------------------------------------------------------------------
configure_postgresql() {
    log_info "Configurando PostgreSQL..."
    systemctl start postgresql || service postgresql start
    systemctl enable postgresql >/dev/null 2>&1 || update-rc.d postgresql enable

    # Cria usuário e banco se não existirem
    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE ROLE $DB_USER WITH LOGIN PASSWORD '$DB_PASS';\""
    fi

    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database WHERE datname='$DB_NAME'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE DATABASE $DB_NAME OWNER $DB_USER;\""
    fi

    su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"GRANT ALL ON SCHEMA public TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $DB_USER;\""

    # Ajustar autenticação para md5
    local pg_hba=$(su - postgres -c "psql -tAc 'SHOW hba_file;'" 2>/dev/null | tr -d ' ')
    if [ -f "$pg_hba" ]; then
        cp "$pg_hba" "${pg_hba}.bak"
        sed -i 's/peer$/md5/' "$pg_hba"
        sed -i 's/scram-sha-256$/md5/' "$pg_hba"
        systemctl restart postgresql || service postgresql restart
        sleep 2
    fi

    # Testar conexão
    if ! PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1" >/dev/null 2>&1; then
        log_error "Falha na conexão com o banco."
    fi
    log_ok "PostgreSQL configurado"
}

# -----------------------------------------------------------------------------
# 6. Aplicar schema e importar scripts
# -----------------------------------------------------------------------------
setup_database() {
    log_info "Configurando banco de dados..."
    # Schema
    if [ -f "$TEMP_DIR/database/schema.sql" ]; then
        PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -f "$TEMP_DIR/database/schema.sql" >/dev/null 2>&1
        log_ok "Schema aplicado"
    fi

    # Tabela users
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

    # Importar scripts core
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

    # Criar admin
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
# 7. Instalar arquivos no diretório web (preservando storage e config)
# -----------------------------------------------------------------------------
install_web_files() {
    log_info "Instalando arquivos do sistema em $WEB_DIR..."

    # Backup do diretório existente
    if [ -d "$WEB_DIR" ]; then
        log_warn "Diretório $WEB_DIR já existe. Criando backup em $BACKUP_DIR"
        mkdir -p "$BACKUP_DIR"
        # Preserva storage e config se existirem
        if [ -d "$WEB_DIR/storage" ]; then
            mv "$WEB_DIR/storage" "$BACKUP_DIR/storage"
        fi
        if [ -d "$WEB_DIR/config" ]; then
            mv "$WEB_DIR/config" "$BACKUP_DIR/config"
        fi
        rm -rf "$WEB_DIR"
        mkdir -p "$WEB_DIR"
        # Restaura storage e config do backup
        [ -d "$BACKUP_DIR/storage" ] && mv "$BACKUP_DIR/storage" "$WEB_DIR/storage"
        [ -d "$BACKUP_DIR/config" ] && mv "$BACKUP_DIR/config" "$WEB_DIR/config"
    else
        mkdir -p "$WEB_DIR"
    fi

    # Copia os arquivos organizados (exceto storage e config que já foram preservados)
    rsync -a --exclude='storage' --exclude='config' "$TEMP_DIR/" "$WEB_DIR/" >/dev/null 2>&1

    # Cria diretórios storage se não existirem
    mkdir -p "$WEB_DIR/storage"/{cache,uploads,logs,bundles}
    chmod -R 775 "$WEB_DIR/storage"

    # Gera config/database.php a partir das credenciais atuais
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

    # Cria lib/db.php apontando para o config
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
# 10. Rollback (em caso de erro)
# -----------------------------------------------------------------------------
rollback() {
    log_error "Falha na instalação. Executando rollback..."
    if [ -d "$BACKUP_DIR" ]; then
        rm -rf "$WEB_DIR"
        mv "$BACKUP_DIR" "$WEB_DIR"
        log_ok "Rollback concluído: estado anterior restaurado."
    else
        log_warn "Nenhum backup encontrado para rollback."
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

# -----------------------------------------------------------------------------
# Resumo final
# -----------------------------------------------------------------------------
IP=$(hostname -I | awk '{print $1}')
echo ""
echo -e "${VERDE}====================================================${SEM_COR}"
echo -e "${VERDE}       Instalação Concluída com Sucesso!            ${SEM_COR}"
echo -e "${VERDE}====================================================${SEM_COR}"
echo ""
echo -e "🌐 URL do sistema:  ${AZUL}https://$DOMAIN/${SEM_COR}  (ou https://$IP/)"
echo -e "🔐 Painel admin:    ${AZUL}https://$DOMAIN/painel/${SEM_COR}"
echo -e "🔌 API:             ${AZUL}https://$DOMAIN/api/${SEM_COR}"
echo -e "🗄️  Banco:          ${AZUL}$DB_NAME (usuário: $DB_USER / senha: $DB_PASS)${SEM_COR}"
echo -e "👨‍💼 Administrador:   ${AZUL}$ADMIN_EMAIL / $ADMIN_PASS${SEM_COR}"
echo ""
echo -e "${AMARELO}⚠️  ATENÇÃO:${SEM_COR} O certificado SSL é autoassinado."
echo "   Seu navegador mostrará um aviso de segurança."
echo "   Clique em 'Avançado' e 'Prosseguir' para acessar."
echo ""
echo -e "${AMARELO}💡 Dica:${SEM_COR} Para usar o domínio $DOMAIN, adicione ao /etc/hosts:"
echo "   echo '$IP $DOMAIN' | sudo tee -a /etc/hosts"
echo ""
echo -e "${AMARELO}🔑 Importante:${SEM_COR} Altere as senhas padrão em produção!"
echo -e "📁 Arquivos em:     $WEB_DIR"
echo -e "📝 Log de instalação: $LOG_FILE"
echo ""

# Limpeza do diretório temporário
rm -rf "$TEMP_DIR"

exit 0
VERMELHO='\033[0;31m'
SEM_COR='\033[0m'

# -----------------------------------------------------------------------------
# Funções de logging e utilitários
# -----------------------------------------------------------------------------
log_info() { echo -e "${AZUL}➜${SEM_COR} $1"; }
log_ok()   { echo -e "${VERDE}✓${SEM_COR} $1"; }
log_warn() { echo -e "${AMARELO}⚠${SEM_COR} $1"; }
log_error(){ echo -e "${VERMELHO}✗${SEM_COR} $1"; exit 1; }

LOG_FILE="/var/log/seederlinux-install.log"
exec > >(tee -a "$LOG_FILE") 2>&1

# -----------------------------------------------------------------------------
# Verificação inicial
# -----------------------------------------------------------------------------
if [ "$EUID" -ne 0 ]; then
    log_error "Execute como root: sudo ./install.sh"
fi

echo -e "${AZUL}====================================================${SEM_COR}"
echo -e "${AZUL}     SeederLinux Lite - Instalação Oficial (v2)     ${SEM_COR}"
echo -e "${AZUL}====================================================${SEM_COR}"
echo ""

# -----------------------------------------------------------------------------
# Configurações
# -----------------------------------------------------------------------------
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
# 1. Função para organizar arquivos no diretório temporário
# -----------------------------------------------------------------------------
organize_project() {
    log_info "Organizando arquivos do projeto (cópia em $TEMP_DIR)..."

    # Copia todo o conteúdo do projeto para o temporário
    rsync -a --exclude='install.sh' "$PROJECT_DIR/" "$TEMP_DIR/" >/dev/null 2>&1

    # Remove arquivos indesejados
    find "$TEMP_DIR" -type f \( -name "*.zip" -o -name "*.txt" -o -name "*.orig" \
        -o -name ".gitattributes" -o -name "install_orig.sh" \
        -o -name "instalar_seederlinux.sh" \) -delete

    # Cria diretórios necessários
    mkdir -p "$TEMP_DIR"/{api,painel,lib,scripts,database,assets/{img,css,js,fonts},storage/{cache,uploads,logs,bundles},config,tmp,docs}

    # Move arquivos por tipo
    move_files() {
        local src_pattern="$1"
        local dest_dir="$2"
        find "$TEMP_DIR" -maxdepth 1 -type f -name "$src_pattern" -exec mv -t "$dest_dir" {} + 2>/dev/null || true
    }

    # PHP da API
    for file in bundle.php generate-bundle.php login.php organizations.php variables.php; do
        [ -f "$TEMP_DIR/$file" ] && mv "$TEMP_DIR/$file" "$TEMP_DIR/api/"
    done

    # Painel (HTML)
    [ -f "$TEMP_DIR/index.html" ] && mv "$TEMP_DIR/index.html" "$TEMP_DIR/painel/"
    [ -f "$TEMP_DIR/login.html" ] && mv "$TEMP_DIR/login.html" "$TEMP_DIR/painel/"

    # Lib
    [ -f "$TEMP_DIR/db.php" ] && mv "$TEMP_DIR/db.php" "$TEMP_DIR/lib/"

    # Scripts core
    mkdir -p "$TEMP_DIR/scripts"
    find "$TEMP_DIR" -maxdepth 1 -type f -name "core_*.sh" -exec mv -t "$TEMP_DIR/scripts" {} + 2>/dev/null || true

    # Banco de dados
    [ -f "$TEMP_DIR/schema.sql" ] && mv "$TEMP_DIR/schema.sql" "$TEMP_DIR/database/"

    # Imagens (favicon, logos)
    move_files "*.ico" "$TEMP_DIR/assets/img/"
    move_files "*.png" "$TEMP_DIR/assets/img/"
    move_files "*.jpg" "$TEMP_DIR/assets/img/"
    move_files "*.svg" "$TEMP_DIR/assets/img/"

    # CSS, JS, Fontes
    move_files "*.css" "$TEMP_DIR/assets/css/"
    move_files "*.js"  "$TEMP_DIR/assets/js/"
    move_files "*.woff*" "$TEMP_DIR/assets/fonts/"
    move_files "*.ttf" "$TEMP_DIR/assets/fonts/"

    # Documentação
    find "$TEMP_DIR" -maxdepth 1 -type f \( -name "*.md" -o -name "*.pdf" -o -name "*.docx" \) -exec mv -t "$TEMP_DIR/docs" {} + 2>/dev/null || true

    # Remove diretórios vazios (opcional)
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
# 4. Instalar dependências (modular)
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
                php_pkgs="php libapache2-mod-php"
                for ext in pgsql curl mbstring xml json; do
                    if apt-cache show "php-${ext}" &>/dev/null; then
                        php_pkgs="$php_pkgs php-${ext}"
                    fi
                done
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
# 5. Configurar PostgreSQL (com rollback)
# -----------------------------------------------------------------------------
configure_postgresql() {
    log_info "Configurando PostgreSQL..."
    systemctl start postgresql || service postgresql start
    systemctl enable postgresql >/dev/null 2>&1 || update-rc.d postgresql enable

    # Cria usuário e banco se não existirem
    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE ROLE $DB_USER WITH LOGIN PASSWORD '$DB_PASS';\""
    fi

    if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database WHERE datname='$DB_NAME'\"" 2>/dev/null | grep -q 1; then
        su - postgres -c "psql -c \"CREATE DATABASE $DB_NAME OWNER $DB_USER;\""
    fi

    su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"GRANT ALL ON SCHEMA public TO $DB_USER;\""
    su - postgres -c "psql -d $DB_NAME -c \"ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $DB_USER;\""

    # Ajustar autenticação para md5
    local pg_hba=$(su - postgres -c "psql -tAc 'SHOW hba_file;'" 2>/dev/null | tr -d ' ')
    if [ -f "$pg_hba" ]; then
        cp "$pg_hba" "${pg_hba}.bak"
        sed -i 's/peer$/md5/' "$pg_hba"
        sed -i 's/scram-sha-256$/md5/' "$pg_hba"
        systemctl restart postgresql || service postgresql restart
        sleep 2
    fi

    # Testar conexão
    if ! PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1" >/dev/null 2>&1; then
        log_error "Falha na conexão com o banco."
    fi
    log_ok "PostgreSQL configurado"
}

# -----------------------------------------------------------------------------
# 6. Aplicar schema e importar scripts
# -----------------------------------------------------------------------------
setup_database() {
    log_info "Configurando banco de dados..."
    # Schema
    if [ -f "$TEMP_DIR/database/schema.sql" ]; then
        PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -f "$TEMP_DIR/database/schema.sql" >/dev/null 2>&1
        log_ok "Schema aplicado"
    fi

    # Tabela users
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

    # Importar scripts core
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

    # Criar admin
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
# 7. Instalar arquivos no diretório web (preservando storage e config)
# -----------------------------------------------------------------------------
install_web_files() {
    log_info "Instalando arquivos do sistema em $WEB_DIR..."

    # Backup do diretório existente
    if [ -d "$WEB_DIR" ]; then
        log_warn "Diretório $WEB_DIR já existe. Criando backup em $BACKUP_DIR"
        mkdir -p "$BACKUP_DIR"
        # Preserva storage e config se existirem
        if [ -d "$WEB_DIR/storage" ]; then
            mv "$WEB_DIR/storage" "$BACKUP_DIR/storage"
        fi
        if [ -d "$WEB_DIR/config" ]; then
            mv "$WEB_DIR/config" "$BACKUP_DIR/config"
        fi
        rm -rf "$WEB_DIR"
        mkdir -p "$WEB_DIR"
        # Restaura storage e config do backup
        [ -d "$BACKUP_DIR/storage" ] && mv "$BACKUP_DIR/storage" "$WEB_DIR/storage"
        [ -d "$BACKUP_DIR/config" ] && mv "$BACKUP_DIR/config" "$WEB_DIR/config"
    else
        mkdir -p "$WEB_DIR"
    fi

    # Copia os arquivos organizados (exceto storage e config que já foram preservados)
    rsync -a --exclude='storage' --exclude='config' "$TEMP_DIR/" "$WEB_DIR/" >/dev/null 2>&1

    # Cria diretórios storage se não existirem
    mkdir -p "$WEB_DIR/storage"/{cache,uploads,logs,bundles}
    chmod -R 775 "$WEB_DIR/storage"

    # Gera config/database.php a partir das credenciais atuais
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

    # Cria lib/db.php apontando para o config
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
# 10. Rollback (em caso de erro)
# -----------------------------------------------------------------------------
rollback() {
    log_error "Falha na instalação. Executando rollback..."
    if [ -d "$BACKUP_DIR" ]; then
        rm -rf "$WEB_DIR"
        mv "$BACKUP_DIR" "$WEB_DIR"
        log_ok "Rollback concluído: estado anterior restaurado."
    else
        log_warn "Nenhum backup encontrado para rollback."
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

# -----------------------------------------------------------------------------
# Resumo final
# -----------------------------------------------------------------------------
IP=$(hostname -I | awk '{print $1}')
echo ""
echo -e "${VERDE}====================================================${SEM_COR}"
echo -e "${VERDE}       Instalação Concluída com Sucesso!            ${SEM_COR}"
echo -e "${VERDE}====================================================${SEM_COR}"
echo ""
echo -e "🌐 URL do sistema:  ${AZUL}https://$DOMAIN/${SEM_COR}  (ou https://$IP/)"
echo -e "🔐 Painel admin:    ${AZUL}https://$DOMAIN/painel/${SEM_COR}"
echo -e "🔌 API:             ${AZUL}https://$DOMAIN/api/${SEM_COR}"
echo -e "🗄️  Banco:          ${AZUL}$DB_NAME (usuário: $DB_USER / senha: $DB_PASS)${SEM_COR}"
echo -e "👨‍💼 Administrador:   ${AZUL}$ADMIN_EMAIL / $ADMIN_PASS${SEM_COR}"
echo ""
echo -e "${AMARELO}⚠️  ATENÇÃO:${SEM_COR} O certificado SSL é autoassinado."
echo "   Seu navegador mostrará um aviso de segurança."
echo "   Clique em 'Avançado' e 'Prosseguir' para acessar."
echo ""
echo -e "${AMARELO}💡 Dica:${SEM_COR} Para usar o domínio $DOMAIN, adicione ao /etc/hosts:"
echo "   echo '$IP $DOMAIN' | sudo tee -a /etc/hosts"
echo ""
echo -e "${AMARELO}🔑 Importante:${SEM_COR} Altere as senhas padrão em produção!"
echo -e "📁 Arquivos em:     $WEB_DIR"
echo -e "📝 Log de instalação: $LOG_FILE"
echo ""

# Limpeza do diretório temporário
rm -rf "$TEMP_DIR"

exit 0

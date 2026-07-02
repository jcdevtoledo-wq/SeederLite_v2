#!/bin/bash
# =============================================================================
# install.sh - Instalação Completa do SeederLinux Lite (com organização automática)
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

log_info() { echo -e "${AZUL}➜${SEM_COR} $1"; }
log_ok()   { echo -e "${VERDE}✓${SEM_COR} $1"; }
log_warn() { echo -e "${AMARELO}⚠${SEM_COR} $1"; }
log_error(){ echo -e "${VERMELHO}✗${SEM_COR} $1"; exit 1; }

if [ "$EUID" -ne 0 ]; then
    log_error "Execute como root: sudo ./install.sh"
fi

echo -e "${AZUL}====================================================${SEM_COR}"
echo -e "${AZUL}     SeederLinux Lite - Instalação Completa (SSL)   ${SEM_COR}"
echo -e "${AZUL}====================================================${SEM_COR}"
echo ""

# -----------------------------------------------------------------------------
# Configurações
# -----------------------------------------------------------------------------
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WEB_DIR="/var/www/html/seederlinux"

DB_NAME="seederlinux"
DB_USER="seeder"
DB_PASS="seeder123"

ADMIN_USER="admin"
ADMIN_PASS="admin123"
ADMIN_EMAIL="admin@seeder.local"

DOMAIN="seederlinux.local"
CERT_DIR="/etc/apache2/ssl"

# -----------------------------------------------------------------------------
# [0/9] ORGANIZAR ARQUIVOS NA ESTRUTURA CORRETA (antes de copiar)
# -----------------------------------------------------------------------------
log_info "[0/9] Organizando arquivos do projeto..."

# Função para mover arquivo para destino, criando diretório se necessário
move_to() {
    local src="$1"
    local dest_dir="$2"
    local dest_file="$dest_dir/$(basename "$src")"
    mkdir -p "$dest_dir"
    if [ -f "$src" ]; then
        mv "$src" "$dest_file"
        log_ok "   $src -> $dest_file"
    fi
}

# 1. Arquivos da raiz que devem permanecer na raiz
ROOT_FILES=("index.php" "public.html" "agent.py" "DOCUMENTACAO.md" ".htaccess")
for file in "${ROOT_FILES[@]}"; do
    if [ -f "$PROJECT_DIR/$file" ]; then
        # já está na raiz, não move
        log_ok "   $file (raiz)"
    fi
done

# 2. Painel (frontend)
move_to "$PROJECT_DIR/index.html" "$PROJECT_DIR/painel"      # painel admin
move_to "$PROJECT_DIR/login.html" "$PROJECT_DIR/painel"      # login

# 3. API (endpoints)
API_FILES=("bundle.php" "generate-bundle.php" "login.php" "organizations.php" "variables.php")
for file in "${API_FILES[@]}"; do
    move_to "$PROJECT_DIR/$file" "$PROJECT_DIR/api"
done

# 4. Lib (bibliotecas)
move_to "$PROJECT_DIR/db.php" "$PROJECT_DIR/lib"

# 5. Scripts core
mkdir -p "$PROJECT_DIR/scripts"
for script in core_*.sh; do
    if [ -f "$PROJECT_DIR/$script" ]; then
        move_to "$PROJECT_DIR/$script" "$PROJECT_DIR/scripts"
    fi
done

# 6. Database (schema)
move_to "$PROJECT_DIR/schema.sql" "$PROJECT_DIR/database"

# 7. Remover arquivos desnecessários que não devem ser copiados
rm -f "$PROJECT_DIR/install.sh"  # o próprio script não vai para o webdir
rm -f "$PROJECT_DIR/instalar_seederlinux.sh"  # possível outro instalador

log_ok "Arquivos organizados na estrutura correta"

# -----------------------------------------------------------------------------
# [1/9] Detectar sistema
# -----------------------------------------------------------------------------
log_info "[1/9] Detectando sistema operacional..."
if [ -f /etc/os-release ]; then
    . /etc/os-release
    DISTRO=$ID
    echo "   Distribuição: $NAME $VERSION"
else
    log_error "Sistema não suportado."
fi

# -----------------------------------------------------------------------------
# [2/9] Instalar dependências
# -----------------------------------------------------------------------------
log_info "[2/9] Instalando dependências..."
apt-get update -y

BASE_PKGS="apache2 postgresql postgresql-contrib curl git unzip openssl jq rsync"

case $DISTRO in
    ubuntu|linuxmint|zorin|pop)
        if ! grep -q "ondrej/php" /etc/apt/sources.list /etc/apt/sources.list.d/* 2>/dev/null; then
            apt-get install -y software-properties-common
            add-apt-repository -y ppa:ondrej/php
            apt-get update -y
        fi
        apt-get install -y $BASE_PKGS \
            libapache2-mod-php8.1 php8.1 php8.1-cli php8.1-common \
            php8.1-pgsql php8.1-curl php8.1-mbstring php8.1-xml php8.1-json
        ;;
    debian)
        PHP_PACKAGES="php libapache2-mod-php"
        for ext in pgsql curl mbstring xml json; do
            if apt-cache show "php-${ext}" &>/dev/null; then
                PHP_PACKAGES="$PHP_PACKAGES php-${ext}"
            fi
        done
        apt-get install -y $BASE_PKGS $PHP_PACKAGES
        ;;
    *)
        log_error "Distribuição não suportada: $DISTRO"
        ;;
esac

a2enmod rewrite ssl headers >/dev/null 2>&1 || true
systemctl restart apache2 || service apache2 restart
log_ok "Dependências instaladas"

# -----------------------------------------------------------------------------
# [3/9] Configurar PostgreSQL
# -----------------------------------------------------------------------------
log_info "[3/9] Configurando PostgreSQL..."
systemctl start postgresql || service postgresql start
systemctl enable postgresql >/dev/null 2>&1 || update-rc.d postgresql enable

if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'\"" 2>/dev/null | grep -q 1; then
    su - postgres -c "psql -c \"CREATE ROLE $DB_USER WITH LOGIN PASSWORD '$DB_PASS';\""
fi

if ! su - postgres -c "psql -tAc \"SELECT 1 FROM pg_database WHERE datname='$DB_NAME'\"" 2>/dev/null | grep -q 1; then
    su - postgres -c "psql -c \"CREATE DATABASE $DB_NAME OWNER $DB_USER;\""
fi

su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;\""
su - postgres -c "psql -d $DB_NAME -c \"GRANT ALL ON SCHEMA public TO $DB_USER;\""
su - postgres -c "psql -d $DB_NAME -c \"ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $DB_USER;\""

PG_HBA=$(su - postgres -c "psql -tAc 'SHOW hba_file;'" 2>/dev/null | tr -d ' ')
if [ -f "$PG_HBA" ]; then
    cp "$PG_HBA" "${PG_HBA}.bak"
    sed -i 's/peer$/md5/' "$PG_HBA"
    sed -i 's/scram-sha-256$/md5/' "$PG_HBA"
    systemctl restart postgresql || service postgresql restart
    sleep 2
fi

if ! PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1" >/dev/null 2>&1; then
    log_error "Falha na conexão com o banco."
fi
log_ok "PostgreSQL configurado"

# -----------------------------------------------------------------------------
# [4/9] Aplicar schema e criar tabela users
# -----------------------------------------------------------------------------
log_info "[4/9] Aplicando estrutura do banco..."
if [ -f "$PROJECT_DIR/database/schema.sql" ]; then
    PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" -f "$PROJECT_DIR/database/schema.sql" >/dev/null 2>&1
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

# -----------------------------------------------------------------------------
# [5/9] Importar scripts core para o banco
# -----------------------------------------------------------------------------
log_info "[5/9] Importando scripts core..."
if [ -d "$PROJECT_DIR/scripts" ]; then
    for script_file in "$PROJECT_DIR/scripts"/*.sh; do
        if [ -f "$script_file" ]; then
            script_name=$(basename "$script_file" .sh)
            script_content=$(cat "$script_file" | sed "s/'/''/g")
            PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<SQLEOF 2>/dev/null
INSERT INTO scripts (name, content, is_core, version)
VALUES ('$script_name', '$script_content', TRUE, 1)
ON CONFLICT (name) DO UPDATE 
SET content = '$script_content', version = version + 1;
SQLEOF
            log_ok "   Script $script_name importado"
        fi
    done
fi

# -----------------------------------------------------------------------------
# [6/9] Copiar arquivos para o diretório web e criar lib/db.php
# -----------------------------------------------------------------------------
log_info "[6/9] Instalando arquivos do sistema..."
if [ -d "$WEB_DIR" ]; then
    log_warn "Diretório $WEB_DIR já existe. Será sobrescrito (exceto storage)."
    [ -d "$WEB_DIR/storage" ] && mv "$WEB_DIR/storage" /tmp/seeder_storage_backup
    rm -rf "$WEB_DIR"
fi

mkdir -p "$WEB_DIR"
rsync -av --exclude='install.sh' --exclude='organize.sh' "$PROJECT_DIR/" "$WEB_DIR/" >/dev/null 2>&1
[ -d "/tmp/seeder_storage_backup" ] && mv /tmp/seeder_storage_backup "$WEB_DIR/storage"
mkdir -p "$WEB_DIR/storage"

# Gerar lib/db.php com as credenciais
mkdir -p "$WEB_DIR/lib"
cat > "$WEB_DIR/lib/db.php" <<PHPEOF
<?php
// Database configuration
\$host = 'localhost';
\$db   = '$DB_NAME';
\$user = '$DB_USER';
\$pass = '$DB_PASS';

try {
    \$pdo = new PDO("pgsql:host=\$host;dbname=\$db", \$user, \$pass);
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
?>
PHPEOF

# Criar administrador
HASH=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_BCRYPT);")
PGPASSWORD="$DB_PASS" psql -h localhost -U "$DB_USER" -d "$DB_NAME" <<SQLEOF 2>/dev/null
INSERT INTO users (name, email, password_hash, role, active, created_at)
VALUES ('$ADMIN_USER', '$ADMIN_EMAIL', '$HASH', 'admin_gap', TRUE, NOW())
ON CONFLICT (email) 
DO UPDATE SET password_hash = '$HASH', role = 'admin_gap', active = TRUE;
SQLEOF

log_ok "Arquivos instalados e admin criado"

# -----------------------------------------------------------------------------
# [7/9] Gerar certificado SSL e configurar Apache com HTTPS
# -----------------------------------------------------------------------------
log_info "[7/9] Configurando Apache com HTTPS..."
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
chown -R www-data:www-data "$WEB_DIR"
chmod -R 755 "$WEB_DIR"
chmod -R 775 "$WEB_DIR/storage"

systemctl restart apache2 || service apache2 restart
log_ok "Apache configurado com HTTPS (certificado autoassinado)"

# -----------------------------------------------------------------------------
# [8/9] Verificação final
# -----------------------------------------------------------------------------
log_info "[8/9] Verificando instalação..."
sleep 2
API_RESPONSE=$(curl -s -k -o /dev/null -w "%{http_code}" https://localhost/api/organizations 2>/dev/null || echo "000")
if [ "$API_RESPONSE" = "200" ] || [ "$API_RESPONSE" = "401" ] || [ "$API_RESPONSE" = "405" ]; then
    log_ok "API respondeu (HTTPS) - HTTP $API_RESPONSE"
else
    log_warn "API não respondeu como esperado. Código: $API_RESPONSE"
fi

# -----------------------------------------------------------------------------
# [9/9] Resumo final
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
echo ""

exit 0

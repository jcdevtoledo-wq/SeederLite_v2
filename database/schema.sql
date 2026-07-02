-- SeederLinux Lite Database Schema

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    acronym VARCHAR(20) UNIQUE NOT NULL,
    domain VARCHAR(100),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS variables (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    value TEXT,
    type VARCHAR(20) DEFAULT 'string',
    description TEXT,
    UNIQUE(organization_id, name)
);

CREATE TABLE IF NOT EXISTS scripts (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    filename VARCHAR(255), -- Para scripts customizados
    content TEXT NOT NULL,
    version INT DEFAULT 1,
    is_core BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS deploy_bundles (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE CASCADE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS activity_log (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir usuário admin padrão (senha: admin123)
-- Hash gerado via password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (username, password_hash) 
VALUES ('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm')
ON CONFLICT (username) DO NOTHING;

-- Seed some core modules metadata
INSERT INTO scripts (name, content, is_core) VALUES 
('core_domain.sh', '#!/bin/bash\necho "Ingressando no domínio {{DOMINIO}}..."', TRUE),
('core_branding.sh', '#!/bin/bash\necho "Configurando branding para {{DOMINIO_NETBIOS}}..."', TRUE),
('core_inventory.sh', '#!/bin/bash\necho "Configurando inventário..."', TRUE),
('core_network.sh', '#!/bin/bash\necho "Configurando rede..."', TRUE);

-- Seed default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('agent_download_path', '/agent.py', 'Caminho para download do agente Python'),
('docs_download_path', '/DOCUMENTACAO.md', 'Caminho para download da documentação'),
('system_name', 'SeederLinux Lite', 'Nome do sistema'),
('system_version', '2.0', 'Versão do sistema')
ON CONFLICT (setting_key) DO NOTHING;

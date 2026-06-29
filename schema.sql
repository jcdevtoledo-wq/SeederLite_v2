-- SeederLinux Lite Database Schema

CREATE TABLE IF NOT EXISTS organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    acronym VARCHAR(20) UNIQUE NOT NULL,
    domain VARCHAR(100),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS variables (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    value TEXT,
    type VARCHAR(20) DEFAULT 'string', -- string, int, bool, json
    description TEXT
);

CREATE TABLE IF NOT EXISTS scripts (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE SET NULL,
    name VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    version INT DEFAULT 1,
    is_core BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS deploy_bundles (
    id SERIAL PRIMARY KEY,
    organization_id INT REFERENCES organizations(id) ON DELETE CASCADE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    content TEXT NOT NULL
);

-- Seed some core modules
INSERT INTO scripts (name, content, is_core) VALUES 
('core_domain.sh', '#!/bin/bash\necho "Ingressando no domínio {{DOMINIO}}..."', TRUE),
('core_branding.sh', '#!/bin/bash\necho "Configurando branding para {{DOMINIO_NETBIOS}}..."', TRUE);

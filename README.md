# SeederLinux Lite v2

Sistema de gerenciamento centralizado de scripts para provisionamento de estações Linux, com foco em Organizações Militares.

## Estrutura do Projeto

```
SeederLite_v2/
├── api/                  # Endpoints da API (PHP)
│   ├── login.php         # Autenticação
│   ├── organizations.php # CRUD de OMs
│   ├── variables.php     # CRUD de variáveis
│   ├── generate-bundle.php # Motor de bundle
│   └── bundle.php        # Download de bundle
├── database/
│   └── schema.sql        # Schema do PostgreSQL
├── lib/
│   ├── db.php            # Conexão PDO + funções utilitárias
│   └── db.example.php    # Exemplo de configuração
├── painel/               # Frontend (PHP + Tailwind CSS)
│   ├── public.php        # Página pública
│   ├── login.php         # Tela de login
│   └── index.php         # Dashboard administrativo
├── scripts/              # Scripts core de provisionamento
│   ├── core_domain.sh    # Ingresso no AD
│   ├── core_branding.sh  # Identidade visual
│   ├── core_inventory.sh # OCS Inventory
│   └── core_network.sh   # Proxy e rede
├── agent.py              # Agente Python para estações
├── install.sh            # Instalador automático
├── index.php             # Roteador principal
└── .htaccess             # Rewrite rules
```

## Instalação Rápida

```bash
git clone https://github.com/jcdevtoledo-wq/SeederLite_v2.git
cd SeederLite_v2
sudo chmod +x install.sh
sudo ./install.sh
```

## Acesso

- **Página Pública:** `http://SEU_IP/`
- **Painel Admin:** `http://SEU_IP/login`
- **Usuário:** `admin`
- **Senha:** `admin123` *(altere em produção!)*

## Uso do Agente

```bash
python3 agent.py --server http://SEU_SERVIDOR --org SIGLA_OM --bundle ID_BUNDLE
```

## Tecnologias

- **Backend:** PHP 8+ com PDO/PostgreSQL
- **Frontend:** Tailwind CSS + Vanilla JavaScript
- **Banco de Dados:** PostgreSQL 16+
- **Agente:** Python 3
- **Servidor:** Apache2 com mod_rewrite

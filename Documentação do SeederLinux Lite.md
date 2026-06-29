# Documentação do SeederLinux Lite

## 1. Visão Geral

O SeederLinux Lite é uma solução minimalista e eficiente para o gerenciamento centralizado de scripts de provisionamento para estações Linux, com foco especial no Linux Lite. Ele permite que administradores configurem e distribuam scripts de forma dinâmica para múltiplas Organizações Militares (OMs), garantindo personalização, funcionalidade offline-first e simplicidade operacional.

**Objetivos Principais:**

*   **Gerenciamento Centralizado:** Administrar scripts e variáveis de provisionamento a partir de um único painel web.
*   **Personalização por OM:** Cada organização pode ter seu próprio conjunto de variáveis, branding e scripts, adaptando-se às suas necessidades específicas.
*   **Substituição Dinâmica de Variáveis:** Utilização de placeholders nos scripts que são automaticamente preenchidos com valores reais definidos para cada OM.
*   **Provisionamento Offline:** Geração de bundles de scripts autônomos que podem ser executados nas estações mesmo sem conexão de rede ativa.
*   **Interface Pública e Administrativa:** Oferece uma página pública com informações sobre o sistema e downloads, além de um painel restrito para gerentes.
*   **Simplicidade e Eficiência:** Construído com PHP, PostgreSQL e Shell puro, evitando complexidades desnecessárias como Docker ou containers.

## 2. Arquitetura do Sistema

A arquitetura do SeederLinux Lite é dividida em três componentes principais:

*   **Servidor Central (PHP + PostgreSQL):**
    *   **Frontend Público (`painel/public.html`):** Uma página inicial informativa que descreve as funcionalidades do sistema e oferece uma área de download para o agente e a documentação.
    *   **Frontend Administrativo (`painel/index.html`):** Um painel web restrito para gerentes, onde é possível cadastrar OMs, gerenciar variáveis e gerar bundles de provisionamento.
    *   **API (`api/`):** Endpoints PHP para comunicação com o frontend e o agente, responsável pelo CRUD de organizações e variáveis, além da lógica de geração de bundles.
    *   **Scripts Core (`scripts/`):** Scripts shell pré-definidos que realizam tarefas comuns de provisionamento, como ingresso em domínio AD, configuração de rede e inventário.
    *   **Biblioteca (`lib/`):** Funções PHP compartilhadas, como a conexão com o banco de dados.
*   **Agente Python (`agent.py`):** Um script leve executado nas estações Linux. Ele se comunica com a API do servidor para baixar e executar o bundle de provisionamento mais recente para sua OM.

**Tecnologias Utilizadas:**

*   **Backend:** PHP 8+ (puro).
*   **Banco de Dados:** PostgreSQL 16+.
*   **Frontend:** HTML5, CSS3 (PicoCSS), JavaScript (Fetch API).
*   **Scripts:** Bash shell (com placeholders `{{VARIAVEL}}`).
*   **Agente:** Python 3 (com a biblioteca `requests`).

## 3. Instalação do Servidor

O script `install.sh` automatiza a configuração do ambiente do servidor. É fundamental executá-lo como `root` ou com `sudo`.

**Pré-requisitos:**

*   Sistema operacional baseado em Debian/Ubuntu (ex: Ubuntu Server, Debian).
*   Acesso à internet para instalação de pacotes.

**Passos de Instalação:**

1.  **Baixe o projeto:**
    ```bash
    git clone <URL_DO_REPOSITORIO> /home/ubuntu/seederlinux-lite
    cd /home/ubuntu/seederlinux-lite
    ```
    *(Substitua `<URL_DO_REPOSITORIO>` pelo link real do seu repositório Git, ou crie o diretório e copie os arquivos manualmente.)*

2.  **Execute o script de instalação:**
    ```bash
    sudo chmod +x install.sh
    sudo ./install.sh
    ```

O script `install.sh` realizará as seguintes ações:

*   Atualização dos pacotes do sistema.
*   Instalação do PHP, PostgreSQL e Apache2.
*   Criação do banco de dados `seederlinux` e do usuário `seeder` com senha padrão `seeder123`.
*   Aplicação do schema do banco de dados (`schema.sql`).
*   Configuração do Apache2 para servir o projeto, tornando-o acessível em `http://localhost/`.
*   Definição das permissões de arquivo e diretório adequadas.

Após a conclusão, o sistema estará pronto para uso. A página pública estará acessível em `http://localhost/` e o painel administrativo em `http://localhost/admin`.

## 4. Uso do Painel Web

O painel web oferece duas interfaces:

### 4.1. Página Pública

A página pública (`http://localhost/`) serve como um portal de entrada para o sistema. Ela exibe:

*   Uma visão geral das funcionalidades do SeederLinux Lite.
*   Uma área de downloads para o `agent.py` e a `DOCUMENTACAO.md`.
*   Um botão de "Login Gerente" que redireciona para a página de login (`http://localhost/login`).

### 4.2. Painel Administrativo

O painel administrativo (`http://localhost/admin`) é a interface principal para gerentes. O acesso é protegido por um sistema de login simples.

**Login:**

*   Acesse `http://localhost/login`.
*   **Usuário Padrão:** `admin`
*   **Senha Padrão:** `admin123`
    *(É **altamente recomendado** alterar essas credenciais em um ambiente de produção e implementar um sistema de gerenciamento de usuários mais robusto.)*

**Funcionalidades do Painel:**

1.  **Gerenciamento de Organizações (OMs):**
    *   No menu lateral esquerdo, você verá a lista de OMs cadastradas.
    *   Clique em "+ Nova OM" para adicionar uma nova organização, preenchendo o nome completo, sigla e domínio AD.
    *   Ao clicar na sigla de uma OM existente, você acessará suas configurações e variáveis.

2.  **Gerenciamento de Variáveis por OM:**
    *   Para cada OM selecionada, o painel exibirá uma lista completa de variáveis configuráveis. Estas variáveis são essenciais para a personalização dos scripts de provisionamento.
    *   **Lista de Variáveis Suportadas:**

        | Placeholder           | Descrição                                   | Exemplo de Valor Original       |
        | :-------------------- | :------------------------------------------ | :------------------------------ |
        | `{{DOMINIO}}`         | Domínio AD completo                         | `comara.intraer`                |
        | `{{DOMINIO_NETBIOS}}` | Nome NetBIOS do domínio                     | `COMARA`                        |
        | `{{DC_IP}}`           | IP do Controlador de Domínio                | `10.108.64.51`                  |
        | `{{DNS_INTERNET}}`    | DNS para internet (fallback)                | `10.108.64.27`                  |
        | `{{BASE_URL}}`        | URL base do repositório de scripts          | `https://softwarelivre.comara.intraer` |
        | `{{OCS_SERVER}}`      | Servidor OCS Inventory                      | `http://ocs.comara.intraer/ocsinventory` |
        | `{{OCS_TAG}}`         | Tag OCS da organização                      | `GAPBE-COMARA`                  |
        | `{{PRINT_SERVER}}`    | Servidor de impressão                       | `10.108.64.20`                  |
        | `{{PROXY_HTTP}}`      | Proxy HTTP corporativo                      | `10.108.88.4`                   |
        | `{{PROXY_PORTA}}`     | Porta do proxy                              | `8080`                          |
        | `{{HOMEPAGE}}`        | Página inicial do portal                    | `www.comara.intraer`            |
        | `{{PROXY_URL}}`       | URL completa do proxy                       | `http://proxy...`               |
        | `{{GRUPO_ADMIN_AD}}`  | Grupo admin no AD para sudo                 | `Dominio\ Admins`               |
        | `{{GRUPO_ADMIN_LINUX}}`| Grupo local para sudo                       | `linux-admins`                  |
        | `{{GRUPO_DASTI}}`     | Grupo DASTI para sudo                       | `_DASTI`                        |

    *   Preencha os campos com os valores desejados para a OM selecionada e clique em "Salvar Variáveis". As alterações serão persistidas no banco de dados.

3.  **Geração de Bundle de Provisionamento:**
    *   Na seção "Gerar Bundle de Provisionamento", os scripts core (obrigatórios) serão listados.
    *   Clique em "Gerar e Provisionar" para criar um script shell (`.sh`) que contém todos os scripts core e personalizados, com os placeholders substituídos pelas variáveis da OM selecionada.
    *   Um link de download será fornecido para o arquivo `.sh` gerado, que pode ser executado diretamente na estação Linux.

## 5. Uso do Agente Python

O agente Python (`agent.py`) é um componente crucial para automatizar o processo de provisionamento nas estações Linux. Ele se comunica com o servidor SeederLinux Lite para obter e executar o bundle de scripts.

**Pré-requisitos na Estação Linux:**

*   Python 3 instalado.
*   Biblioteca `requests` para Python (instale com `pip install requests`).
*   Acesso de rede ao servidor SeederLinux Lite.

**Configuração do Agente:**

Edite o arquivo `agent.py` e ajuste as seguintes variáveis:

*   `SERVER_URL`: A URL base do seu servidor SeederLinux Lite (ex: `http://192.168.1.100:80/`).
*   `ORG_ACRONYM`: A sigla da OM à qual esta estação pertence. *(No MVP, esta variável é estática e o agente busca um bundle de exemplo. Em uma versão completa, o agente faria um check-in mais elaborado para receber o bundle correto com base em sua identificação.)*

**Execução do Agente:**

```bash
python3 /caminho/para/agent.py
```

O agente fará o download do bundle e o executará com `sudo bash`. A saída do script de provisionamento será exibida no terminal, detalhando as ações realizadas.

## 6. Estrutura de Scripts Core

Os scripts core são a base do provisionamento e são fornecidos pelo sistema. Eles são projetados para serem modulares e utilizam os placeholders `{{VARIAVEL}}` para personalização.

*   `core_domain.sh`: Responsável pelo ingresso da estação no Active Directory (SSSD/Winbind) e configuração de grupos de sudoers. Utiliza `{{DOMINIO}}`, `{{DC_IP}}`, `{{DOMINIO_NETBIOS}}`, `{{DNS_INTERNET}}`, `{{GRUPO_ADMIN_AD}}`, `{{GRUPO_ADMIN_LINUX}}`, `{{GRUPO_DASTI}}`.
*   `core_branding.sh`: Aplica configurações de identidade visual, como wallpaper e tema. Utiliza `{{OM_ACRONYM}}` e `{{WALLPAPER_URL}}`.
*   `core_inventory.sh`: Configura o agente OCS Inventory para coleta de informações da estação. Utiliza `{{OCS_SERVER}}` e `{{OCS_TAG}}`.
*   `core_network.sh`: Gerencia configurações de rede, incluindo proxy, página inicial do navegador e servidor de impressão. Utiliza `{{PROXY_HTTP}}`, `{{PROXY_PORTA}}`, `{{PROXY_URL}}`, `{{HOMEPAGE}}`, `{{PRINT_SERVER}}`.

## 7. Personalização e Extensão

O SeederLinux Lite foi projetado para ser extensível:

*   **Adicionar Novos Scripts Core:** Crie novos arquivos `.sh` no diretório `scripts/` e insira-os no banco de dados como scripts `is_core = TRUE`. Eles serão automaticamente incluídos na geração de bundles.
*   **Adicionar Scripts Personalizados por OM:** O sistema pode ser estendido para permitir o upload de scripts específicos para cada OM, que seriam concatenados aos scripts core durante a geração do bundle.
*   **Adicionar Novas Variáveis:** Novas variáveis podem ser adicionadas ao banco de dados e utilizadas nos scripts shell. Lembre-se de usar o formato `{{NOME_DA_VARIAVEL}}` para que a substituição dinâmica funcione.
*   **Estender o Frontend:** O painel web é construído com HTML, CSS e JavaScript vanilla, facilitando a adição de novas funcionalidades, como gerenciamento de usuários, upload de scripts personalizados, etc.

## 8. Considerações de Segurança

É crucial implementar medidas de segurança adequadas, especialmente em ambientes de produção:

*   **Credenciais do Banco de Dados:** Altere as credenciais padrão (`seeder`/`seeder123`) no arquivo `lib/db.php` e no script `install.sh` para senhas fortes e únicas.
*   **Autenticação da API:** A API não possui autenticação robusta no MVP. Em produção, implemente um sistema de autenticação (ex: tokens JWT, chaves de API) para proteger os endpoints e garantir que apenas clientes autorizados possam interagir com o servidor.
*   **Execução de Scripts:** O agente Python executa os bundles com `sudo bash`. Garanta que apenas scripts confiáveis e auditados sejam gerados e distribuídos, pois a execução de scripts arbitrários pode comprometer seriamente a segurança da estação.
*   **Permissões de Arquivo:** Mantenha as permissões de arquivo do projeto restritas ao usuário do servidor web (`www-data` para Apache) para evitar acesso não autorizado e vulnerabilidades.
*   **HTTPS:** Utilize HTTPS para todas as comunicações entre o agente, o painel web e a API para proteger os dados em trânsito.

---

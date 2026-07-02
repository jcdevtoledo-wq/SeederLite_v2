import os
import requests
import subprocess
import sys
import platform
import argparse
import json
import hashlib
import time

# Configuração Padrão do Agente
DEFAULT_SERVER_URL = "http://localhost"
AGENT_VERSION = "1.1" # Versão atual do agente

def calculate_file_hash(filepath):
    """Calcula o hash SHA256 de um arquivo."""
    hasher = hashlib.sha256()
    with open(filepath, 'rb') as f:
        while True:
            chunk = f.read(8192)
            if not chunk: break
            hasher.update(chunk)
    return hasher.hexdigest()

def self_update(server_url):
    """
    Verifica se há uma nova versão do agente no servidor e se atualiza.
    Retorna True se o agente foi atualizado e precisa ser reiniciado.
    """
    print("[*] Verificando por atualizações do agente...")
    try:
        response = requests.get(f"{server_url}/api/agent_checkin.php?action=version")
        response.raise_for_status()
        server_versions = response.json()
        
        latest_version = server_versions.get("latest_version", "1.0")
        min_version = server_versions.get("min_version", "1.0")
        download_url = server_versions.get("agent_download_url", "/agent.py")

        # Verifica se a versão atual é menor que a mínima ou a mais recente
        if AGENT_VERSION < min_version or AGENT_VERSION < latest_version:
            print(f"[*] Nova versão do agente disponível: {latest_version}. Versão atual: {AGENT_VERSION}.")
            print(f"[*] Baixando nova versão de {server_url}{download_url}...")
            
            new_agent_content = requests.get(f"{server_url}{download_url}").text
            current_agent_path = os.path.abspath(__file__)
            
            # Salva a nova versão em um arquivo temporário
            temp_agent_path = current_agent_path + ".new"
            with open(temp_agent_path, "w") as f:
                f.write(new_agent_content)
            
            # Calcula o hash da nova versão para verificar integridade (opcional, mas recomendado)
            # new_agent_hash = calculate_file_hash(temp_agent_path)
            # print(f"[*] Hash da nova versão: {new_agent_hash}")

            # Substitui o agente atual pela nova versão
            os.replace(temp_agent_path, current_agent_path)
            print("[+] Agente atualizado com sucesso. Reiniciando...")
            return True
        else:
            print("[+] Agente já está na versão mais recente.")
            return False

    except requests.exceptions.RequestException as e:
        print(f"[!] Erro ao verificar atualizações: {e}")
    except Exception as e:
        print(f"[!] Falha no processo de atualização: {e}")
    return False

def get_om_acronym_from_hostname():
    """
    Tenta inferir a sigla da OM a partir do hostname.
    Ex: GAPBE-01 -> GAPBE
    """
    hostname = platform.node()
    parts = hostname.split('-')
    if len(parts) > 1 and parts[0].isupper() and len(parts[0]) >= 3:
        print(f"[*] OM inferida do hostname: {parts[0]}")
        return parts[0]
    print("[!] Não foi possível inferir a OM do hostname.")
    return None

def collect_hardware_info():
    """
    Coleta informações básicas de hardware.
    """
    info = {
        "hostname": platform.node(),
        "ip_address": "",
        "cpu_info": "",
        "ram_gb": 0,
        "disk_gb": 0
    }

    # IP Address
    try:
        ip_output = subprocess.check_output("hostname -I | awk '{print $1}'", shell=True, text=True).strip()
        info["ip_address"] = ip_output if ip_output else "N/A"
    except:
        info["ip_address"] = "N/A"

    # CPU Info
    try:
        cpu_output = subprocess.check_output("lscpu | grep 'Model name' | cut -d:' ' -f2 | sed -e 's/^ *//'", shell=True, text=True).strip()
        info["cpu_info"] = cpu_output if cpu_output else "N/A"
    except:
        info["cpu_info"] = "N/A"

    # RAM (GB)
    try:
        ram_output = subprocess.check_output("free -m | awk 'NR==2{printf "%.0f" , $2/1024}'", shell=True, text=True).strip()
        info["ram_gb"] = int(ram_output) if ram_output.isdigit() else 0
    except:
        info["ram_gb"] = 0

    # Disk (GB) - Root partition
    try:
        disk_output = subprocess.check_output("df -h / | awk 'NR==2{print $2}' | sed 's/G//'", shell=True, text=True).strip()
        info["disk_gb"] = int(float(disk_output)) if disk_output.replace('.', '', 1).isdigit() else 0
    except:
        info["disk_gb"] = 0

    return info

def report_inventory(server_url, inventory_data, org_acronym):
    """
    Envia os dados de inventário para o servidor.
    """
    print("[*] Reportando inventário para o servidor...")
    try:
        payload = {
            "hostname": inventory_data["hostname"],
            "ip_address": inventory_data["ip_address"],
            "cpu_info": inventory_data["cpu_info"],
            "ram_gb": inventory_data["ram_gb"],
            "disk_gb": inventory_data["disk_gb"],
            "agent_version": AGENT_VERSION,
            "org_acronym": org_acronym
        }
        response = requests.post(f"{server_url}/api/agent_checkin.php?action=report_inventory", json=payload)
        response.raise_for_status()
        result = response.json()
        if result.get("message"):
            print(f"[+] Inventário reportado com sucesso: {result["message"]}")
        else:
            print(f"[!] Erro ao reportar inventário: {result.get("error", "Erro desconhecido")}")
    except requests.exceptions.RequestException as e:
        print(f"[!] Erro de conexão ao reportar inventário: {e}")
    except Exception as e:
        print(f"[!] Falha ao reportar inventário: {e}")

def get_latest_bundle(server_url, org_acronym):
    """
    No MVP, estamos usando um ID de bundle estático para fins de demonstração,
    pois a lógica real exigiria autenticação do agente ou um endpoint público de check-in.
    """
    print(f"[*] Consultando servidor {server_url} para OM {org_acronym}...")
    # Para demonstração, pedimos ao usuário o ID do bundle ou usamos o ID 1
    return 1

def download_and_execute(server_url, bundle_id):
    url = f"{server_url}/api/bundle.php?id={bundle_id}"
    local_filename = "/tmp/seeder_bundle.sh"
    
    print(f"[*] Baixando bundle ID {bundle_id} de {url}...")
    
    try:
        r = requests.get(url, stream=True)
        if r.status_code == 404:
            print("[!] Erro: Bundle não encontrado no servidor.")
            return
        r.raise_for_status()
        
        with open(local_filename, 'wb') as f:
            for chunk in r.iter_content(chunk_size=8192):
                f.write(chunk)
                
        print("[*] Executando script de provisionamento...")
        os.chmod(local_filename, 0o755)
        
        # Executa o script e captura a saída
        process = subprocess.Popen(['sudo', 'bash', local_filename], 
                                   stdout=subprocess.PIPE, 
                                   stderr=subprocess.STDOUT,
                                   text=True)
                                   
        for line in process.stdout:
            print(f"  > {line.strip()}")
            
        process.wait()
        
        if process.returncode == 0:
            print("[+] Provisionamento concluído com sucesso!")
        else:
            print(f"[!] Erro na execução: Código {process.returncode}")
            
    except requests.exceptions.RequestException as e:
        print(f"[!] Erro de conexão: {e}")
    except Exception as e:
        print(f"[!] Falha no processo: {e}")
    finally:
        if os.path.exists(local_filename):
            os.remove(local_filename)
            print("[*] Arquivo temporário removido.")

if __name__ == "__main__":
    if platform.system() != "Linux":
        print("[!] Este agente foi projetado para sistemas Linux.")
        sys.exit(1)
        
    parser = argparse.ArgumentParser(description='SeederLinux Lite Agent')
    parser.add_argument('--server', default=DEFAULT_SERVER_URL, help='URL do servidor SeederLinux Lite')
    parser.add_argument('--org', help='Sigla da Organização Militar (OM). Se omitido, tentará inferir do hostname.')
    parser.add_argument('--bundle', type=int, help='ID específico do bundle para baixar (opcional)')
    
    args = parser.parse_args()

    # 1. Auto-Update
    if self_update(args.server):
        # Se atualizou, reinicia o próprio script
        os.execv(sys.executable, ['python3'] + sys.argv)

    # 2. Auto-Discovery da OM
    om_acronym = args.org
    if not om_acronym:
        om_acronym = get_om_acronym_from_hostname()
        if not om_acronym:
            print("[!] OM não especificada e não pôde ser inferida. Use --org ou ajuste o hostname.")
            sys.exit(1)

    # 3. Coleta e Report de Inventário
    hardware_info = collect_hardware_info()
    report_inventory(args.server, hardware_info, om_acronym)

    # 4. Download e Execução do Bundle
    bid = args.bundle if args.bundle else get_latest_bundle(args.server, om_acronym)
    
    if bid:
        download_and_execute(args.server, bid)

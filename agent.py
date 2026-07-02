import os
import requests
import subprocess
import sys
import platform
import argparse

# Configuração Padrão do Agente
DEFAULT_SERVER_URL = "http://localhost"

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
    parser.add_argument('--org', required=True, help='Sigla da Organização Militar (OM)')
    parser.add_argument('--bundle', type=int, help='ID específico do bundle para baixar (opcional)')
    
    args = parser.parse_args()
    
    bid = args.bundle if args.bundle else get_latest_bundle(args.server, args.org)
    
    if bid:
        download_and_execute(args.server, bid)

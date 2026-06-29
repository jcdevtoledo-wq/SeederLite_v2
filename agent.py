import os
import requests
import subprocess
import sys
import platform

# Configuração do Agente
SERVER_URL = "http://localhost:8000"  # URL do Servidor SeederLinux
ORG_ACRONYM = "COMARA"                # Sigla da OM para identificação

def get_bundle_id():
    """
    Consulta a API para saber qual o bundle mais recente para esta OM.
    Em um cenário real, isso poderia ser baseado em um check-in ou UUID da máquina.
    """
    try:
        # Simplificado: pegando o último bundle gerado para a OM
        # Em produção, haveria um endpoint /api/checkin.php
        print(f"[*] Consultando servidor {SERVER_URL} para OM {ORG_ACRONYM}...")
        return 1 # Exemplo estático para o MVP
    except Exception as e:
        print(f"[!] Erro ao consultar servidor: {e}")
        return None

def download_and_execute(bundle_id):
    url = f"{SERVER_URL}/api/bundle.php?id={bundle_id}"
    local_filename = "/tmp/seeder_bundle.sh"
    
    print(f"[*] Baixando bundle {bundle_id}...")
    try:
        r = requests.get(url, stream=True)
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
            
    except Exception as e:
        print(f"[!] Falha no processo: {e}")

if __name__ == "__main__":
    if platform.system() != "Linux":
        print("[!] Este agente só funciona em sistemas Linux.")
        sys.exit(1)
        
    bid = get_bundle_id()
    if bid:
        download_and_execute(bid)

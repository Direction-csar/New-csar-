import paramiko
import sys
import time

host = "192.168.2.141"
user = "msow"
password = "CsarMS20@"
command = "cd /var/www/csar && bash deploy-server.sh && php artisan db:seed --class=AgentsImportSeeder --force && php artisan contracts:generate --user-id=1"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connexion à {host} en tant que {user}...")
    client.connect(host, username=user, password=password, timeout=20)
    print("Connecté. Déploiement + migration + seeder + génération contrats en cours...\n")

    stdin, stdout, stderr = client.exec_command(command, get_pty=True)

    while not stdout.channel.exit_status_ready():
        if stdout.channel.recv_ready():
            data = stdout.channel.recv(1024).decode("utf-8", errors="replace")
            print(data, end="")
            sys.stdout.flush()
        if stderr.channel.recv_stderr_ready():
            err = stderr.channel.recv_stderr(1024).decode("utf-8", errors="replace")
            print(err, end="", file=sys.stderr)
            sys.stderr.flush()
        time.sleep(0.2)

    print(stdout.read().decode("utf-8", errors="replace"), end="")
    print(stderr.read().decode("utf-8", errors="replace"), end="", file=sys.stderr)

    exit_code = stdout.channel.recv_exit_status()
    print(f"\n\nTerminé avec le code de sortie : {exit_code}")
    sys.exit(exit_code)

except Exception as e:
    print(f"Erreur : {e}", file=sys.stderr)
    sys.exit(1)
finally:
    client.close()

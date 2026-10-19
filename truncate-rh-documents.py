import getpass
import os
import paramiko
import sys

host = os.environ.get("CSAR_SSH_HOST", "192.168.2.141")
user = os.environ.get("CSAR_SSH_USER", "msow")
password = os.environ.get("CSAR_SSH_PASSWORD") or getpass.getpass(f"Mot de passe SSH pour {user}@{host} : ")
command = "cd /var/www/csar && php artisan tinker --execute=\"App\\Models\\RhDocument::truncate(); echo 'Suppression terminee';\""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connexion à {host}...")
    client.connect(host, username=user, password=password, timeout=20)
    print("Suppression de l'historique des documents RH...\n")

    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    print(stdout.read().decode("utf-8", errors="replace"), end="")
    print(stderr.read().decode("utf-8", errors="replace"), end="", file=sys.stderr)

    exit_code = stdout.channel.recv_exit_status()
    print(f"\nTerminé avec le code de sortie : {exit_code}")
    sys.exit(exit_code)

except Exception as e:
    print(f"Erreur : {e}", file=sys.stderr)
    sys.exit(1)
finally:
    client.close()

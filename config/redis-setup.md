# Configuration Redis pour CSAR - Support 1000 utilisateurs

## 1. Installation Redis (Ubuntu/Debian)

```bash
# Installation
sudo apt update
sudo apt install redis-server -y

# Démarrage automatique
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Vérification
redis-cli ping
# Réponse attendue: PONG
```

## 2. Installation extension PHP Redis

```bash
# Installation
sudo apt install php8.2-redis -y

# Redémarrage PHP-FPM
sudo systemctl restart php8.2-fpm

# Vérification
php -m | grep redis
```

## 3. Configuration Redis (/etc/redis/redis.conf)

```conf
# Performance
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Sécurité
bind 127.0.0.1
protected-mode yes
requirepass VotreMotDePasseSecurise

# Performance réseau
tcp-backlog 511
timeout 300
tcp-keepalive 300
```

## 4. Configuration Laravel (.env)

```env
# Cache
CACHE_STORE=redis
CACHE_PREFIX=csar_cache

# Sessions
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=redis

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=VotreMotDePasseSecurise
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2
```

## 5. Test de configuration

```bash
# Artisan
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test Redis
php artisan tinker
>>> Cache::put('test', 'valeur', 60);
>>> Cache::get('test');
# Doit retourner: "valeur"
```

## 6. Monitoring Redis

```bash
# Stats en temps réel
redis-cli --stat

# Info détaillées
redis-cli info

# Nombre de clés
redis-cli dbsize
```

## Impact attendu

- ✅ **Cache hits** : 95%+
- ✅ **Temps réponse** : -70%
- ✅ **Charge BDD** : -80%
- ✅ **Support** : 1000+ utilisateurs simultanés

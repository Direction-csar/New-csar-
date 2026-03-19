# 🚀 Guide d'Optimisation Performance CSAR - Support 1000 Utilisateurs

**Date**: 13 Mars 2026  
**Objectif**: Rendre la plateforme CSAR opérationnelle pour 1000+ utilisateurs simultanés

---

## 📊 État Actuel vs Objectif

| Métrique | Actuel | Objectif | Actions |
|----------|--------|----------|---------|
| **Utilisateurs simultanés** | ~50 | 1000+ | Redis + Load Balancing |
| **Temps réponse moyen** | 2-3s | <500ms | Cache + Index BDD |
| **Cache** | Database | Redis | Migration Redis |
| **Queue** | Sync | Redis | Workers asynchrones |
| **Index BDD** | Partiels | Complets | Migration créée |

---

## ✅ ÉTAPE 1 : MIGRATION REDIS (CRITIQUE)

### Installation Redis

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server php8.2-redis -y
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Test
redis-cli ping
# Réponse: PONG
```

### Configuration Redis

Éditez `/etc/redis/redis.conf`:

```conf
# Performance pour 1000 users
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Sécurité
bind 127.0.0.1
protected-mode yes
requirepass VotreMotDePasseSecurise123!

# Réseau
tcp-backlog 511
timeout 300
```

Redémarrez Redis:
```bash
sudo systemctl restart redis-server
```

### Configuration Laravel

Modifiez votre `.env`:

```env
# Cache Redis
CACHE_STORE=redis
CACHE_PREFIX=csar_cache

# Sessions Redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue Redis
QUEUE_CONNECTION=redis

# Connexion Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=VotreMotDePasseSecurise123!
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2
REDIS_SESSION_DB=3
```

### Test de Configuration

```bash
# Nettoyer et reconfigurer
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# Tester Redis
php artisan tinker
>>> Cache::put('test', 'valeur', 60);
>>> Cache::get('test');
# Doit retourner: "valeur"
```

**Impact attendu**: ⚡ **+500% performance**, support 1000+ users

---

## ✅ ÉTAPE 2 : INDEX BASE DE DONNÉES

### Application des Index

```bash
# Appliquer la migration d'index
php artisan migrate

# Vérifier les index
php artisan csar:optimize
```

### Index Créés

La migration `2026_03_13_100000_add_performance_indexes.php` ajoute:

- **demandes**: statut, created_at, code_suivi, type+statut
- **stocks**: warehouse_id, stock_type_id
- **stock_movements**: stock_id, created_at, type
- **news**: is_published, published_at, combo
- **users**: role, is_active
- **personnel**: warehouse_id, statut, poste
- **sim_collections**: market_id, collector_id, status, date
- **messages**: read_at, created_at
- **notifications**: user_id, read_at

**Impact attendu**: ⚡ **-70% temps requêtes**, -90% charge CPU

---

## ✅ ÉTAPE 3 : QUEUE WORKERS

### Configuration Supervisor

Créez `/etc/supervisor/conf.d/csar-worker.conf`:

```ini
[program:csar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/csar/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/csar/storage/logs/worker.log
stopwaitsecs=3600
```

### Démarrage Workers

```bash
# Recharger Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Démarrer les workers
sudo supervisorctl start csar-worker:*

# Vérifier le statut
sudo supervisorctl status
```

### Tâches à Mettre en Queue

Modifiez vos contrôleurs pour utiliser les queues:

```php
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailJob;

// Avant (bloquant)
Mail::to($user)->send(new WelcomeEmail());

// Après (asynchrone)
SendEmailJob::dispatch($user);
```

**Impact attendu**: 🎯 **Temps réponse < 200ms**, pas de timeout

---

## ✅ ÉTAPE 4 : OPTIMISATION REQUÊTES N+1

### Identifier les Requêtes N+1

Installez Laravel Debugbar (dev uniquement):

```bash
composer require barryvdh/laravel-debugbar --dev
```

### Corriger avec Eager Loading

```php
// ❌ MAUVAIS (N+1)
$demandes = Demande::all();
foreach ($demandes as $demande) {
    echo $demande->user->name; // Requête pour chaque demande !
}

// ✅ BON (1 requête)
$demandes = Demande::with('user')->get();
foreach ($demandes as $demande) {
    echo $demande->user->name;
}

// ✅ ENCORE MIEUX (relations multiples)
$demandes = Demande::with(['user', 'warehouse', 'comments'])->get();
```

### Exemples de Corrections

**DashboardController.php**:
```php
// Avant
$demandes = PublicRequest::latest()->take(10)->get();

// Après
$demandes = PublicRequest::with('user')
    ->latest()
    ->take(10)
    ->get();
```

**StockController.php**:
```php
// Avant
$stocks = Stock::all();

// Après
$stocks = Stock::with(['warehouse', 'stockType', 'movements'])
    ->get();
```

**Impact attendu**: ⚡ **-80% requêtes BDD**

---

## ✅ ÉTAPE 5 : CACHE STRATÉGIQUE

### Cache des Requêtes Fréquentes

```php
use Illuminate\Support\Facades\Cache;

// Cache des statistiques (1 heure)
$stats = Cache::remember('dashboard_stats', 3600, function () {
    return [
        'total_demandes' => Demande::count(),
        'demandes_pending' => Demande::where('statut', 'pending')->count(),
        'total_warehouses' => Warehouse::count(),
        'total_stocks' => Stock::sum('quantite'),
    ];
});

// Cache des actualités publiques (30 min)
$news = Cache::remember('public_news_latest', 1800, function () {
    return News::where('is_published', true)
        ->latest('published_at')
        ->take(6)
        ->get();
});
```

### Invalidation du Cache

```php
// Dans le contrôleur après modification
public function update(Request $request, $id)
{
    $demande = Demande::findOrFail($id);
    $demande->update($request->all());
    
    // Invalider le cache
    Cache::forget('dashboard_stats');
    
    return redirect()->back();
}
```

**Impact attendu**: 🚀 **95% cache hits**, -80% charge BDD

---

## ✅ ÉTAPE 6 : OPTIMISATION ASSETS

### Images WebP

Activez dans `.env`:

```env
PERFORMANCE_WEBP_ENABLED=true
PERFORMANCE_IMAGE_QUALITY=85
PERFORMANCE_MAX_IMAGE_WIDTH=1920
PERFORMANCE_LAZY_LOADING=true
```

### Minification CSS/JS

```bash
# Build production
npm run build

# Vérifier les fichiers générés
ls -lh public/build/assets/
```

### CDN Cloudflare

1. Créez un compte Cloudflare
2. Ajoutez votre domaine
3. Configurez `.env`:

```env
PERFORMANCE_CDN_ENABLED=true
PERFORMANCE_CDN_URL=https://cdn.csar.sn
PERFORMANCE_CDN_IMAGES=true
PERFORMANCE_CDN_CSS=true
PERFORMANCE_CDN_JS=true
```

**Impact attendu**: ⚡ **PageSpeed 90+**, temps chargement < 2s

---

## ✅ ÉTAPE 7 : MONITORING

### Laravel Telescope

```bash
# Installation
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Accès: http://csar.sn/telescope
```

### Monitoring Redis

```bash
# Stats en temps réel
redis-cli --stat

# Info détaillées
redis-cli info

# Nombre de clés
redis-cli dbsize

# Monitoring continu
redis-cli monitor
```

### Logs de Performance

Ajoutez dans `config/logging.php`:

```php
'performance' => [
    'driver' => 'daily',
    'path' => storage_path('logs/performance.log'),
    'level' => 'info',
    'days' => 14,
],
```

**Impact attendu**: 🔍 **Visibilité complète**, détection proactive

---

## ✅ ÉTAPE 8 : TESTS DE CHARGE

### Installation Apache JMeter

```bash
# Ubuntu
sudo apt install jmeter

# Ou téléchargement
wget https://dlcdn.apache.org//jmeter/binaries/apache-jmeter-5.6.3.tgz
tar -xzf apache-jmeter-5.6.3.tgz
```

### Plan de Test

Créez `csar-load-test.jmx`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan" testname="CSAR Load Test">
      <elementProp name="TestPlan.user_defined_variables" elementType="Arguments">
        <collectionProp name="Arguments.arguments"/>
      </elementProp>
    </TestPlan>
    <hashTree>
      <ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="1000 Users">
        <stringProp name="ThreadGroup.num_threads">1000</stringProp>
        <stringProp name="ThreadGroup.ramp_time">60</stringProp>
        <stringProp name="ThreadGroup.duration">300</stringProp>
      </ThreadGroup>
    </hashTree>
  </hashTree>
</jmeterTestPlan>
```

### Exécution Tests

```bash
# Test avec 100 users
jmeter -n -t csar-load-test.jmx -l results-100.jtl -e -o report-100

# Test avec 500 users
# Modifier num_threads à 500
jmeter -n -t csar-load-test.jmx -l results-500.jtl -e -o report-500

# Test avec 1000 users
# Modifier num_threads à 1000
jmeter -n -t csar-load-test.jmx -l results-1000.jtl -e -o report-1000
```

### Métriques à Valider

- ✅ Temps réponse moyen < 500ms
- ✅ Taux erreur < 1%
- ✅ CPU < 70%
- ✅ RAM < 80%
- ✅ Aucun timeout

**Impact attendu**: ✅ **Validation capacité 1000 users**

---

## 🎯 COMMANDE RAPIDE D'OPTIMISATION

```bash
# Commande tout-en-un
php artisan csar:optimize

# Avec optimisation tables MySQL
php artisan csar:optimize --force
```

Cette commande vérifie et optimise:
- ✅ Connexion Redis
- ✅ Caches Laravel
- ✅ Index de base de données
- ✅ Tables MySQL
- ✅ Configuration

---

## 📊 CHECKLIST FINALE

### Infrastructure
- [ ] Redis installé et configuré
- [ ] Extension PHP Redis active
- [ ] Cache configuré sur Redis
- [ ] Sessions configurées sur Redis
- [ ] Queue configurée sur Redis

### Base de Données
- [ ] Migration d'index appliquée
- [ ] Tables optimisées
- [ ] Requêtes N+1 corrigées
- [ ] Eager loading systématique

### Workers
- [ ] Supervisor configuré
- [ ] 8 workers actifs
- [ ] Logs workers fonctionnels
- [ ] Tâches en queue (emails, SMS, exports)

### Assets
- [ ] Images WebP activées
- [ ] CSS/JS minifiés
- [ ] CDN configuré
- [ ] Lazy loading actif

### Monitoring
- [ ] Laravel Telescope installé
- [ ] Logs performance actifs
- [ ] Redis monitoring configuré
- [ ] Alertes configurées

### Tests
- [ ] Tests 100 users réussis
- [ ] Tests 500 users réussis
- [ ] Tests 1000 users réussis
- [ ] Métriques validées

---

## 🚨 TROUBLESHOOTING

### Redis ne démarre pas

```bash
# Vérifier les logs
sudo journalctl -u redis-server -n 50

# Vérifier la config
redis-server /etc/redis/redis.conf --test-memory 1

# Redémarrer
sudo systemctl restart redis-server
```

### Workers ne démarrent pas

```bash
# Vérifier Supervisor
sudo supervisorctl status

# Logs
tail -f /var/www/csar/storage/logs/worker.log

# Redémarrer
sudo supervisorctl restart csar-worker:*
```

### Performance toujours lente

```bash
# Vérifier les requêtes lentes
php artisan telescope:prune --hours=1

# Analyser les logs
tail -f storage/logs/laravel.log | grep "slow"

# Profiler une page
# Installer Clockwork
composer require itsgoingd/clockwork
```

---

## 📞 SUPPORT

Pour toute question:
- 📧 Email: support@csar.sn
- 📱 Téléphone: +221 XX XXX XX XX
- 📚 Documentation: `/docs`

---

**Dernière mise à jour**: 13 Mars 2026  
**Version**: 1.0  
**Auteur**: Équipe Technique CSAR

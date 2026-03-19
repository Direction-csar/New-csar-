# 📱 Formulaire Mobile de Collecte SIM - Interface Complète

## 🎯 **Vue d'ensemble du Formulaire**

Voici l'interface mobile que j'ai créée pour la collecte SIM, basée sur les besoins réels des collecteurs :

## 📋 **Sections du Formulaire**

### **1. 🏪 Sélection du Marché**
- Liste déroulante des marchés autorisés
- Filtrage par zone géographique du collecteur
- Affichage du nom et région du marché

### **2. 📦 Sélection du Produit**
- Liste des produits actifs (millet, maïs, riz, etc.)
- Affichage du code et unité du produit
- Recherche rapide par nom

### **3. 💰 Saisie des Prix**
- **Prix de vente** (obligatoire)
- **Prix détail** (optionnel)
- **Prix gros** (optionnel)
- Validation numérique automatique

### **4. 📅 Date de Collecte**
- Date par défaut : aujourd'hui
- Calendrier interactif
- Format : YYYY-MM-DD

### **5. 📍 Localisation GPS**
- Capture automatique des coordonnées
- Bouton de rafraîchissement manuel
- Affichage : Lat/Lng avec 6 décimales
- Validation de la zone autorisée

### **6. 📸 Photos du Marché**
- **📷 Prendre photo** : Appareil photo natif
- **📁 Galerie** : Sélection depuis téléphone
- Maximum 5 photos par collecte
- Compression automatique (800x600 max)
- Aperçu avec possibilité de suppression

### **7. 📝 Notes Optionnelles**
- Champ texte multiligne
- Observations sur le marché
- Conditions spéciales
- Informations supplémentaires

## 🔧 **Fonctionnalités Techniques**

### **🔄 Mode Hors-ligne**
```javascript
// Sauvegarde locale automatique
await AsyncStorage.setItem('pending_collection_' + Date.now(), JSON.stringify(formData));

// Synchronisation automatique quand connexion disponible
const syncPendingData = async () => {
  const keys = await AsyncStorage.getAllKeys();
  const pendingKeys = keys.filter(key => key.startsWith('pending_collection_'));
  // ... synchronisation avec l'API
};
```

### **📍 GPS Intégré**
```javascript
// Permission automatique
const requestLocationPermission = async () => {
  const granted = await PermissionsAndroid.request(
    PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION
  );
  
  // Capture des coordonnées
  Geolocation.getCurrentPosition(
    (position) => {
      setFormData(prev => ({
        ...prev,
        latitude: position.coords.latitude,
        longitude: position.coords.longitude
      }));
    }
  );
};
```

### **📸 Gestion des Photos**
```javascript
// Options de capture optimisées
const options = {
  mediaType: 'photo',
  quality: 0.7,
  maxWidth: 800,
  maxHeight: 600
};

// Compression et stockage
launchCamera(options, (response) => {
  if (response.assets && response.assets[0]) {
    const newPhoto = {
      uri: response.assets[0].uri,
      type: response.assets[0].type,
      name: response.assets[0].fileName
    };
    setFormData(prev => ({
      ...prev,
      photos: [...prev.photos, newPhoto]
    }));
  }
});
```

### **🔐 Validation et Sécurité**
```javascript
const validateForm = () => {
  if (!formData.market_id) {
    Alert.alert('Erreur', 'Veuillez sélectionner un marché');
    return false;
  }
  
  if (!formData.product_id) {
    Alert.alert('Erreur', 'Veuillez sélectionner un produit');
    return false;
  }
  
  if (!formData.price || parseFloat(formData.price) <= 0) {
    Alert.alert('Erreur', 'Veuillez entrer un prix valide');
    return false;
  }
  
  return true;
};
```

## 🎨 **Design et UX**

### **📱 Interface Mobile Optimisée**
- **Scroll vertical** pour tous les écrans
- **Touch targets** minimum 44px
- **Contraste élevé** pour visibilité extérieure
- **Icônes intuitives** (🏪, 📦, 💰, 📅, 📍, 📸, 📝)

### **🎯 Thème de Couleurs**
- **Vert CSAR** : `#27ae60` (boutons principaux)
- **Bleu secondaire** : `#3498db` (boutons secondaires)
- **Gris neutre** : `#f5f5f5` (fond)
- **Texte sombre** : `#2c3e50` (lisibilité)

### **📊 Feedback Utilisateur**
- **Chargement** : ActivityIndicator
- **Succès** : Modal avec confirmation
- **Erreur** : Alert avec message clair
- **Hors-ligne** : Notification de sauvegarde locale

## 🔄 **Workflow de Collecte**

1. **📱 Ouverture de l'application**
2. **🔐 Connexion du collecteur**
3. **📊 Remplissage du formulaire**
   - Sélection marché/produit
   - Saisie des prix
   - Capture photos
   - GPS automatique
4. **💾 Validation et sauvegarde**
   - Mode hors-ligne si nécessaire
   - Synchronisation automatique
5. **✅ Confirmation de succès**

## 📈 **Statistiques en Temps Réel**

Le formulaire affiche également :
- **Nombre total de collectes** du collecteur
- **Dernière synchronisation** réussie
- **Données en attente** de sync
- **Score de performance** mensuel

---

**Ce formulaire est optimisé pour une utilisation rapide sur le terrain, même avec connexion limitée. Les collecteurs peuvent saisir les données en quelques minutes et l'application gère automatiquement la synchronisation !**

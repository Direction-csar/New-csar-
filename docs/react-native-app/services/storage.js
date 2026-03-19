import AsyncStorage from '@react-native-async-storage/async-storage';
import api from './api';

class StorageService {
  // Clés de stockage
  static KEYS = {
    AUTH_TOKEN: 'auth_token',
    USER_DATA: 'user_data',
    PENDING_COLLECTIONS: 'pending_collections_',
    OFFLINE_MODE: 'offline_mode',
    LAST_SYNC: 'last_sync',
    APP_SETTINGS: 'app_settings'
  };

  // Authentification
  static async saveAuthToken(token) {
    await AsyncStorage.setItem(this.KEYS.AUTH_TOKEN, token);
  }

  static async getAuthToken() {
    return await AsyncStorage.getItem(this.KEYS.AUTH_TOKEN);
  }

  static async saveUserData(userData) {
    await AsyncStorage.setItem(this.KEYS.USER_DATA, JSON.stringify(userData));
  }

  static async getUserData() {
    const data = await AsyncStorage.getItem(this.KEYS.USER_DATA);
    return data ? JSON.parse(data) : null;
  }

  // Collections hors-ligne
  static async savePendingCollection(collection) {
    const key = `${this.KEYS.PENDING_COLLECTIONS}${Date.now()}`;
    const collectionWithTimestamp = {
      ...collection,
      id: key,
      created_at: new Date().toISOString(),
      sync_status: 'pending'
    };
    
    await AsyncStorage.setItem(key, JSON.stringify(collectionWithTimestamp));
    return collectionWithTimestamp;
  }

  static async getPendingCollections() {
    try {
      const keys = await AsyncStorage.getAllKeys();
      const pendingKeys = keys.filter(key => key.startsWith(this.KEYS.PENDING_COLLECTIONS));
      
      const collections = await Promise.all(
        pendingKeys.map(async (key) => {
          const data = await AsyncStorage.getItem(key);
          return data ? JSON.parse(data) : null;
        })
      );
      
      return collections.filter(collection => collection !== null);
    } catch (error) {
      console.error('Error getting pending collections:', error);
      return [];
    }
  }

  static async removePendingCollection(key) {
    await AsyncStorage.removeItem(key);
  }

  static async clearPendingCollections() {
    try {
      const keys = await AsyncStorage.getAllKeys();
      const pendingKeys = keys.filter(key => key.startsWith(this.KEYS.PENDING_COLLECTIONS));
      
      await Promise.all(
        pendingKeys.map(key => AsyncStorage.removeItem(key))
      );
    } catch (error) {
      console.error('Error clearing pending collections:', error);
    }
  }

  // Mode hors-ligne
  static async setOfflineMode(isOffline) {
    await AsyncStorage.setItem(this.KEYS.OFFLINE_MODE, JSON.stringify(isOffline));
  }

  static async isOfflineMode() {
    const isOffline = await AsyncStorage.getItem(this.KEYS.OFFLINE_MODE);
    return isOffline ? JSON.parse(isOffline) : false;
  }

  // Synchronisation
  static async saveLastSync(syncData) {
    await AsyncStorage.setItem(this.KEYS.LAST_SYNC, JSON.stringify(syncData));
  }

  static async getLastSync() {
    const syncData = await AsyncStorage.getItem(this.KEYS.LAST_SYNC);
    return syncData ? JSON.parse(syncData) : null;
  }

  // Paramètres de l'application
  static async saveSettings(settings) {
    await AsyncStorage.setItem(this.KEYS.APP_SETTINGS, JSON.stringify(settings));
  }

  static async getSettings() {
    const settings = await AsyncStorage.getItem(this.KEYS.APP_SETTINGS);
    return settings ? JSON.parse(settings) : {
      autoSync: true,
      photoQuality: 'medium',
      gpsAccuracy: 'high',
      notifications: true
    };
  }

  // Nettoyage des données
  static async clearAllData() {
    try {
      await AsyncStorage.clear();
    } catch (error) {
      console.error('Error clearing all data:', error);
    }
  }

  // Statistiques de stockage
  static async getStorageStats() {
    try {
      const keys = await AsyncStorage.getAllKeys();
      const pendingCollections = keys.filter(key => key.startsWith(this.KEYS.PENDING_COLLECTIONS));
      
      return {
        totalKeys: keys.length,
        pendingCollections: pendingCollections.length,
        hasAuth: await this.getAuthToken() !== null,
        hasUserData: await this.getUserData() !== null,
        lastSync: await this.getLastSync()
      };
    } catch (error) {
      console.error('Error getting storage stats:', error);
      return null;
    }
  }

  // Synchronisation des données locales avec le serveur
  static async syncPendingCollections() {
    try {
      const pendingCollections = await this.getPendingCollections();
      
      if (pendingCollections.length === 0) {
        return { success: true, synced: 0, failed: 0 };
      }

      let synced = 0;
      let failed = 0;

      for (const collection of pendingCollections) {
        try {
          // Supprimer les champs spécifiques au stockage local
          const { id, created_at, sync_status, ...collectionData } = collection;
          
          // Envoyer au serveur
          await api.submitCollection(collectionData);
          
          // Supprimer de la sauvegarde locale
          await this.removePendingCollection(collection.id);
          
          synced++;
        } catch (error) {
          console.error('Failed to sync collection:', error);
          failed++;
        }
      }

      // Mettre à jour la date de dernière synchronisation
      await this.saveLastSync({
        date: new Date().toISOString(),
        synced,
        failed,
        total: pendingCollections.length
      });

      return { success: true, synced, failed };
    } catch (error) {
      console.error('Error during sync:', error);
      return { success: false, error: error.message };
    }
  }
}

export default StorageService;

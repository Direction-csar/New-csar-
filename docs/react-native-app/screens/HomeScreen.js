import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
  RefreshControl,
  Modal
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import api from '../services/api';
import StorageService from '../services/storage';

const HomeScreen = ({ navigation }) => {
  const [userData, setUserData] = useState(null);
  const [stats, setStats] = useState({
    totalCollections: 0,
    pendingCollections: 0,
    lastSync: null,
    todayCollections: 0,
    thisWeek: 0,
    thisMonth: 0,
  });
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [syncing, setSyncing] = useState(false);
  const [showSyncModal, setShowSyncModal] = useState(false);
  const [syncResult, setSyncResult] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  useFocusEffect(
    React.useCallback(() => {
      loadData();
    }, [])
  );

  const loadData = async () => {
    try {
      setLoading(true);
      
      // Charger les données utilisateur
      const userDataFromStorage = await StorageService.getUserData();
      if (userDataFromStorage) {
        setUserData(userDataFromStorage);
      }

      // Essayer de charger les données depuis l'API
      try {
        const profileResponse = await api.getProfile();
        if (profileResponse.success) {
          setUserData(profileResponse.data);
          await StorageService.saveUserData(profileResponse.data);
        }
      } catch (error) {
        console.log('Profile API failed, using cached data');
      }

      // Charger les stats depuis l'API
      try {
        const statsResp = await api.getStats();
        if (statsResp.success) {
          const s = statsResp.data;
          setStats({
            totalCollections: s.total,
            pendingCollections: s.pending_sync,
            lastSync: s.last_sync,
            todayCollections: s.today,
            thisWeek: s.this_week,
            thisMonth: s.this_month,
          });
          return;
        }
      } catch {}
      await calculateStats();
      
    } catch (error) {
      console.error('Error loading data:', error);
    } finally {
      setLoading(false);
    }
  };

  const calculateStats = async () => {
    try {
      const pendingCollections = await StorageService.getPendingCollections();
      const lastSync = await StorageService.getLastSync();
      
      // Calculer les collectes d'aujourd'hui
      const today = new Date().toDateString();
      const todayCollections = pendingCollections.filter(collection => {
        return new Date(collection.created_at).toDateString() === today;
      }).length;

      setStats({
        totalCollections: userData?.total_collections || 0,
        pendingCollections: pendingCollections.length,
        lastSync,
        todayCollections
      });
    } catch (error) {
      console.error('Error calculating stats:', error);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await loadData();
    setRefreshing(false);
  };

  const handleSync = async () => {
    setSyncing(true);
    
    try {
      const result = await StorageService.syncPendingCollections();
      setSyncResult(result);
      setShowSyncModal(true);
      
      // Recharger les données après synchronisation
      await loadData();
    } catch (error) {
      Alert.alert('Erreur', 'La synchronisation a échoué');
    } finally {
      setSyncing(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Déconnexion',
      'Êtes-vous sûr de vouloir vous déconnecter?',
      [
        {
          text: 'Annuler',
          style: 'cancel'
        },
        {
          text: 'Déconnexion',
          style: 'destructive',
          onPress: async () => {
            await api.logout();
            navigation.replace('Login');
          }
        }
      ]
    );
  };

  const formatLastSync = (lastSync) => {
    if (!lastSync) return 'Jamais';
    
    const syncDate = new Date(lastSync.date);
    const now = new Date();
    const diffInMinutes = Math.floor((now - syncDate) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Il y a quelques instants';
    if (diffInMinutes < 60) return `Il y a ${diffInMinutes} minutes`;
    if (diffInMinutes < 1440) return `Il y a ${Math.floor(diffInMinutes / 60)} heures`;
    return `Il y a ${Math.floor(diffInMinutes / 1440)} jours`;
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#27ae60" />
        <Text style={styles.loadingText}>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
      }
    >
      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.welcomeText}>Bienvenue,</Text>
            <Text style={styles.userName}>{userData?.name || 'Collecteur'}</Text>
          </View>
          <TouchableOpacity onPress={handleLogout}>
            <Text style={styles.logoutText}>🚪</Text>
          </TouchableOpacity>
        </View>
        
        <View style={styles.zonesContainer}>
          <Text style={styles.zonesLabel}>Zones assignées:</Text>
          <View style={styles.zonesList}>
            {userData?.assigned_zones?.map((zone, index) => (
              <View key={index} style={styles.zoneBadge}>
                <Text style={styles.zoneText}>{zone}</Text>
              </View>
            )) || <Text style={styles.noZonesText}>Aucune zone assignée</Text>}
          </View>
        </View>
      </View>

      {/* Statistiques */}
      <View style={styles.statsContainer}>
        <Text style={styles.sectionTitle}>📊 Vos statistiques</Text>
        
        <View style={styles.statsGrid}>
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.totalCollections}</Text>
            <Text style={styles.statLabel}>Total collectes</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.todayCollections}</Text>
            <Text style={styles.statLabel}>Aujourd'hui</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.pendingCollections}</Text>
            <Text style={styles.statLabel}>En attente</Text>
          </View>
          
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{userData?.status === 'active' ? '✅' : '⏸️'}</Text>
            <Text style={styles.statLabel}>Statut</Text>
          </View>
        </View>
      </View>

      {/* Actions principales */}
      <View style={styles.actionsContainer}>
        <Text style={styles.sectionTitle}>🎯 Actions</Text>
        
        <TouchableOpacity 
          style={styles.actionButton}
          onPress={() => navigation.navigate('Collect')}
        >
          <Text style={styles.actionButtonIcon}>📊</Text>
          <Text style={styles.actionButtonText}>Nouvelle collecte de prix</Text>
          <Text style={styles.actionButtonSubtext}>Enregistrer les prix sur le marché</Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.actionButton}
          onPress={() => navigation.navigate('Map')}
        >
          <Text style={styles.actionButtonIcon}>🗺️</Text>
          <Text style={styles.actionButtonText}>Carte des marchés</Text>
          <Text style={styles.actionButtonSubtext}>Voir les marchés et ma position</Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.actionButton}
          onPress={() => navigation.navigate('SyncHistory')}
        >
          <Text style={styles.actionButtonIcon}>📋</Text>
          <Text style={styles.actionButtonText}>Historique de sync</Text>
          <Text style={styles.actionButtonSubtext}>{formatLastSync(stats.lastSync)}</Text>
        </TouchableOpacity>
      </View>

      {/* Synchronisation */}
      <View style={styles.syncContainer}>
        <View style={styles.syncHeader}>
          <Text style={styles.syncTitle}>🔄 Synchronisation</Text>
          <Text style={styles.syncStatus}>
            {stats.pendingCollections > 0 ? `${stats.pendingCollections} en attente` : 'À jour'}
          </Text>
        </View>
        
        <TouchableOpacity 
          style={[styles.syncButton, syncing && styles.syncButtonDisabled]}
          onPress={handleSync}
          disabled={syncing}
        >
          {syncing ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.syncButtonText}>Synchroniser maintenant</Text>
          )}
        </TouchableOpacity>
      </View>

      {/* Modal de synchronisation */}
      <Modal
        visible={showSyncModal}
        transparent={true}
        animationType="fade"
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>
              {syncResult?.success ? '✅ Synchronisation réussie!' : '❌ Synchronisation échouée'}
            </Text>
            
            {syncResult?.success && (
              <View style={styles.syncResult}>
                <Text style={styles.syncResultText}>
                  ✅ {syncResult.synced} synchronisée(s)
                </Text>
                {syncResult.failed > 0 && (
                  <Text style={styles.syncResultText}>
                    ❌ {syncResult.failed} échec(s)
                  </Text>
                )}
              </View>
            )}
            
            <TouchableOpacity 
              style={styles.modalButton}
              onPress={() => setShowSyncModal(false)}
            >
              <Text style={styles.modalButtonText}>OK</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
};

const styles = {
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    padding: 20,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#7f8c8d',
  },
  header: {
    backgroundColor: '#27ae60',
    borderRadius: 10,
    padding: 20,
    marginBottom: 20,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  welcomeText: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.9,
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  logoutText: {
    fontSize: 24,
  },
  zonesContainer: {
    marginTop: 10,
  },
  zonesLabel: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
    marginBottom: 5,
  },
  zonesList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 5,
  },
  zoneBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  zoneText: {
    fontSize: 12,
    color: '#fff',
    fontWeight: '600',
  },
  noZonesText: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.7,
  },
  statsContainer: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 15,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  statCard: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  statNumber: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#27ae60',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 12,
    color: '#7f8c8d',
    textAlign: 'center',
  },
  actionsContainer: {
    marginBottom: 20,
  },
  actionButton: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 15,
    marginBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  actionButtonIcon: {
    fontSize: 24,
    marginRight: 15,
  },
  actionButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
    flex: 1,
  },
  actionButtonSubtext: {
    fontSize: 12,
    color: '#7f8c8d',
  },
  syncContainer: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  syncHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  syncTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
  },
  syncStatus: {
    fontSize: 14,
    color: '#7f8c8d',
  },
  syncButton: {
    backgroundColor: '#3498db',
    padding: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  syncButtonDisabled: {
    backgroundColor: '#95a5a6',
  },
  syncButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#fff',
    padding: 30,
    borderRadius: 10,
    alignItems: 'center',
    margin: 20,
    maxWidth: 300,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 15,
    textAlign: 'center',
  },
  syncResult: {
    marginBottom: 20,
  },
  syncResultText: {
    fontSize: 14,
    color: '#2c3e50',
    marginBottom: 5,
  },
  modalButton: {
    backgroundColor: '#27ae60',
    padding: 10,
    borderRadius: 8,
    paddingHorizontal: 20,
  },
  modalButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
};

export default HomeScreen;

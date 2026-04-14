import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity,
  ScrollView, ActivityIndicator, Alert, RefreshControl
} from 'react-native';
import api from '../services/api';
import StorageService from '../services/storage';

const STATUS_COLORS = { success: '#00a86b', failed: '#ef4444', pending: '#f59e0b' };
const STATUS_LABELS = { success: 'Succès', failed: 'Échec', pending: 'En attente' };

export default function SyncScreen() {
  const [syncing, setSyncing] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [pendingCount, setPendingCount] = useState(0);
  const [syncHistory, setSyncHistory] = useState([]);
  const [lastSync, setLastSync] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);

      // Collectes locales en attente
      const local = await StorageService.getPendingCollections();
      setPendingCount(local?.length || 0);

      // Historique depuis l'API
      const histResp = await api.getSyncHistory();
      if (histResp?.success && histResp.data?.data) {
        setSyncHistory(histResp.data.data);
        const first = histResp.data.data[0];
        if (first) setLastSync(first.sync_completed_at || first.sync_started_at);
      }

      // Stats
      const statsResp = await api.getStats();
      if (statsResp?.success) {
        setPendingCount(statsResp.data.pending_sync);
        if (statsResp.data.last_sync) setLastSync(statsResp.data.last_sync);
      }
    } catch (e) {
      console.warn('loadData sync:', e);
    } finally {
      setLoading(false);
    }
  };

  const handleSync = async () => {
    if (pendingCount === 0) {
      Alert.alert('Aucune donnée', 'Toutes les collectes sont déjà synchronisées.');
      return;
    }

    try {
      setSyncing(true);

      // 1. Synchro locale (collectes hors-ligne)
      const localPending = await StorageService.getPendingCollections();
      let localSynced = 0;
      for (const col of (localPending || [])) {
        try {
          await api.submitCollection(col);
          localSynced++;
        } catch {}
      }
      if (localSynced > 0) {
        await StorageService.clearPendingCollections();
      }

      // 2. Synchro serveur
      const result = await api.syncCollections();

      if (result?.success) {
        Alert.alert(
          '✅ Synchronisation réussie',
          `${result.data?.synced_count || localSynced} collectes synchronisées.`,
          [{ text: 'OK', onPress: loadData }]
        );
      } else {
        Alert.alert('Terminé', `${localSynced} collecte(s) locale(s) synchronisées.`);
        loadData();
      }
    } catch (e) {
      Alert.alert('Erreur', 'La synchronisation a échoué. Vérifiez votre connexion.');
    } finally {
      setSyncing(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadData();
    setRefreshing(false);
  };

  const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - d) / 60000);
    if (diff < 1) return 'Il y a quelques instants';
    if (diff < 60) return `Il y a ${diff} min`;
    if (diff < 1440) return `Il y a ${Math.floor(diff / 60)}h`;
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#00a86b" />
        <Text style={styles.loadingText}>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#00a86b" />}
    >
      {/* Statut de sync */}
      <View style={[styles.syncStatusCard, { borderLeftColor: pendingCount > 0 ? '#f59e0b' : '#00a86b' }]}>
        <View style={styles.syncStatusRow}>
          <View>
            <Text style={styles.syncStatusTitle}>
              {pendingCount > 0 ? `⚠️ ${pendingCount} collecte(s) en attente` : '✅ Tout est synchronisé'}
            </Text>
            <Text style={styles.syncStatusSub}>
              Dernière sync : {formatDate(lastSync)}
            </Text>
          </View>
          <TouchableOpacity
            style={[styles.syncBtn, syncing && styles.syncBtnDisabled]}
            onPress={handleSync}
            disabled={syncing}
          >
            {syncing
              ? <ActivityIndicator color="white" size="small" />
              : <Text style={styles.syncBtnText}>🔄 Sync</Text>
            }
          </TouchableOpacity>
        </View>
        {pendingCount > 0 && (
          <View style={styles.progressBar}>
            <View style={styles.progressBg}>
              <View style={[styles.progressFill, { width: '100%', backgroundColor: '#f59e0b' }]} />
            </View>
            <Text style={styles.progressText}>{pendingCount} en attente de connexion</Text>
          </View>
        )}
      </View>

      {/* Historique */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>📋 Historique des synchronisations</Text>

        {syncHistory.length === 0 ? (
          <View style={styles.emptyState}>
            <Text style={styles.emptyIcon}>🔄</Text>
            <Text style={styles.emptyText}>Aucune synchronisation effectuée</Text>
            <Text style={styles.emptySubText}>Les syncs apparaîtront ici après la première connexion.</Text>
          </View>
        ) : (
          syncHistory.map((item, idx) => (
            <View key={idx} style={styles.historyItem}>
              <View style={[styles.historyDot, { backgroundColor: STATUS_COLORS[item.status] || '#9ca3af' }]} />
              <View style={styles.historyContent}>
                <View style={styles.historyHeader}>
                  <Text style={styles.historyTitle}>
                    {STATUS_LABELS[item.status] || item.status}
                    {item.data_count > 0 && <Text style={styles.historyCount}> · {item.data_count} éléments</Text>}
                  </Text>
                  <Text style={styles.historyDate}>{formatDate(item.sync_completed_at || item.sync_started_at)}</Text>
                </View>
                {item.error_message && (
                  <Text style={styles.historyError}>{item.error_message}</Text>
                )}
                <Text style={styles.historyType}>
                  {item.sync_type === 'manual' ? '👆 Manuel' : '⚙️ Automatique'}
                </Text>
              </View>
            </View>
          ))
        )}
      </View>

      {/* Info */}
      <View style={styles.infoBox}>
        <Text style={styles.infoText}>
          💡 La synchronisation envoie automatiquement toutes les collectes enregistrées hors-ligne vers le serveur CSAR.
          Assurez-vous d'avoir une connexion internet.
        </Text>
      </View>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 12, color: '#6b7280', fontSize: 14 },

  syncStatusCard: {
    backgroundColor: 'white', margin: 12, borderRadius: 14, padding: 16,
    borderLeftWidth: 5, shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08, elevation: 3,
  },
  syncStatusRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  syncStatusTitle: { fontWeight: '700', fontSize: 15, color: '#1f2937' },
  syncStatusSub: { fontSize: 12, color: '#9ca3af', marginTop: 3 },
  syncBtn: {
    backgroundColor: '#00a86b', borderRadius: 10, paddingHorizontal: 18, paddingVertical: 10,
    shadowColor: '#00a86b', shadowOffset: { width: 0, height: 3 }, shadowOpacity: 0.3, elevation: 4,
  },
  syncBtnDisabled: { opacity: 0.6 },
  syncBtnText: { color: 'white', fontWeight: '700', fontSize: 14 },
  progressBar: { marginTop: 12 },
  progressBg: { height: 6, backgroundColor: '#f3f4f6', borderRadius: 3 },
  progressFill: { height: 6, borderRadius: 3 },
  progressText: { fontSize: 11, color: '#9ca3af', marginTop: 4 },

  section: {
    backgroundColor: 'white', margin: 12, borderRadius: 14, padding: 16,
    shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, elevation: 2,
  },
  sectionTitle: { fontWeight: '700', fontSize: 15, color: '#1f2937', marginBottom: 16 },

  emptyState: { alignItems: 'center', paddingVertical: 24 },
  emptyIcon: { fontSize: 40, marginBottom: 8 },
  emptyText: { fontWeight: '600', color: '#6b7280', fontSize: 14 },
  emptySubText: { color: '#9ca3af', fontSize: 12, textAlign: 'center', marginTop: 4 },

  historyItem: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 16 },
  historyDot: { width: 12, height: 12, borderRadius: 6, marginTop: 4, marginRight: 12, flexShrink: 0 },
  historyContent: { flex: 1 },
  historyHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  historyTitle: { fontWeight: '600', fontSize: 14, color: '#1f2937' },
  historyCount: { fontWeight: '400', color: '#6b7280' },
  historyDate: { fontSize: 11, color: '#9ca3af' },
  historyError: { fontSize: 12, color: '#ef4444', marginTop: 3 },
  historyType: { fontSize: 11, color: '#9ca3af', marginTop: 3 },

  infoBox: {
    marginHorizontal: 12, marginBottom: 12, backgroundColor: '#eff6ff',
    borderRadius: 10, padding: 14, borderLeftWidth: 3, borderLeftColor: '#0078d4',
  },
  infoText: { fontSize: 12, color: '#1e40af', lineHeight: 18 },
});

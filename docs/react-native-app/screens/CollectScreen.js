import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, TextInput, TouchableOpacity, ScrollView,
  StyleSheet, Alert, ActivityIndicator, Modal, FlatList,
  KeyboardAvoidingView, Platform
} from 'react-native';
import api from '../services/api';
import StorageService from '../services/storage';
import LocationService from '../services/location';

const STATUS_COLORS = {
  active: '#00a86b',
  collecting: '#0078d4',
  paused: '#f59e0b',
  offline: '#6b7280',
};

export default function CollectScreen({ navigation }) {
  const [markets, setMarkets] = useState([]);
  const [products, setProducts] = useState([]);
  const [selectedMarket, setSelectedMarket] = useState(null);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [form, setForm] = useState({
    price: '',
    retail_price: '',
    wholesale_price: '',
    provenance: '',
    quantity_collected: '',
    collection_date: new Date().toISOString().split('T')[0],
  });
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [showMarketPicker, setShowMarketPicker] = useState(false);
  const [showProductPicker, setShowProductPicker] = useState(false);
  const [marketSearch, setMarketSearch] = useState('');
  const [productSearch, setProductSearch] = useState('');
  const [location, setLocation] = useState(null);
  const [collectionsToday, setCollectionsToday] = useState(0);
  const locationInterval = useRef(null);

  useEffect(() => {
    loadReferenceData();
    startLocationTracking();
    return () => {
      if (locationInterval.current) clearInterval(locationInterval.current);
    };
  }, []);

  const loadReferenceData = async () => {
    try {
      setLoadingData(true);
      // Essayer depuis le cache d'abord
      const [cachedMarkets, cachedProducts] = await Promise.all([
        StorageService.getMarkets(),
        StorageService.getProducts(),
      ]);
      if (cachedMarkets?.length) setMarkets(cachedMarkets);
      if (cachedProducts?.length) setProducts(cachedProducts);

      // Mettre à jour depuis l'API
      const [mkResp, pdResp] = await Promise.all([
        api.getMarkets(),
        api.getProducts(),
      ]);
      if (mkResp.success) {
        setMarkets(mkResp.data);
        await StorageService.saveMarkets(mkResp.data);
      }
      if (pdResp.success) {
        setProducts(pdResp.data);
        await StorageService.saveProducts(pdResp.data);
      }

      // Stats du jour
      const statsResp = await api.getStats();
      if (statsResp.success) setCollectionsToday(statsResp.data.today);
    } catch (e) {
      console.warn('loadReferenceData:', e);
    } finally {
      setLoadingData(false);
    }
  };

  const startLocationTracking = async () => {
    try {
      const pos = await LocationService.getCurrentPosition();
      setLocation(pos);
      await api.updateLocation({
        latitude: pos.latitude,
        longitude: pos.longitude,
        accuracy: pos.accuracy,
        status: 'active',
        collectionsToday,
      });

      // Envoyer la position toutes les 2 minutes
      locationInterval.current = setInterval(async () => {
        try {
          const p = await LocationService.getCurrentPosition();
          setLocation(p);
          await api.updateLocation({
            latitude: p.latitude,
            longitude: p.longitude,
            accuracy: p.accuracy,
            status: selectedMarket ? 'collecting' : 'active',
            currentMarket: selectedMarket?.name,
            collectionsToday,
          });
        } catch {}
      }, 120000);
    } catch (e) {
      console.warn('Location:', e);
    }
  };

  const handleSubmit = async () => {
    if (!selectedMarket) { Alert.alert('Erreur', 'Sélectionnez un marché'); return; }
    if (!selectedProduct) { Alert.alert('Erreur', 'Sélectionnez un produit'); return; }
    if (!form.price && !form.retail_price && !form.wholesale_price) {
      Alert.alert('Erreur', 'Entrez au moins un prix');
      return;
    }

    try {
      setLoading(true);

      const payload = {
        market_id: selectedMarket.id,
        product_id: selectedProduct.id,
        price: form.price ? parseFloat(form.price) : null,
        retail_price: form.retail_price ? parseFloat(form.retail_price) : null,
        wholesale_price: form.wholesale_price ? parseFloat(form.wholesale_price) : null,
        provenance: form.provenance || null,
        quantity_collected: form.quantity_collected ? parseFloat(form.quantity_collected) : null,
        collection_date: form.collection_date,
        latitude: location?.latitude || null,
        longitude: location?.longitude || null,
      };

      let result;
      try {
        result = await api.submitCollection(payload);
      } catch (apiError) {
        // Sauvegarder hors-ligne si l'API échoue
        await StorageService.savePendingCollection(payload);
        Alert.alert('Hors ligne', 'Collecte sauvegardée localement. Elle sera synchronisée dès que le réseau est disponible.');
        resetForm();
        setCollectionsToday(prev => prev + 1);
        return;
      }

      if (result.success) {
        setCollectionsToday(prev => prev + 1);
        Alert.alert('✅ Succès', `Collecte enregistrée.\nMarché: ${selectedMarket.name}\nProduit: ${selectedProduct.name}`, [
          { text: 'Nouvelle collecte', onPress: resetForm },
          { text: 'Retour', onPress: () => navigation.goBack() },
        ]);
      }
    } catch (e) {
      Alert.alert('Erreur', 'Impossible d\'enregistrer la collecte');
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setSelectedProduct(null);
    setForm({ price: '', retail_price: '', wholesale_price: '', provenance: '', quantity_collected: '', collection_date: new Date().toISOString().split('T')[0] });
  };

  const filteredMarkets = markets.filter(m =>
    m.name.toLowerCase().includes(marketSearch.toLowerCase()) ||
    (m.commune || '').toLowerCase().includes(marketSearch.toLowerCase())
  );

  const filteredProducts = products.filter(p =>
    p.name.toLowerCase().includes(productSearch.toLowerCase()) ||
    (p.category || '').toLowerCase().includes(productSearch.toLowerCase())
  );

  if (loadingData) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#00a86b" />
        <Text style={styles.loadingText}>Chargement des données...</Text>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView style={styles.container} keyboardShouldPersistTaps="handled">

        {/* Header stats */}
        <View style={styles.statsBar}>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{collectionsToday}</Text>
            <Text style={styles.statLabel}>Collectes aujourd'hui</Text>
          </View>
          <View style={styles.statItem}>
            <View style={[styles.dot, { backgroundColor: location ? '#00a86b' : '#ef4444' }]} />
            <Text style={styles.statLabel}>{location ? 'GPS actif' : 'GPS inactif'}</Text>
          </View>
          <View style={styles.statItem}>
            <Text style={styles.statValue}>{form.collection_date}</Text>
            <Text style={styles.statLabel}>Date collecte</Text>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>📍 Marché</Text>

          {/* Sélecteur Marché */}
          <TouchableOpacity style={[styles.picker, selectedMarket && styles.pickerSelected]} onPress={() => setShowMarketPicker(true)}>
            <Text style={selectedMarket ? styles.pickerTextSelected : styles.pickerPlaceholder}>
              {selectedMarket ? `${selectedMarket.name} — ${selectedMarket.commune || ''}` : 'Sélectionner un marché...'}
            </Text>
            <Text style={styles.pickerArrow}>▼</Text>
          </TouchableOpacity>

          {/* Sélecteur Produit */}
          <Text style={[styles.sectionTitle, { marginTop: 16 }]}>🛒 Produit</Text>
          <TouchableOpacity style={[styles.picker, selectedProduct && styles.pickerSelected]} onPress={() => setShowProductPicker(true)}>
            <Text style={selectedProduct ? styles.pickerTextSelected : styles.pickerPlaceholder}>
              {selectedProduct ? `${selectedProduct.name} (${selectedProduct.unit || ''})` : 'Sélectionner un produit...'}
            </Text>
            <Text style={styles.pickerArrow}>▼</Text>
          </TouchableOpacity>
          {selectedProduct?.category && (
            <Text style={styles.categoryBadge}>{selectedProduct.category}</Text>
          )}
        </View>

        {/* Prix */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>💰 Prix (FCFA)</Text>
          <View style={styles.priceRow}>
            <View style={styles.priceField}>
              <Text style={styles.label}>Producteur</Text>
              <TextInput style={styles.input} keyboardType="numeric" placeholder="0" value={form.price} onChangeText={v => setForm(f => ({ ...f, price: v }))} />
            </View>
            <View style={styles.priceField}>
              <Text style={styles.label}>Détail</Text>
              <TextInput style={styles.input} keyboardType="numeric" placeholder="0" value={form.retail_price} onChangeText={v => setForm(f => ({ ...f, retail_price: v }))} />
            </View>
            <View style={styles.priceField}>
              <Text style={styles.label}>½ Gros</Text>
              <TextInput style={styles.input} keyboardType="numeric" placeholder="0" value={form.wholesale_price} onChangeText={v => setForm(f => ({ ...f, wholesale_price: v }))} />
            </View>
          </View>
        </View>

        {/* Infos complémentaires */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>ℹ️ Informations complémentaires</Text>
          <Text style={styles.label}>Provenance / Origine</Text>
          <TextInput style={styles.input} placeholder="Ex: Local, Importé, Mali..." value={form.provenance} onChangeText={v => setForm(f => ({ ...f, provenance: v }))} />
          <Text style={[styles.label, { marginTop: 12 }]}>Quantité disponible</Text>
          <TextInput style={styles.input} keyboardType="numeric" placeholder="0" value={form.quantity_collected} onChangeText={v => setForm(f => ({ ...f, quantity_collected: v }))} />
        </View>

        {/* Bouton submit */}
        <TouchableOpacity style={[styles.submitBtn, loading && styles.submitBtnDisabled]} onPress={handleSubmit} disabled={loading}>
          {loading
            ? <ActivityIndicator color="white" />
            : <Text style={styles.submitBtnText}>✅ Enregistrer la collecte</Text>
          }
        </TouchableOpacity>

        <View style={{ height: 40 }} />
      </ScrollView>

      {/* Modal Marchés */}
      <Modal visible={showMarketPicker} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modal}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Choisir un marché</Text>
              <TouchableOpacity onPress={() => { setShowMarketPicker(false); setMarketSearch(''); }}>
                <Text style={styles.modalClose}>✕</Text>
              </TouchableOpacity>
            </View>
            <TextInput style={styles.searchInput} placeholder="Rechercher..." value={marketSearch} onChangeText={setMarketSearch} autoFocus />
            <FlatList
              data={filteredMarkets}
              keyExtractor={item => String(item.id)}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.listItem} onPress={() => { setSelectedMarket(item); setShowMarketPicker(false); setMarketSearch(''); }}>
                  <Text style={styles.listItemTitle}>{item.name}</Text>
                  <Text style={styles.listItemSub}>{item.commune} · {item.market_type}</Text>
                </TouchableOpacity>
              )}
              ListEmptyComponent={<Text style={styles.emptyText}>Aucun marché trouvé</Text>}
            />
          </View>
        </View>
      </Modal>

      {/* Modal Produits */}
      <Modal visible={showProductPicker} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modal}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Choisir un produit</Text>
              <TouchableOpacity onPress={() => { setShowProductPicker(false); setProductSearch(''); }}>
                <Text style={styles.modalClose}>✕</Text>
              </TouchableOpacity>
            </View>
            <TextInput style={styles.searchInput} placeholder="Rechercher..." value={productSearch} onChangeText={setProductSearch} autoFocus />
            <FlatList
              data={filteredProducts}
              keyExtractor={item => String(item.id)}
              renderItem={({ item }) => (
                <TouchableOpacity style={styles.listItem} onPress={() => { setSelectedProduct(item); setShowProductPicker(false); setProductSearch(''); }}>
                  <Text style={styles.listItemTitle}>{item.name}</Text>
                  <Text style={styles.listItemSub}>{item.category} · {item.unit}</Text>
                </TouchableOpacity>
              )}
              ListEmptyComponent={<Text style={styles.emptyText}>Aucun produit trouvé</Text>}
            />
          </View>
        </View>
      </Modal>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f8fafc' },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 12, color: '#6b7280', fontSize: 14 },
  statsBar: { flexDirection: 'row', backgroundColor: '#00a86b', padding: 12, justifyContent: 'space-around', alignItems: 'center' },
  statItem: { alignItems: 'center' },
  statValue: { color: 'white', fontWeight: '700', fontSize: 18 },
  statLabel: { color: 'rgba(255,255,255,0.8)', fontSize: 11 },
  dot: { width: 10, height: 10, borderRadius: 5, marginBottom: 4 },
  section: { backgroundColor: 'white', margin: 12, borderRadius: 12, padding: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, elevation: 2 },
  sectionTitle: { fontWeight: '700', fontSize: 15, color: '#1f2937', marginBottom: 12 },
  picker: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderWidth: 1.5, borderColor: '#e5e7eb', borderRadius: 10, padding: 14, backgroundColor: '#f9fafb' },
  pickerSelected: { borderColor: '#00a86b', backgroundColor: '#f0fdf4' },
  pickerPlaceholder: { color: '#9ca3af', fontSize: 14, flex: 1 },
  pickerTextSelected: { color: '#1f2937', fontSize: 14, flex: 1, fontWeight: '500' },
  pickerArrow: { color: '#9ca3af', fontSize: 12 },
  categoryBadge: { marginTop: 6, fontSize: 12, color: '#0078d4', backgroundColor: '#eff6ff', alignSelf: 'flex-start', paddingHorizontal: 10, paddingVertical: 3, borderRadius: 20 },
  priceRow: { flexDirection: 'row', gap: 8 },
  priceField: { flex: 1 },
  label: { fontSize: 12, color: '#6b7280', marginBottom: 4, fontWeight: '500' },
  input: { borderWidth: 1.5, borderColor: '#e5e7eb', borderRadius: 8, padding: 10, fontSize: 15, backgroundColor: '#f9fafb', color: '#1f2937' },
  submitBtn: { backgroundColor: '#00a86b', margin: 12, borderRadius: 14, padding: 18, alignItems: 'center', shadowColor: '#00a86b', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, elevation: 6 },
  submitBtnDisabled: { opacity: 0.6 },
  submitBtnText: { color: 'white', fontWeight: '700', fontSize: 16 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modal: { backgroundColor: 'white', borderTopLeftRadius: 20, borderTopRightRadius: 20, maxHeight: '85%' },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 16, borderBottomWidth: 1, borderBottomColor: '#f3f4f6' },
  modalTitle: { fontWeight: '700', fontSize: 16, color: '#1f2937' },
  modalClose: { fontSize: 18, color: '#6b7280', padding: 4 },
  searchInput: { margin: 12, borderWidth: 1.5, borderColor: '#e5e7eb', borderRadius: 10, padding: 12, fontSize: 14 },
  listItem: { padding: 14, borderBottomWidth: 1, borderBottomColor: '#f3f4f6' },
  listItemTitle: { fontWeight: '600', fontSize: 14, color: '#1f2937' },
  listItemSub: { fontSize: 12, color: '#9ca3af', marginTop: 2 },
  emptyText: { textAlign: 'center', color: '#9ca3af', padding: 20 },
});

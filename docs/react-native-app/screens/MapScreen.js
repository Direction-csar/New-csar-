import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity,
  ActivityIndicator, Alert, ScrollView, Dimensions
} from 'react-native';
import MapView, { Marker, Callout, PROVIDER_GOOGLE } from 'react-native-maps';
import api from '../services/api';
import LocationService from '../services/location';
import StorageService from '../services/storage';

const { width, height } = Dimensions.get('window');

const STATUS_COLORS = {
  active: '#00a86b',
  collecting: '#0078d4',
  paused: '#f59e0b',
  offline: '#9ca3af',
};

const STATUS_LABELS = {
  active: 'Actif',
  collecting: 'En collecte',
  paused: 'En pause',
  offline: 'Hors ligne',
};

const MARKET_COLORS = {
  rural: '#16a34a',
  urban: '#0078d4',
  periodic: '#7c3aed',
};

export default function MapScreen({ navigation }) {
  const mapRef = useRef(null);
  const locationInterval = useRef(null);

  const [myLocation, setMyLocation] = useState(null);
  const [markets, setMarkets] = useState([]);
  const [status, setStatus] = useState('active');
  const [currentMarket, setCurrentMarket] = useState(null);
  const [collectionsToday, setCollectionsToday] = useState(0);
  const [loading, setLoading] = useState(true);
  const [mapType, setMapType] = useState('standard');
  const [showMarkets, setShowMarkets] = useState(true);

  // Région initiale : Dakar, Sénégal
  const [region, setRegion] = useState({
    latitude: 14.6928,
    longitude: -17.4467,
    latitudeDelta: 0.5,
    longitudeDelta: 0.5,
  });

  useEffect(() => {
    initialize();
    return () => {
      if (locationInterval.current) clearInterval(locationInterval.current);
      // Passer hors ligne à la sortie
      if (myLocation) {
        api.updateLocation({
          latitude: myLocation.latitude,
          longitude: myLocation.longitude,
          status: 'offline',
          collectionsToday,
        }).catch(() => {});
      }
    };
  }, []);

  const initialize = async () => {
    try {
      setLoading(true);

      // Charger marchés depuis cache puis API
      const cached = await StorageService.getMarkets();
      if (cached?.length) setMarkets(cached);

      const [mkResp, statsResp] = await Promise.allSettled([
        api.getMarkets(),
        api.getStats(),
      ]);

      if (mkResp.status === 'fulfilled' && mkResp.value.success) {
        setMarkets(mkResp.value.data);
      }
      if (statsResp.status === 'fulfilled' && statsResp.value.success) {
        setCollectionsToday(statsResp.value.data.today);
      }

      // Position initiale
      await refreshLocation();

      // Mise à jour position toutes les 90s
      locationInterval.current = setInterval(refreshLocation, 90000);
    } catch (e) {
      Alert.alert('Erreur', 'Impossible de charger la carte');
    } finally {
      setLoading(false);
    }
  };

  const refreshLocation = async () => {
    try {
      const pos = await LocationService.getCurrentPosition();
      setMyLocation(pos);

      setRegion(prev => ({
        ...prev,
        latitude: pos.latitude,
        longitude: pos.longitude,
      }));

      await api.updateLocation({
        latitude: pos.latitude,
        longitude: pos.longitude,
        accuracy: pos.accuracy,
        status,
        currentMarket: currentMarket?.name,
        collectionsToday,
      });
    } catch (e) {
      console.warn('refreshLocation:', e);
    }
  };

  const centerOnMe = () => {
    if (!myLocation) { Alert.alert('GPS', 'Position non disponible'); return; }
    mapRef.current?.animateToRegion({
      latitude: myLocation.latitude,
      longitude: myLocation.longitude,
      latitudeDelta: 0.02,
      longitudeDelta: 0.02,
    }, 500);
  };

  const selectMarket = (market) => {
    setCurrentMarket(market);
    setStatus('collecting');
    mapRef.current?.animateToRegion({
      latitude: parseFloat(market.latitude),
      longitude: parseFloat(market.longitude),
      latitudeDelta: 0.02,
      longitudeDelta: 0.02,
    }, 500);
  };

  const changeStatus = (newStatus) => {
    setStatus(newStatus);
    if (myLocation) {
      api.updateLocation({
        latitude: myLocation.latitude,
        longitude: myLocation.longitude,
        status: newStatus,
        currentMarket: currentMarket?.name,
        collectionsToday,
      }).catch(() => {});
    }
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#00a86b" />
        <Text style={styles.loadingText}>Chargement de la carte...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Carte */}
      <MapView
        ref={mapRef}
        style={styles.map}
        provider={PROVIDER_GOOGLE}
        mapType={mapType}
        initialRegion={region}
        showsUserLocation={true}
        showsMyLocationButton={false}
        showsCompass={true}
        showsScale={true}
      >
        {/* Marqueur position actuelle */}
        {myLocation && (
          <Marker
            coordinate={{ latitude: myLocation.latitude, longitude: myLocation.longitude }}
            anchor={{ x: 0.5, y: 0.5 }}
          >
            <View style={[styles.myMarker, { borderColor: STATUS_COLORS[status] }]}>
              <View style={[styles.myMarkerDot, { backgroundColor: STATUS_COLORS[status] }]} />
            </View>
          </Marker>
        )}

        {/* Marchés */}
        {showMarkets && markets.filter(m => m.latitude && m.longitude).map(market => (
          <Marker
            key={market.id}
            coordinate={{ latitude: parseFloat(market.latitude), longitude: parseFloat(market.longitude) }}
            onPress={() => selectMarket(market)}
          >
            <View style={[styles.marketMarker, { backgroundColor: MARKET_COLORS[market.market_type] || '#6b7280' }]}>
              <Text style={styles.marketMarkerText}>🏪</Text>
            </View>
            <Callout tooltip>
              <View style={styles.callout}>
                <Text style={styles.calloutTitle}>{market.name}</Text>
                <Text style={styles.calloutSub}>{market.commune}</Text>
                <Text style={styles.calloutType}>{market.market_type}</Text>
                <TouchableOpacity style={styles.calloutBtn} onPress={() => navigation.navigate('Collect')}>
                  <Text style={styles.calloutBtnText}>Démarrer la collecte</Text>
                </TouchableOpacity>
              </View>
            </Callout>
          </Marker>
        ))}
      </MapView>

      {/* Barre de statut */}
      <View style={styles.statusBar}>
        <View style={[styles.statusDot, { backgroundColor: STATUS_COLORS[status] }]} />
        <Text style={styles.statusText}>{STATUS_LABELS[status]}</Text>
        {currentMarket && <Text style={styles.currentMarketText}> · {currentMarket.name}</Text>}
        <View style={{ flex: 1 }} />
        <Text style={styles.statsText}>📊 {collectionsToday} collectes</Text>
      </View>

      {/* Contrôles carte */}
      <View style={styles.mapControls}>
        <TouchableOpacity style={styles.controlBtn} onPress={centerOnMe}>
          <Text style={styles.controlIcon}>📍</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.controlBtn} onPress={() => setMapType(t => t === 'standard' ? 'satellite' : 'standard')}>
          <Text style={styles.controlIcon}>{mapType === 'standard' ? '🛰️' : '🗺️'}</Text>
        </TouchableOpacity>
        <TouchableOpacity style={[styles.controlBtn, showMarkets && styles.controlBtnActive]} onPress={() => setShowMarkets(v => !v)}>
          <Text style={styles.controlIcon}>🏪</Text>
        </TouchableOpacity>
      </View>

      {/* Sélecteur statut */}
      <View style={styles.statusSelector}>
        {Object.entries(STATUS_LABELS).filter(([k]) => k !== 'offline').map(([key, label]) => (
          <TouchableOpacity
            key={key}
            style={[styles.statusOption, status === key && { backgroundColor: STATUS_COLORS[key], borderColor: STATUS_COLORS[key] }]}
            onPress={() => changeStatus(key)}
          >
            <Text style={[styles.statusOptionText, status === key && { color: 'white' }]}>{label}</Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Bouton aller à la collecte */}
      <TouchableOpacity style={styles.collectBtn} onPress={() => navigation.navigate('Collect')}>
        <Text style={styles.collectBtnText}>📝 Saisir des prix</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  centerContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#f8fafc' },
  loadingText: { marginTop: 12, color: '#6b7280', fontSize: 14 },
  map: { flex: 1 },

  statusBar: {
    position: 'absolute', top: 0, left: 0, right: 0,
    backgroundColor: 'white', flexDirection: 'row', alignItems: 'center',
    paddingHorizontal: 16, paddingVertical: 10,
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, elevation: 4,
  },
  statusDot: { width: 10, height: 10, borderRadius: 5, marginRight: 6 },
  statusText: { fontWeight: '600', fontSize: 13, color: '#1f2937' },
  currentMarketText: { fontSize: 13, color: '#6b7280' },
  statsText: { fontSize: 13, color: '#00a86b', fontWeight: '600' },

  mapControls: {
    position: 'absolute', right: 12, top: 60,
    gap: 8,
  },
  controlBtn: {
    backgroundColor: 'white', width: 44, height: 44, borderRadius: 22,
    justifyContent: 'center', alignItems: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.15, elevation: 4,
    marginBottom: 8,
  },
  controlBtnActive: { backgroundColor: '#00a86b' },
  controlIcon: { fontSize: 18 },

  myMarker: {
    width: 24, height: 24, borderRadius: 12, borderWidth: 3,
    backgroundColor: 'white', justifyContent: 'center', alignItems: 'center',
  },
  myMarkerDot: { width: 10, height: 10, borderRadius: 5 },

  marketMarker: {
    width: 32, height: 32, borderRadius: 8,
    justifyContent: 'center', alignItems: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.2, elevation: 3,
  },
  marketMarkerText: { fontSize: 16 },

  callout: {
    backgroundColor: 'white', borderRadius: 12, padding: 12,
    minWidth: 160, shadowColor: '#000', shadowOpacity: 0.15, elevation: 6,
  },
  calloutTitle: { fontWeight: '700', fontSize: 14, color: '#1f2937' },
  calloutSub: { fontSize: 12, color: '#6b7280', marginTop: 2 },
  calloutType: { fontSize: 11, color: '#0078d4', marginTop: 4, textTransform: 'capitalize' },
  calloutBtn: { backgroundColor: '#00a86b', borderRadius: 8, padding: 8, marginTop: 8, alignItems: 'center' },
  calloutBtnText: { color: 'white', fontWeight: '600', fontSize: 12 },

  statusSelector: {
    position: 'absolute', bottom: 80, left: 12, right: 12,
    flexDirection: 'row', gap: 8, justifyContent: 'center',
  },
  statusOption: {
    flex: 1, paddingVertical: 8, borderRadius: 20, borderWidth: 1.5,
    borderColor: '#e5e7eb', backgroundColor: 'white', alignItems: 'center',
  },
  statusOptionText: { fontSize: 11, fontWeight: '600', color: '#6b7280' },

  collectBtn: {
    position: 'absolute', bottom: 24, left: 40, right: 40,
    backgroundColor: '#00a86b', borderRadius: 14, padding: 16,
    alignItems: 'center',
    shadowColor: '#00a86b', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.35, elevation: 8,
  },
  collectBtnText: { color: 'white', fontWeight: '700', fontSize: 15 },
});

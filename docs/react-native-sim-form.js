import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
  Image,
  PermissionsAndroid,
  Platform,
  Modal
} from 'react-native';
import { launchCamera, launchImageLibrary } from 'react-native-image-picker';
import Geolocation from 'react-native-geolocation-service';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../services/api';

const SimCollectionForm = ({ navigation }) => {
  const [loading, setLoading] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  
  // Données du formulaire
  const [formData, setFormData] = useState({
    market_id: '',
    product_id: '',
    price: '',
    retail_price: '',
    wholesale_price: '',
    collection_date: new Date().toISOString().split('T')[0],
    latitude: null,
    longitude: null,
    photos: [],
    notes: ''
  });

  // Données de référence
  const [markets, setMarkets] = useState([]);
  const [products, setProducts] = useState([]);
  const [selectedMarket, setSelectedMarket] = useState(null);
  const [selectedProduct, setSelectedProduct] = useState(null);

  useEffect(() => {
    loadData();
    requestLocationPermission();
  }, []);

  const loadData = async () => {
    try {
      const [marketsRes, productsRes] = await Promise.all([
        api.get('/markets'),
        api.get('/products')
      ]);
      
      setMarkets(marketsRes.data.data || []);
      setProducts(productsRes.data.data || []);
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger les données');
    }
  };

  const requestLocationPermission = async () => {
    if (Platform.OS === 'android') {
      try {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION
        );
        if (granted === PermissionsAndroid.RESULTS.GRANTED) {
          getCurrentLocation();
        }
      } catch (err) {
        console.warn(err);
      }
    } else {
      getCurrentLocation();
    }
  };

  const getCurrentLocation = () => {
    Geolocation.getCurrentPosition(
      (position) => {
        setFormData(prev => ({
          ...prev,
          latitude: position.coords.latitude,
          longitude: position.coords.longitude
        }));
      },
      (error) => {
        console.log('Location error:', error);
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
    );
  };

  const handlePhotoCapture = () => {
    const options = {
      mediaType: 'photo',
      quality: 0.7,
      maxWidth: 800,
      maxHeight: 600
    };

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
  };

  const handlePhotoSelection = () => {
    const options = {
      mediaType: 'photo',
      quality: 0.7,
      maxWidth: 800,
      maxHeight: 600
    };

    launchImageLibrary(options, (response) => {
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
  };

  const removePhoto = (index) => {
    setFormData(prev => ({
      ...prev,
      photos: prev.photos.filter((_, i) => i !== index)
    }));
  };

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

  const handleSubmit = async () => {
    if (!validateForm()) return;

    setLoading(true);
    
    try {
      // Sauvegarder en local d'abord (mode hors-ligne)
      await AsyncStorage.setItem('pending_collection_' + Date.now(), JSON.stringify(formData));
      
      // Tenter d'envoyer au serveur
      const response = await api.post('/collections', formData);
      
      if (response.data.success) {
        setShowSuccessModal(true);
        resetForm();
        
        // Supprimer de la sauvegarde locale si envoyé avec succès
        AsyncStorage.removeItem('pending_collection_' + Date.now());
      }
    } catch (error) {
      // En cas d'erreur, les données restent en local pour synchronisation ultérieure
      Alert.alert(
        'Sauvegardé localement',
        'Les données ont été sauvegardées localement et seront synchronisées lorsque la connexion sera disponible.',
        [{ text: 'OK' }]
      );
    } finally {
      setLoading(false);
    }
  };

  const resetForm = () => {
    setFormData({
      market_id: '',
      product_id: '',
      price: '',
      retail_price: '',
      wholesale_price: '',
      collection_date: new Date().toISOString().split('T')[0],
      latitude: null,
      longitude: null,
      photos: [],
      notes: ''
    });
    setSelectedMarket(null);
    setSelectedProduct(null);
    getCurrentLocation();
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>📊 Collecte SIM</Text>
        <Text style={styles.subtitle}>Enregistrez les prix du marché</Text>
      </View>

      {/* Sélection du marché */}
      <View style={styles.section}>
        <Text style={styles.label}>🏪 Marché *</Text>
        <TouchableOpacity 
          style={styles.selector}
          onPress={() => navigation.navigate('MarketSelector', { 
            markets, 
            onSelect: (market) => {
              setSelectedMarket(market);
              setFormData(prev => ({ ...prev, market_id: market.id }));
            }
          })}
        >
          <Text style={styles.selectorText}>
            {selectedMarket ? selectedMarket.name : 'Sélectionner un marché'}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Sélection du produit */}
      <View style={styles.section}>
        <Text style={styles.label}>📦 Produit *</Text>
        <TouchableOpacity 
          style={styles.selector}
          onPress={() => navigation.navigate('ProductSelector', { 
            products, 
            onSelect: (product) => {
              setSelectedProduct(product);
              setFormData(prev => ({ ...prev, product_id: product.id }));
            }
          })}
        >
          <Text style={styles.selectorText}>
            {selectedProduct ? selectedProduct.name : 'Sélectionner un produit'}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Prix */}
      <View style={styles.section}>
        <Text style={styles.label}>💰 Prix de vente *</Text>
        <TextInput
          style={styles.input}
          value={formData.price}
          onChangeText={(text) => setFormData(prev => ({ ...prev, price: text }))}
          placeholder="Ex: 350.50"
          keyboardType="numeric"
        />
        
        <Text style={styles.label}>Prix détail</Text>
        <TextInput
          style={styles.input}
          value={formData.retail_price}
          onChangeText={(text) => setFormData(prev => ({ ...prev, retail_price: text }))}
          placeholder="Ex: 400.00"
          keyboardType="numeric"
        />
        
        <Text style={styles.label}>Prix gros</Text>
        <TextInput
          style={styles.input}
          value={formData.wholesale_price}
          onChangeText={(text) => setFormData(prev => ({ ...prev, wholesale_price: text }))}
          placeholder="Ex: 320.00"
          keyboardType="numeric"
        />
      </View>

      {/* Date */}
      <View style={styles.section}>
        <Text style={styles.label}>📅 Date de collecte</Text>
        <TouchableOpacity style={styles.selector}>
          <Text style={styles.selectorText}>{formData.collection_date}</Text>
        </TouchableOpacity>
      </View>

      {/* Localisation */}
      <View style={styles.section}>
        <Text style={styles.label}>📍 Localisation</Text>
        <View style={styles.locationContainer}>
          <Text style={styles.locationText}>
            {formData.latitude && formData.longitude 
              ? `Lat: ${formData.latitude.toFixed(6)}, Lng: ${formData.longitude.toFixed(6)}`
              : 'Localisation non disponible'
            }
          </Text>
          <TouchableOpacity 
            style={styles.refreshButton}
            onPress={getCurrentLocation}
          >
            <Text style={styles.refreshButtonText}>🔄</Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Photos */}
      <View style={styles.section}>
        <Text style={styles.label}>📸 Photos du marché</Text>
        <View style={styles.photoButtons}>
          <TouchableOpacity style={styles.photoButton} onPress={handlePhotoCapture}>
            <Text style={styles.photoButtonText}>📷 Prendre photo</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.photoButton} onPress={handlePhotoSelection}>
            <Text style={styles.photoButtonText}>📁 Galerie</Text>
          </TouchableOpacity>
        </View>
        
        {formData.photos.length > 0 && (
          <View style={styles.photosContainer}>
            {formData.photos.map((photo, index) => (
              <View key={index} style={styles.photoItem}>
                <Image source={{ uri: photo.uri }} style={styles.photo} />
                <TouchableOpacity 
                  style={styles.removePhotoButton}
                  onPress={() => removePhoto(index)}
                >
                  <Text style={styles.removePhotoText}>❌</Text>
                </TouchableOpacity>
              </View>
            ))}
          </View>
        )}
      </View>

      {/* Notes */}
      <View style={styles.section}>
        <Text style={styles.label}>📝 Notes (optionnel)</Text>
        <TextInput
          style={[styles.input, styles.textArea]}
          value={formData.notes}
          onChangeText={(text) => setFormData(prev => ({ ...prev, notes: text }))}
          placeholder="Observations sur le marché..."
          multiline
          numberOfLines={3}
        />
      </View>

      {/* Bouton de soumission */}
      <TouchableOpacity 
        style={[styles.submitButton, loading && styles.disabledButton]}
        onPress={handleSubmit}
        disabled={loading}
      >
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.submitButtonText}>💾 Enregistrer la collecte</Text>
        )}
      </TouchableOpacity>

      {/* Modal de succès */}
      <Modal
        visible={showSuccessModal}
        transparent={true}
        animationType="fade"
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>✅ Succès!</Text>
            <Text style={styles.modalText}>Les données ont été enregistrées avec succès</Text>
            <TouchableOpacity 
              style={styles.modalButton}
              onPress={() => setShowSuccessModal(false)}
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
  header: {
    alignItems: 'center',
    marginBottom: 30,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 16,
    color: '#7f8c8d',
  },
  section: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    marginBottom: 10,
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  selector: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    marginBottom: 10,
  },
  selectorText: {
    fontSize: 16,
    color: '#2c3e50',
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
  },
  locationText: {
    flex: 1,
    fontSize: 14,
    color: '#2c3e50',
  },
  refreshButton: {
    padding: 5,
  },
  refreshButtonText: {
    fontSize: 20,
  },
  photoButtons: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 10,
  },
  photoButton: {
    flex: 1,
    backgroundColor: '#3498db',
    padding: 10,
    borderRadius: 8,
    alignItems: 'center',
  },
  photoButtonText: {
    color: '#fff',
    fontWeight: '600',
  },
  photosContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  photoItem: {
    position: 'relative',
  },
  photo: {
    width: 80,
    height: 80,
    borderRadius: 8,
  },
  removePhotoButton: {
    position: 'absolute',
    top: -5,
    right: -5,
    backgroundColor: 'rgba(255,255,255,0.9)',
    borderRadius: 10,
    width: 20,
    height: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  removePhotoText: {
    fontSize: 12,
  },
  submitButton: {
    backgroundColor: '#27ae60',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 20,
  },
  disabledButton: {
    backgroundColor: '#95a5a6',
  },
  submitButtonText: {
    color: '#fff',
    fontSize: 16,
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
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 10,
  },
  modalText: {
    fontSize: 16,
    textAlign: 'center',
    marginBottom: 20,
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

export default SimCollectionForm;

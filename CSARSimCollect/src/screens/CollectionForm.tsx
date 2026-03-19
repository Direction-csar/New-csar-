import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
  Modal,
  StyleSheet
} from 'react-native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import api from '../services/api';

type CollectionFormNavigationProp = NativeStackNavigationProp<any, 'CollectionForm'>;

interface Props {
  navigation: CollectionFormNavigationProp;
}

interface FormData {
  market_id: string;
  product_id: string;
  price: string;
  retail_price: string;
  wholesale_price: string;
  collection_date: string;
  latitude: number | null;
  longitude: number | null;
  notes: string;
}

const CollectionForm: React.FC<Props> = ({ navigation }) => {
  const [loading, setLoading] = useState(false);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [markets, setMarkets] = useState<any[]>([]);
  const [products, setProducts] = useState<any[]>([]);
  
  const [formData, setFormData] = useState<FormData>({
    market_id: '',
    product_id: '',
    price: '',
    retail_price: '',
    wholesale_price: '',
    collection_date: new Date().toISOString().split('T')[0],
    latitude: 14.6928, // Coordonnées Dakar par défaut
    longitude: -17.4467,
    notes: ''
  });

  const [selectedMarket, setSelectedMarket] = useState<any>(null);
  const [selectedProduct, setSelectedProduct] = useState<any>(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      // Données de test pour l'instant
      const testMarkets = [
        { id: '1', name: 'Marché Tilène', region: 'Dakar' },
        { id: '2', name: 'Marché Sandaga', region: 'Dakar' },
        { id: '3', name: 'Marché Kolda', region: 'Kolda' }
      ];
      
      const testProducts = [
        { id: '1', name: 'Millet', unit: 'kg' },
        { id: '2', name: 'Maïs', unit: 'kg' },
        { id: '3', name: 'Riz', unit: 'kg' }
      ];
      
      setMarkets(testMarkets);
      setProducts(testProducts);
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de charger les données');
    }
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
      // Simulation de soumission
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      setShowSuccessModal(true);
      resetForm();
      
    } catch (error) {
      Alert.alert('Erreur', 'La soumission a échoué');
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
      latitude: 14.6928,
      longitude: -17.4467,
      notes: ''
    });
    setSelectedMarket(null);
    setSelectedProduct(null);
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
          onPress={() => {
            Alert.alert(
              'Sélectionner un marché',
              'Choisissez dans la liste:',
              markets.map(market => ({
                text: `${market.name} (${market.region})`,
                onPress: () => {
                  setSelectedMarket(market);
                  setFormData(prev => ({ ...prev, market_id: market.id }));
                }
              }))
            );
          }}
        >
          <Text style={styles.selectorText}>
            {selectedMarket ? `${selectedMarket.name} (${selectedMarket.region})` : 'Sélectionner un marché'}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Sélection du produit */}
      <View style={styles.section}>
        <Text style={styles.label}>📦 Produit *</Text>
        <TouchableOpacity 
          style={styles.selector}
          onPress={() => {
            Alert.alert(
              'Sélectionner un produit',
              'Choisissez dans la liste:',
              products.map(product => ({
                text: `${product.name} (${product.unit})`,
                onPress: () => {
                  setSelectedProduct(product);
                  setFormData(prev => ({ ...prev, product_id: product.id }));
                }
              }))
            );
          }}
        >
          <Text style={styles.selectorText}>
            {selectedProduct ? `${selectedProduct.name} (${selectedProduct.unit})` : 'Sélectionner un produit'}
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
          <TouchableOpacity style={styles.refreshButton}>
            <Text style={styles.refreshButtonText}>🔄</Text>
          </TouchableOpacity>
        </View>
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
              onPress={() => {
                setShowSuccessModal(false);
                navigation.goBack();
              }}
            >
              <Text style={styles.modalButtonText}>OK</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
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
    textAlign: 'center',
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
});

export default CollectionForm;

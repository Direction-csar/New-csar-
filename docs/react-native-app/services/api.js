import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://localhost:8000/api/mobile/v1';

class ApiService {
  constructor() {
    this.token = null;
    this.setupInterceptors();
  }

  setupInterceptors() {
    // Intercepteur pour ajouter le token
    axios.interceptors.request.use(async (config) => {
      const token = await this.getToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

    // Intercepteur pour gérer les erreurs
    axios.interceptors.response.use(
      (response) => response,
      async (error) => {
        if (error.response?.status === 401) {
          await this.logout();
        }
        return Promise.reject(error);
      }
    );
  }

  async getToken() {
    if (!this.token) {
      this.token = await AsyncStorage.getItem('auth_token');
    }
    return this.token;
  }

  async setToken(token) {
    this.token = token;
    await AsyncStorage.setItem('auth_token', token);
  }

  async logout() {
    this.token = null;
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user_data');
  }

  // Authentification
  async login(email, password, deviceToken = null) {
    try {
      const response = await axios.post(`${API_BASE_URL}/login`, {
        email,
        password,
        device_token: deviceToken
      });
      
      if (response.data.success) {
        await this.setToken(response.data.data.token);
        await AsyncStorage.setItem('user_data', JSON.stringify(response.data.data.collector));
        return response.data;
      }
      throw new Error(response.data.message || 'Login failed');
    } catch (error) {
      throw error.response?.data || error.message;
    }
  }

  async logout() {
    try {
      await axios.post(`${API_BASE_URL}/logout`);
    } catch (error) {
      console.log('Logout error:', error);
    } finally {
      await this.logout();
    }
  }

  async getProfile() {
    const response = await axios.get(`${API_BASE_URL}/profile`);
    return response.data;
  }

  // Données de référence
  async getMarkets() {
    const response = await axios.get(`${API_BASE_URL}/markets`);
    return response.data;
  }

  async getProducts() {
    const response = await axios.get(`${API_BASE_URL}/products`);
    return response.data;
  }

  // Collecte de données
  async submitCollection(formData) {
    try {
      const response = await axios.post(`${API_BASE_URL}/collections`, formData);
      return response.data;
    } catch (error) {
      // En cas d'erreur, sauvegarder localement pour sync ultérieur
      throw error.response?.data || error.message;
    }
  }

  async getPendingCollections() {
    const response = await axios.get(`${API_BASE_URL}/collections/pending`);
    return response.data;
  }

  async syncCollections() {
    const response = await axios.post(`${API_BASE_URL}/sync`);
    return response.data;
  }

  async getSyncHistory() {
    const response = await axios.get(`${API_BASE_URL}/sync/history`);
    return response.data;
  }
}

export default new ApiService();

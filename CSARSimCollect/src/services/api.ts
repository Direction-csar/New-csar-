import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://localhost:8000/api/mobile/v1';

interface LoginResponse {
  success: boolean;
  data: {
    token: string;
    collector: any;
  };
  message?: string;
}

interface ApiResponse {
  success: boolean;
  data?: any;
  message?: string;
}

class ApiService {
  private token: string | null = null;

  constructor() {
    this.setupInterceptors();
  }

  setupInterceptors() {
    axios.interceptors.request.use(async (config) => {
      const token = await this.getToken();
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

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

  async getToken(): Promise<string | null> {
    if (!this.token) {
      this.token = await AsyncStorage.getItem('auth_token');
    }
    return this.token;
  }

  async setToken(token: string): Promise<void> {
    this.token = token;
    await AsyncStorage.setItem('auth_token', token);
  }

  async logout(): Promise<void> {
    this.token = null;
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('user_data');
  }

  async login(email: string, password: string, deviceToken?: string): Promise<LoginResponse> {
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
    } catch (error: any) {
      throw error.response?.data || error.message;
    }
  }

  async getProfile(): Promise<ApiResponse> {
    const response = await axios.get(`${API_BASE_URL}/profile`);
    return response.data;
  }

  async getMarkets(): Promise<ApiResponse> {
    const response = await axios.get(`${API_BASE_URL}/markets`);
    return response.data;
  }

  async getProducts(): Promise<ApiResponse> {
    const response = await axios.get(`${API_BASE_URL}/products`);
    return response.data;
  }

  async submitCollection(formData: any): Promise<ApiResponse> {
    try {
      const response = await axios.post(`${API_BASE_URL}/collections`, formData);
      return response.data;
    } catch (error: any) {
      throw error.response?.data || error.message;
    }
  }
}

export default new ApiService();

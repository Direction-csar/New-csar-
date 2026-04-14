import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
  ActivityIndicator,
  Modal
} from 'react-native';
import api from '../services/api';
import StorageService from '../services/storage';
import LocationService from '../services/location';

const LoginScreen = ({ navigation, onLoginSuccess }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);

  useEffect(() => {
    // Vérifier si l'utilisateur est déjà connecté
    checkAuthStatus();
  }, []);

  const checkAuthStatus = async () => {
    try {
      const token = await StorageService.getAuthToken();
      const userData = await StorageService.getUserData();
      
      if (token && userData) {
        navigation.replace('Main');
      }
    } catch (error) {
      console.log('Auth check failed:', error);
    }
  };

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    setLoading(true);

    try {
      const response = await api.login(email, password);
      
      if (response.success) {
        // Sauvegarder les préférences
        if (rememberMe) {
          await StorageService.saveSettings({ ...await StorageService.getSettings(), email });
        }
        
        if (onLoginSuccess) {
          onLoginSuccess();
        } else {
          navigation.replace('Main');
        }
      }
    } catch (error) {
      let errorMessage = 'Erreur de connexion';
      
      if (typeof error === 'string') {
        errorMessage = error;
      } else if (error.message) {
        errorMessage = error.message;
      } else if (error.error) {
        errorMessage = error.error;
      }
      
      Alert.alert('Erreur', errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleForgotPassword = () => {
    Alert.alert(
      'Mot de passe oublié',
      'Veuillez contacter votre administrateur CSAR pour réinitialiser votre mot de passe.',
      [{ text: 'OK' }]
    );
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.logo}>🌾</Text>
        <Text style={styles.title}>CSAR SIM Collect</Text>
        <Text style={styles.subtitle}>Application de collecte de données SIM</Text>
      </View>

      <View style={styles.form}>
        <View style={styles.inputContainer}>
          <Text style={styles.label}>Email</Text>
          <TextInput
            style={styles.input}
            value={email}
            onChangeText={setEmail}
            placeholder="votre.email@sim.sn"
            keyboardType="email-address"
            autoCapitalize="none"
            autoCorrect={false}
          />
        </View>

        <View style={styles.inputContainer}>
          <Text style={styles.label}>Mot de passe</Text>
          <View style={styles.passwordContainer}>
            <TextInput
              style={[styles.input, styles.passwordInput]}
              value={password}
              onChangeText={setPassword}
              placeholder="••••••••"
              secureTextEntry={!showPassword}
              autoCapitalize="none"
            />
            <TouchableOpacity 
              style={styles.eyeButton}
              onPress={() => setShowPassword(!showPassword)}
            >
              <Text style={styles.eyeText}>{showPassword ? '👁️' : '👁️‍🗨️'}</Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.rememberContainer}>
          <TouchableOpacity 
            style={styles.checkbox}
            onPress={() => setRememberMe(!rememberMe)}
          >
            <Text style={styles.checkboxText}>{rememberMe ? '☑️' : '⬜'}</Text>
          </TouchableOpacity>
          <Text style={styles.rememberText}>Se souvenir de moi</Text>
        </View>

        <TouchableOpacity 
          style={[styles.loginButton, loading && styles.disabledButton]}
          onPress={handleLogin}
          disabled={loading}
        >
          {loading ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.loginButtonText}>Se connecter</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.forgotButton}
          onPress={handleForgotPassword}
        >
          <Text style={styles.forgotButtonText}>Mot de passe oublié?</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.footer}>
        <Text style={styles.footerText}>
          Application officielle du CSAR
        </Text>
        <Text style={styles.footerText}>
          Commissariat à la Sécurité Alimentaire et à la Résilience
        </Text>
        <Text style={styles.versionText}>Version 1.0.0</Text>
      </View>
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
    marginTop: 60,
    marginBottom: 40,
  },
  logo: {
    fontSize: 60,
    marginBottom: 10,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#27ae60',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 16,
    color: '#7f8c8d',
    textAlign: 'center',
  },
  form: {
    backgroundColor: '#fff',
    borderRadius: 10,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2c3e50',
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    backgroundColor: '#f8f9fa',
  },
  passwordContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  passwordInput: {
    flex: 1,
  },
  eyeButton: {
    padding: 10,
    marginLeft: 10,
  },
  eyeText: {
    fontSize: 20,
  },
  rememberContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  checkbox: {
    marginRight: 10,
  },
  checkboxText: {
    fontSize: 20,
  },
  rememberText: {
    fontSize: 16,
    color: '#2c3e50',
  },
  loginButton: {
    backgroundColor: '#27ae60',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginBottom: 15,
  },
  disabledButton: {
    backgroundColor: '#95a5a6',
  },
  loginButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  forgotButton: {
    alignItems: 'center',
  },
  forgotButtonText: {
    color: '#3498db',
    fontSize: 14,
  },
  footer: {
    alignItems: 'center',
    marginTop: 40,
  },
  footerText: {
    fontSize: 12,
    color: '#7f8c8d',
    textAlign: 'center',
    marginBottom: 5,
  },
  versionText: {
    fontSize: 10,
    color: '#bdc3c7',
  },
};

export default LoginScreen;

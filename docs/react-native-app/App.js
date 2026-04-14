import React, { useState, useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { View, Text, ActivityIndicator, StyleSheet } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

import LoginScreen from './screens/LoginScreen';
import HomeScreen from './screens/HomeScreen';
import CollectScreen from './screens/CollectScreen';
import MapScreen from './screens/MapScreen';
import SyncScreen from './screens/SyncScreen';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

const CSAR_GREEN = '#00a86b';
const CSAR_BLUE = '#0078d4';

function TabIcon({ name, focused }) {
  const icons = {
    Home: focused ? '🏠' : '🏡',
    Collect: focused ? '📝' : '📋',
    Map: focused ? '🗺️' : '🗺',
    Sync: focused ? '🔄' : '↻',
  };
  return (
    <Text style={{ fontSize: 20, opacity: focused ? 1 : 0.5 }}>
      {icons[name] || '●'}
    </Text>
  );
}

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerStyle: { backgroundColor: CSAR_GREEN },
        headerTintColor: 'white',
        headerTitleStyle: { fontWeight: '700' },
        tabBarActiveTintColor: CSAR_GREEN,
        tabBarInactiveTintColor: '#9ca3af',
        tabBarStyle: {
          backgroundColor: 'white',
          borderTopWidth: 1,
          borderTopColor: '#f3f4f6',
          paddingBottom: 6,
          paddingTop: 4,
          height: 60,
        },
        tabBarLabelStyle: { fontSize: 11, fontWeight: '600' },
        tabBarIcon: ({ focused }) => <TabIcon name={route.name} focused={focused} />,
      })}
    >
      <Tab.Screen
        name="Home"
        component={HomeScreen}
        options={{ title: 'Accueil', tabBarLabel: 'Accueil', headerTitle: '🌿 CSAR SIM Collecte' }}
      />
      <Tab.Screen
        name="Collect"
        component={CollectScreen}
        options={{ title: 'Collecte', tabBarLabel: 'Collecte', headerTitle: '📝 Nouvelle collecte' }}
      />
      <Tab.Screen
        name="Map"
        component={MapScreen}
        options={{ title: 'Carte', tabBarLabel: 'Carte', headerShown: false }}
      />
      <Tab.Screen
        name="Sync"
        component={SyncScreen}
        options={{ title: 'Sync', tabBarLabel: 'Sync', headerTitle: '🔄 Synchronisation' }}
      />
    </Tab.Navigator>
  );
}

export default function App() {
  const [isLoading, setIsLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    checkAuthStatus();
  }, []);

  const checkAuthStatus = async () => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      setIsAuthenticated(!!token);
    } catch {
      setIsAuthenticated(false);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <View style={styles.splash}>
        <Text style={styles.splashLogo}>🌿</Text>
        <Text style={styles.splashTitle}>CSAR SIM Collecte</Text>
        <ActivityIndicator color={CSAR_GREEN} style={{ marginTop: 24 }} />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {!isAuthenticated ? (
          <Stack.Screen name="Login">
            {(props) => (
              <LoginScreen
                {...props}
                onLoginSuccess={() => setIsAuthenticated(true)}
              />
            )}
          </Stack.Screen>
        ) : (
          <Stack.Screen name="Main" component={MainTabs} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  splash: {
    flex: 1,
    backgroundColor: '#f8fafc',
    justifyContent: 'center',
    alignItems: 'center',
  },
  splashLogo: { fontSize: 72, marginBottom: 12 },
  splashTitle: {
    fontSize: 22,
    fontWeight: '800',
    color: CSAR_GREEN,
    letterSpacing: 0.5,
  },
});

import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import LoginScreen from './src/screens/LoginScreen';
import HomeScreen from './src/screens/HomeScreen';
import CollectionForm from './src/screens/CollectionForm';

const Stack = createStackNavigator();

const App = () => {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="Login">
        <Stack.Screen 
          name="Login" 
          component={LoginScreen} 
          options={{ headerShown: false }}
        />
        <Stack.Screen 
          name="Home" 
          component={HomeScreen} 
          options={{ title: 'CSAR SIM Collect' }}
        />
        <Stack.Screen 
          name="CollectionForm" 
          component={CollectionForm} 
          options={{ title: 'Nouvelle Collecte' }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default App;

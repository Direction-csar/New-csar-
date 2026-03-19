import Geolocation from 'react-native-geolocation-service';
import { Platform, PermissionsAndroid, Alert } from 'react-native';

class LocationService {
  static async requestLocationPermission() {
    if (Platform.OS === 'android') {
      try {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION
        );
        return granted === PermissionsAndroid.RESULTS.GRANTED;
      } catch (err) {
        console.warn('Location permission error:', err);
        return false;
      }
    }
    return true; // iOS gère les permissions différemment
  }

  static getCurrentPosition(options = {}) {
    return new Promise((resolve, reject) => {
      const defaultOptions = {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 10000,
        ...options
      };

      Geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitudeAccuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            timestamp: position.timestamp
          });
        },
        (error) => {
          let errorMessage = 'Erreur de localisation';
          
          switch (error.code) {
            case 1:
              errorMessage = 'Permission de localisation refusée';
              break;
            case 2:
              errorMessage = 'Position indisponible';
              break;
            case 3:
              errorMessage = 'Timeout de localisation';
              break;
            default:
              errorMessage = 'Erreur inconnue de localisation';
          }
          
          reject(new Error(errorMessage));
        },
        defaultOptions
      );
    });
  }

  static async getCurrentLocationWithFallback() {
    try {
      // Demander la permission d'abord
      const hasPermission = await this.requestLocationPermission();
      if (!hasPermission) {
        throw new Error('Permission de localisation requise');
      }

      // Essayer avec haute précision
      try {
        return await this.getCurrentPosition({
          enableHighAccuracy: true,
          timeout: 10000
        });
      } catch (highAccuracyError) {
        console.warn('High accuracy location failed, trying low accuracy:', highAccuracyError);
        
        // Fallback avec basse précision
        return await this.getCurrentPosition({
          enableHighAccuracy: false,
          timeout: 15000
        });
      }
    } catch (error) {
      throw error;
    }
  }

  static watchPosition(successCallback, errorCallback, options = {}) {
    const defaultOptions = {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 10000,
      distanceFilter: 10, // Mettre à jour tous les 10 mètres
      ...options
    };

    return Geolocation.watchPosition(
      successCallback,
      errorCallback,
      defaultOptions
    );
  }

  static clearWatch(watchId) {
    Geolocation.clearWatch(watchId);
  }

  // Calcul de distance entre deux points (formule Haversine)
  static calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en km
    const dLat = this.toRad(lat2 - lat1);
    const dLon = this.toRad(lon2 - lon1);
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance en km
  }

  static toRad(degrees) {
    return degrees * (Math.PI / 180);
  }

  // Vérifier si un point est dans une zone géographique
  static isWithinZone(userLat, userLon, zoneCenterLat, zoneCenterLon, radiusKm = 5) {
    const distance = this.calculateDistance(userLat, userLon, zoneCenterLat, zoneCenterLon);
    return distance <= radiusKm;
  }

  // Formater les coordonnées pour l'affichage
  static formatCoordinates(latitude, longitude, precision = 6) {
    return {
      latitude: latitude.toFixed(precision),
      longitude: longitude.toFixed(precision),
      formatted: `${latitude.toFixed(precision)}, ${longitude.toFixed(precision)}`
    };
  }

  // Obtenir l'adresse à partir des coordonnées (nécessite une API de géocodage)
  static async reverseGeocode(latitude, longitude) {
    try {
      // Utiliser une API de géocodage (OpenStreetMap Nominatim par exemple)
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`,
        {
          headers: {
            'User-Agent': 'CSAR-SIM-Collect/1.0'
          }
        }
      );
      
      const data = await response.json();
      
      if (data && data.display_name) {
        return {
          success: true,
          address: data.display_name,
          details: data.address,
          components: {
            country: data.address?.country,
            state: data.address?.state,
            city: data.address?.city || data.address?.town || data.address?.village,
            postcode: data.address?.postcode,
            road: data.address?.road
          }
        };
      }
      
      return { success: false, error: 'Adresse non trouvée' };
    } catch (error) {
      console.error('Reverse geocoding error:', error);
      return { success: false, error: error.message };
    }
  }

  // Valider la précision de la localisation
  static validateLocationAccuracy(position, requiredAccuracy = 20) {
    return position.accuracy <= requiredAccuracy;
  }

  // Obtenir la meilleure position disponible avec plusieurs tentatives
  static async getBestPosition(maxAttempts = 3) {
    let bestPosition = null;
    let bestAccuracy = Infinity;

    for (let attempt = 1; attempt <= maxAttempts; attempt++) {
      try {
        console.log(`Location attempt ${attempt}/${maxAttempts}`);
        
        const position = await this.getCurrentPosition({
          enableHighAccuracy: true,
          timeout: 10000
        });

        if (position.accuracy < bestAccuracy) {
          bestPosition = position;
          bestAccuracy = position.accuracy;
        }

        // Si la précision est bonne (< 20m), on arrête
        if (position.accuracy <= 20) {
          break;
        }

        // Attendre un peu entre les tentatives
        if (attempt < maxAttempts) {
          await new Promise(resolve => setTimeout(resolve, 1000));
        }
      } catch (error) {
        console.warn(`Attempt ${attempt} failed:`, error);
        
        if (attempt === maxAttempts && !bestPosition) {
          throw error;
        }
      }
    }

    if (!bestPosition) {
      throw new Error('Impossible d\'obtenir une position valide');
    }

    return bestPosition;
  }
}

export default LocationService;

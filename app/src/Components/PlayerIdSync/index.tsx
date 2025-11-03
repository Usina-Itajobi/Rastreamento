import React, { useEffect } from 'react';
import { OneSignal } from 'react-native-onesignal';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { AppState, AppStateStatus } from 'react-native';
import * as Sentry from '@sentry/react-native';

const PlayerIdSync: React.FC = () => {
  const syncPlayerId = async () => {
    let enterpriseData: string | null = null;
    let userData: string | null = null;

    try {
      // Get enterprise data
      enterpriseData = await AsyncStorage.getItem('@ctracker:enterprise');
      if (!enterpriseData) return;

      const enterprise = JSON.parse(enterpriseData);

      // Get user data
      userData = await AsyncStorage.getItem('@ctracker:user');
      if (!userData) return;

      const user = JSON.parse(userData);

      // Get player ID from OneSignal
      const playerId = await OneSignal.User.pushSubscription.getIdAsync();
      if (!playerId) return;

      // Send player ID to backend
      await axios.post(
        `${enterprise.baseUrl}/metronic/api/playerid.php`,
        null,
        {
          params: {
            email: user.email,
            user_id: user.id,
            tipo_usuario: user.tipo_usuario || null,
            playerid: playerId,
          },
        },
      );

      // Get list accounts data
      const listAccountsData = await AsyncStorage.getItem('@ctracker:accounts');
      if(listAccountsData){
        const listAccounts = JSON.parse(listAccountsData);
        listAccounts.forEach(account => {
          // Send player ID to backend
          axios.post(
            `${enterprise.baseUrl}/metronic/api/playerid.php`,
            null,
            {
              params: {
                email: account.email,
                user_id: account.id,
                tipo_usuario: account.tipo_usuario || null,
                playerid: playerId,
              },
            },
          );
        });
      }
    } catch (error) {
      console.error('Error syncing player ID:', error);
      Sentry.captureException(error, {
        tags: {
          location: 'PlayerIdSync.syncPlayerId',
        },
        extra: {
          enterpriseData,
          userData,
        },
      });
    }
  };

  useEffect(() => {
    let subscription: any;

    try {
      // Initial sync
      syncPlayerId();

      // Set up subscription to OneSignal push subscription changes
      OneSignal.User.pushSubscription.addEventListener('change', (s) => {
        try {
          syncPlayerId();
          subscription = s;
        } catch (error) {
          console.error('Error in OneSignal subscription change:', error);
          Sentry.captureException(error, {
            tags: {
              location: 'PlayerIdSync.OneSignalSubscription',
            },
          });
        }
      });

      // Set up AppState listener for app focus
      const handleAppStateChange = (nextAppState: AppStateStatus) => {
        try {
          if (nextAppState === 'active') {
            syncPlayerId();
          }
        } catch (error) {
          console.error('Error in AppState change handler:', error);
          Sentry.captureException(error, {
            tags: {
              location: 'PlayerIdSync.AppStateChange',
              appState: nextAppState,
            },
          });
        }
      };

      const appStateSubscription = AppState.addEventListener(
        'change',
        handleAppStateChange,
      );

      return () => {
        try {
          if (subscription) {
            OneSignal.User.pushSubscription.removeEventListener(
              'change',
              subscription,
            );
          }
          appStateSubscription.remove();
        } catch (error) {
          console.error('Error cleaning up subscriptions:', error);
          Sentry.captureException(error, {
            tags: {
              location: 'PlayerIdSync.cleanup',
            },
          });
        }
      };
    } catch (error) {
      console.error('Error in useEffect setup:', error);
      Sentry.captureException(error, {
        tags: {
          location: 'PlayerIdSync.useEffect',
        },
      });
    }
  }, []);

  return null;
};

export default PlayerIdSync;

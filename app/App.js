import React, { useEffect, useState } from 'react';
import { LogLevel, OneSignal } from 'react-native-onesignal';
import { Provider } from 'react-redux';
import { Provider as PaperProvider } from 'react-native-paper';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import AsyncStorage from '@react-native-async-storage/async-storage';

import NetInfo from '@react-native-community/netinfo';

import { StatusBar } from 'react-native';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import Navigation from './src/navigation';
import store from './src/store';
import { AuthProvider } from './src/context/authContext';
import PlayerIdSync from './src/Components/PlayerIdSync';
import AppStateUser from './src/Components/AppStateUser';
import Connection from './src/Components/Connection';

import './src/config';

function App() {
  useEffect(() => {
    // Define enterprise fixo no AsyncStorage na inicialização do app
    const setDefaultEnterprise = async () => {
      try {
        const enterpriseData = {
          baseUrl: 'https://itajobi.usinaitajobi.com.br',
        };
        await AsyncStorage.setItem(
          '@ctracker:enterprise',
          JSON.stringify(enterpriseData),
        );
        console.log('Enterprise padrão definido:', enterpriseData);
      } catch (error) {
        console.error('Erro ao definir enterprise padrão:', error);
      }
    };

    setDefaultEnterprise();

    // Replace 'YOUR_ONESIGNAL_APP_ID' with your OneSignal App ID.
    OneSignal.Debug.setLogLevel(LogLevel.Verbose);
    OneSignal.initialize('52dad7f5-77ec-420d-aa13-45f139fd74df');

    // Remove this method to stop OneSignal Debugging

    // requestPermission will show the native iOS or Android notification permission prompt.
    // We recommend removing the following code and instead using an In-App Message to prompt for notification permission
    OneSignal.Notifications.requestPermission(true);

    // Method for listening for notification clicks
    OneSignal.Notifications.addEventListener('click', (event) => {
      console.log('OneSignal: notification clicked:', event);
    });
  }, []);

  const [isConnected, setIsConnected] = useState(null);

  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener((state) => {
      setIsConnected(state.isConnected);
    });

    // Checagem inicial
    NetInfo.fetch().then((state) => {
      setIsConnected(state.isConnected);
    });

    return () => unsubscribe();
  }, []);

  return (
    <>
      <StatusBar
        backgroundColor="#FFFFFF"
        barStyle="dark-content"
        translucent
      />

      <GestureHandlerRootView style={{ flex: 1 }}>
        <SafeAreaProvider>
          <Provider store={store}>
            <PaperProvider>
              <AuthProvider>
                {!isConnected ? (
                  <Connection connected={isConnected} />
                ) : (
                  <>
                    <PlayerIdSync />
                    <AppStateUser />
                    <Navigation uriPrefix="ctracker://" />
                  </>
                )}

                {/* Uncomment the line below to test Crashlytics */}
                {/* <Button title="Test Crash" onPress={testCrash} /> */}
              </AuthProvider>
            </PaperProvider>
          </Provider>
        </SafeAreaProvider>
      </GestureHandlerRootView>
    </>
  );
}

export default App;

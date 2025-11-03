import React, { useEffect, useRef } from 'react';
import { AppState } from 'react-native';
import { useAuth } from '../../context/authContext';

const AppStateUser: React.FC = () => {
  const { selectedAccount, updateSelectedAccountData } = useAuth();

  const appState = useRef(AppState.currentState);

  const handleAppStateChange = nextAppState => {
    if (
      appState.current.match(/inactive|background/) &&
      nextAppState === 'active'
    ) {
      // Essa função será chamada quando voltar ao app
      updateSelectedAccountData();
    }

    appState.current = nextAppState;
  };

  useEffect(() => {
    const subscription = AppState.addEventListener('change', handleAppStateChange);

    return () => {
      subscription.remove();
    };
  }, []);

  return null;
};

export default AppStateUser;

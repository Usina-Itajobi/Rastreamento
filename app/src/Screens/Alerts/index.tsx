import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  ActivityIndicator,
  ImageBackground,
  RefreshControl,
  TouchableOpacity,
  Platform,
} from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import styles from './styles';
import Header from '../../Components/Header';
import { useAuth } from '../../context/authContext';

type Alert = {
  msg: string;
};

const AlertsScreen: React.FC = (props) => {
  const { selectedAccount } = useAuth();

  const [loading, setLoading] = useState(true);
  const [alerts, setAlerts] = useState<Alert[]>([]);

  const getAlerts = useCallback(async () => {
    try {
      setLoading(true);
      const accessToken = selectedAccount.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@grupoitajobi:enterprise',
      );

      console.log(JSON.parse(enterpriseStorage!).baseUrl)

      const result = await fetch(
        `${
          JSON.parse(enterpriseStorage!).baseUrl
        }/metronic/api/get.alertas.php?h=${accessToken}`,
      );

      let data = await result.text();

      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      setAlerts(JSON.parse(data));
    } catch (error) {
      console.log(error);
    } finally {
      setLoading(false);
    }
  }, [selectedAccount]);

  useEffect(() => {
    getAlerts();
  }, [getAlerts]);

  const renderAlert = ({ item }: { item: Alert }) => {
    return (
      <View style={styles.alertContainer}>
        <Text
          style={{
            fontSize: 16,
            color: 'black',
            fontWeight: 'bold',
            textAlign: 'center',
            marginTop: 15,
          }}
        >
          {item.msg}
        </Text>
      </View>
    );
  };

  return (
    <ImageBackground
      source={require('../../assets/images/VheicleBackground.jpg')}
      style={styles.container}
    >
      <Header title="Alertas" {...props} />

      {loading ? (
        <ActivityIndicator
          size="large"
          color="#414141"
          style={{ flex: 1 }}
        />
      ) : (
        <FlatList
          keyExtractor={(_, index) => index.toString()}
          contentContainerStyle={{
            paddingBottom: 220,
          }}
          data={alerts}
          renderItem={renderAlert}
          refreshControl={
            <RefreshControl refreshing={false} onRefresh={getAlerts} />
          }
        />
      )}

      <TouchableOpacity
        style={{
          position: 'absolute',
          right: 16,
          bottom: 48,
          width: 64,
          height: 64,
          backgroundColor: '#004e70',
          borderRadius: 50,
          alignItems: 'center',
          justifyContent: 'center',
        }}
        onPress={getAlerts}
      >
        <MaterialCommunityIcons name="refresh" color="#fff" size={34} />
      </TouchableOpacity>
    </ImageBackground>
  );
};

export default AlertsScreen;

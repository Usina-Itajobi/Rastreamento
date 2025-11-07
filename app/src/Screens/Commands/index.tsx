import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  ScrollView,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  ImageBackground,
  ToastAndroid,
  Alert,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useNavigation } from '@react-navigation/native';
import styles from './styles';
import Header from '../../Components/Header';
import { useAuth } from '../../context/authContext';

interface Vehicle {
  id_bem: string;
  name: string;
  address: string;
  imei: string;
}

const CommandsPage = () => {
  const navigation = useNavigation();
  const { selectedAccount } = useAuth();

  const [loading, setLoading] = useState(false);
  const [loadingCommand, setLoadingCommand] = useState<string | null>(null);
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);

  useEffect(() => {
    getVehicles();
  }, []);

  const getVehicles = useCallback(async () => {
    setLoading(true);
    try {
      const accessToken = selectedAccount?.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@grupoitajobi:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const result = await fetch(
        `${enterprise.baseUrl}/metronic/api/get.veiculos.php?h=${accessToken}`,
      );

      let data = await result.text();
      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      if (JSON.parse(data)?.error === 'S') {
        ToastAndroid.show(
          'Não foi possível carregar os veículos. Verifique sua conexão com a internet!',
          ToastAndroid.SHORT,
        );
        return;
      }

      setVehicles(JSON.parse(data));
    } finally {
      setLoading(false);
    }
  }, []);

  const sendCommand = async (vehicle: Vehicle, command: string) => {
    setLoadingCommand(`${command}-${vehicle.imei}`);
    try {
      const accessToken = selectedAccount?.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@grupoitajobi:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const formData = new FormData();
      formData.append('h', accessToken);
      formData.append('imei', vehicle.imei);
      formData.append('command', command);

      const result = await fetch(
        `${enterprise.baseUrl}/metronic/api/send.command.php`,
        {
          body: formData,
          method: 'POST',
        },
      );

      let data = await result.text();
      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      const hex = JSON.parse(data);
      Alert.alert(hex.msg);
      ToastAndroid.show('Comando enviado com sucesso.', ToastAndroid.SHORT);
    } catch (error) {
      ToastAndroid.show(
        'Não foi possível enviar o comando. Verifique sua conexão com a internet!',
        ToastAndroid.SHORT,
      );
    } finally {
      setLoadingCommand(null);
    }
  };

  const renderVehicle = (vehicle: Vehicle) => (
    <View style={styles.vehicle} key={vehicle.id_bem}>
      <Text style={styles.vehicleTitle}>{vehicle.name}</Text>
      <Text style={styles.vehicleAddress}>{vehicle.address}</Text>
      <View style={styles.vehicleActions}>
        <TouchableOpacity
          style={styles.vehicleActionsBlock}
          activeOpacity={0.7}
          onPress={() => sendCommand(vehicle, 'block')}
        >
          {loadingCommand == null ||
          loadingCommand !== `block-${vehicle.imei}` ? (
            <Text style={{ fontSize: 16, color: 'black', textAlign: 'center' }}>
              Bloquear
            </Text>
          ) : (
            <ActivityIndicator size="small" color="black" />
          )}
        </TouchableOpacity>
        <TouchableOpacity
          style={styles.vehicleActionsUnBlock}
          activeOpacity={0.7}
          onPress={() => sendCommand(vehicle, 'unblock')}
        >
          {loadingCommand == null ||
          loadingCommand !== `unblock-${vehicle.imei}` ? (
            <Text style={{ fontSize: 16, color: 'black', textAlign: 'center' }}>
              Desbloquear
            </Text>
          ) : (
            <ActivityIndicator size="small" color="black" />
          )}
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <ImageBackground
      source={require('../../assets/images/VheicleBackground.jpg')}
      style={styles.container}
    >
      <Header title="Enviar comandos" name="comand" navigation={navigation} />
      {loading ? (
        <ActivityIndicator size="large" color="#414141" style={{ flex: 1 }} />
      ) : (
        <ScrollView>
          {vehicles.map((vehicle) => renderVehicle(vehicle))}
        </ScrollView>
      )}
    </ImageBackground>
  );
};

export default CommandsPage;

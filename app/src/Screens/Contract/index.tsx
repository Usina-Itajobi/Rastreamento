/* eslint-disable react/jsx-props-no-spreading */
import React, { useState, useEffect } from 'react';
import { requestMultiple, request, PERMISSIONS, RESULTS } from 'react-native-permissions';
import { Modal, View, Text, StyleSheet, ActivityIndicator, Alert, TouchableOpacity, Platform } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Geolocation from '@react-native-community/geolocation';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { WebView } from 'react-native-webview';
import { useAuth } from '../../context/authContext';

const ContractScreen = (props) => {
  const navigation = useNavigation();
  const { selectedAccount, updateAccount } = useAuth();
  const [loadingContract, setLoadingContract] = useState(false);
  const [visible, setVisible] = useState(false);
  const [podeAceitar, setPodeAceitar] = useState(false);

  const handleLogOut = async () => {
    await AsyncStorage.clear()
      .then(() => {
        setVisible(false);
        setPodeAceitar(false);
        navigation.navigate('Welcome');
      })
      .catch(() => {
        Alert.alert('Ocorreu um erro!');
      });
  };

  const handleMessage = (event) => {
    const data = event.nativeEvent.data;

    if (data === 'scrolledToBottom') {
      setPodeAceitar(true);
    }
  };

  const getLocationAsync = () => {
    return new Promise((resolve, reject) => {
      Geolocation.getCurrentPosition(
        position => resolve(position),
        error => reject(error),
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 10000,
        }
      );
    });
  };

  const handleAceitar = async () => {
    try {
      setLoadingContract(true);

      if (!selectedAccount?.h) {
        Alert.alert('Erro', 'Por favor faça o login.');
        setLoadingContract(false);
        return;
      }

      // Verifica e solicita permissão de localização
      if (Platform.OS === 'android') {
        const result = await requestMultiple([
          PERMISSIONS.ANDROID.ACCESS_FINE_LOCATION,
          PERMISSIONS.ANDROID.ACCESS_COARSE_LOCATION,
        ]);

        const fineGranted = result[PERMISSIONS.ANDROID.ACCESS_FINE_LOCATION] === RESULTS.GRANTED;
        const coarseGranted = result[PERMISSIONS.ANDROID.ACCESS_COARSE_LOCATION] === RESULTS.GRANTED;

        if (!fineGranted || !coarseGranted) {
          Alert.alert('Permissão necessária', 'Permissões de localização são obrigatórias para continuar.');
          setLoadingContract(false);
          return;
        }
      }

      if (Platform.OS === 'ios') {
        const status = await request(PERMISSIONS.IOS.LOCATION_WHEN_IN_USE);
        if (status !== RESULTS.GRANTED) {
          Alert.alert('Permissão necessária', 'Permissão de localização é obrigatória para continuar.');
          setLoadingContract(false);
          return;
        }
      }

      const position = await getLocationAsync();
      const { latitude, longitude } = position.coords;

      const form = new FormData();
      form.append('h', selectedAccount?.h);
      form.append('lat', latitude);
      form.append('long', longitude);

      const result = await fetch('https://ctracker.com.br/metronic/api/upload_contrato_pdf.php', {
        method: 'POST',
        body: form
      });

      let dataText = await result.text();
      if (Platform.OS === 'android') {
        dataText = dataText.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      const data = JSON.parse(dataText);

      if(data.error){
        Alert.alert('Erro', 'Falha no envio do contrato.');
        console.error(data);
        setLoadingContract(false);
        return;
      }

      const updatedAccount: any = {
        ...selectedAccount,
        novo_contrato: false,
      };

      updateAccount(updatedAccount);
      setVisible(false);
      setPodeAceitar(false);

    } catch (err) {
      Alert.alert('Erro', 'Erro ao aceitar o contrato.');
      console.error(err);
    } finally {
      setLoadingContract(false);
    }
  };

  const handleRecusar = async () => {
    setLoadingContract(true);
    await handleLogOut();
    setLoadingContract(false);
  };

  useEffect(() => {
    if (selectedAccount?.novo_contrato) {
      const timer = setTimeout(() => {
        setVisible(true);
      }, 5000); // 5 segundos

      return () => clearTimeout(timer); // limpar timeout se o componente for desmontado
    }
  }, [selectedAccount]);

  return (
    <>
      {visible ? (
        <Modal
          visible={visible}
          animationType="fade"
          transparent={true} // deixa fundo escurecido
        >
        <View style={styles.overlay}>
          <View style={styles.modalContainer}>
            <Text style={styles.title}>Contrato</Text>

            <View style={styles.webviewContainer}>
              <WebView
                source={{ uri: `https://itajobi.usinaitajobi.com.br/contrato-texto.php?h=${selectedAccount.h}` }}
                style={{ flex: 1 }}
                javaScriptEnabled
                onMessage={handleMessage}
                injectedJavaScript={`
                  window.onscroll = function() {
                    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                      window.ReactNativeWebView.postMessage('scrolledToBottom');
                    }
                  };
                  true;
                `}
              />
            </View>

            <View style={styles.buttonContainer}>
              {loadingContract ? (
                <ActivityIndicator size="small" color="#000" />
              ) : (
                <>
                  <Text style={styles.infoText}>
                    Ao continuar e contratar esse serviço, você concorda com os termos acima.
                  </Text>

                  <TouchableOpacity
                    onPress={handleAceitar}
                    disabled={!podeAceitar}
                    style={[styles.button, { backgroundColor: podeAceitar ? 'green' : '#ccc' }]}
                  >
                    <MaterialIcons
                      name="check-circle"
                      size={20}
                      color="#4CAF50"
                    />
                    <Text style={styles.buttonText}>
                      Aceitar
                    </Text>
                  </TouchableOpacity>

                  <TouchableOpacity
                    onPress={handleRecusar}
                    style={[styles.button, { backgroundColor: 'red' }]}
                  >
                    <MaterialIcons
                      name="cancel"
                      size={20}
                      color="#B71C1C"
                    />
                    <Text style={styles.buttonText}>Recusar</Text>
                  </TouchableOpacity>
                </>
              )}
            </View>
          </View>
        </View>
      </Modal>
      ) : (<></>)}
    </>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)', // fundo escuro
    justifyContent: 'center',
    alignItems: 'center',
    padding: 16,
  },
  modalContainer: {
    width: '100%',
    height: '90%',
    backgroundColor: '#fff',
    borderRadius: 10,
    overflow: 'hidden',
    elevation: 10,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    textAlign: 'center',
    paddingVertical: 12,
    color: '#fff',
    backgroundColor: '#004e70',
  },
  webviewContainer: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 5,
  },
  buttonContainer: {
    padding: 5,
    gap: 5,
    backgroundColor: '#eee',

  },
  button: {
    padding: 12,
    borderRadius: 6,
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 5,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    textAlign: 'center',
  },
  infoText: {
    color: '#000',
    fontSize: 16,
    textAlign: 'center',
  },
});


export default ContractScreen;

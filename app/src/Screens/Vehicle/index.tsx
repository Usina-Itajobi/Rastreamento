import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  ScrollView,
  Text,
  ImageBackground,
  TouchableOpacity,
  ActivityIndicator,
  Linking,
  Platform,
  ToastAndroid,
  Image,
  Alert,
  Modal,
  Dimensions,
  TouchableHighlight,
  Switch,
} from 'react-native';

import AsyncStorage from '@react-native-async-storage/async-storage';
import Icon from 'react-native-vector-icons/MaterialIcons';
import MapView, { Marker } from 'react-native-maps';
import AntIcon from 'react-native-vector-icons/AntDesign';
import FAIcon from 'react-native-vector-icons/FontAwesome';
import {
  heightPercentageToDP,
  widthPercentageToDP,
} from 'react-native-responsive-screen';
import { getStatusBarHeight } from 'react-native-iphone-x-helper';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import styles from './styles';
import Header from '../../Components/Header';
import assets from '../../assets';
import { useAuth } from '../../context/authContext';

interface Vehicle {
  id: string;
  name: string;
  endereco: string;
  lat: string;
  lng: string;
  data_posicao: string;
  data_comunicacao: string;
  ligado: string;
  odometro: string;
  template_telemetria: any;
  ign_color: string;
  combustivel_nivel: string;
  economia_instantanea: string;
  combustivel_total_usado: string;
  combustivel_pressao: string;
  combustivel_status: string;
  imei: string;
  velocidade: string;
  voltagem_bateria: string;
  voltagem_bateria_int: string;
  temp_oleo: string,
  torque: string,
  horimetro: string,
  acelerador_posicao: string,
  freio_posicao: string,
  marcha_atual: string,
  carga_motor: string,
  temp_motor: string,
  bloqueado: string;
  evento: string;
  rpm: string;
  tipo: string;
  motorista: string;
  imagem_icone: string;
  ancora: string;
  alert_ign: string;
}

const MaterialIcon = Icon as any;
const AntDesignIcon = AntIcon as any;
const FontAwesomeIcon = FAIcon as any;

const VehiclePage: React.FC<{ navigation: any; route: any }> = ({
  navigation,
  route,
}) => {
  const { selectedAccount } = useAuth();

  if (!selectedAccount) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color="#414141" style={{ flex: 1 }} />
      </View>
    );
  }

  const [vehicle, setVehicle] = useState<Vehicle | null>(null);
  const [visible, setVisible] = useState(false);
  const [loadingCommand, setLoadingCommand] = useState<string | null>(null);
  const [mapType, setMapType] = useState<'standard' | 'satellite'>('standard');
  const [modalVisible, setModalVisible] = useState(false);
  const [toggle, setToggle] = useState(false);
  const [showTraffic, setShowTraffic] = useState(false);
  const [latitude, setLatitude] = useState<number>(0);
  const [longitude, setLongitude] = useState<number>(0);
  const [latitudeDelta, setLatitudeDelta] = useState(0.005);
  const [longitudeDelta, setLongitudeDelta] = useState(
    (Dimensions.get('window').width / Dimensions.get('window').height) * 0.005,
  );

  const mapRef = useRef<MapView | null>(null);
  const scrollViewRef = useRef<ScrollView | null>(null);

  const refreshHandler = useCallback(async () => {
    try {
      const accessToken = selectedAccount.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@ctracker:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const fetchUrl = `${enterprise.baseUrl}/metronic/api/get_veiculo.php?&h=${accessToken}&id=${route.params.vehicleId}`;

      const result = await fetch(fetchUrl, {
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      });

      let data = await result.text();

      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      const vehicles = JSON.parse(data);
      const firstVehicle = vehicles.data;

      setVehicle(firstVehicle);
      setLatitude(parseFloat(firstVehicle.lat));
      setLongitude(parseFloat(firstVehicle.lng));
    } catch (error) {
      console.error(error);
    }
  }, [route.params.vehicleId, selectedAccount]);

  useEffect(() => {
    const intervalId = setInterval(() => {
      refreshHandler();
    }, 30000);

    refreshHandler();

    return () => {
      clearInterval(intervalId);
    };
  }, [navigation, refreshHandler]);

  const sendCommand = async (command: string) => {
    try {
      const accessToken = selectedAccount.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@ctracker:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const formData = new FormData();
      formData.append('h', accessToken);
      formData.append('id', vehicle!.id);
      formData.append('qual', command);

      await fetch(`${enterprise.baseUrl}/metronic/api/ancorar.veiculo.php`, {
        body: formData,
        method: 'POST',
      });

      setVehicle({ ...vehicle!, ancora: `${command}` });
      refreshHandler();
      Alert.alert('Comando enviado com sucesso.');
      ToastAndroid.show('Comando enviado com sucesso.', ToastAndroid.SHORT);
    } catch (error) {
      ToastAndroid.show(
        'Não foi possível enviar o comando. Verifique sua conexão com a internet!',
        ToastAndroid.SHORT,
      );
    }
  };

  const sendIgination = async (command: string) => {
    try {
      const accessToken = selectedAccount.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@ctracker:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const formData = new FormData();
      formData.append('h', accessToken);
      formData.append('id', vehicle!.id);
      formData.append('qual', command);

      const result = await fetch(
        `${
          enterprise.baseUrl
        }/metronic/api/alert_ign_veiculo.php?h=${accessToken}&qual=${command}&id=${
          vehicle!.id
        }`,
        {
          body: formData,
          method: 'POST',
        },
      );

      let data = await result.text();
      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }
      const response = JSON.parse(data);
      Alert.alert(response.msg);
      setVehicle({ ...vehicle!, alert_ign: `${command}` });
      refreshHandler();
    } catch (error) {
      ToastAndroid.show(
        'Não foi possível enviar o comando. Verifique sua conexão com a internet!',
        ToastAndroid.SHORT,
      );
    }
  };

  const sendBlocker = async (
    { imei }: Vehicle,
    command: string,
    hex: string,
  ) => {
    setLoadingCommand(`${command}-${imei}`);

    try {
      const accessToken = selectedAccount.h;
      const enterpriseStorage = await AsyncStorage.getItem(
        '@ctracker:enterprise',
      );
      const enterprise = JSON.parse(enterpriseStorage!);

      const formData = new FormData();
      formData.append('h', accessToken);
      formData.append('imei', imei);
      formData.append('command', command);

      await fetch(`${enterprise.baseUrl}/metronic/api/send.command.php`, {
        body: formData,
        method: 'POST',
      });

      Alert.alert(hex);
    } catch (error) {
      Alert.alert(hex);
    } finally {
      setLoadingCommand(null);
    }
  };

  const onPressZoomIn = () => {
    const newRegion = {
      latitude,
      longitude,
      latitudeDelta: latitudeDelta * 10,
      longitudeDelta: longitudeDelta * 10,
    };

    setLatitudeDelta(newRegion.latitudeDelta);
    setLongitudeDelta(newRegion.longitudeDelta);
    mapRef.current?.animateToRegion(newRegion, 100);
  };

  const onPressZoomOut = () => {
    const newRegion = {
      latitude,
      longitude,
      latitudeDelta: latitudeDelta / 10,
      longitudeDelta: longitudeDelta / 10,
    };

    setLatitudeDelta(newRegion.latitudeDelta);
    setLongitudeDelta(newRegion.longitudeDelta);
    mapRef.current?.animateToRegion(newRegion, 100);
  };

  if (!vehicle) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color="#414141" style={{ flex: 1 }} />
      </View>
    );
  }

  vehicle.imagem_icone = vehicle.imagem_icone.replace('http://', 'https://');

  return (
    <View style={styles.container}>
      {toggle === false ? <Header title="Veículo" {...navigation} /> : null}

      <ScrollView style={styles.details} ref={scrollViewRef}>
        <Modal
          animationType="slide"
          transparent
          visible={visible}
          onRequestClose={() => setVisible(false)}
        >
          <View
            style={{
              flex: 1,
              justifyContent: 'center',
              alignItems: 'center',
              backgroundColor: 'rgba(0, 0, 0, 0.5)',
            }}
          >
            <View
              style={{
                backgroundColor: 'white',
                borderRadius: 20,
                padding: 20,
                width: '90%',
                maxHeight: '80%',
                alignItems: 'center',
                shadowColor: '#000',
                shadowOffset: {
                  width: 0,
                  height: 2,
                },
                shadowOpacity: 0.25,
                shadowRadius: 4,
                elevation: 5,
              }}
            >
              <Image
                source={require('../../assets/images/setting.png')}
                style={{
                  height: 50,
                  width: 50,
                  alignSelf: 'center',
                  marginTop: 50,
                  marginBottom: 30,
                }}
              />
              <Text
                style={{
                  fontWeight: 'bold',
                  alignSelf: 'center',
                  marginTop: 10,
                  fontSize: 20,
                  color: '#004e70',
                }}
                allowFontScaling={false}
              >
                Funções no Veículo
              </Text>
              <View style={{ flexDirection: 'row', marginTop: 20 }}>
                <Switch
                  value={vehicle.ancora === '1'}
                  onValueChange={() =>
                    sendCommand(vehicle.ancora === '1' ? '0' : '1')
                  }
                  trackColor={{ false: '#767577', true: '#81b0ff' }}
                  thumbColor={vehicle.ancora === '1' ? '#004e70' : '#f4f3f4'}
                />
                <Text
                  style={{
                    fontWeight: 'bold',
                    marginLeft: 20,
                    marginTop: 3,
                    fontSize: 20,
                    color: 'grey',
                  }}
                  allowFontScaling={false}
                >
                  {vehicle.ancora === '1' ? 'Remover âncora' : 'Ancorar'}
                </Text>
              </View>

              <View style={{ flexDirection: 'row', marginTop: 20 }}>
                <Switch
                  value={vehicle.alert_ign === '1'}
                  onValueChange={() =>
                    sendIgination(vehicle.alert_ign === '1' ? '0' : '1')
                  }
                  trackColor={{ false: '#767577', true: '#81b0ff' }}
                  thumbColor={vehicle.alert_ign === '1' ? '#004e70' : '#f4f3f4'}
                />
                <Text
                  style={{
                    fontWeight: 'bold',
                    marginLeft: 20,
                    marginTop: 3,
                    fontSize: 20,
                    color: 'grey',
                  }}
                  allowFontScaling={false}
                >
                  Alerta de Ignição
                </Text>
              </View>

              <View style={styles.vehicleActions}>
                <TouchableOpacity
                  style={styles.vehicleActionsBlock}
                  activeOpacity={0.7}
                  onPress={() =>
                    Alert.alert(
                      'Alert',
                      'Deseja realmente bloquear veiculo',
                      [
                        {
                          text: 'Cancel',
                          onPress: () => {},
                          style: 'cancel',
                        },
                        {
                          text: 'OK',
                          onPress: () =>
                            sendBlocker(vehicle, 'block', 'Veículo Bloqueado'),
                        },
                      ],
                      { cancelable: false },
                    )
                  }
                >
                  {loadingCommand == null ||
                  loadingCommand !== `block-${vehicle.imei}` ? (
                    <Image
                      source={require('../../assets/images/of.png')}
                      style={{
                        height: 100,
                        width: 62,
                        marginLeft: '30%',
                      }}
                    />
                  ) : (
                    <ActivityIndicator
                      style={{ marginLeft: 50 }}
                      size="small"
                      color="black"
                    />
                  )}
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.vehicleActionsUnBlock}
                  activeOpacity={0.7}
                  onPress={() =>
                    Alert.alert(
                      'Alert',
                      'Deseja realmente desbloquear veiculo',
                      [
                        {
                          text: 'Cancel',
                          onPress: () => {},
                          style: 'cancel',
                        },
                        {
                          text: 'OK',
                          onPress: () =>
                            sendBlocker(
                              vehicle,
                              'unblock',
                              'Veículo Desbloqueado',
                            ),
                        },
                      ],
                      { cancelable: false },
                    )
                  }
                >
                  {loadingCommand == null ||
                  loadingCommand !== `unblock-${vehicle.imei}` ? (
                    <Image
                      source={require('../../assets/images/on.png')}
                      style={{
                        height: 100,
                        width: 62,
                        marginRight: '15%',
                      }}
                    />
                  ) : (
                    <ActivityIndicator
                      style={{ marginLeft: -100 }}
                      size="small"
                      color="black"
                    />
                  )}
                </TouchableOpacity>
              </View>
              <TouchableOpacity
                onPress={() => setVisible(false)}
                style={{
                  backgroundColor: '#004e70',
                  paddingHorizontal: 30,
                  paddingVertical: 10,
                  borderRadius: 5,
                  marginTop: 20,
                }}
              >
                <Text style={{ color: 'white', fontSize: 16 }} allowFontScaling={false}>Fechar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </Modal>

        <TouchableOpacity
          style={[
            styles.buttonMapBase,
            {
              width: 34,
              height: 34,
              right: 0,
              bottom: 0,
              left: 0,
              flexDirection: 'row',
              backgroundColor: 'rgba(255,255,255,0.6)',
              marginLeft: 15,
              borderWidth: mapType === 'satellite' ? 3 : 0,
              borderColor: mapType === 'satellite' ? '#004e70' : 'transparent',
            },
          ]}
          onPress={() =>
            setMapType(mapType === 'satellite' ? 'standard' : 'satellite')
          }
        >
          {mapType === 'satellite' ? (
            <Image source={assets.images.icon_satellite_2} />
          ) : (
            <Image source={assets.images.icon_satellite} />
          )}
        </TouchableOpacity>

        <TouchableOpacity
          style={[
            styles.buttonMapBase,
            {
              width: 34,
              height: 34,
              right: 0,
              bottom: 0,
              left: 0,
              flexDirection: 'row',
              backgroundColor: 'rgba(255,255,255,0.6)',
              marginLeft: 15,
              top: getStatusBarHeight() + 74,
              borderWidth: showTraffic ? 3 : 0,
              borderColor: showTraffic ? '#004e70' : 'transparent',
            },
          ]}
          onPress={() => setShowTraffic(!showTraffic)}
        >
          <MaterialIcon name="traffic" size={24} color="black" />
        </TouchableOpacity>

        <TouchableOpacity
          style={[
            styles.buttonMapBase,
            {
              width: 34,
              height: 34,
              right: 0,
              bottom: 0,
              left: 0,
              flexDirection: 'row',
              backgroundColor: 'rgba(255,255,255,0.6)',
              marginLeft: 15,
              top: getStatusBarHeight() + 124,
              borderWidth: showTraffic ? 3 : 0,
              borderColor: showTraffic ? '#004e70' : 'transparent',
            },
          ]}
          onPress={() =>
            Linking.openURL(
              `http://maps.google.com/?cbll=${parseFloat(
                vehicle.lat,
              )},${parseFloat(vehicle.lng)}&cbp=12,20.09,,0,5&layer=c`,
            )
          }
        >
          <FontAwesomeIcon name="street-view" color="#ffcc00" size={20} />
        </TouchableOpacity>

        <TouchableOpacity
          style={{
            position: 'absolute',
            top: 5,
            right: 0,
            bottom: 0,
            left: 0,
            width: 100,
            height: 50,
            justifyContent: 'center',
            alignItems: 'center',
            marginLeft: widthPercentageToDP('70%'),
            marginTop: heightPercentageToDP('3%'),
            zIndex: 2,
          }}
          onPress={() => setToggle(!toggle)}
        >
          <Image source={require('../../assets/images/fullscreen.png')} />
        </TouchableOpacity>

        <View
          style={{
            position: 'absolute',
            top: 90,
            right: 0,
            bottom: 0,
            left: 0,
            backgroundColor: '#fff',
            width: 70,
            height: 30,
            justifyContent: 'center',
            alignItems: 'center',
            elevation: 2,
            marginLeft: widthPercentageToDP('70%'),
            marginTop:
              toggle === false
                ? heightPercentageToDP('25%')
                : heightPercentageToDP('70%'),
            zIndex: 2,
            flexDirection: 'row',
          }}
        >
          <TouchableOpacity onPress={onPressZoomOut}>
            <AntDesignIcon name="plus" size={20} />
          </TouchableOpacity>
          <Text>{'      '}</Text>
          <TouchableOpacity onPress={onPressZoomIn}>
            <AntDesignIcon name="minus" size={20} />
          </TouchableOpacity>
        </View>

        <MapView
          style={{
            width: widthPercentageToDP('100%'),
            height:
              toggle === false
                ? heightPercentageToDP('50%')
                : heightPercentageToDP('100%'),
          }}
          mapType={mapType}
          showsTraffic={showTraffic}
          ref={mapRef}
          region={{
            latitude,
            longitude,
            latitudeDelta,
            longitudeDelta,
          }}
          showsUserLocation
          zoomEnabled
        >
          <Marker
            coordinate={{
              latitude: parseFloat(vehicle.lat),
              longitude: parseFloat(vehicle.lng),
            }}
            title={vehicle.name}
          >
            <ImageBackground
              source={{ uri: vehicle.imagem_icone }}
              resizeMode="contain"
              style={{ width: 48, height: 32 }}
            />
          </Marker>
        </MapView>

        <View style={{ marginTop: 10 }}>
          <Modal
            animationType="slide"
            transparent={false}
            visible={modalVisible}
            onRequestClose={() => setModalVisible(!modalVisible)}
          >
            <TouchableHighlight
              onPress={() => setModalVisible(!modalVisible)}
              style={{ alignSelf: 'center' }}
            >
              <AntDesignIcon name="arrowdown" size={60} color="black" />
            </TouchableHighlight>
          </Modal>

          <View style={{ marginTop: 10, margin: 10 }}>
            <View>
              <View
                style={{
                  flexDirection: 'row',
                  alignSelf: 'center',
                  marginTop: 30,
                }}
              >
                <TouchableOpacity
                  onPress={() =>
                    scrollViewRef.current?.scrollTo({
                      y: Dimensions.get('window').height,
                    })
                  }
                  style={{
                    width: 75,
                    height: 75,
                    backgroundColor: '#004e70',
                    borderRadius: 100,
                  }}
                >
                  <AntDesignIcon
                    name="arrowdown"
                    size={50}
                    color="white"
                    style={{ alignSelf: 'center', padding: 10 }}
                  />
                </TouchableOpacity>

                <TouchableOpacity onPress={refreshHandler}>
                  <ImageBackground
                    source={require('../../assets/images/refresh.png')}
                    resizeMode="contain"
                    style={{ width: 80, height: 80 }}
                  />
                </TouchableOpacity>

                <TouchableOpacity onPress={() => setVisible(true)}>
                  <ImageBackground
                    source={require('../../assets/images/car-action.png')}
                    resizeMode="contain"
                    style={{ width: 80, height: 80 }}
                  />
                </TouchableOpacity>
              </View>
            </View>

            {/* Detail sections */}
            <View style={styles.headerContainer}>
              <Text style={styles.vehicleName} allowFontScaling={false}>{vehicle.name + ' (' + vehicle.tipo + ')'}</Text>
              {vehicle.motorista && (
                <View style={{ flexDirection: 'row' }}><MaterialCommunityIcons name="account" size={20} color="#005580" /><Text style={styles.vehicleMotorista}>{vehicle.motorista}</Text></View>
              )}
              {vehicle.endereco && (
                <Text style={styles.vehicleAddress} allowFontScaling={false}>{vehicle.endereco}</Text>
              )}
            </View>

            <View style={styles.cardContainer}>
              {/* Cabeçalho */}
              <View style={styles.cardHeader}>
                <Text style={styles.header} allowFontScaling={false}>
                  Posição: {vehicle.data_posicao}
                </Text>

                <Text style={styles.header} allowFontScaling={false}>
                  Comunicação: {vehicle.data_comunicacao}
                </Text>

                <Text style={styles.header} allowFontScaling={false}>
                  IMEI: {vehicle.imei}
                </Text>
              </View>

              {/* Grid principal */}
              <View style={styles.grid}>
                {[
                  { icon: "speedometer", label: "VELOCIDADE", value: `${vehicle.velocidade} KM/H` },
                  { icon: "engine-off", label: "IGNAÇÃO", value: (vehicle.ligado ? 'Ligada' : 'Desligada'), valueStyle: { color: vehicle.ign_color } },
                  vehicle.ligado && vehicle.template_telemetria?.rpm && { icon: "gauge", label: "RPM", value: vehicle.rpm },
                  vehicle.ligado && vehicle.template_telemetria?.odometro && { icon: "counter", label: "ODÔMETRO", value: `${vehicle.odometro} KM` },
                  { icon: "flash", label: "BATERIA", value: vehicle.voltagem_bateria ? `${vehicle.voltagem_bateria} V` : '-' },
                  { icon: "flash", label: "BATERIA INT.", value: vehicle.voltagem_bateria_int ? `${vehicle.voltagem_bateria_int} V` : '-' },
                  { icon: "anchor", label: "ÂNCORA", value: vehicle.ancora === '1' ? 'Ativo' : 'Inativo' },
                  { icon: "lock-open", label: "STATUS", value: vehicle.bloqueado ? 'Bloqueado' : 'Desbloq.' },
                  vehicle.ligado && vehicle.template_telemetria?.ambiente_temp && { icon: "thermometer", label: "TEMP. MOTOR", value: `${vehicle.temp_motor} °C` },
                  vehicle.ligado && vehicle.template_telemetria?.oleo_temp && { icon: "oil", label: "TEMP. ÓLEO", value: `${vehicle.temp_oleo} °C` },
                  vehicle.ligado && vehicle.template_telemetria?.torque && { icon: "engine", label: "TORQUE", value: `${vehicle.torque} Nm` },
                  vehicle.ligado && vehicle.template_telemetria?.arrefecimento_temp && { icon: "coolant-temperature", label: "HORIMETRO", value: `${vehicle.horimetro} h` },
                  vehicle.ligado && vehicle.template_telemetria?.acelerador_posicao && { icon: "arrow-right-bold", label: "POSIÇÃO DO ACELERADOR", value: `${vehicle.acelerador_posicao} %` },
                  vehicle.ligado && vehicle.template_telemetria?.freio_posicao && { icon: "car-brake-hold", label: "POSIÇÃO DO FREIO", value: `${vehicle.freio_posicao} %` },
                  vehicle.ligado && vehicle.template_telemetria?.marcha_atual && { icon: "car-shift-pattern", label: "MARCHA ATUAL", value: `${vehicle.marcha_atual}` },
                  vehicle.ligado && vehicle.template_telemetria?.motor_carga_calculada && { icon: "engine-outline", label: "CARGA DO MOTOR", value: `${vehicle.carga_motor} %` },
                  vehicle.ligado && vehicle.template_telemetria?.distancia_desde_limpeza && { icon: "cog", label: "DISTÂNCIA DESDE LIMP.", value: `${vehicle.distancia_desde_limpeza} KM` },
                  vehicle.ligado && vehicle.template_telemetria?.distancia_percorrida_lamp && { icon: "wrench", label: "DISTÂNCIA PERC. MIL", value: `${vehicle.distancia_percorrida_lamp} KM` },
                  { icon: "alert-circle-outline", label: "EVENTO", value: vehicle.evento },
                  { icon: "card-account-details-outline", label: "ID", value: vehicle.id },
                ]
                  .filter(Boolean) // remove os falsos (ex: rpm/odometro quando não existe)
                  .map((item, index) => (
                    <View
                      key={index}
                      style={[
                        styles.item,
                        (index + 1) % 3 !== 0 && styles.itemWithBorder // borda apenas se não for múltiplo de 3
                      ]}
                    >
                      <MaterialCommunityIcons name={item.icon} size={20} color="#333" />
                      <Text style={styles.label} allowFontScaling={false}>{item.label}</Text>
                      <Text style={[styles.value, item.valueStyle]} allowFontScaling={false}>{item.value}</Text>
                    </View>
                  ))
                }
              </View>

              { vehicle.ligado && vehicle.template_telemetria?.combustivel_nivel && (
                <View style={styles.fuelContainer}>
                  <Text style={styles.label} allowFontScaling={false}>Nível de Combustível</Text>
                  <Text style={styles.fuelValue} allowFontScaling={false}>
                    {(() => {
                      const combustivelNum = Number(vehicle.combustivel_nivel);
                      if (!vehicle?.combustivel_nivel || combustivelNum < 0) return '0%';
                      if (combustivelNum > 100) return '100%';
                      return combustivelNum + '%';
                    })()}
                  </Text>
                  <View style={styles.fuelBar}>
                    <View
                      style={[
                        styles.fuelFill,
                        {
                          width: (() => {
                            const combustivelNum = Number(vehicle.combustivel_nivel);
                            if (!vehicle?.combustivel_nivel || combustivelNum < 0) return '0%';
                            if (combustivelNum > 100) return '100%';
                            return combustivelNum + '%';
                          })(),
                        },
                      ]}
                    />
                  </View>
                </View>
              )}

              {/* Rodapé */}
              { vehicle.ligado && (
                  vehicle.template_telemetria?.economia_instantanea ||
                  vehicle.template_telemetria?.combustivel_total_usado ||
                  vehicle.template_telemetria?.combustivel_pressao ||
                  vehicle.template_telemetria?.combustivel_status
                ) && (
                <View style={styles.footer}>
                  { vehicle.template_telemetria?.economia_instantanea && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>ECONOMIA INSTANTÂNEA</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.economia_instantanea} KM/L</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_total_usado && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>TOTAL USADO</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_total_usado} L</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_pressao && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>PRESSÃO</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_pressao} kPa</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_status && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>STATUS SISTEMA</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_status}</Text>
                    </View>
                  )}
                </View>
              )}

              <View style={styles.latLongContainer}>
                <View>
                  <Text style={styles.label} allowFontScaling={false}>Latitude / Longitude</Text>
                  <Text style={styles.value} allowFontScaling={false}>
                    {vehicle.lat} / {vehicle.lng}
                  </Text>
                </View>

                <TouchableOpacity
                  style={styles.latLongIcon}
                  onPress={() => {
                    const scheme = Platform.select({
                      ios: 'maps:0,0?q=',
                      android: 'geo:0,0?q=',
                    });

                    const latLng = `${vehicle.lat},${vehicle.lng}`;
                    const url = Platform.select({
                      ios: `${scheme}${vehicle.name}@${latLng}`,
                      android: `${scheme}${latLng}(${vehicle.name})`,
                    });

                    Linking.openURL(url);
                  }}
                >

                  <MaterialCommunityIcons
                    name="directions"
                    size={20}
                    color="#fff"
                  />
                </TouchableOpacity>
              </View>
            </View>

          </View>
        </View>
      </ScrollView>
    </View>
  );
};

export default VehiclePage;

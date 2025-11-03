import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  ImageBackground,
  Image,
  Modal,
  AppState,
  FlatList,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import { SearchBar } from 'react-native-elements';
import { connect } from 'react-redux';
import styles from './styles';
import Header from '../../Components/Header';
import ModalCreateLinkVehicle from '../../Components/ModalCreateLinkVehicle';
import ModalOptionsOpenLocationVehicle from '../../Components/ModalOptionsOpenLocationVehicle';
import { useAuth } from '../../context/authContext';

const VehiclesPage = (props) => {
  const { selectedAccount } = useAuth();

  const [loading, setLoading] = useState(false);
  const [vehicles, setVehicles] = useState([]);

  const [page, setPage] = useState(0); // começa na página 0
  const [limit] = useState(10);
  const [hasMore, setHasMore] = useState(true);

  const [search, setSearch] = useState(null);
  const [searchBar, setSearchBar] = useState('');
  const [command, setCommand] = useState(null);
  const [visible, setVisible] = useState(false);
  const [desligado, setDesligado] = useState(0);
  const [ligado, setLigado] = useState(0);
  const [total, setTotal] = useState(0);
  const [parado, setParado] = useState(0);
  const [showModalCreateLink, setShowModalCreateLink] = useState(false);
  const [showOptionsOpenLocation, setShowOptionsOpenLocation] = useState(false);
  const [idBemVehicleSelected, setIdBemVehicleSelected] = useState('');
  const [vehicleSelected, setVehicleSelected] = useState({});

  const hideModal = () => setVisible(false);
  const showModal = () => setVisible(true);

  const getData = useCallback(
    async (load = false) => {
      if (load) {
        setLoading(true);
      }

      try {
        const accessUserName = selectedAccount.username;
        const result = await fetch(
          `${props.baseUrl}/metronic/api/get_qtd.php?v_login=${accessUserName}`,
        );

        let data = await result.text();

        if (Platform.OS === 'android') {
          data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
        }

        data = JSON.parse(data);
        setDesligado(data.desligado);
        setLigado(data.ligado);
        setTotal(data.total);
        setParado(data.parado);
      } catch (error) {
        console.log({ error });
      } finally {
        setLoading(false);
      }
    },
    [props.baseUrl, selectedAccount.username],
  );

  const abortControllerRef = useRef(null);

  const getVehicles = useCallback(
    async (pageParam = 0, searchParam = '', commandParam = '') => {
      let currentPage = pageParam;
      let currentSearch = searchParam;
      let currentCommand = commandParam;

      if (pageParam === null) {
        currentPage = 0;
        setHasMore(true);
        setVehicles([]);

        // Cancelar requisições anteriores
        if (abortControllerRef.current) {
          abortControllerRef.current.abort();
        }
      }

      if (searchParam === undefined || searchParam === null) {
        setSearch(null);
        setSearchBar('');
        currentSearch = '';
      }

      if (commandParam === undefined || commandParam === null) {
        setCommand(null);
        currentCommand = '';
      }

      if (loading || !hasMore) return;
      setLoading(true);

      try {
        const accessUserName = selectedAccount.username;
        const enterpriseStorage = await AsyncStorage.getItem(
          '@ctracker:enterprise',
        );
        const { baseUrl } = JSON.parse(enterpriseStorage);

        // cria novo AbortController e salva no ref
        const controller = new AbortController();
        abortControllerRef.current = controller;

        const result = await fetch(
          `${baseUrl}/metronic/api/get_veiculos.php?v_login=${accessUserName}&v_busca=${currentSearch}&v_chave=${currentCommand}&paginacao=${1}&pagina=${currentPage}&limite=${limit}`,
          { signal: controller.signal },
        );

        let data = await result.text();

        if (Platform.OS === 'android') {
          data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
        }

        data = JSON.parse(data);

        if (data && data.length < limit) {
          setHasMore(false); // não tem mais páginas
        }

        if (currentPage === 0) {
          setVehicles(data); // primeira página substitui
        } else {
          setVehicles((prev) => {
            // remove duplicados
            const ids = new Set(prev.map((v) => v.id_bem));
            const newVehicles = (Array.isArray(data) ? data : []).filter(
              (v) => !ids.has(v.id_bem),
            );
            return [...prev, ...newVehicles];
          });
        }

        setPage(currentPage);
      } catch (error) {
        if (error.name === 'AbortError' || error.message === 'Aborted') {
          console.log('Requisição cancelada');
          return; // não deixa continuar pro finally
        }
        console.log('Erro na requisição:', error);
      } finally {
        setLoading(false);
      }
    },
    [selectedAccount.username],
  );

  useEffect(() => {
    const interval = setInterval(() => {
      getData();
    }, 30000);

    return () => {
      clearInterval(interval);
    };
  }, [getData, props.navigation]);

  // Primeira ao inicial a página
  useEffect(() => {
    const unsubscribe = props.navigation.addListener('focus', async () => {
      setVehicles([]);
      await getData(true);
      await getVehicles(null);
    });

    return unsubscribe;
  }, [props.navigation, getVehicles]);

  // Pesquisa
  useEffect(() => {
    if (
      (search !== undefined && search !== null) ||
      (command !== undefined && command !== null)
    ) {
      getVehicles(null, search, command);
    }
  }, [search, command]);

  // Ao minimizar o APP
  const appState = useRef(AppState.currentState);
  const handleAppStateChange = async (nextAppState) => {
    if (
      appState.current.match(/inactive|background/) &&
      nextAppState === 'active'
    ) {
      // Essa função será chamada quando voltar ao app
      setVehicles([]);
      await getData(true);
      await getVehicles(null);
    }

    appState.current = nextAppState;
  };

  useEffect(() => {
    const subscription = AppState.addEventListener(
      'change',
      handleAppStateChange,
    );

    return () => {
      subscription.remove();
    };
  }, []);

  const loadMore = () => {
    if (vehicles.length === 0) return;

    if (!loading && hasMore) {
      getVehicles(page + 1, search, command);
    }
  };

  const openVehicle = (vehicleId) => {
    props.navigation.navigate('Vehicle', { vehicleId });
  };

  const sendCommand = async (commandParam) => {
    hideModal();
    setCommand(commandParam);
  };

  const renderFooter = () => {
    if (!loading) return null;
    if (vehicles.length > 0) return null;
    return (
      <ActivityIndicator size="large" color="#414141" style={{ margin: 20 }} />
    );
  };

  const renderVehicle = ({ item, index }) => {
    const isLast = index === vehicles.length - 1;

    return (
      <React.Fragment key={item.id_bem}>
        <TouchableOpacity
          style={[
            styles.baseCard,
            isLast && styles.baseCardLast, // aplica estilo se for o último
          ]}
          activeOpacity={0.7}
          onPress={() => openVehicle(item.id_bem)}
        >
          <View style={styles.card}>
            {/* Header com placa e status */}
            <View style={styles.header}>
              <View style={styles.baseIconCar}>
                <Image
                  source={
                    item.imagem_veiculo || item.imagem_icone
                      ? { uri: item.imagem_veiculo || item.imagem_icone }
                      : require('../../assets/images/default_car.png')
                  }
                  style={styles.iconCar}
                  resizeMode="cover"
                />
              </View>

              <View style={{ flex: 1, marginLeft: 10 }}>
                <Text
                  style={styles.plate}
                  numberOfLines={1}
                  ellipsizeMode="tail"
                >
                  {item.name}
                </Text>
                <Text
                  style={styles.subtitle}
                  numberOfLines={1}
                  ellipsizeMode="tail"
                >
                  {item.tipo}
                </Text>
              </View>
            </View>

            <View style={styles.contentStatus}>
              <View
                style={[
                  styles.iconStatus,
                  item.ancora === '1' ? styles.on : styles.off,
                ]}
              >
                <MaterialCommunityIcons
                  name="anchor"
                  size={16}
                  color={item.ancora === '1' ? '#065f46' : '#991b1b'}
                />
              </View>

              <View
                style={[
                  styles.iconStatus,
                  item.bloqueado === '1' ? styles.off : styles.on,
                ]}
              >
                <MaterialCommunityIcons
                  name={item.bloqueado === '1' ? 'lock' : 'lock-open'}
                  size={16}
                  color={item.bloqueado === '1' ? '#991b1b' : '#065f46'}
                />
              </View>

              <View
                style={[
                  styles.statusBadge,
                  item.speed > 0
                    ? styles.moving
                    : item.ligado === 'S'
                    ? styles.on
                    : styles.off,
                ]}
              >
                <MaterialCommunityIcons
                  name={item.speed > 0 ? 'run' : 'power'}
                  size={16}
                  color={
                    item.speed > 0
                      ? '#0000ff'
                      : item.ligado === 'S'
                      ? '#065f46'
                      : '#991b1b'
                  }
                />

                <Text
                  style={[
                    styles.statusText,
                    item.speed > 0
                      ? { color: '#0000ff' }
                      : item.ligado === 'S'
                      ? { color: '#065f46' }
                      : { color: '#991b1b' },
                  ]}
                >
                  {item.speed > 0
                    ? 'Em movimento'
                    : item.ligado === 'S'
                    ? 'Ligado'
                    : 'Desligado'}
                </Text>
              </View>
            </View>

            {/* Informações */}
            <View style={styles.infoRow}>
              <View style={styles.infoItem}>
                <View style={styles.infoItemIcon}>
                  <MaterialCommunityIcons
                    name="speedometer"
                    size={20}
                    color="#fff"
                  />
                </View>
                <View>
                  <Text style={styles.infoLabel}>VELOCIDADE</Text>
                  <Text style={styles.infoValue}>{item.speed} KM/H</Text>
                </View>
              </View>
              <View style={styles.infoItem}>
                <View style={styles.infoItemIcon}>
                  <MaterialCommunityIcons
                    name="counter"
                    size={20}
                    color="#fff"
                  />
                </View>
                <View>
                  <Text style={styles.infoLabel}>ODÔMETRO</Text>
                  <Text style={styles.infoValue}>{item.km_rodado} KM</Text>
                </View>
              </View>
              <View style={styles.infoItem}>
                <View style={styles.infoItemIcon}>
                  <MaterialCommunityIcons name="flash" size={20} color="#fff" />
                </View>
                <View>
                  <Text style={styles.infoLabel}>BATERIA</Text>
                  <Text style={styles.infoValue}>
                    {item.voltagem_bateria} V
                  </Text>
                </View>
              </View>
            </View>

            {/* Motorista */}
            {item.motorista && (
              <View style={styles.driverBox}>
                <MaterialCommunityIcons
                  name="account"
                  size={20}
                  color="#005580"
                />
                <Text style={styles.driver}>{item.motorista}</Text>
              </View>
            )}

            {/* Endereço */}
            <View style={styles.addressBox}>
              <MaterialIcons name="place" size={20} color="#005580" />
              <Text style={styles.address}>{item.address_short}</Text>
            </View>

            {/* Última atualização */}
            <View style={styles.cardFooter}>
              <View style={styles.footerLastUpdateBox}>
                <Text style={styles.lastUpdate}>
                  Última atualização em {item.dia}
                </Text>
              </View>

              <View style={styles.cardFooterBotoes}>
                <TouchableOpacity
                  style={styles.btnFooterIcon}
                  onPress={() => {
                    /* const scheme = Platform.select({
                      ios: 'maps:0,0?q=',
                      android: 'geo:0,0?q=',
                    });

                    const latLng = `${item.lat},${item.lng}`;
                    const url = Platform.select({
                      ios: `${scheme}${item.name}@${latLng}`,
                      android: `${scheme}${latLng}(${item.name})`,
                    });

                    Linking.openURL(url); */

                    setShowOptionsOpenLocation(true);
                    setVehicleSelected(item);
                  }}
                >
                  <MaterialCommunityIcons
                    name="directions"
                    size={20}
                    color="#fff"
                  />
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.btnFooterIcon}
                  onPress={() => {
                    setShowModalCreateLink(true);
                    setIdBemVehicleSelected(item.id_bem);
                  }}
                >
                  <MaterialCommunityIcons
                    name="share-variant"
                    size={20}
                    color="#fff"
                  />
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </TouchableOpacity>
      </React.Fragment>
    );
  };

  return (
    <ImageBackground
      source={require('../../assets/images/VheicleBackground.jpg')}
      style={styles.container}
    >
      <Header title="Veículos" {...props} />
      <Modal
        animationType="slide"
        transparent
        visible={visible}
        onRequestClose={hideModal}
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
              source={require('../../assets/images/filter.png')}
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
            >
              Filtrar por
            </Text>
            <View
              style={{
                flexDirection: 'row',
                marginTop: '10%',
                alignSelf: 'center',
              }}
            >
              <TouchableOpacity
                style={styles.vehicleActionsBlock}
                activeOpacity={0.7}
                onPress={() => sendCommand('S', 'Veiculos Ligados')}
              >
                <View style={{ flexDirection: 'column' }}>
                  <Image
                    source={require('../../assets/images/on-1.png')}
                    style={{
                      height: 85,
                      width: 80,
                      marginLeft: '25%',
                    }}
                  />
                  <Text
                    style={{ textAlign: 'center', color: 'grey', fontSize: 13 }}
                  >
                    Veiculos Ligados
                  </Text>
                </View>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.vehicleActionsUnBlock}
                activeOpacity={0.7}
                onPress={() => sendCommand('N', 'Veiculos Desligados')}
              >
                <View style={{ flexDirection: 'column' }}>
                  <Image
                    source={require('../../assets/images/of-1.png')}
                    style={{
                      height: 85,
                      width: 80,
                      marginLeft: '20%',
                    }}
                  />
                  <Text
                    style={{ textAlign: 'center', color: 'grey', fontSize: 13 }}
                  >
                    Veiculos Desligados
                  </Text>
                </View>
              </TouchableOpacity>
            </View>
            <TouchableOpacity
              onPress={hideModal}
              style={{
                backgroundColor: '#004e70',
                paddingHorizontal: 30,
                paddingVertical: 10,
                borderRadius: 5,
                marginTop: 20,
              }}
            >
              <Text style={{ color: 'white', fontSize: 16 }}>Fechar</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      <FlatList
        data={vehicles}
        renderItem={renderVehicle}
        onEndReached={loadMore}
        onEndReachedThreshold={0.5}
        ListHeaderComponent={
          <>
            <View style={{ flexDirection: 'row', marginTop: 5 }}>
              <SearchBar
                placeholder="Pesquisar por Veículo..."
                onChangeText={(val) => {
                  setSearchBar(val);
                }}
                onClear={() => {
                  setSearch('');
                  setSearchBar('');
                }}
                value={searchBar}
                onSubmitEditing={(e) => {
                  setSearch(e.nativeEvent.text);
                }} // não faz nada, teclado não abaixa
                returnKeyType="search"
                platform={Platform.OS === 'ios' ? 'ios' : 'android'} // deixa nativo no iOS
                lightTheme
                round
                searchIcon={{ size: 22, color: '#555' }}
                clearIcon={{ color: '#999' }}
                containerStyle={{
                  flex: 1,
                  backgroundColor: 'transparent',
                  borderTopWidth: 0,
                  borderBottomWidth: 0,
                  padding: 0,
                  justifyContent: 'center',
                  height: Platform.OS === 'android' ? 58 : 49,
                  width: '100%',
                  marginHorizontal: 10,
                }}
                inputContainerStyle={{
                  backgroundColor: '#fff',
                  borderRadius: 12,
                  paddingHorizontal: 8,
                  height: 50,
                  borderWidth: 1,
                  borderColor: '#ddd',
                }}
                inputStyle={{
                  fontSize: 16,
                  color: '#333',
                }}
              />
              <TouchableOpacity
                onPress={() => getVehicles(null, search, command)}
              >
                <Image
                  source={require('../../assets/images/reload.png')}
                  style={{
                    height: 47,
                    width: 45,
                    marginRight: 10,
                    marginTop: Platform.OS === 'ios' ? null : 5,
                  }}
                />
              </TouchableOpacity>
              <TouchableOpacity onPress={showModal}>
                <Image
                  source={require('../../assets/images/filter.png')}
                  style={{
                    height: 47,
                    width: 45,
                    marginRight: 10,
                    marginTop: Platform.OS === 'ios' ? null : 5,
                  }}
                />
              </TouchableOpacity>
            </View>
            <View style={styles.itensTopo}>
              <View style={styles.itemTopo}>
                <Text
                  style={{ color: '#004e70', fontWeight: 'bold', fontSize: 13 }}
                >
                  Total:{' '}
                </Text>
                <Text style={{ color: '#004e70', fontSize: 13 }}>{total}</Text>
              </View>

              <View style={styles.itemTopo}>
                <Text
                  style={{ color: '#00b300', fontWeight: 'bold', fontSize: 13 }}
                >
                  Ligados:{' '}
                </Text>
                <Text style={{ color: '#00b300', fontSize: 13 }}>{ligado}</Text>
              </View>

              <View style={styles.itemTopo}>
                <Text
                  style={{ color: '#cc0000', fontWeight: 'bold', fontSize: 13 }}
                >
                  Desligados:{' '}
                </Text>
                <Text style={{ color: '#cc0000', fontSize: 13 }}>
                  {desligado}
                </Text>
              </View>

              <View style={styles.itemTopo}>
                <Text
                  style={{ color: 'black', fontWeight: 'bold', fontSize: 13 }}
                >
                  Parado:{' '}
                </Text>
                <Text style={{ color: 'black', fontSize: 13 }}>{parado}</Text>
              </View>
            </View>
          </>
        }
        ListFooterComponent={renderFooter}
        keyboardShouldPersistTaps="handled"
        // contentContainerStyle={{ paddingBottom: 100 }}
      />

      {/* Loader flutuante */}
      {loading && vehicles.length > 0 && (
        <View
          style={{
            position: 'absolute',
            bottom: 40, // sobe um pouco do rodapé
            left: 0,
            right: 0,
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 9999999,
          }}
        >
          <View
            style={{
              backgroundColor: 'rgba(0,0,0,0.5)', // fundo escuro com opacidade
              borderRadius: 10,
              padding: 20,
            }}
          >
            <ActivityIndicator size="large" color="#fff" />
          </View>
        </View>
      )}

      <ModalCreateLinkVehicle
        handleClose={() => setShowModalCreateLink(false)}
        visible={showModalCreateLink}
        id_bem={idBemVehicleSelected}
      />
      <ModalOptionsOpenLocationVehicle
        handleClose={() => setShowOptionsOpenLocation(false)}
        visible={showOptionsOpenLocation}
        vehicle={vehicleSelected}
      />
    </ImageBackground>
  );
};

const mapStateToProps = (state) => state.enterprise;
export default connect(mapStateToProps, null)(VehiclesPage);

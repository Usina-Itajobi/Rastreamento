/* eslint-disable react/prop-types */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/jsx-props-no-spreading */
import React, {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  Dimensions,
  SafeAreaView,
  Image,
  Linking,
} from 'react-native';
import LottieView from 'lottie-react-native';
import { PROVIDER_GOOGLE, Marker, Callout } from 'react-native-maps';
import Icon from 'react-native-vector-icons/AntDesign';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import MapView from 'react-native-map-clustering';

import { getStatusBarHeight } from 'react-native-iphone-x-helper';
import { useIsFocused } from '@react-navigation/native';
import Header from '../../Components/Header';
import BottomSheet from './components/BottomSheet';
import VehicleBottomSheet from './components/VehicleBottomSheet';
import assets from '../../assets';

import styles from './styles';
import CalloutVehicle from './components/CalloutVehicle';
import { useAuth } from '../../context/authContext';

const dimension =
  Dimensions.get('window').width / Dimensions.get('window').height;

function Map(props) {
  const map = useRef();
  const isFocused = useIsFocused();
  const { selectedAccount } = useAuth();

  const [vehicle, setVehicle] = useState([]);
  const [vehiclesFilter, setVehiclesFilter] = useState([]);
  const [markersRefs, setMarkersRefs] = useState([]);

  const [mapType, setMapType] = useState('standard');
  const [latitude, setLatitude] = useState(-23.533773);
  const [longitude, setLongitude] = useState(-46.62529);
  const [latitudeDelta, setLatitudeDelta] = useState(8);
  const [longitudeDelta, setLongitudeDelta] = useState(dimension * 0.00522);
  const [showTraffic, setShowTraffic] = useState(false);
  const [vehicleSelected, setVehicleSelected] = useState(null);
  const [loadingVehicleUnique, setLoadingVehicleUnique] = useState(false);

  const getVehicles = useCallback(
    async (firstAccess = false) => {
      try {
        const accessUserName = selectedAccount.username;
        const enterprise = await AsyncStorage.getItem('@grupoitajobi:enterprise');
        console.log('enterprise:', enterprise);

        const baseUrl = 'https://itajobi.usinaitajobi.com.br';
        console.log('baseUrl:', baseUrl);

        const url = `${baseUrl}/metronic/api/get_veiculos.php?&v_login=${accessUserName}`;
        console.log('fetch URL:', url);

        const result = await fetch(url);
        let data = await result.text();
        console.log('fetch result text:', data);

        if (Platform.OS === 'android') {
          data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
        }

        data = JSON.parse(data);
        console.log('parsed data:', data);

        if (firstAccess) {
          if (data[0]?.lat && data[0]?.lng) {
            setLatitude(Number(data[0]?.lat));
            setLongitude(Number(data[0]?.lng));
          }

          const coordinates = data.map((c) => ({
            latitude: Number(c.lat),
            longitude: Number(c.lng),
          }));

          setTimeout(() => {
            if (map.current) {
              map.current.fitToCoordinates(coordinates, {
                edgePadding: {
                  top: 150,
                  right: 150,
                  bottom: 150,
                  left: 150,
                },
              });
            }
          }, 3000);
        }

        setVehicle(data.map((v) => ({ ...v, user: accessUserName })));

        setVehiclesFilter((oldData) => {
          if (oldData.length === 1) {
            let vehicleFilter = data.filter(
              (d) => d.id_bem === oldData[0].id_bem,
            );

            vehicleFilter = vehicleFilter.map((v) => ({
              ...v,
              user: accessUserName,
            }));

            try {
              map.current.animateCamera({
                center: {
                  latitude: Number(vehicleFilter[0]?.lat),
                  longitude: Number(vehicleFilter[0]?.lng),
                },
                zoom: 24,
              });
            } catch {
              setLatitude(Number(vehicleFilter[0]?.lat));
              setLongitude(Number(vehicleFilter[0]?.lng));
            }

            return vehicleFilter;
          }
          return data.map((v) => ({ ...v, user: accessUserName }));
        });
      } catch (error) {
        console.log(error);
      }
    },
    [selectedAccount.username],
  );

  useEffect(() => {
    getVehicles(true);
  }, [getVehicles]);

  useEffect(() => {
    const interval = setInterval(() => {
      getVehicles();

      if (vehicleSelected?.id) {
        getVehicleUnique(vehicleSelected.id, false);
      }
    }, 30000);

    return () => clearInterval(interval);
  }, [getVehicles]);

  function onPressZoomIn() {
    const region = {
      latitude,
      longitude,
      latitudeDelta: latitudeDelta * 10,
      longitudeDelta: longitudeDelta * 10,
    };

    setLatitudeDelta(region.latitudeDelta);
    setLongitudeDelta(region.longitudeDelta);
    setLatitude(region.latitude);
    setLongitude(region.longitude);

    if (map.current) {
      map.current.animateToRegion(region, 100);
    }
  }

  async function getVehicleUnique(id, loading = true) {
    if (loadingVehicleUnique || !id) {
      return;
    }

    if (loading) {
      setLoadingVehicleUnique(true);
    }

    try {
      const accessToken = selectedAccount.h;
      const enterprise = await AsyncStorage.getItem('@grupoitajobi:enterprise');
      console.log('enterprise:', enterprise);

      const { baseUrl } = JSON.parse(enterprise);
      console.log('baseUrl:', baseUrl);

      const url = `${baseUrl}/metronic/api/get_veiculo.php?&h=${accessToken}&id=${id}`;
      console.log('fetch URL:', url);

      const result = await fetch(url);
      let data = await result.text();
      console.log('fetch result text:', data);

      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      data = JSON.parse(data);
      console.log('parsed data:', data);

      setVehicleSelected(data?.data || null);
    } catch (error) {
      console.log(error);
    } finally {
      setLoadingVehicleUnique(false);
    }
  }

  function onPressZoomOut() {
    const region = {
      latitude,
      longitude,
      latitudeDelta: latitudeDelta / 10,
      longitudeDelta: longitudeDelta / 10,
    };

    setLatitudeDelta(region.latitudeDelta);
    setLongitudeDelta(region.longitudeDelta);
    setLatitude(region.latitude);
    setLongitude(region.longitude);

    if (map.current) {
      map.current.animateToRegion(region, 100);
    }
  }

  const animateToCoordinateVehicle = useCallback(
    (lat, lng, vehicleSelectedParam = null) => {
      try {
        map.current.animateCamera({
          center: { latitude: Number(lat), longitude: Number(lng) },
          zoom: 24,
        });

        if (vehicleSelectedParam) {
          setVehiclesFilter(() => {
            return vehicle.filter(
              (v) => v.id_bem === vehicleSelectedParam.id_bem,
            );
          });
        }
      } catch (error) {
        console.log(error);
      }
    },
    [vehicle],
  );

  useEffect(() => {
    if (vehicle && vehicle.length > 0) {
      const unsubscribe = props.navigation.addListener('focus', () => {
        const timer = setTimeout(() => {
          fitMapWithAllMarkers();
        }, 100); // 1000ms = 1 segundo

        return () => clearTimeout(timer); // limpa o timer caso saia antes
      });

      return unsubscribe;
    }
  }, [props.navigation, vehicle]);

  const fitMapWithAllMarkers = useCallback(() => {
    try {
      setVehiclesFilter(vehicle);
      const coordinates = vehicle.map((c) => ({
        latitude: Number(c.lat),
        longitude: Number(c.lng),
      }));

      if (map.current) {
        map.current.fitToCoordinates(coordinates, {
          edgePadding: {
            top: 150,
            right: 150,
            bottom: 150,
            left: 150,
          },
        });
      }

      markersRefs.forEach((m) => {
        if (m) {
          m.hideCallout();
        }
      });

      closeBottomSheet();
    } catch (error) {
      console.log(error);
    }
  }, [vehicle]);

  function onBlurVehicle(indexVehicle, mapFit = false) {
    try {
      if (mapFit) {
        fitMapWithAllMarkers();
      }

      markersRefs[indexVehicle].hideCallout();
    } catch (error) {
      console.log(error);
    }
  }

  const vehiclesFilterByUser = useMemo(() => {
    return vehiclesFilter.filter((v) => v.user === selectedAccount.username);
  }, [vehiclesFilter, selectedAccount.username]);

  if (vehiclesFilterByUser.length === 0) {
    return (
      <View style={styles.containerAnimation}>
        <View
          style={{
            height: 210,
            marginBottom: 56,
          }}
        >
          <LottieView
            style={{ height: 290, width: 290 }}
            source={assets.animations.areaMap}
            resizeMode="cover"
            autoPlay
            loop
          />
        </View>

        <ActivityIndicator size="large" color="#004e70" />
      </View>
    );
  }

  function closeBottomSheet() {
    setVehicleSelected(null);
  }

  return (
    <>
      <Header title="Mapa Geral" {...props} />
      <SafeAreaView style={styles.container}>
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
          <MaterialIcons name="traffic" size={24} color="black" />
        </TouchableOpacity>

        {vehiclesFilterByUser.length === 1 && (
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
              },
            ]}
            onPress={() => {
              const latParse = parseFloat(vehiclesFilterByUser[0].lat);
              const lngParse = parseFloat(vehiclesFilterByUser[0].lng);

              Linking.openURL(
                `http://maps.google.com/?cbll=${latParse},${lngParse}&cbp=12,20.09,,0,5&layer=c`,
              );
            }}
          >
            <FontAwesome
              name="street-view"
              color="#ffcc00"
              style={styles.icon}
              size={20}
            />
          </TouchableOpacity>
        )}

        {vehiclesFilterByUser.length === 1 && (
          <TouchableOpacity
            style={styles.buttonMapBase}
            onPress={fitMapWithAllMarkers}
          >
            <Text style={{ fontWeight: 'bold', color: '#fff' }}>
              Mostrar todos
            </Text>
          </TouchableOpacity>
        )}
        <View
          style={[
            styles.buttonMapBase,
            {
              width: 70,
              right: 16,
              flexDirection: 'row',
              backgroundColor: '#fff',
            },
          ]}
        >
          <TouchableOpacity
            style={styles.zoomIn}
            onPress={() => {
              onPressZoomOut();
            }}
          >
            <Icon name="plus" style={styles.icon} size={20} color="#111" />
          </TouchableOpacity>
          <Text>{'      '}</Text>
          <TouchableOpacity
            style={styles.zoomOut}
            onPress={() => {
              onPressZoomIn();
            }}
          >
            <Icon name="minus" style={styles.icon} size={20} color="#111" />
          </TouchableOpacity>
        </View>

        {isFocused && (
          <MapView
            style={styles.map}
            mapType={mapType}
            ref={map}
            provider={PROVIDER_GOOGLE}
            showsTraffic={showTraffic}
            key={selectedAccount.username}
            initialRegion={{
              latitude,
              longitude,
              latitudeDelta,
              longitudeDelta,
            }}
            zoomEnabled
            zoomControlEnabled
            onRegionChangeComplete={(e) => {
              setLatitudeDelta(e.latitudeDelta);
              setLongitudeDelta(e.longitudeDelta);
              setLatitude(e.latitude);
              setLongitude(e.longitude);
            }}
          >
            {vehiclesFilterByUser?.map((i) => (
              <Marker
                key={String(i.id_bem)}
                ref={(ref) => {
                  const markesRefsTemp = markersRefs;

                  markesRefsTemp[Number(i.id_bem)] = ref;

                  setMarkersRefs(markesRefsTemp);
                }}
                coordinate={{
                  latitude: parseFloat(i.lat),
                  longitude: parseFloat(i.lng),
                }}
                onPress={(event) => {
                  try {
                    const {
                      latitude: latitudeEvent,
                      longitude: longitudeEvent,
                    } = event.nativeEvent.coordinate;

                    animateToCoordinateVehicle(latitudeEvent, longitudeEvent);

                    const selected = vehicle.find((v) => v.id_bem === i.id_bem);
                    setVehiclesFilter(() => [selected]);

                    getVehicleUnique(i.id_bem);
                  } catch (error) {
                    console.log(error);
                  }
                }}
              >
                <View style={{ alignItems: 'center' }}>
                  <Image
                    source={{ uri: i.imagem_icone }}
                    resizeMode="contain"
                    style={{ width: 68, height: 32 }}
                  />
                  <View
                    style={{
                      padding: 6,
                      backgroundColor: '#fff',
                      alignItems: 'center',
                      justifyContent: 'center',
                      borderRadius: 6,
                      marginTop: -6,
                    }}
                  >
                    <Text
                      style={{
                        fontWeight: 'bold',
                        fontSize: 12,
                        color: '#999',
                      }}
                    >
                      {i.name}
                    </Text>
                  </View>
                </View>
                {/* <Callout
                  style={
                    Platform.OS === 'ios'
                      ? {
                          alignItems: 'center',
                          height: 316,
                          width: 316,
                        }
                      : {
                          alignItems: 'center',
                          padding: 12,
                        }
                  }
                  onPress={() => onBlurVehicle(Number(i.id_bem))}
                >
                  <CalloutVehicle vehicle={i} />
                </Callout> */}
              </Marker>
            ))}
          </MapView>
        )}

        {vehicleSelected || loadingVehicleUnique ? (
          <VehicleBottomSheet
            vehicle={vehicleSelected}
            loading={loadingVehicleUnique}
            onClose={closeBottomSheet}
          />
        ) : (
          <BottomSheet
            vehicles={vehicle}
            animateToCoordinateVehicle={animateToCoordinateVehicle}
          />
        )}
      </SafeAreaView>
    </>
  );
}

export default Map;

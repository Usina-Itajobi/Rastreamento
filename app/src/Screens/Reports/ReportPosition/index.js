/* eslint-disable no-plusplus */
/* eslint-disable no-param-reassign */
/* eslint-disable react/no-array-index-key */
import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Dimensions,
  SafeAreaView,
  ActivityIndicator,
  Platform,
  Image,
  Linking,
} from 'react-native';
import MapView, {
  Callout,
  Marker,
  Polyline,
  PROVIDER_GOOGLE,
  MarkerAnimated,
} from 'react-native-maps';
import LottieView from 'lottie-react-native';
import FontAwesome from 'react-native-vector-icons/FontAwesome';
import {
  heightPercentageToDP,
  widthPercentageToDP,
} from 'react-native-responsive-screen';
import Icon from 'react-native-vector-icons/AntDesign';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

import { getStatusBarHeight } from 'react-native-iphone-x-helper';
import { captureScreen } from 'react-native-view-shot';
import { readFile } from 'react-native-fs';
import assets from '../../../assets';

import { getPixelSize } from '../../../utils/getPixelSize';
import CalloutPosition from './CalloutPosition';

import * as S from './styles';
import createPdfReportPosition from '../../../utils/createPdfReportPosition';

const dimension =
  Dimensions.get('window').width / Dimensions.get('window').height;

const ReportPosition = ({ navigation, route }) => {
  const map = useRef();
  const markerRefInit = useRef();
  const markerRefFinal = useRef();
  const markerNavigationRef = useRef();

  const { positions } = route.params;
  const { vehicleSelected } = route.params;

  const [latitude, setLatitude] = useState(Number(positions[0].latitude));
  const [longitude, setLongitude] = useState(Number(positions[0].longitude));
  const [latitudeDelta, setLatitudeDelta] = useState(0.5);
  const [longitudeDelta, setLongitudeDelta] = useState(dimension * 0.00522);
  const [mapType, setMapType] = useState('standard');
  const [mapReady, setMapReady] = useState(false);
  const [tabActive, setTabActive] = useState('map');
  const [markersRefs, setMarkersRefs] = useState([]);
  const [intervalNavigation, setIntervalNavigation] = useState(null);
  const [navigationMode, setnavigationMode] = useState(false);
  const [markerNavigationRender, setMarkerNavigationRender] = useState(null);
  const [percentageNavigate, setPercentageNavigate] = useState(0);
  const [showTraffic, setShowTraffic] = useState(false);
  const [locationStreetView, setLocationStreetView] = useState(null);
  const [speedNavigation, setSpeedNavigation] = useState(500);
  const [rebootNavigation, setRebootNavigation] = useState(false);
  const [loadingGenerateReportPDF, setLoadingGenerateReportPDF] =
    useState(false);
  const [base64PrintScreenMap, setBase64PrintScreenMap] = useState(null);

  const [coordnateMarkerNavigationIOS, setcoordnateMarkerNavigationIOS] =
    useState({
      latitude: Number(positions[0].latitude),
      longitude: Number(positions[0].longitude),
      latitudeDelta: 0.5,
      longitudeDelta: dimension * 0.00522,
    });
  const [disableShowListPositions, setDisableShowListPositions] =
    useState(true);

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

  function fitMapWithAllMarkers() {
    if (map.current && mapReady) {
      setTimeout(() => {
        try {
          map.current.fitToCoordinates(
            positions.map((c) => ({
              latitude: c.latitude,
              longitude: c.longitude,
            })),
            {
              edgePadding: {
                right: getPixelSize(50),
                left: getPixelSize(50),
                top: getPixelSize(150),
                bottom: getPixelSize(50),
              },
            },
          );
        } catch (error) {
          console.log(error);
        }
      }, 500);

      setTimeout(async () => {
        try {
          const uri = await captureScreen({
            format: 'jpg',
            quality: 0.8,
          });

          const res = await readFile(uri, 'base64');

          setBase64PrintScreenMap(res);
        } catch (error) {
          console.log(error);
        } finally {
          setDisableShowListPositions(false);
        }
      }, 1500);
    }
  }

  useEffect(() => {
    if (mapReady && positions?.length > 0) {
      fitMapWithAllMarkers();
    }
  }, [mapReady]);

  function animateMarkerIOS(position) {
    setcoordnateMarkerNavigationIOS(position);
  }

  function initNavigationSimulator(speed) {
    try {
      setnavigationMode(true);
      if (intervalNavigation && !speed) {
        clearInterval(intervalNavigation);
        setIntervalNavigation(null);
        setnavigationMode(false);
        return;
      }
      if (speed) {
        clearInterval(intervalNavigation);
        setSpeedNavigation(speed);
      }

      let i = 0;

      if (markerNavigationRender?.index > 0) {
        i = markerNavigationRender.index;
      }

      const interval = setInterval(() => {
        if (i > positions.length - 1) {
          clearInterval(interval);
          setIntervalNavigation(null);
          setnavigationMode(false);

          setMarkerNavigationRender((old) => ({
            ...old,
            index: 0,
          }));

          i = 0;

          setRebootNavigation(true);
        } else {
          setRebootNavigation(false);
          setPercentageNavigate(Math.floor((100 * i) / positions.length));

          if (map.current) {
            map.current.animateCamera({
              center: {
                latitude: Number(positions[i].latitude),
                longitude: Number(positions[i].longitude),
              },
            });
          }

          if (Platform.OS === 'android') {
            if (markerNavigationRef.current) {
              markerNavigationRef.current.animateMarkerToCoordinate(
                {
                  latitude: positions[i].latitude,
                  longitude: positions[i].longitude,
                },
                speed || speedNavigation,
              );
            }
          } else {
            const newCoordinate = {
              latitude: positions[i].latitude,
              longitude: positions[i].longitude,
              latitudeDelta: 0.5,
              longitudeDelta: dimension * 0.00522,
            };

            animateMarkerIOS(newCoordinate);
          }

          if (i > 0) {
            setMarkerNavigationRender({
              rotation: positions[i - 1].rotation,
              index: i,
            });
          } else {
            setMarkerNavigationRender({
              rotation: positions[i].rotation,
              index: i,
            });
          }

          i++;
        }
      }, speed || speedNavigation);

      setIntervalNavigation(interval);
    } catch (error) {
      console.log(error);
    }
  }

  function pauseNavigationSimulator() {
    setIntervalNavigation(null);
    setnavigationMode(false);

    if (intervalNavigation) {
      clearInterval(intervalNavigation);
    }
  }

  function stopNavigationSimulator() {
    if (intervalNavigation) {
      clearInterval(intervalNavigation);
    }

    setIntervalNavigation(null);
    setnavigationMode(false);
    setMarkerNavigationRender(null);

    fitMapWithAllMarkers();
  }

  async function handleCreateReportPDF() {
    setLoadingGenerateReportPDF(true);
    await createPdfReportPosition(positions, base64PrintScreenMap);
    setLoadingGenerateReportPDF(false);
  }

  return (
    <SafeAreaView
      style={{
        flex: 1,
        backgroundColor: '#ffffff',
        position: 'relative',
      }}
    >
      <S.Header>
        <S.WrapperInfoCar>
          <MaterialIcons
            name="arrow-back"
            color="#fff"
            size={24}
            onPress={() => {
              navigation.goBack();
            }}
          />
          <S.CarName>{vehicleSelected.placa}</S.CarName>
        </S.WrapperInfoCar>
        <S.WrapperButtons>
          <S.ButtonTab
            active={tabActive === 'map'}
            onPress={() => setTabActive('map')}
          >
            <S.LabelTab active={tabActive === 'map'}>Mapa</S.LabelTab>
          </S.ButtonTab>
          <S.ButtonTab
            active={tabActive === 'position'}
            onPress={() => setTabActive('position')}
            disabled={disableShowListPositions}
          >
            <S.LabelTab active={tabActive === 'position'}>Posições</S.LabelTab>
          </S.ButtonTab>
        </S.WrapperButtons>
      </S.Header>

      {tabActive === 'map' && (
        <>
          {markerNavigationRender && (
            <>
              <S.WrapperSlider>
                <S.WrapperLabelOptionSpeed>
                  <S.OptionLabel selected={speedNavigation === 1000}>
                    0.5x
                  </S.OptionLabel>
                  <S.OptionLabel selected={speedNavigation === 500}>
                    1.0x
                  </S.OptionLabel>
                  <S.OptionLabel selected={speedNavigation === 200}>
                    2.0x
                  </S.OptionLabel>
                </S.WrapperLabelOptionSpeed>
                <S.WrapperOptionSpeeds>
                  <S.WrapperOptionSpeed>
                    <S.OptionSpeed
                      selected={speedNavigation === 1000}
                      onPress={() => initNavigationSimulator(1000)}
                      hitSlop={{ top: 12, bottom: 12, right: 12, left: 12 }}
                    />
                  </S.WrapperOptionSpeed>
                  <S.LineOptionSpeed />
                  <S.WrapperOptionSpeed>
                    <S.OptionSpeed
                      selected={speedNavigation === 500}
                      onPress={() => initNavigationSimulator(500)}
                      hitSlop={{ top: 12, bottom: 12, right: 12, left: 12 }}
                    />
                  </S.WrapperOptionSpeed>
                  <S.LineOptionSpeed />
                  <S.WrapperOptionSpeed>
                    <S.OptionSpeed
                      selected={speedNavigation === 200}
                      onPress={() => initNavigationSimulator(200)}
                      hitSlop={{ top: 12, bottom: 12, right: 12, left: 12 }}
                    />
                  </S.WrapperOptionSpeed>
                </S.WrapperOptionSpeeds>
              </S.WrapperSlider>
              <S.ControlsNavigation>
                <S.ButtonControl onPress={stopNavigationSimulator}>
                  <MaterialIcons name="stop" color="#111" size={45} />
                </S.ButtonControl>
                <S.ButtonControl onPress={() => initNavigationSimulator(false)}>
                  <MaterialIcons
                    name={rebootNavigation ? 'refresh' : 'play-arrow'}
                    color="#111"
                    size={45}
                  />
                </S.ButtonControl>
                <S.ButtonControl onPress={pauseNavigationSimulator}>
                  <MaterialIcons name="pause" color="#111" size={45} />
                </S.ButtonControl>
              </S.ControlsNavigation>
            </>
          )}

          <S.Legend>
            {!markerNavigationRender && !navigationMode ? (
              <>
                <S.WrapperLegend>
                  <S.LegendIcon lg source={assets.images.start_flag} />
                  <S.LegendLabel>Início</S.LegendLabel>
                </S.WrapperLegend>
                <S.WrapperLegend>
                  <S.LegendIcon source={assets.images.direction_green} />
                  <S.LegendLabel>Ignição ON</S.LegendLabel>
                </S.WrapperLegend>
                <S.WrapperLegend>
                  <S.LegendIcon source={assets.images.direction_red} />
                  <S.LegendLabel>Ignição OFF</S.LegendLabel>
                </S.WrapperLegend>
                <S.WrapperLegend>
                  <S.LegendIcon lg source={assets.images.final_flag} />
                  <S.LegendLabel>Fim</S.LegendLabel>
                </S.WrapperLegend>
              </>
            ) : (
              <View style={{ alignItems: 'center', flexDirection: 'row' }}>
                <View
                  style={{
                    height: 10,
                    width: `${percentageNavigate}%`,
                    backgroundColor: '#004e70',
                    borderRadius: 6,
                  }}
                />
                <MaterialCommunityIcons
                  name="car-hatchback"
                  size={24}
                  style={{
                    marginLeft: -18,
                    backgroundColor: '#fff',
                  }}
                />
              </View>
            )}
          </S.Legend>

          <S.ButtonMapRight
            style={{
              borderWidth: mapType === 'satellite' ? 3 : 0,
              borderColor: mapType === 'satellite' ? '#004e70' : 'transparent',
            }}
            onPress={() =>
              setMapType(mapType === 'satellite' ? 'standard' : 'satellite')
            }
          >
            {mapType === 'satellite' ? (
              <Image source={assets.images.icon_satellite_2} />
            ) : (
              <Image source={assets.images.icon_satellite} />
            )}
          </S.ButtonMapRight>

          <S.ButtonMapRight
            style={{
              top: getStatusBarHeight() + 190,
              borderWidth: showTraffic ? 3 : 0,
              borderColor: showTraffic ? '#004e70' : 'transparent',
            }}
            onPress={() => setShowTraffic(!showTraffic)}
          >
            <MaterialIcons name="traffic" size={24} color="black" />
          </S.ButtonMapRight>

          <S.ButtonMapRight
            style={{
              top: getStatusBarHeight() + 230,
              borderWidth: markerNavigationRender ? 3 : 0,
              borderColor: markerNavigationRender ? '#004e70' : 'transparent',
            }}
            onPress={() => initNavigationSimulator(false)}
          >
            <MaterialIcons name="play-arrow" color="#004e70" size={25} />
          </S.ButtonMapRight>

          {locationStreetView && !markerNavigationRender && (
            <S.ButtonMapRight
              style={{
                top: getStatusBarHeight() + 270,
              }}
              onPress={() => {
                const latParse = parseFloat(locationStreetView.latitude);
                const lngParse = parseFloat(locationStreetView.longitude);

                Linking.openURL(
                  `http://maps.google.com/?cbll=${latParse},${lngParse}&cbp=12,20.09,,0,5&layer=c`,
                );
              }}
            >
              <FontAwesome name="street-view" color="#ffcc00" size={20} />
            </S.ButtonMapRight>
          )}

          <View
            style={{
              position: 'absolute',
              right: 16,
              top: 150,
              backgroundColor: '#fff',
              color: '#000',
              width: 70,
              height: 30,
              elevation: 2,
              justifyContent: 'center',
              alignItems: 'center',
              fontSize: 24,
              zIndex: 2,
              flexDirection: 'row',
            }}
          >
            <TouchableOpacity
              onPress={() => {
                onPressZoomOut();
              }}
            >
              <Icon name="plus" size={20} />
            </TouchableOpacity>
            <Text>{'      '}</Text>
            <TouchableOpacity
              onPress={() => {
                onPressZoomIn();
              }}
            >
              <Icon name="minus" size={20} />
            </TouchableOpacity>
          </View>

          <MapView
            style={{
              width: widthPercentageToDP('100%'),
              height: heightPercentageToDP('100%'),
            }}
            onMapReady={() => setMapReady(true)}
            zoomTapEnabled
            showsTraffic={showTraffic}
            mapType={mapType}
            ref={map}
            provider={PROVIDER_GOOGLE}
            initialRegion={{
              latitude,
              longitude,
              latitudeDelta,
              longitudeDelta,
            }}
            zoomEnabled
            zoomControlEnabled
          >
            {positions.length > 0 && (
              <>
                <Polyline
                  coordinates={positions.map((c) => ({
                    latitude: c.latitude,
                    longitude: c.longitude,
                  }))}
                  strokeColor="#000"
                  strokeWidth={4}
                />

                <Marker
                  ref={markerRefInit}
                  coordinate={{
                    latitude: positions[0].latitude,
                    longitude: positions[0].longitude,
                  }}
                >
                  <S.PositionMarkerImageFlags
                    source={assets.images.start_flag}
                    resizeMode="contain"
                  />
                  {mapReady && (
                    <Callout
                      onPress={() => {
                        try {
                          markerRefInit.current.hideCallout();
                        } catch (error) {
                          console.log(error);
                        }
                      }}
                    >
                      <CalloutPosition position={positions[0].info} />
                    </Callout>
                  )}
                </Marker>

                {!markerNavigationRender &&
                  positions.map((p, index) => (
                    <Marker
                      ref={(ref) => {
                        const markesRefsTemp = markersRefs;

                        markesRefsTemp[index] = ref;

                        setMarkersRefs(markesRefsTemp);
                      }}
                      key={`${p.latitude}_${p.longitude}_${index}`}
                      coordinate={{
                        latitude: p.latitude,
                        longitude: p.longitude,
                      }}
                      rotation={p.rotation}
                      onPress={(e) => {
                        setLocationStreetView({
                          latitude: e.nativeEvent.coordinate.latitude,
                          longitude: e.nativeEvent.coordinate.longitude,
                        });
                      }}
                    >
                      <S.PositionMarkerImage
                        source={
                          p.ignicao === 'Sim'
                            ? assets.images.direction_green
                            : assets.images.direction_red
                        }
                      />
                      {mapReady && (
                        <Callout
                          onPress={() => {
                            try {
                              markersRefs[index].hideCallout();
                            } catch (error) {
                              console.log(error);
                            }
                          }}
                        >
                          <CalloutPosition position={p.info} />
                        </Callout>
                      )}
                    </Marker>
                  ))}

                {markerNavigationRender && (
                  <>
                    {Platform.OS === 'android' ? (
                      <Marker
                        ref={markerNavigationRef}
                        coordinate={{
                          latitude: positions[0].latitude,
                          longitude: positions[0].longitude,
                        }}
                        rotation={markerNavigationRender.rotation}
                      >
                        <S.PositionMarkerImage
                          source={assets.images.direction_green}
                        />
                      </Marker>
                    ) : (
                      <MarkerAnimated
                        coordinate={coordnateMarkerNavigationIOS}
                        rotation={markerNavigationRender.rotation}
                      >
                        <S.PositionMarkerImage
                          source={assets.images.direction_green}
                        />
                      </MarkerAnimated>
                    )}
                  </>
                )}

                <Marker
                  ref={markerRefFinal}
                  coordinate={{
                    latitude: positions[positions.length - 1].latitude,
                    longitude: positions[positions.length - 1].longitude,
                  }}
                >
                  <S.PositionMarkerImageFlags
                    source={assets.images.final_flag}
                    resizeMode="contain"
                  />
                  {mapReady && (
                    <Callout
                      onPress={() => {
                        try {
                          markerRefFinal.current.hideCallout();
                        } catch (error) {
                          console.log(error);
                        }
                      }}
                    >
                      <CalloutPosition
                        position={positions[positions.length - 1].info}
                      />
                    </Callout>
                  )}
                </Marker>
              </>
            )}
          </MapView>
        </>
      )}

      {tabActive === 'position' && (
        <>
          <TouchableOpacity
            style={{
              marginTop: Platform.OS === 'android' ? 140 : 100,
              backgroundColor: '#004e70',
              width: '60%',
              alignSelf: 'center',
              alignItems: 'center',
              justifyContent: 'center',
              flexDirection: 'row',
              height: 41,
              borderRadius: 6,
              marginBottom: 12,
            }}
            onPress={handleCreateReportPDF}
            disabled={loadingGenerateReportPDF}
          >
            {loadingGenerateReportPDF ? (
              <ActivityIndicator
                size="small"
                color="#fff"
                animating={loadingGenerateReportPDF}
              />
            ) : (
              <>
                <Text
                  style={{ color: '#fff', fontWeight: 'bold', marginRight: 12 }}
                >
                  GERAR PDF
                </Text>

                <MaterialCommunityIcons name="file" color="#fff" size={24} />
              </>
            )}
          </TouchableOpacity>
          <S.ListPositions
            data={positions}
            keyExtractor={(_, index) => index}
            renderItem={({ item, index }) => (
              <S.PositionItem
                key={index}
                ign={item.ignicao === 'Sim'}
                ign_color={item.ign_color}
              >
                <S.PositionHeader>
                  <S.PositionDate>{item.data}</S.PositionDate>
                  <S.PositionDate>{item.evento}</S.PositionDate>
                </S.PositionHeader>
                <S.WrapperAddress>
                  <S.PositionTitle>Endereço:</S.PositionTitle>
                  <S.PositionDesc> {item.address}</S.PositionDesc>
                </S.WrapperAddress>

                <S.PositionFooter>
                  <S.WrapperItemPositionFooter>
                    <S.PositionTitle>
                      Velocidade:{' '}
                      <S.PositionDesc>{item.velocidade}</S.PositionDesc>{' '}
                    </S.PositionTitle>
                    <S.PositionTitle>
                      Voltagem:{' '}
                      <S.PositionDesc>{item.voltagem_bateria}</S.PositionDesc>{' '}
                    </S.PositionTitle>
                  </S.WrapperItemPositionFooter>
                  <S.WrapperItemPositionFooter>
                    <S.PositionTitle>
                      Ignição: <S.PositionDesc>{item.ignicao}</S.PositionDesc>
                    </S.PositionTitle>
                    <S.PositionTitle>
                      KM rodado:{' '}
                      <S.PositionDesc>{item.km_rodado} KM/h</S.PositionDesc>
                    </S.PositionTitle>
                    {/* <S.PositionTitle>
                    Odômetro: <S.PositionDesc>ASASDASD</S.PositionDesc>
                  </S.PositionTitle>
                  <S.PositionTitle>
                    Horímetro: <S.PositionDesc>ASASDASD</S.PositionDesc>
                  </S.PositionTitle> */}
                  </S.WrapperItemPositionFooter>
                </S.PositionFooter>
              </S.PositionItem>
            )}
          />
        </>
      )}

      {!mapReady && (
        <S.LoadingMap>
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
        </S.LoadingMap>
      )}
    </SafeAreaView>
  );
};

export default ReportPosition;

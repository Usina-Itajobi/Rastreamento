/* eslint-disable react/prop-types */
import React, { useEffect, useMemo, useRef, useState } from 'react';
import BottomSheetLib from '@gorhom/bottom-sheet';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';

import * as S from './styles';

const BottomSheet = ({ vehicles, animateToCoordinateVehicle }) => {
  const bottomSheetRef = useRef(null);

  const [index, setIndex] = useState(0);

  const snapPoints = useMemo(() => ['15%', '35%'], []);

  const [vehiclesFilter, setVehiclesFilter] = useState([]);

  useEffect(() => {
    setVehiclesFilter(vehicles);
  }, [vehicles]);

  function handlePressVehicle(lat, long, vehicleSelected) {
    animateToCoordinateVehicle(lat, long, vehicleSelected);
  }

  function handleFilterVehicles(filterValue) {
    try {
      const regex = new RegExp(`${filterValue.trim()}`, 'i');

      const vehiclesFilterTemp = vehicles?.filter(
        (v) => v.name.search(regex) >= 0,
      );

      setVehiclesFilter(vehiclesFilterTemp);
    } catch (error) {
      console.log(error);
    }
  }

  return (
    <BottomSheetLib ref={bottomSheetRef} index={index} snapPoints={snapPoints}>
      <S.Container>
        <S.Title>VEÍCULOS</S.Title>

        <S.WrapperTextInput>
          <MaterialIcons name="search" color="#999" size={24} />
          <S.TextInput
            placeholder="Pesquisar por Veículo"
            onChangeText={handleFilterVehicles}
            onFocus={() => setIndex(1)}
            placeholderTextColor={'#999'}
          />
        </S.WrapperTextInput>

        <S.ListVehicles
          data={vehiclesFilter}
          keyExtractor={(item) => item.id_bem}
          renderItem={({ item }) => (
            <S.Vehicle
              onPress={() => handlePressVehicle(item.lat, item.lng, item)}
            >
              {item?.imagem_icone && (
                <S.WrapperVehicleImage>
                  <S.VehicleImage
                    source={{
                      uri: item?.imagem_icone?.replace('http://', 'https://'),
                    }}
                    resizeMode="contain"
                  />
                  <S.VehicleImageLabel color={item.ign_color} />
                </S.WrapperVehicleImage>
              )}
              <S.WrapperVehiclesDesc>
                <S.VehiclesTitle>{item.name}</S.VehiclesTitle>
                <S.VehiclesDate>{item.dia}</S.VehiclesDate>
              </S.WrapperVehiclesDesc>

              <S.VehiclesStatus>
                {item.ligado === 'N' ? 'OFF' : 'ON'}
              </S.VehiclesStatus>
            </S.Vehicle>
          )}
        />
      </S.Container>
    </BottomSheetLib>
  );
};

export default BottomSheet;

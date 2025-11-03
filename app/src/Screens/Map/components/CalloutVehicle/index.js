/* eslint-disable react/prop-types */
import React from 'react';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';

import { Platform } from 'react-native';
import * as S from './styles';

const CalloutVehicle = ({ vehicle }) => {
  if (Platform.OS === 'ios') {
    return (
      <S.Container style={{ paddingTop: 24 }}>
        <S.Title style={{ fontSize: 13 }}>
          Veículo: <S.Desc style={{ fontSize: 11 }}>{vehicle.name}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Endereço: <S.Desc style={{ fontSize: 11 }}>{vehicle.address}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Posição: <S.Desc style={{ fontSize: 11 }}>{vehicle.dia}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Comunicação :
          <S.Desc style={{ fontSize: 11 }}>{vehicle.date_comuni}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Tipo: <S.Desc style={{ fontSize: 11 }}>{vehicle.tipo} </S.Desc>-
          Bloqueado:
          <S.Desc style={{ fontSize: 11 }}>
            {' '}
            {vehicle.bloqueado === 'N' ? 'NÃO' : 'SIM'}
          </S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Ligado:
          <S.Desc style={{ fontSize: 11 }}>
            {' '}
            {vehicle.ligado === 'N' ? 'NÃO' : 'SIM'}{' '}
          </S.Desc>
          - KM rodado:
          <S.Desc style={{ fontSize: 11 }}> {vehicle.km_rodado}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Bateria:
          <S.Desc style={{ fontSize: 11 }}> {vehicle.voltagem_bateria} </S.Desc>
          - Bateria interna:
          <S.Desc style={{ fontSize: 11 }}> {vehicle.bat_interna}</S.Desc>
        </S.Title>
        <S.Title style={{ fontSize: 13 }}>
          Combustível: <S.Desc style={{ fontSize: 11 }}>{vehicle.rpm} </S.Desc>-
          RPM: <S.Desc style={{ fontSize: 11 }}>{vehicle.combustivel} </S.Desc>
        </S.Title>

        <S.Title style={{ fontSize: 13 }}>
          Velocidade:
          <S.Desc style={{ fontSize: 11 }}> {vehicle.speed} KM/h </S.Desc>-
          Evento:
          <S.Desc style={{ fontSize: 11 }}>{vehicle.evento}</S.Desc>
        </S.Title>
        <S.ButtonCloseVehicleInfo
          hitSlop={{
            bottom: 12,
            left: 12,
            right: 12,
            top: 12,
          }}
          style={{
            right: 0,
            top: 12,
          }}
        >
          <MaterialIcons name="close" color="#999" size={24} />
        </S.ButtonCloseVehicleInfo>
      </S.Container>
    );
  }
  return (
    <S.Container>
      <S.Title>
        Veículo : <S.Desc> {vehicle.name}</S.Desc>
      </S.Title>
      <S.Title>
        Endereço : <S.Desc> {vehicle.address}</S.Desc>
      </S.Title>
      <S.Title>
        Posição : <S.Desc> {vehicle.dia}</S.Desc>
      </S.Title>
      <S.Title>
        Comunicação : <S.Desc> {vehicle.date_comuni}</S.Desc>
      </S.Title>
      <S.Title>
        Tipo : <S.Desc> {vehicle.tipo}</S.Desc>
      </S.Title>
      <S.Title>
        Bloqueado :{' '}
        <S.Desc> {vehicle.bloqueado === 'N' ? 'NÃO' : 'SIM'}</S.Desc>
      </S.Title>
      <S.Title>
        Ligado : <S.Desc> {vehicle.ligado === 'N' ? 'NÃO' : 'SIM'}</S.Desc>
      </S.Title>
      <S.Title>
        KM rodado : <S.Desc> {vehicle.km_rodado}</S.Desc>
      </S.Title>
      <S.Title>
        Bateria : <S.Desc> {vehicle.voltagem_bateria}</S.Desc>
      </S.Title>
      <S.Title>
        Bateria interna : <S.Desc> {vehicle.bat_interna}</S.Desc>
      </S.Title>
      <S.Title>
        Combustível : <S.Desc> {vehicle.combustivel}</S.Desc>
      </S.Title>
      <S.Title>
        RPM : <S.Desc> {vehicle.rpm}</S.Desc>
      </S.Title>
      <S.Title>
        Evento : <S.Desc> {vehicle.evento}</S.Desc>
      </S.Title>
      <S.Title>
        Velocidade : <S.Desc> {vehicle.speed} KM/h</S.Desc>
      </S.Title>

      <S.ButtonCloseVehicleInfo
        hitSlop={{
          bottom: 12,
          left: 12,
          right: 12,
          top: 12,
        }}
      >
        <MaterialIcons name="close" color="#999" size={24} />
      </S.ButtonCloseVehicleInfo>
    </S.Container>
  );
};

export default CalloutVehicle;

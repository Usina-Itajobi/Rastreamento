/* eslint-disable react/prop-types */
import React from 'react';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';

import * as S from './styles';

const CalloutPosition = ({ position }) => {
  return (
    <S.Container>
      <S.Desc>{position}</S.Desc>

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

export default CalloutPosition;

// {
//     "data": "20\/09\/2021 17:24:32",
//     "data_comunica": "20\/09\/2021 17:25:18",
//     "minutocomunica": "0",
//     "ignicao": "Não",
//     "evento": "Parado { event_id: 2 }",
//     "voltagem_bateria": "25.91",
//     "address": "Jardim Golive, Sertãozinho, Região Imediata de Ribeirão Preto, Região Metropolitana de Ribeirão Preto, Região Geográfica Intermediária de Ribeirão Preto, São Paulo, Região Sudeste, 14170-585, Brasil",
//     "placa": "EYS9326",
//     "velocidade": "0",
//     "km_rodado": "134938",
//     "rpm": "0",
//     "cliente": "Sertemil serviços de maquinas e montagens industr",
//     "motorista": "",
//     "modelo_rastreador": "RST",
//     "lat": "-21.148899",
//     "lng": "-48.001884"
//   },

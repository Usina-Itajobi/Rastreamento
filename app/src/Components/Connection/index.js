import React from 'react';
import {
  View,
  ActivityIndicator,
  Text,
  ImageBackground
} from 'react-native';
import moment from 'moment';
import 'moment/locale/pt-br';
import * as S from './styles';

const Connection = (props) => {
  moment.locale('pt-br');

  return (
    <View style={{ flex: 1, backgroundColor: '#FFFFFF' }}>
      {props.connected === null ? (
        <S.ContainerAnimation>
          <View
            style={{
              flexDirection: 'row',
              justifyContent: 'center',
              alignItems: 'center',
            }}
          >
            <Text
              style={{
                color: '#004e70',
                fontSize: 16,
                fontWeight: 'bold',
                textAlign: 'center',
                marginRight: 10,
              }}
            >
              Verificando conexão
            </Text>

            <ActivityIndicator size="large" color="#004e70" />
          </View>
        </S.ContainerAnimation>
      ) : (
          <ImageBackground
            resizeMode="cover"
            source={require('../../assets/images/VheicleBackground.jpg')}
            style={{
              flex: 1,
              justifyContent: 'center',
              alignItems: 'center',
            }}
          >

          <S.MainLabel>
            Sem conexão com a internet.{"\n"}Verifique sua rede.
          </S.MainLabel>

        </ImageBackground>
      )}
    </View>
  );
};

export default Connection;

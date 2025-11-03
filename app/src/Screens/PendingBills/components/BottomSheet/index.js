/* eslint-disable react/prop-types */
import React, {
  useMemo,
} from 'react';
import {
  Image,
  Dimensions,
  View,
  ToastAndroid
} from 'react-native';
import BottomSheetLib from '@gorhom/bottom-sheet';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import Clipboard from '@react-native-clipboard/clipboard';

import * as S from './styles';

const BottomSheet = ({ base64Pix, copiaColaPix, bottomSheetRef, }) => {
  const DIMENSIONS = Dimensions.get('window');
  const snapPoints = useMemo(() => ['95%'], []);

  const copyPixToClipboard = (text) => {
    if (!text || text === '') return
    Clipboard.setString(text);
    ToastAndroid.show('PIX copiado para a área de transferênia!', ToastAndroid.SHORT);
  }

  return (
    <BottomSheetLib
      ref={bottomSheetRef}
      snapPoints={snapPoints}
      index={-1}
      handleStyle={{
        backgroundColor: '#004E70',
      }}
      handleIndicatorStyle={{
        backgroundColor: '#F69C33'
      }}
      enablePanDownToClose={true}

    >
      <S.Container
        source={require('../../../../assets/images/LoginBackground.jpg')}
        resizeMode="contain"
        style={{
          width: DIMENSIONS.width,
          height: DIMENSIONS.height
        }}
      >
        <View
          style={{
            height: DIMENSIONS.height / 2.3,
            width: DIMENSIONS.width / 1.2,
            marginTop: 20,
            alignSelf: 'center',
            borderWidth: 1.5,
            borderRadius: 12,
            borderColor: '#C0C0C0',
            elevation: 2,
            backgroundColor: '#FFF',
            justifyContent: 'center'
          }}
        >
          {(base64Pix && base64Pix !== '') ? (
            <Image
              style={{
                height: DIMENSIONS.height / 2
              }}
              resizeMode={'contain'}
              source={{ uri: `data:image/png;base64,${base64Pix}` }}
            />
          ) : (
            <View
              style={{
                alignItems: 'center'
              }}
            >
              <MaterialCommunityIcons
                name={'image-broken'}
                size={180}
                color={'#C0C0C0'}
              />
              <S.Title
                style={{
                  color: '#C0C0C0'
                }}
              >
                Oops... Ocorreu um erro ao carregar o QR Code!
              </S.Title>
            </View>
          )}
        </View>

        {(copiaColaPix) && (
          <S.ButtonContainer
            onPress={() => copyPixToClipboard(copiaColaPix)}
            activeOpacity={0.8}
            style={{
              marginTop: 40
            }}
          >
            <S.ButtonText>
              {'PIX Copia e Cola'}
            </S.ButtonText>
            <MaterialCommunityIcons
              name={'qrcode'}
              size={20}
              color={'#FFF'}
            />
          </S.ButtonContainer>
        )}

        <S.ButtonContainer
          onPress={() => {
            bottomSheetRef.current.close()
          }}
          activeOpacity={0.8}
          style={{
            marginTop: 30
          }}
        >
          <S.ButtonText>
            {'Fechar'}
          </S.ButtonText>
          <MaterialCommunityIcons
            name={'close'}
            size={20}
            color={'#FFF'}
          />
        </S.ButtonContainer>
      </S.Container>
    </BottomSheetLib>
  );
};

export default BottomSheet;

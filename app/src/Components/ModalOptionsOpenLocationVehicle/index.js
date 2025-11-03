/* eslint-disable react/prop-types */
import React from 'react';
import { Linking, Modal, TouchableWithoutFeedback } from 'react-native';

import * as S from './styles';
import assets from '../../assets';

const ModalOptionsOpenLocationVehicle = ({ visible, handleClose, vehicle }) => {
  const handleOpenGoogleMaps = () => {
    handleClose();

    const url = `https://www.google.com/maps/search/?api=1&query=${vehicle.lat},${vehicle.lng}`;

    Linking.openURL(url);
  };

  const handleOpenWaze = () => {
    handleClose();

    const url = `https://waze.com/ul?ll=${vehicle.lat},${vehicle.lng}&navigate=yes`;
    Linking.openURL(url);
  };

  return (
    <Modal visible={visible} onRequestClose={handleClose} transparent>
      <TouchableWithoutFeedback onPress={handleClose}>
        <S.ModalContainer />
      </TouchableWithoutFeedback>

      <S.Content>
        <S.Title>Escolha um aplicativo para abrir a localização</S.Title>
        <S.WraperOptions>
          <S.Option onPress={handleOpenGoogleMaps}>
            <S.ImageOption source={assets.images.google_maps} />
          </S.Option>

          <S.Option onPress={handleOpenWaze}>
            <S.ImageOption source={assets.images.waze} />
          </S.Option>
        </S.WraperOptions>

        <S.CloseButton onPress={handleClose}>
          <S.CloseText>Cancelar</S.CloseText>
        </S.CloseButton>
      </S.Content>
    </Modal>
  );
};

export default ModalOptionsOpenLocationVehicle;

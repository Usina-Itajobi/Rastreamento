/* eslint-disable react/prop-types */
import React from 'react';
import { Modal } from 'react-native';
import { RadioButton } from 'react-native-paper';

import * as S from './styles';

const ModalSoundNotification = ({ visible, onClose }) => {
  const [checked, setChecked] = React.useState('default');

  return (
    <Modal
      animationType="fade"
      transparent
      visible={visible}
      onRequestClose={onClose}
    >
      <S.ModalContainer>
        <S.ModalContent>
          <S.TitleModal>Som da Notificação</S.TitleModal>

          <S.WrapperRadios
            onPress={() => {
              setChecked('default');
            }}
          >
            <RadioButton
              value="default"
              status={checked === 'default' ? 'checked' : 'unchecked'}
              onPress={() => {
                setChecked('default');
              }}
              color="#004e70"
            />

            <S.RadiosLabel>Padrão</S.RadiosLabel>
          </S.WrapperRadios>

          <S.WrapperRadios
            onPress={() => {
              setChecked('alarm');
            }}
          >
            <RadioButton
              value="alarm"
              status={checked === 'alarm' ? 'checked' : 'unchecked'}
              onPress={() => {
                setChecked('alarm');
              }}
              color="#004e70"
            />
            <S.RadiosLabel>Alarme</S.RadiosLabel>
          </S.WrapperRadios>

          <S.ButtonSave onPress={onClose}>
            <S.ButtonSaveText>SALVAR</S.ButtonSaveText>
          </S.ButtonSave>
        </S.ModalContent>
      </S.ModalContainer>
    </Modal>
  );
};

export default ModalSoundNotification;

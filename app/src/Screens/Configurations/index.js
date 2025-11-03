import React, { useState } from 'react';

import Header from '../../Components/Header';
import ModalSoundNotification from './ModalSoundNotification';

import * as S from './styles';

const Configurations = (props) => {
  const [showModal, setShowModal] = useState(false);

  return (
    <S.Container>
      <Header title="Configurações" {...props} />
      <S.Option onPress={() => setShowModal(true)}>
        <S.OptionTitle>Som</S.OptionTitle>
        <S.OptionSubTitle>Som da notificação</S.OptionSubTitle>
      </S.Option>

      <S.Option onPress={() => props.navigation.navigate('ChangePassword')}>
        <S.OptionTitle>Alterar Senha</S.OptionTitle>
        <S.OptionSubTitle>Alterar senha do aplicativo</S.OptionSubTitle>
      </S.Option>

      {showModal && (
        <ModalSoundNotification
          visible={showModal}
          onClose={() => setShowModal(false)}
        />
      )}
    </S.Container>
  );
};

export default Configurations;

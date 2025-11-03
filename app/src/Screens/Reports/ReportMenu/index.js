/* eslint-disable react/jsx-props-no-spreading */
import React from 'react';

import Header from '../../../Components/Header';

import * as S from './styles';

const ReportMenu = (props) => {
  function handleNavigateReport() {
    props.navigation.navigate('ReportsScreen');
  }

  function handleOldNavigateReport() {
    props.navigation.navigate('OldReports');
  }

  return (
    <S.Container>
      <Header title="Relatórios" name="relat" {...props} />

      <S.Content>
        <S.ButtonReport onPress={handleNavigateReport}>
          <S.New>NEW</S.New>
          <S.ButtonReportText>Relatórios Novos</S.ButtonReportText>
        </S.ButtonReport>
        <S.ButtonReport onPress={handleOldNavigateReport}>
          <S.ButtonReportText>Relatórios</S.ButtonReportText>
        </S.ButtonReport>
      </S.Content>
    </S.Container>
  );
};

export default ReportMenu;

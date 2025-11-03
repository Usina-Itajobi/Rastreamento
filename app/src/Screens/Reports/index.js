/* eslint-disable react/jsx-props-no-spreading */
import React from 'react';
import Header from '../../Components/Header';
import * as S from './styles';

const Reports = (props) => {
  return (
    <S.Container>
      <Header title="Relatórios" name="relat" {...props} showBackButton/>

      <S.Content>
        <S.Title>
          Selecione o <S.TitleBold>Relatório</S.TitleBold> abaixo*
        </S.Title>
        <S.ButtonReport onPress={()=>{props.navigation.navigate('FilterReport');}}>
          <S.ButtonReportText>Posições</S.ButtonReportText>
        </S.ButtonReport>

        <S.ButtonReport onPress={()=>{props.navigation.navigate('FilterSpeed');}}>
          <S.ButtonReportText>Velocidade</S.ButtonReportText>
        </S.ButtonReport>

        <S.ButtonReport onPress={()=>{props.navigation.navigate('FilterKmAccumulated');}}>
          <S.ButtonReportText>KM Acumulado</S.ButtonReportText>
        </S.ButtonReport>
      </S.Content>
    </S.Container>
  );
};

export default Reports;

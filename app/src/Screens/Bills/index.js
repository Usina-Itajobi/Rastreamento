import React, { useState, useEffect, useCallback } from 'react';

import {
  View,
  Text,
  useWindowDimensions,
  ImageBackground,
  TouchableOpacity,
  ActivityIndicator,
  Linking,
  Alert,
  RefreshControl,
  ScrollView,
} from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import moment from 'moment';
import 'moment/locale/pt-br';

import Header from '../../Components/Header';
import * as S from './styles';
import { useAuth } from '../../context/authContext';

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const BillsScreen = (props) => {
  moment.locale('pt-br');
  const { height, width } = useWindowDimensions();
  const { selectedAccount } = useAuth();
  const [loading, setLoading] = useState(false);
  const [bills, setBills] = useState([]);
  const [flatListItemIndex, seFlatListItemIndex] = useState();
  const [currentItem, setCurrentItem] = useState({
    cnrt_id: null,
    cnrt_data_lancamento: null,
    cnrt_tipo: null,
    cnrt_vencimento: null,
    cnrt_valor: null,
    cnrt_categoria: null,
    cnrt_desc: null,
    cnrt_arq: null,
    cnrt_data_pgto: null,
    cnrt_id_client: null,
    cnrt_excluido: null,
    cnrt_forn_id: null,
    cnrt_bol_id: null,
    atendimentos: null,
    comprovante: null,
    bol_link: null,
    bol_paynumber: null,
  });

  const getBills = useCallback(async () => {
    setLoading(true);
    try {
      const enterprise = await AsyncStorage.getItem('@grupoitajobi:enterprise');

      const { baseUrl } = JSON.parse(enterprise);

      const result = await fetch(
        `${baseUrl}/metronic/api/get_boletos.php?&v_login=${selectedAccount.h}`,
      );
      let data = await result.text();
      data = JSON.parse(data);

      if (data.data) {
        setBills(data.data.reverse());
        seFlatListItemIndex(data.data.length - 1);
      }
    } catch (error) {
      Alert.alert('Erro', 'Ocorreu um erro ao carregar os dados!');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    getBills();
  }, []);

  useEffect(() => {
    if (bills.length > 0) {
      if (bills[flatListItemIndex]) {
        let currentFlatListItem = { ...currentItem };
        currentFlatListItem = bills[flatListItemIndex];
        setCurrentItem(currentFlatListItem);
      }
    }
  }, [flatListItemIndex]);

  const nextItem = () => {
    if (flatListItemIndex >= 0 && flatListItemIndex < bills.length) {
      const index = flatListItemIndex;
      seFlatListItemIndex(index + 1);
    }
  };

  const previousItem = () => {
    if (flatListItemIndex > 0 && flatListItemIndex <= bills.length) {
      const index = flatListItemIndex;
      seFlatListItemIndex(index - 1);
    }
  };

  return (
    <ImageBackground
      resizeMode="cover"
      source={require('../../assets/images/VheicleBackground.jpg')}
      style={{ flex: 1 }}
    >
      <Header title="Minhas Faturas" {...props} />

      <S.BackContainer
        style={{
          width: width / 1.1,
          height: height / 1.3,
        }}
      />
      <ScrollView
        contentContainerStyle={{
          width: width / 1.2,
          paddingBottom: width > height ? height / 1.4 : height / 8,
          height: loading
            ? height / 1.2
            : width > height
            ? 'auto'
            : height / 1.2,
          backgroundColor: '#004E70',
          alignSelf: 'center',
          borderRadius: 15,
          marginTop: 10,
          elevation: 4,
          paddingTop: 10,
        }}
        style={{ flexGrow: 1 }}
        refreshControl={
          <RefreshControl
            refreshing={loading}
            onRefresh={async () => {
              await getBills();
            }}
          />
        }
      >
        <View style={{ flex: 1, justifyContent: 'center' }}>
          {!loading && bills.length > 0 ? (
            <View style={{ height: '100%' }}>
              <S.MonthContainer>
                {bills.length > 0 && flatListItemIndex > 0 && (
                  <TouchableOpacity
                    activeOpacity={0.8}
                    style={{
                      position: 'absolute',
                      left: 20,
                    }}
                    onPress={() => previousItem()}
                  >
                    <MaterialCommunityIcons
                      name={'arrow-left'}
                      size={30}
                      color={'#F69C33'}
                    />
                  </TouchableOpacity>
                )}

                <View
                  style={{ justifyContent: 'center', alignItems: 'center' }}
                >
                  <S.MonthText>
                    {currentItem.cnrt_vencimento
                      ? capitalizeFirstLetter(
                          moment(currentItem.cnrt_vencimento).format('MMMM'),
                        )
                      : '----'}
                  </S.MonthText>
                  <S.MonthTextUnderline />
                </View>

                {bills.length > 0 && flatListItemIndex < bills.length - 1 && (
                  <TouchableOpacity
                    activeOpacity={0.8}
                    style={{
                      position: 'absolute',
                      right: 20,
                    }}
                    onPress={() => nextItem()}
                  >
                    <MaterialCommunityIcons
                      name={'arrow-right'}
                      size={30}
                      color={'#F69C33'}
                    />
                  </TouchableOpacity>
                )}
              </S.MonthContainer>

              <S.BillDescriptionMainContainer>
                <S.BillDescriptionContainer>
                  <S.BillDescriptionTitle>Valor</S.BillDescriptionTitle>
                  <Text
                    style={{
                      color: '#FFF',
                      fontSize: 22,
                      fontWeight: 'bold',
                      marginTop: 4,
                    }}
                  >
                    R${' '}
                    {currentItem.cnrt_valor
                      ? parseFloat(currentItem.cnrt_valor)
                          .toFixed(2)
                          .replace('.', ',')
                      : '----'}
                  </Text>
                </S.BillDescriptionContainer>

                <S.BillDescriptionContainer>
                  <S.BillDescriptionTitle>Status</S.BillDescriptionTitle>
                  <S.BillDescriptionTitle
                    style={{
                      color: currentItem.cnrt_data_pgto ? '#32CD32' : 'red',
                      fontSize: 18,
                      marginTop: 1,
                    }}
                  >
                    {currentItem.cnrt_data_pgto ? 'Paga' : 'Pagamento Pendente'}
                  </S.BillDescriptionTitle>
                </S.BillDescriptionContainer>

                <S.BillDescriptionContainer>
                  <S.BillDescriptionTitle>Lançamento</S.BillDescriptionTitle>
                  <S.BillDescriptionTitle
                    style={{ color: '#FFF', fontWeight: '500', marginTop: 4 }}
                  >
                    {currentItem.cnrt_data_lancamento
                      ? moment(currentItem.cnrt_data_lancamento).format(
                          'DD/MM/YYYY',
                        )
                      : '----'}
                  </S.BillDescriptionTitle>
                </S.BillDescriptionContainer>

                <S.BillDescriptionContainer>
                  <S.BillDescriptionTitle>Vencimento</S.BillDescriptionTitle>
                  <S.BillDescriptionTitle
                    style={{ color: '#FFF', fontWeight: '500', marginTop: 4 }}
                  >
                    {currentItem.cnrt_vencimento
                      ? moment(currentItem.cnrt_vencimento).format('DD/MM/YYYY')
                      : '----'}
                  </S.BillDescriptionTitle>
                </S.BillDescriptionContainer>

                {currentItem.cnrt_data_pgto && (
                  <S.BillDescriptionContainer>
                    <S.BillDescriptionTitle>Pagamento</S.BillDescriptionTitle>
                    <S.BillDescriptionTitle
                      style={{ color: '#FFF', fontWeight: '500', marginTop: 4 }}
                    >
                      {currentItem.cnrt_data_pgto
                        ? moment(currentItem.cnrt_data_pgto).format(
                            'DD/MM/YYYY',
                          )
                        : '----'}
                    </S.BillDescriptionTitle>
                  </S.BillDescriptionContainer>
                )}

                <S.BillDescriptionContainer style={{ maxWidth: width / 1.2 }}>
                  <S.BillDescriptionTitle>Nº Boleto</S.BillDescriptionTitle>
                  <S.BillDescriptionTitle
                    selectable
                    selectionColor={'#F69C33'}
                    style={{
                      color: '#FFF',
                      fontWeight: '500',
                      marginTop: 4,
                    }}
                  >
                    {currentItem.bol_paynumber
                      ? currentItem.bol_paynumber
                      : '----'}
                  </S.BillDescriptionTitle>
                </S.BillDescriptionContainer>

                <S.BillDescriptionContainer style={{ maxWidth: width / 1.2 }}>
                  <S.BillDescriptionTitle>Descrição</S.BillDescriptionTitle>
                  <S.BillDescriptionTitle
                    style={{ color: '#FFF', fontWeight: '500', marginTop: 4 }}
                  >
                    {currentItem.cnrt_desc ? currentItem.cnrt_desc : '----'}
                  </S.BillDescriptionTitle>
                </S.BillDescriptionContainer>
              </S.BillDescriptionMainContainer>

              <S.DownloadButtonContainer
                activeOpacity={0.8}
                disabled={
                  currentItem.bol_link === null ||
                  currentItem.bol_link === undefined ||
                  currentItem.bol_link === ''
                }
                onPress={async () => {
                  try {
                    setLoading(true);
                    await Linking.openURL(currentItem.bol_link);
                  } catch (error) {
                    Alert.alert(
                      'Ocorreu um erro!',
                      'Tivemos problemas para carregar o boleto, por favor tente novamente.',
                    );
                  } finally {
                    setLoading(false);
                  }
                }}
              >
                <S.DownloadButtonText>
                  {currentItem.bol_link === null ||
                  currentItem.bol_link === undefined ||
                  currentItem.bol_link === ''
                    ? 'Visualização Indisponível'
                    : 'Visualizar Boleto'}
                </S.DownloadButtonText>
                <MaterialCommunityIcons
                  name={'file'}
                  size={20}
                  color={'#FFF'}
                />
              </S.DownloadButtonContainer>
            </View>
          ) : (
            <View>
              {!loading && (
                <S.LoaderContainer>
                  <MaterialCommunityIcons
                    name={'text-box-search-outline'}
                    size={50}
                    color={'#FFF'}
                  />
                  <S.UpdateText>Nenhum dado encontrado!</S.UpdateText>
                </S.LoaderContainer>
              )}
            </View>
          )}

          {loading && (
            <S.LoaderContainer>
              <ActivityIndicator size="large" color="#FFF" />
              <S.UpdateText>Carregando dados...</S.UpdateText>
            </S.LoaderContainer>
          )}
        </View>
      </ScrollView>
    </ImageBackground>
  );
};

export default BillsScreen;

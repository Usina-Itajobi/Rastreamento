import React, { useState, useEffect } from 'react';

import {
  View,
  ActivityIndicator,
  Alert,
  Linking,
  useWindowDimensions,
  Text,
  ScrollView,
  TouchableOpacity,
  RefreshControl,
  ImageBackground
} from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import moment from 'moment';
import 'moment/locale/pt-br';
import { useAuth } from '../../context/authContext';
import * as S from './styles';

const PendingBillsScreen = (props) => {
  moment.locale('pt-br');

  const { tipo, token } = props.route.params || {};

  const { selectedAccount } = useAuth();
  const [flatListItemIndex, seFlatListItemIndex] = useState();

  const { height, width } = useWindowDimensions();

  const [loading, setLoading] = useState(true);
  const [billsQtde, setBillsQtde] = useState(0);
  const [bills, setBills] = useState([]);

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

  const [bill] = useState({
    bol_link: null,
    bol_paynumber: null,
    base64Pix: null,
    copiaColaPix: null,
  });

  const logOut = async () => {
    await AsyncStorage.clear()
      .then(() => {
        props.navigation.navigate('Welcome');
      })
      .catch(() => {
        Alert.alert('Ocorreu um erro!');
      });
  };

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  const getBills = async () => {
    setLoading(true);
    try {
      /**
       * Consultar o acesso do usuário ao aplicativo. Caso ele tenha contas em atraso, seu acesso ao aplicativo será bloqueado.
       */
      const getBloqueioApp = await fetch(
        `https://api.ctracker.com.br/metronic/api/get_bloqueio_app.php?&h=${tipo === 'esqueciSenha' ? token : selectedAccount.h}`,
      );

      let bloqueioAppData = await getBloqueioApp.text();
      bloqueioAppData = JSON.parse(bloqueioAppData);

      if (
        bloqueioAppData.data &&
        bloqueioAppData.data.bloqueio_automatico_cobranca
      ) {
        if (bloqueioAppData.data.bloqueio_automatico_cobranca === 'N') {
          if (tipo === 'esqueciSenha') {
            props.navigation.goBack();
          } else {
            props.navigation.navigate('AppStack');
          }
        } else {
          const getBoletos = await fetch(
            `https://api.ctracker.com.br/metronic/api/get_boletos_pendentes.php?&h=${tipo === 'esqueciSenha' ? token : selectedAccount.h}`,
          );
          let boletosData = await getBoletos.text();
          boletosData = JSON.parse(boletosData);

          if (boletosData.data) {
            const qtde = boletosData.data.length;
            setBillsQtde(qtde);

            if (!(qtde > 0)) {
              if (tipo === 'esqueciSenha') {
                props.navigation.goBack();
              } else {
                props.navigation.navigate('AppStack');
              }
            } else {
              setBills(boletosData.data.reverse());
              seFlatListItemIndex(boletosData.data.length - 1);
            }
          } else {
            if(boletosData?.vencidosRecentes){
              Alert.alert(
                'Pagamento em Atraso',
                'Detectamos boletos vencidos. Caso o pagamento não seja realizado, o acesso ao aplicativo poderá ser bloqueado.\n\nSe você já efetuou o pagamento, favor desconsiderar este aviso.',
              );
            }
            if (tipo === 'esqueciSenha') {
              props.navigation.goBack();
            } else {
              props.navigation.navigate('AppStack');
            }
          }
        }
      } else {
        Alert.alert(
          'Erro',
          'Ocorreu um erro ao carregar algumas informações!',
        );
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Erro', 'Ocorreu um erro ao carregar os dados!');
    } finally {
      setTimeout(() => {
        setLoading(false);
      }, 2000);
    }
  };

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

  useEffect(() => {
    getBills();
  }, [billsQtde, bill.bol_paynumber, selectedAccount?.username, token]);

  return (
    <View style={{ flex: 1, backgroundColor: '#FFFFFF' }}>
      {!tipo && loading ? (
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
              Validando usuário
            </Text>

            <ActivityIndicator size="large" color="#004e70" />
          </View>
        </S.ContainerAnimation>
      ) : (
          <ImageBackground
            resizeMode="cover"
            source={require('../../assets/images/VheicleBackground.jpg')}
            style={{ flex: 1 }}
          >

          <S.MainLabel>
            Favor entrar em contato com Financeiro {'\n'} (16) 99733-9299,
            existem faturas em atraso, seu acesso foi bloqueado!
          </S.MainLabel>

          <S.DownloadButtonContainer
            onPress={async () => {
              await logOut();
            }}
            activeOpacity={0.8}
            style={{
              marginTop: 0,
              marginbottom: 0,
            }}
          >
            <S.DownloadButtonText>Sair</S.DownloadButtonText>
            <MaterialCommunityIcons name={'logout'} size={20} color={'#FFF'} />
          </S.DownloadButtonContainer>

          <S.BackContainer
            style={{
              width: width / 1.1,
              height: height / 1.6,
            }}
          />

          <ScrollView
            contentContainerStyle={{
              width: width / 1.2,
              paddingBottom: width > height ? height / 1.4 : height / 8,
              height: loading
                ? height / 1.4
                : width > height
                ? 'auto'
                : height / 1.4,
              backgroundColor: '#004E70',
              alignSelf: 'center',
              borderRadius: 15,
              marginTop: 40,
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
      )}
    </View>
  );
};

export default PendingBillsScreen;

/* eslint-disable no-param-reassign */
/* eslint-disable react/jsx-props-no-spreading */
import AsyncStorage from '@react-native-async-storage/async-storage';
import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  ImageBackground,
  Platform,
  RefreshControl,
  SafeAreaView,
  FlatList,
  ScrollView
} from 'react-native';
import { Searchbar } from 'react-native-paper';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import moment from 'moment';
import DatePicker from '@react-native-community/datetimepicker';
import axios from 'axios';

import Header from '../../../Components/Header';

import * as S from './styles';
import assets from '../../../assets';

const currentDate = moment().locale('pt-br').format('D/MM/yyyy');
const date = new Date();

const Reports = (props) => {
  const [vehicles, setVehicles] = useState([]);
  const [vehicleSelected, setVehicleSelected] = useState(null);
  const [loading, setLoading] = useState(false);
  const [loadingGenerateReport, setLoadingGenerateReport] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [dateInit, setDateInit] = useState(currentDate || '01/01/2021');
  const [hourInit, setHourInit] = useState('00:00:00');
  const [dateFinal, setDateFinal] = useState(currentDate || '17/09/2021');
  const [hourFinal, setHourFinal] = useState('23:59:00');
  const [dateInitPicker, setDateInitPicker] = useState(date);
  const [hourInitPicker, setHourInitPicker] = useState(date);
  const [dateFinalPicker, setDateFinalPicker] = useState(date);
  const [hourFinalPicker, setHourFinalPicker] = useState(date);

  const [mode, setMode] = useState('date');
  const [show, setShow] = useState(false);
  const [calendarTypeChange, setCalendarTypeChange] = useState('DATE_INITIAL');

  const [vehiclesFilter, setVehiclesFilter] = useState([]);

  const [reportData, setReportData] = useState([]);

  const getVehicles = useCallback(async () => {
    try {
      setLoading(true);
      const accessToken = await AsyncStorage.getItem('@ctracker:accessToken');

      const form = new FormData();
      form.append('h', accessToken);

      const options = {
        method: 'POST',
        url: 'https://ctracker.com.br/metronic/api/listar_veiculos.php',
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        data: form,
      };

      const response = await axios.request(options);

      const { data } = response;

      setVehicles(data);
      setVehiclesFilter(data);
    } catch (error) {
      console.log(error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    getVehicles();
  }, []);

  function handleSelectVehicle(vehicle) {
    setVehicleSelected(vehicle);
  }

  const DateInitChange = (event, selectedDate) => {
    let formatDate = dateInit;
    if (selectedDate) {
      formatDate = moment(selectedDate).locale('pt-br').format('DD/MM/yyyy');
    }
    setDateInit(formatDate);
    setDateInitPicker(selectedDate);
  };
  const DateFinalChange = (event, selectedDate) => {
    let formatDate = dateFinal;
    if (selectedDate) {
      formatDate = moment(selectedDate).locale('pt-br').format('DD/MM/yyyy');
    }
    setDateFinal(formatDate);
    setDateFinalPicker(selectedDate);
  };
  const HourInitChange = (event, selectedDate) => {
    const formatHour = moment(selectedDate).locale('pt-br').format('hh:mm:ss');

    setHourInit(formatHour);
    setHourInitPicker(selectedDate);
  };
  const HourFinalChange = (event, selectedDate) => {
    const formatHour = moment(selectedDate).locale('pt-br').format('hh:mm:ss');

    setHourFinal(formatHour);
    setHourFinalPicker(selectedDate);
  };

  const onChange = (event, selectedDate) => {
    setShow(false);

    try {
      if (mode === 'date') {
        let formatDate = dateInit;
        if (selectedDate) {
          formatDate = moment(selectedDate)
            .locale('pt-br')
            .format('DD/MM/yyyy');
        }

        switch (calendarTypeChange) {
          case 'DATE_INIT':
            setDateInit(formatDate);
            setDateInitPicker(selectedDate);
            break;
          case 'DATE_FINAL':
            setDateFinal(formatDate);
            setDateFinalPicker(selectedDate);
            break;
          default:
            break;
        }
      }

      if (mode === 'time') {
        if (selectedDate) {
          const formatHour = moment(selectedDate)
            .locale('pt-br')
            .format('hh:mm:ss');

          switch (calendarTypeChange) {
            case 'HOUR_INIT':
              setHourInit(formatHour);
              setHourInitPicker(selectedDate);
              break;
            case 'HOUR_FINAL':
              setHourFinal(formatHour);
              setHourFinalPicker(selectedDate);
              break;

            default:
              break;
          }
        }
      }
    } catch (error) {
      console.log(error);
    }
  };

  const showMode = (currentMode, typeDate) => {
    setShow(true);
    setMode(currentMode);
    setCalendarTypeChange(typeDate);
  };

  async function handleGenerateReport() {
    try {
      setLoadingGenerateReport(true);

      const [dayInit, monthInit, yearInit] = dateInit.split('/');
      const [dayFinal, monthFinal, yearFinal] = dateFinal.split('/');
      const dateInitFormated = `${yearInit}-${monthInit}-${dayInit}`;
      const dateFinalFormated = `${yearFinal}-${monthFinal}-${dayFinal}`;

      const dateInitFormatedComparation = `${dayInit}-${monthInit}-${yearInit}`;
      const dateFinalFormatedComparation = `${dayFinal}-${monthFinal}-${yearFinal}`;

      const date1 = moment(dateInitFormatedComparation, 'DD-MM-YYYY');
      const date2 = moment(dateFinalFormatedComparation, 'DD-MM-YYYY');

      if (date1 > date2) {
        Alert.alert(
          'Datas inválidas',
          'A data final deve ser maior que a inicial',
        );

        return;
      }

      const diffDays = date2.diff(date1, 'days');

      if (diffDays > 7) {
        Alert.alert(
          'Datas inválidas',
          'A data deve estar em um periodo de 7 dias',
        );
        return;
      }

      const form = new FormData();
      form.append('data_ini', `${dateInitFormated} ${hourInit}`);
      form.append('data_fim', `${dateFinalFormated} ${hourFinal}`);
      form.append('v_veiculo', vehicleSelected.id);

      const options = {
        method: 'POST',
        url: `https://ctracker.com.br/metronic/api/rel_km.php`,
        headers: {
          'Content-Type': 'multipart/form-date',
        },
        data: form,
      };

      const response = await axios.request(options);

      const { data } = response;

      if (data?.length === 0) {
        Alert.alert(
          'Nenhuma localização encontrada',
          'Escolha outro veículo ou tente novamente mais tarde!',
        );

        return;
      }

      setReportData(data ?? []);

    } catch (error) {
      Alert.alert('Erro ao gerar relatório', 'Tente novamente mais tarde!');
      console.log(error);
    } finally {
      setLoadingGenerateReport(false);
    }
  }

  function handleFilterVehicles(filterValue) {
    try {
      setSearchQuery(filterValue);
      const regex = new RegExp(`${filterValue.trim()}`, 'i');
      const vehiclesFilterTemp = vehicles?.filter(
        (v) => v.placa.search(regex) >= 0,
      );

      setVehiclesFilter(vehiclesFilterTemp);
    } catch (error) {
      console.log('error', JSON.stringify(error));
    }
  }

  return (
    <>
      <Header title="Relatório KM Acumulado" {...props} showBackButton />
      <ImageBackground
        source={assets.images.vheicle_background}
        style={{ flex: 1 }}
      >
        <SafeAreaView style={{ flex: 1 }}>
          <S.Container>
            <S.WrapperFilterDateHour>
              <S.WrapperFilterRow>
                <S.WrapperButtonChooseDate>
                  <S.ButtonChooseDateLabel>
                    Data Inicial
                  </S.ButtonChooseDateLabel>
                  <S.ButtonChooseDate
                    onPress={() => showMode('date', 'DATE_INIT')}
                  >
                    {Platform.OS === 'ios' ? (
                      <DatePicker
                        testID="dateTimePicker"
                        value={dateInitPicker}
                        mode="date"
                        is24Hour
                        display="default"
                        onChange={DateInitChange}
                        themeVariant="light"
                        style={{
                          flex: 1,
                          alignItems: 'center',
                          justifyContent: 'center',
                        }}
                      />
                    ) : (
                      <S.ButtonChooseDateText>
                        {dateInit}
                      </S.ButtonChooseDateText>
                    )}
                    <MaterialIcons
                      name="calendar-today"
                      color="#004e70"
                      size={24}
                    />
                  </S.ButtonChooseDate>
                </S.WrapperButtonChooseDate>

                <S.WrapperButtonChooseDate>
                  <S.ButtonChooseDateLabel>Data Final</S.ButtonChooseDateLabel>
                  <S.ButtonChooseDate
                    onPress={() => showMode('date', 'DATE_FINAL')}
                  >
                    {Platform.OS === 'ios' ? (
                      <DatePicker
                        testID="dateTimePicker"
                        value={dateFinalPicker}
                        mode="date"
                        themeVariant="light"
                        is24Hour
                        display="default"
                        onChange={DateFinalChange}
                        style={{
                          flex: 1,
                          alignItems: 'center',
                          justifyContent: 'center',
                        }}
                      />
                    ) : (
                      <S.ButtonChooseDateText>
                        {dateFinal}
                      </S.ButtonChooseDateText>
                    )}
                    <MaterialIcons
                      name="calendar-today"
                      color="#004e70"
                      size={24}
                    />
                  </S.ButtonChooseDate>
                </S.WrapperButtonChooseDate>
              </S.WrapperFilterRow>
              <S.WrapperFilterRow>
                <S.WrapperButtonChooseDate>
                  <S.ButtonChooseDateLabel>
                    Hora Inicial
                  </S.ButtonChooseDateLabel>
                  <S.ButtonChooseDate
                    onPress={() => showMode('time', 'HOUR_INIT')}
                  >
                    {Platform.OS === 'ios' ? (
                      <DatePicker
                        testID="dateTimePicker"
                        value={hourInitPicker}
                        mode="time"
                        is24Hour
                        display="default"
                        themeVariant="light"
                        onChange={HourInitChange}
                        style={{
                          flex: 1,
                          alignItems: 'center',
                          justifyContent: 'center',
                        }}
                      />
                    ) : (
                      <S.ButtonChooseDateText>
                        {hourInit}
                      </S.ButtonChooseDateText>
                    )}
                    <MaterialIcons
                      name="access-time"
                      color="#004e70"
                      size={24}
                    />
                  </S.ButtonChooseDate>
                </S.WrapperButtonChooseDate>

                <S.WrapperButtonChooseDate>
                  <S.ButtonChooseDateLabel>Hora Final</S.ButtonChooseDateLabel>
                  <S.ButtonChooseDate
                    onPress={() => showMode('time', 'HOUR_FINAL')}
                  >
                    {Platform.OS === 'ios' ? (
                      <DatePicker
                        testID="dateTimePicker"
                        value={hourFinalPicker}
                        mode="time"
                        is24Hour
                        display="default"
                        themeVariant="light"
                        onChange={HourFinalChange}
                        style={{
                          flex: 1,
                          alignItems: 'center',
                          justifyContent: 'center',
                        }}
                      />
                    ) : (
                      <S.ButtonChooseDateText>
                        {hourFinal}
                      </S.ButtonChooseDateText>
                    )}
                    <MaterialIcons
                      name="access-time"
                      color="#004e70"
                      size={24}
                    />
                  </S.ButtonChooseDate>
                </S.WrapperButtonChooseDate>
              </S.WrapperFilterRow>


            </S.WrapperFilterDateHour>

            {vehicleSelected?.id ? (
              <S.VehicleSelected>
                <S.VehicleSelectedName>
                  {vehicleSelected?.placa}
                </S.VehicleSelectedName>

                <S.VehicleSelectedButtonDelete
                  onPress={() => {
                      setVehicleSelected(null)
                      setReportData([])
                    }
                  }
                >
                  <MaterialIcons name="close" color="#004e70" size={24} />
                </S.VehicleSelectedButtonDelete>
              </S.VehicleSelected>
            ) : (
              <S.LabelRequired>Selecione um veículo*</S.LabelRequired>
            )}

            {vehicleSelected?.id ? (
              <>
                <S.ButtonGenerate onPress={handleGenerateReport}>
                  {loadingGenerateReport ? (
                    <ActivityIndicator
                      size="small"
                      color="#fff"
                      animating={loadingGenerateReport}
                    />
                  ) : (
                    <S.ButtonGenerateText>GERAR</S.ButtonGenerateText>
                  )}
                </S.ButtonGenerate>

                {reportData.length > 0 && (
                  <ScrollView horizontal>
                    <S.Table>
                      <S.TableHeader>
                        <S.TableCellHeader style={{ width: 100 }}>Placa</S.TableCellHeader>
                        <S.TableCellHeader style={{ width: 100 }}>KM</S.TableCellHeader>
                        <S.TableCellHeader style={{ width: 200 }}>Data</S.TableCellHeader>
                      </S.TableHeader>

                      <FlatList
                        data={reportData}
                        keyExtractor={(item, index) => index.toString()}
                        renderItem={({ item }) => (
                          <S.TableRow>
                            <S.TableCell style={{ width: 100 }}>{item.placa}</S.TableCell>
                            <S.TableCell style={{ width: 100 }}>{item.km} km</S.TableCell>
                            <S.TableCell style={{ width: 200 }}>{item.data}</S.TableCell>
                          </S.TableRow>
                        )}
                      />
                    </S.Table>
                  </ScrollView>
                )}

              </>
            ) : (
              <>
                <Searchbar
                  placeholder="Pesquisar por Veículo"
                  onChangeText={handleFilterVehicles}
                  inputStyle={{ color: '#111' }}
                  style={{ backgroundColor: '#fff' }}
                  value={searchQuery}
                  placeholderTextColor={'#999'}
                  iconColor="#999"
                />
                <S.ListVehicles
                  data={vehiclesFilter}
                  keyExtractor={(item) => item.id}
                  refreshControl={
                    <RefreshControl
                      refreshing={loading}
                      onRefresh={getVehicles}
                    />
                  }
                  renderItem={({ item }) => (
                    <S.Vehicle onPress={() => handleSelectVehicle(item)}>
                      <S.WrapperVehiclesDesc>
                        <S.VehiclesTitle>{item.placa}</S.VehiclesTitle>
                      </S.WrapperVehiclesDesc>
                    </S.Vehicle>
                  )}
                />
              </>
            )}
            {show && Platform.OS === 'android' && (
              <DatePicker
                testID="dateTimePicker"
                value={date}
                mode={mode}
                is24Hour
                display="default"
                onChange={onChange}
              />
            )}
          </S.Container>
        </SafeAreaView>
      </ImageBackground>
    </>
  );
};

export default Reports;

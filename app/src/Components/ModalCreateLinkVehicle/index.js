/* eslint-disable react/prop-types */
import React, { useState } from 'react';
import {
  Alert,
  Modal,
  Platform,
  TouchableWithoutFeedback,
  ActivityIndicator,
} from 'react-native';
import DatePicker from '@react-native-community/datetimepicker';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import Share from 'react-native-share';

import moment from 'moment';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as S from './styles';
import { useAuth } from '../../context/authContext';

const currentDate = moment().locale('pt-br').format('D/MM/yyyy');
const currentHour = moment().locale('pt-br').format('H:mm:ss');

const dateSystem = new Date();

const ModalCreateLinkVehicle = ({ visible, handleClose, id_bem }) => {
  const { selectedAccount } = useAuth();

  const [mode, setMode] = useState('date');
  const [show, setShow] = useState(false);
  const [date, setDate] = useState(currentDate);
  const [hour, setHour] = useState(currentHour);
  const [datePicker, setDatePicker] = useState(dateSystem);
  const [hourPicker, setHourPicker] = useState(dateSystem);
  const [loading, setLoading] = useState(false);

  function onChange(_, dateSelected) {
    if (!dateSelected) {
      setShow(false);
      return;
    }

    if (mode === 'date') {
      setShow(false);
      let formatDate = date;

      formatDate = moment(dateSelected || date)
        .locale('pt-br')
        .format('DD/MM/yyyy');

      setDate(formatDate);
      setDatePicker(dateSelected || date);
    }

    if (mode === 'time') {
      setShow(false);
      let formatDate = date;

      formatDate = moment(dateSelected || date)
        .locale('pt-br')
        .format('HH:mm');

      setHour(formatDate);
      setHourPicker(dateSelected || date);
    }
  }

  function handleOpenCalendarDate() {
    setMode('date');
    setShow(true);
  }

  function handleOpenCalendarTime() {
    setMode('time');
    setShow(true);
  }

  function handleDateChangeIOS(_, dateSelected) {
    let formatDate = date;

    formatDate = moment(dateSelected || date)
      .locale('pt-br')
      .format('DD/MM/yyyy');

    setDate(formatDate);
    setDatePicker(dateSelected || date);
  }

  function handleHourChangeIOS(_, hourSelected) {
    let formatDate = date;

    formatDate = moment(hourSelected || date)
      .locale('pt-br')
      .format('HH:mm');

    setHour(formatDate);
    setHourPicker(hourSelected || date);
  }

  async function handleShareLink() {
    try {
      setLoading(true);
      if (moment(datePicker).isBefore(new Date(), 'day')) {
        Alert.alert('Somente datas futuras.');

        return;
      }

      if (!moment(hourPicker).isAfter(new Date(), 'minutes')) {
        Alert.alert('Somente horários futuros.');

        return;
      }

      const enterprise = await AsyncStorage.getItem('@ctracker:enterprise');

      const { baseUrl } = JSON.parse(enterprise);

      let dateFormated = moment(datePicker).format('YYYY-MM-DD');
      dateFormated += ` ${moment(datePicker).format('HH:mm:ss')}`;

      const formData = new FormData();
      formData.append('h', selectedAccount.h);
      formData.append('acao', 'gerar');
      formData.append('id_bem', id_bem);
      formData.append('date', dateFormated);

      const response = await fetch(
        `${baseUrl}/metronic/api/gerar_link_bem.php`,
        {
          method: 'POST',
          body: formData,
        },
      );

      const link = await response.json();

      if (!link.includes('https')) {
        Alert.alert('Houve um erro ao tentar compartilhar.');
        return;
      }

      await Share.open({ title: 'Link Veículo CTracker', message: link });
      handleClose();
    } catch (error) {
      if (error.message === 'User did not share') {
        return;
      }

      Alert.alert('Houve um erro ao tentar compartilhar.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <Modal visible={visible} onRequestClose={handleClose} transparent>
      <TouchableWithoutFeedback onPress={handleClose}>
        <S.ModalContainer />
      </TouchableWithoutFeedback>

      <S.Content>
        <S.Title>Escolha quando vai expirar</S.Title>

        <S.WrapperChooseDate>
          <S.ButtonChooseDate onPress={handleOpenCalendarDate}>
            {Platform.OS === 'ios' ? (
              <DatePicker
                value={datePicker}
                mode="date"
                is24Hour
                display="default"
                themeVariant="light"
                onChange={handleDateChangeIOS}
                style={{
                  flex: 1,
                  alignItems: 'center',
                  justifyContent: 'center',
                }}
              />
            ) : (
              <S.ButtonChooseDateText>{date}</S.ButtonChooseDateText>
            )}
            <MaterialIcons name="access-time" color="#004e70" size={24} />
          </S.ButtonChooseDate>
          <S.ButtonChooseDate onPress={handleOpenCalendarTime}>
            {Platform.OS === 'ios' ? (
              <DatePicker
                value={hourPicker}
                mode="time"
                is24Hour
                display="default"
                themeVariant="light"
                onChange={handleHourChangeIOS}
                style={{
                  flex: 1,
                  alignItems: 'center',
                  justifyContent: 'center',
                }}
              />
            ) : (
              <S.ButtonChooseDateText>{hour}</S.ButtonChooseDateText>
            )}
            <MaterialIcons name="access-time" color="#004e70" size={24} />
          </S.ButtonChooseDate>
        </S.WrapperChooseDate>

        <S.ButtonGenerate onPress={handleShareLink} disabled={loading}>
          {loading ? (
            <ActivityIndicator size="small" color="white" />
          ) : (
            <S.ButtonGenerateText>Compartilhar Link</S.ButtonGenerateText>
          )}
        </S.ButtonGenerate>
      </S.Content>

      {show && Platform.OS === 'android' && (
        <DatePicker
          value={datePicker}
          mode={mode}
          is24Hour
          display="default"
          onChange={onChange}
        />
      )}
    </Modal>
  );
};

export default ModalCreateLinkVehicle;

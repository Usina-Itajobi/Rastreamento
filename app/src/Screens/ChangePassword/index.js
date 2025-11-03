import React, { useState } from 'react';

import {
  View,
  ImageBackground,
  ActivityIndicator,
  Alert,
  Keyboard,
} from 'react-native';
import { TextInput } from 'react-native-paper';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import Header from '../../Components/Header';
import * as S from './styles';
import {
  validateEightCharactersPassword,
  validateLettersPassword,
  validateNumbersPassword,
  validateSpecialCharactersPassword,
} from '../../utils/validations';

const ChangePasswordScreen = (props) => {
  const [loading, setLoading] = useState(false);
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [newPasswordConfirmation, setNewPasswordConfirmation] = useState('');
  const [newPasswordErrorMessage, setNewPasswordErrorMessage] = useState(null);
  const [
    newPasswordConfirmationErrorMessage,
    setNewPasswordConfirmationErrorMessage,
  ] = useState(null);
  const [currentPasswordVisible, setCurrentPasswordVisible] = useState(false);
  const [newPasswordVisible, setNewPasswordVisible] = useState(false);
  const [newPasswordConfirmationVisible, setNewPasswordConfirmationVisible] =
    useState(false);

  const validateCurrentPassword = (value) => {
    if (!value || typeof value !== 'string' || value === '') {
      setNewPasswordErrorMessage(null);
      return;
    }

    if (
      newPassword &&
      typeof newPassword === 'string' &&
      newPassword !== '' &&
      newPassword === value
    ) {
      setNewPasswordErrorMessage(
        'A nova senha deve ser diferente da senha atual',
      );
    } else {
      setNewPasswordErrorMessage(null);
    }
  };

  const validateNewPassword = (value) => {
    if (!value || typeof value !== 'string' || value === '') {
      setNewPasswordErrorMessage(null);
      setNewPasswordConfirmationErrorMessage(null);
      return;
    }

    if (!validateEightCharactersPassword(value)) {
      setNewPasswordErrorMessage(
        'A nova senha deve conter no mínimo 8 caracteres',
      );
    } else if (!validateLettersPassword(value)) {
      setNewPasswordErrorMessage(
        'A nova senha deve conter no mínimo uma letra (A-Z)',
      );
    } else if (!validateNumbersPassword(value)) {
      setNewPasswordErrorMessage(
        'A nova senha deve conter no mínimo um número (0-9)',
      );
    } else if (!validateSpecialCharactersPassword(value)) {
      setNewPasswordErrorMessage(
        'A nova senha deve conter no mínimo um caracter especial (!#&@)',
      );
    } else if (
      currentPassword &&
      typeof currentPassword === 'string' &&
      currentPassword !== '' &&
      currentPassword === value
    ) {
      setNewPasswordErrorMessage(
        'A nova senha deve ser diferente da senha atual',
      );
    } else {
      setNewPasswordErrorMessage(null);
    }

    if (
      newPasswordConfirmation &&
      typeof newPasswordConfirmation === 'string' &&
      newPasswordConfirmation !== '' &&
      newPasswordConfirmation !== value
    ) {
      setNewPasswordConfirmationErrorMessage('As senhas não conferem');
    } else {
      setNewPasswordConfirmationErrorMessage(null);
    }
  };

  const validateNewPasswordConfirmation = (value) => {
    if (!value || typeof value !== 'string' || value === '') {
      setNewPasswordConfirmationErrorMessage(null);
      return;
    }

    if (
      newPassword &&
      typeof newPassword === 'string' &&
      newPassword !== '' &&
      newPassword !== value
    ) {
      setNewPasswordConfirmationErrorMessage('As senhas não conferem');
    } else {
      setNewPasswordConfirmationErrorMessage(null);
    }
  };

  const changePassword = async () => {
    setLoading(true);

    try {
      Keyboard.dismiss();

      const accessUserName = await AsyncStorage.getItem('@ctracker:user_name');
      const enterprise = await AsyncStorage.getItem('@ctracker:enterprise');
      const { baseUrl } = JSON.parse(enterprise);

      const form = new FormData();
      form.append('v_login', accessUserName);
      form.append('v_senha_atual', currentPassword);
      form.append('v_nova_senha', newPassword);
      form.append('v_confirmacao_nova_senha', newPasswordConfirmation);

      const options = {
        method: 'POST',
        url: `${baseUrl}/metronic/api/alterar_senha.php`,
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        data: form,
      };
      const response = await axios.request(options);

      const { data } = response;

      if (data) {
        if (data.error && data.error === 'S') {
          Alert.alert(
            'Erro',
            data.errormsg && data.errormsg !== ''
              ? data.errormsg
              : 'Ocorreu um erro ao alterar a senha!',
          );
        } else {
          setCurrentPassword('');
          setNewPassword('');
          setNewPasswordConfirmation('');
          setLoading(false);

          Alert.alert(
            '',
            data.errormsg && data.errormsg !== ''
              ? data.errormsg
              : 'Senha alterada com sucesso!',
            [
              {
                text: 'OK',
                onPress: () => props.navigation.goBack(),
              },
            ],
          );
        }
      } else {
        Alert.alert('Erro', 'Ocorreu um erro ao alterar a senha!');
      }
    } catch (error) {
      console.error(error);
      Alert.alert(
        'Erro',
        'Ocorreu um erro ao alterar a senha (' + error.message + ')',
      );
    }
    setLoading(false);
  };

  return (
    <ImageBackground
      resizeMode="cover"
      source={require('../../assets/images/LoginBackground.jpg')}
      style={{ flex: 1 }}
    >
      <Header title="Alterar Senha" {...props} />

      <S.BackContainer>
        <View>
          <S.TextInputContainer
            style={{
              flexDirection: 'row',
              alignItems: 'center',
            }}
          >
            <TextInput
              label="Senha Atual"
              theme={{
                colors: {
                  placeholder: 'white',
                  text: 'white',
                  primary: 'white',
                  underlineColor: 'transparent',
                },
              }}
              style={{
                backgroundColor: 'transparent',

                width: '90%',
              }}
              autoCapitalize="none"
              autoCompleteType="off"
              returnKeyType="next"
              secureTextEntry={!currentPasswordVisible}
              value={currentPassword}
              onChangeText={(value) => {
                setCurrentPassword(value);
                validateCurrentPassword(value);
              }}
            />

            <MaterialCommunityIcons
              name={!currentPasswordVisible ? 'eye' : 'eye-off'}
              size={24}
              color="#ffffff"
              onPress={() => setCurrentPasswordVisible(!currentPasswordVisible)}
            />
          </S.TextInputContainer>

          <View>
            <S.TextInputContainer
              style={{
                borderBottomColor:
                  newPasswordErrorMessage && newPasswordErrorMessage !== ''
                    ? '#ff0000'
                    : '#fff',
              }}
            >
              <TextInput
                label="Nova Senha"
                theme={{
                  colors: {
                    placeholder: 'white',
                    text: 'white',
                    primary: 'white',
                    underlineColor: 'transparent',
                  },
                }}
                style={{
                  backgroundColor: 'transparent',
                  width: '90%',
                }}
                autoCapitalize="none"
                autoCompleteType="off"
                returnKeyType="next"
                error={
                  newPasswordErrorMessage && newPasswordErrorMessage !== ''
                }
                secureTextEntry={!newPasswordVisible}
                value={newPassword}
                onChangeText={(value) => {
                  setNewPassword(value);
                  validateNewPassword(value);
                }}
              />

              <MaterialCommunityIcons
                name={!newPasswordVisible ? 'eye' : 'eye-off'}
                size={24}
                color="#ffffff"
                onPress={() => setNewPasswordVisible(!newPasswordVisible)}
              />
            </S.TextInputContainer>

            {newPasswordErrorMessage && newPasswordErrorMessage !== '' && (
              <S.InputErrorMessage>
                {newPasswordErrorMessage}
              </S.InputErrorMessage>
            )}
          </View>

          <View>
            <S.TextInputContainer
              style={{
                borderBottomColor:
                  newPasswordConfirmationErrorMessage &&
                  newPasswordConfirmationErrorMessage !== ''
                    ? '#ff0000'
                    : '#fff',
              }}
            >
              <TextInput
                label="Confirme a Nova Senha"
                theme={{
                  colors: {
                    placeholder: 'white',
                    text: 'white',
                    primary: 'white',
                    underlineColor: 'transparent',
                  },
                }}
                style={{
                  backgroundColor: 'transparent',
                  width: '90%',
                }}
                autoCapitalize="none"
                autoCompleteType="off"
                returnKeyType="done"
                error={
                  newPasswordConfirmationErrorMessage &&
                  newPasswordConfirmationErrorMessage !== ''
                }
                secureTextEntry={!newPasswordConfirmationVisible}
                value={newPasswordConfirmation}
                onChangeText={(value) => {
                  setNewPasswordConfirmation(value);
                  validateNewPasswordConfirmation(value);
                }}
              />

              <MaterialCommunityIcons
                name={!newPasswordConfirmationVisible ? 'eye' : 'eye-off'}
                size={24}
                color="#ffffff"
                onPress={() =>
                  setNewPasswordConfirmationVisible(
                    !newPasswordConfirmationVisible,
                  )
                }
              />
            </S.TextInputContainer>

            {newPasswordConfirmationErrorMessage &&
              newPasswordConfirmationErrorMessage !== '' && (
                <S.InputErrorMessage>
                  {newPasswordConfirmationErrorMessage}
                </S.InputErrorMessage>
              )}
          </View>
        </View>

        <S.SendButton
          activeOpacity={0.8}
          style={{
            backgroundColor:
              (newPasswordConfirmationErrorMessage &&
                newPasswordConfirmationErrorMessage !== '') ||
              (newPasswordConfirmationErrorMessage &&
                newPasswordConfirmationErrorMessage !== '') ||
              !currentPassword ||
              currentPassword === '' ||
              !newPassword ||
              newPassword === '' ||
              !newPasswordConfirmation ||
              newPasswordConfirmation === ''
                ? 'rgba(246, 156, 51, 0.8)'
                : 'rgba(246, 156, 51, 1)',
          }}
          disabled={
            (newPasswordConfirmationErrorMessage &&
              newPasswordConfirmationErrorMessage !== '') ||
            (newPasswordConfirmationErrorMessage &&
              newPasswordConfirmationErrorMessage !== '') ||
            !currentPassword ||
            currentPassword === '' ||
            !newPassword ||
            newPassword === '' ||
            !newPasswordConfirmation ||
            newPasswordConfirmation === '' ||
            loading
          }
          onPress={async () => {
            await changePassword();
          }}
        >
          {loading ? (
            <ActivityIndicator color="#ffffff" />
          ) : (
            <S.SendButtonText>Redefinir Senha</S.SendButtonText>
          )}
        </S.SendButton>
      </S.BackContainer>
    </ImageBackground>
  );
};

export default ChangePasswordScreen;

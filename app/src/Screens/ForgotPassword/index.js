import React, { Component } from 'react';

import {
  KeyboardAvoidingView,
  Text,
  TouchableOpacity,
  ImageBackground,
  ActivityIndicator,
  View,
  Dimensions,
  Alert,
  Keyboard,
} from 'react-native';
import { TextInput, RadioButton } from 'react-native-paper';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import axios from 'axios';

import styles from './styles';
import {
  validateCelular,
  validateCpf,
  validateEmail,
  validatePlaca,
} from '../../utils/validations';

class ForgotPasswordScreen extends Component {
  state = {
    loading: false,
    input: '',
    /**
     * @type {'Placa' | 'CPF' | 'Celular' | 'E-mail'}
     */
    inputType: 'Placa',
    inputErrorMessage: null,
  };

  submitHandler = async () => {
    const { input, inputType } = this.state;

    this.stateHandler('loading', true);

    try {
      Keyboard.dismiss();

      const form = new FormData();
      form.append('metodo_recuperacao', inputType);
      form.append('credenciais_recuperacao', input);

      const options = {
        method: 'POST',
        url: 'https://api.ctracker.com.br/metronic/api/esqueci_senha.php',
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        data: form,
      };
      const response = await axios.request(options);

      const { data } = response;

      if (data) {

        if (data.error && data.error === 'S') {

          if(data?.pagamento_atraso){
            this.props.navigation.navigate('PendingBills', {
              tipo: 'esqueciSenha',
              token: data.h,
            });
            // return;
          }

          Alert.alert(
            data?.pagamento_atraso ? 'Pagamento em Atraso' : 'Erro',
            data.errormsg && data.errormsg !== ''
              ? data.errormsg
              : 'Ocorreu um erro ao enviar o link de redefinição de senha!',
          );
        } else {
          this.stateHandler('input', '');
          this.stateHandler('inputType', 'Placa');

          Alert.alert(
            '',
            data.errormsg && data.errormsg !== ''
              ? data.errormsg
              : 'Link de redefinição de senha enviado com sucesso!',
            [
              {
                text: 'OK',
                onPress: () => this.props.navigation.navigate('Welcome'),
              },
            ],
          );
        }
      } else {
        Alert.alert(
          'Erro',
          'Ocorreu um erro ao enviar o link de redefinição de senha!',
        );
      }
    } catch (error) {
      Alert.alert(
        'Erro',
        `Ocorreu um erro ao enviar o link de redefinição de senha (${error.message})`,
      );
    }

    this.stateHandler('loading', false);
  };

  stateHandler = (key, value) => {
    this.setState({ [key]: value });
  };

  render() {
    const { loading, inputType, input, inputErrorMessage } = this.state;
    const d = Dimensions.get('window');

    /**
     * Validar Input de Esqueci a Senha
     * @param {string} value Valor do Input
     */
    const validateInput = (value) => {
      if (!value || typeof value !== 'string' || value === '') {
        this.stateHandler('inputErrorMessage', null);
        return;
      }

      if (!inputType || typeof inputType !== 'string') {
        this.stateHandler('inputErrorMessage', null);
        return;
      }

      switch (inputType) {
        case 'Placa':
          if (!validatePlaca(value)) {
            this.stateHandler('inputErrorMessage', 'Insira uma Placa válida');
          } else {
            this.stateHandler('inputErrorMessage', null);
          }
          break;
        case 'CPF':
          if (!validateCpf(value)) {
            this.stateHandler(
              'inputErrorMessage',
              'Insira um número de CPF válido\n(Insira apenas números)',
            );
          } else {
            this.stateHandler('inputErrorMessage', null);
          }
          break;
        case 'Celular':
          if (!validateCelular(value)) {
            this.stateHandler(
              'inputErrorMessage',
              'Insira um número de celular válido\n(Insira apenas números e não esqueça o DDD)',
            );
          } else {
            this.stateHandler('inputErrorMessage', null);
          }
          break;
        case 'E-mail':
          if (!validateEmail(value)) {
            this.stateHandler(
              'inputErrorMessage',
              'Insira um endereço de E-mail válido',
            );
          } else {
            this.stateHandler('inputErrorMessage', null);
          }
          break;
        default:
          this.stateHandler('inputErrorMessage', null);
          break;
      }
    };

    return (
      <ImageBackground
        source={require('../../assets/images/LoginBackground.jpg')}
        style={{
          position: 'absolute',
          flex: 1,
          backgroundColor: 'rgba(0,0,0,0.45)',
          width: d.width,
          height: d.height,
        }}
        resizeMode="cover"
      >
        <KeyboardAvoidingView
          style={styles.container}
          behavior="padding"
          enabled
        >
          <MaterialIcons
            name="arrow-back"
            color="#fff"
            size={26}
            style={{
              marginTop: 40,
            }}
            onPress={() => this.props.navigation.navigate('Welcome')}
          />

          <Text style={styles.title}>Esqueci a Senha</Text>
          <View style={styles.rectangle1} />

          <View
            style={{
              marginTop: 10,
            }}
          >
            <Text style={styles.buttonText}>
              Selecione o método pelo qual deseja redefinir a senha
            </Text>

            <View
              style={{
                flexDirection: 'row',
                justifyContent: 'space-around',
                alignItems: 'center',
                marginTop: 20,
              }}
            >
              <View
                style={{
                  flexDirection: 'row',
                  alignItems: 'center',
                }}
              >
                <RadioButton
                  value="Placa"
                  status={inputType === 'Placa' ? 'checked' : 'unchecked'}
                  onPress={() => {
                    this.stateHandler('inputType', 'Placa');
                    this.stateHandler('input', '');
                    this.stateHandler('inputErrorMessage', null);
                  }}
                  color="#f69c33"
                  uncheckedColor="#f69c33"
                />

                <Text style={styles.buttonText}>Placa</Text>
              </View>

              <View
                style={{
                  flexDirection: 'row',
                  alignItems: 'center',
                }}
              >
                <RadioButton
                  value="CPF"
                  status={inputType === 'CPF' ? 'checked' : 'unchecked'}
                  onPress={() => {
                    this.stateHandler('inputType', 'CPF');
                    this.stateHandler('input', '');
                    this.stateHandler('inputErrorMessage', null);
                  }}
                  color="#f69c33"
                  uncheckedColor="#f69c33"
                />

                <Text style={styles.buttonText}>CPF</Text>
              </View>

              <View
                style={{
                  flexDirection: 'row',
                  alignItems: 'center',
                }}
              >
                <RadioButton
                  value="Celular"
                  status={inputType === 'Celular' ? 'checked' : 'unchecked'}
                  onPress={() => {
                    this.stateHandler('inputType', 'Celular');
                    this.stateHandler('input', '');
                    this.stateHandler('inputErrorMessage', null);
                  }}
                  color="#f69c33"
                  uncheckedColor="#f69c33"
                />

                <Text style={styles.buttonText}>Celular</Text>
              </View>

              <View
                style={{
                  flexDirection: 'row',
                  alignItems: 'center',
                }}
              >
                <RadioButton
                  value="E-mail"
                  status={inputType === 'E-mail' ? 'checked' : 'unchecked'}
                  onPress={() => {
                    this.stateHandler('inputType', 'E-mail');
                    this.stateHandler('input', '');
                    this.stateHandler('inputErrorMessage', null);
                  }}
                  color="#f69c33"
                  uncheckedColor="#f69c33"
                />

                <Text style={styles.buttonText}>E-mail</Text>
              </View>
            </View>
          </View>

          {inputType && (
            <View>
              <TextInput
                label={inputType}
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
                  borderBottomColor: 'white',
                  borderBottomWidth: 1,
                }}
                autoCapitalize={inputType === 'Placa' ? 'characters' : 'none'}
                keyboardType={
                  inputType === 'CPF' || inputType === 'Celular'
                    ? 'numeric'
                    : inputType === 'E-mail'
                    ? 'email-address'
                    : 'default'
                }
                autoCompleteType={
                  inputType === 'Celular'
                    ? 'tel'
                    : inputType === 'E-mail'
                    ? 'email'
                    : 'off'
                }
                maxLength={
                  inputType === 'Placa'
                    ? 7
                    : inputType === 'CPF' || inputType === 'Celular'
                    ? 11
                    : 100
                }
                blurOnSubmit
                returnKeyType="done"
                error={inputErrorMessage && inputErrorMessage !== ''}
                value={input}
                onChangeText={(value) => {
                  this.setState({ input: value });
                  validateInput(value);
                }}
              />

              {inputErrorMessage && inputErrorMessage !== '' && (
                <Text style={styles.inputErrorMessage}>
                  {inputErrorMessage}
                </Text>
              )}
            </View>
          )}

          <TouchableOpacity
            style={[
              styles.button,
              {
                backgroundColor:
                  (inputErrorMessage && inputErrorMessage !== '') ||
                  !input ||
                  input === ''
                    ? 'rgba(246, 156, 51, 0.8)'
                    : 'rgba(246, 156, 51, 1)',
              },
            ]}
            activeOpacity={0.8}
            onPress={() => this.submitHandler()}
            disabled={
              (inputErrorMessage && inputErrorMessage !== '') ||
              !input ||
              input === '' ||
              loading
            }
          >
            {loading ? (
              <ActivityIndicator color="#ffffff" />
            ) : (
              <Text style={styles.buttonText}>ENVIAR LINK</Text>
            )}
          </TouchableOpacity>
        </KeyboardAvoidingView>
      </ImageBackground>
    );
  }
}

export default ForgotPasswordScreen;

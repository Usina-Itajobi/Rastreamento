import React, { Component } from 'react';
import { connect } from 'react-redux';

import {
  View,
  KeyboardAvoidingView,
  Text,
  TouchableOpacity,
  ImageBackground,
  ActivityIndicator,
  Platform,
  Image,
  Alert,
} from 'react-native';
import { TextInput } from 'react-native-paper';
import AsyncStorage from '@react-native-async-storage/async-storage';

import styles from './styles';

// Codigo 16001

class WelcomeScreen extends Component {
  state = {
    loading: false,
    code: __DEV__ ? '16001' : '',
    splash: true,
  };

  async componentDidMount() {
    this.hide();

    const enterprise = await AsyncStorage.getItem('@ctracker:enterprise');

    if (enterprise) {
      this.props.defineEnterprise(JSON.parse(enterprise));

      return this.props.navigation.navigate('AuthStack');
    }
  }

  hide() {
    setTimeout(async () => {
      this.setState({ splash: false });
    }, 5000);
  }

  submitHandler = async () => {
    const { code } = this.state;

    if (!code.length) {
      return;
    }

    try {
      this.stateHandler('loading', true);

      const result = await fetch(
        `https://itajobi.usinaitajobi.com.br/metronic/api/enterprise.php?id=${code}`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        },
      );

      let data = await result.text();

      // Alert.alert(result);

      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      data = JSON.parse(data);

      if (data.error === 'S') {
        throw new Error('Empresa não encontrada.');
      }

      const formatedData = {
        name: data?.nome,
        brand: data?.logo,
        clientId: data?.id_cliente,
        baseUrl: data?.url_api,
        cel: data?.cel,
        fone: data?.fone,
      };

      await AsyncStorage.setItem(
        '@ctracker:enterprise',
        JSON.stringify(formatedData),
      );
      await AsyncStorage.setItem('@ctracker:codigo', code);

      this.props.defineEnterprise(formatedData);
      this.props.navigation.navigate('AuthStack');
    } catch (error) {
      Alert.alert(
        error.message || 'Problemas ao procurar empresa. Tente novamente.',
      );
      this.stateHandler('code', '');
    } finally {
      this.stateHandler('loading', false);
    }
  };

  stateHandler = (key, value) => {
    this.setState({ [key]: value });
  };

  render() {
    const { loading, code, splash } = this.state;
    if (splash === true) {
      return (
        <ImageBackground
          resizeMode="cover"
          source={require('../../assets/images/background.jpg')}
          style={styles.backgroundImage}
        >
          <Image
            source={require('../../assets/images/logo.jpg')}
            style={{
              width: 250,
              height: 90,
              alignSelf: 'center',
              marginTop: '70%',
              borderRadius: 12,
            }}
          />
          <View style={{ marginTop: 25 }} />
          <Text
            style={{
              color: 'white',
              textAlign: 'center',
              fontSize: 15,
              marginTop: 20,
            }}
          >
            Validando senha
          </Text>
        </ImageBackground>
      );
    }
    {
      return (
        <ImageBackground
          resizeMode="cover"
          source={require('../../assets/images/background.jpg')}
          style={styles.backgroundImage}
        >
          <KeyboardAvoidingView
            style={{ flex: 1 }}
            behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            enabled
          >
            <View style={styles.container}>
              <Text style={styles.description}>
                A forma mais simples de rastrear seus veículos.
              </Text>

              <View>
                {/* <TextInput
                    style={styles.input}

                    placeholder="código da empresa"
                    value={code}
                    onChangeText={(code) => this.setState({ code })}
                  /> */}

                <TextInput
                  label="chave"
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
                    marginBottom: 20,
                  }}
                  autoCapitalize="none"
                  // placeholder="sua senha secreta"
                  keyboardType="numeric"
                  returnKeyType="send"
                  value={code}
                  onChangeText={(code) => this.setState({ code })}
                />

                <TouchableOpacity
                  style={styles.button}
                  activeOpacity={0.8}
                  onPress={() => this.submitHandler()}
                >
                  {loading ? (
                    <ActivityIndicator color="#ffffff" />
                  ) : (
                    <Text style={styles.buttonText}>ENTRAR</Text>
                  )}
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.forgotPasswordButton}
                  activeOpacity={1}
                  onPress={() =>
                    this.props.navigation.navigate('ForgotPassword')
                  }
                >
                  <Text style={styles.buttonText}>Esqueci a Senha</Text>
                </TouchableOpacity>
              </View>
            </View>
          </KeyboardAvoidingView>
        </ImageBackground>
      );
    }
  }
}

const mapDispatchToProps = (dispatch) => ({
  defineEnterprise: (enterprise) =>
    dispatch({
      type: 'DEFINE_ENTERPRISE',
      payload: enterprise,
    }),
});

export default connect(null, mapDispatchToProps)(WelcomeScreen);

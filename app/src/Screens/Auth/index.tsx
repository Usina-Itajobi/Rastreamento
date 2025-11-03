import React, { useEffect, useState } from 'react';
import {
  KeyboardAvoidingView,
  Text,
  TouchableOpacity,
  ImageBackground,
  ActivityIndicator,
  Platform,
  View,
  Dimensions,
  Image,
  Alert,
} from 'react-native';
import { TextInput } from 'react-native-paper';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';
import { OneSignal } from 'react-native-onesignal';
import {
  StackActions,
  useNavigation,
  useRoute,
} from '@react-navigation/native';
import Icons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as Sentry from '@sentry/react-native';
import styles from './styles';
import { AuthScreenParams } from '../../navigation';
import { Account, useAuth } from '../../context/authContext';

interface Enterprise {
  baseUrl: string;
  brand: string;
}

interface AuthScreenProps {
  navigation: any;
}

const AuthScreen: React.FC<AuthScreenProps> = () => {
  const navigation = useNavigation();
  const route = useRoute();
  const { top } = useSafeAreaInsets();
  const { addAccount, addDefaultAccount, defaultAccount } = useAuth();

  const { addAccountMode } = (route.params as AuthScreenParams) || {};

  const [enterprise, setEnterprise] = useState<Enterprise | null>(null);
  const [loading, setLoading] = useState(false);
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [accountName, setAccountName] = useState('');
  const [imageUrl, setImageUrl] = useState('');
  const [playerId, setPlayerId] = useState('');

  useEffect(() => {
    if (addAccountMode && __DEV__) {
      //         Login: logfaz
      // Senha:1234
      // Login: conecta
      // Senha:6hjg2745
      // Login: fabianobazan
      // Senha:1234
      setAccountName('Fabianobazan');
      setUsername('fabianobazan');
      setPassword('6hjg2745');
    } else if (__DEV__) {
      setAccountName('Conecta');
      setUsername('conecta');
      setPassword('6hjg2745');
    }
  }, [addAccountMode]);

  useEffect(() => {
    const initializeBills = async () => {
      if (addAccountMode) {
        return;
      }

      if (await AsyncStorage.getItem('@ctracker:accessToken')) {
        navigation.dispatch(StackActions.replace('PendingBills'));
      }
    };

    initializeBills();
  }, [navigation, addAccountMode]);

  useEffect(() => {
    const initializePlayerId = async () => {
      try {
        const enterpriseData = await AsyncStorage.getItem(
          '@ctracker:enterprise',
        );
        if (enterpriseData) {
          const parsedEnterprise = JSON.parse(enterpriseData);
          setEnterprise(parsedEnterprise);
          setImageUrl(parsedEnterprise.brand);
          const state = await OneSignal.User.pushSubscription.getIdAsync();
          setPlayerId(state || '');
        }
      } catch (error) {
        console.error('Error initializing player ID:', error);
        Sentry.captureException(error, {
          tags: {
            location: 'AuthScreen.initializePlayerId',
          },
        });
      }
    };

    initializePlayerId();
  }, [navigation]);

  const submitHandler = async () => {
    if (!username.length || !password.length) {
      return;
    }

    try {
      setLoading(true);

      if (!enterprise?.baseUrl) {
        throw new Error('Enterprise URL not found');
      }

      const result = await fetch(
        `${enterprise.baseUrl}/metronic/api/auth.php?v_login=${username}&v_senha=${password}`,
        { method: 'POST' },
      );

      let data = (await result.text()) as any;

      if (Platform.OS === 'android') {
        data = data.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      data = JSON.parse(data);

      if (data.error === 'S') {
        if(data.errormsg){
          throw new Error(data.errormsg);
        } else {
          throw new Error('Usuário e/ou senha invalido(s).');
        }
      }

      if (addAccountMode) {
        const listAccounts = await AsyncStorage.getItem('@ctracker:accounts');
        const accounts = (
          listAccounts ? JSON.parse(listAccounts) : []
        ) as Account[];

        const accountsByParent = accounts.filter(
          (acc) => acc.parentAccountEmail === defaultAccount?.email,
        );

        if (accountsByParent.find((account) => account.id === data.id)) {
          throw new Error('Conta já adicionada');
        }

        addAccount({ ...data, accountName, username });
      } else {
        await AsyncStorage.setItem('@ctracker:accessToken', data.h);
        await AsyncStorage.setItem('@ctracker:user_name', username);
        await AsyncStorage.setItem('@ctracker:keyMaps', data.keyMaps);
        await AsyncStorage.setItem(
          '@ctracker:user',
          JSON.stringify({
            id: data.id,
            name: data.nome,
            email: data.email,
          }),
        );

        addDefaultAccount({ ...data, accountName, username });
      }

      if (playerId && enterprise?.baseUrl) {
        try {
          await axios.post(
            `${enterprise.baseUrl}/metronic/api/playerid.php`,
            null,
            {
              params: {
                email: data.email,
                user_id: data.user_id || null,
                tipo_usuario: data.tipo_usuario || null,
                playerid: playerId,
              },
            },
          );
        } catch (error) {
          console.error('Error sending player ID:', error);
          Sentry.captureException(error, {
            tags: {
              location: 'AuthScreen.submitHandler.playerIdSync',
            },
            extra: {
              email: data.email,
              userId: data.user_id,
              tipoUsuario: data.tipo_usuario,
              playerId,
            },
          });
        }
      }
      navigation.dispatch(StackActions.replace('PendingBills'));
    } catch (error) {
      setLoading(false);
      console.error('Login error:', error);

      Sentry.captureException(error, {
        tags: {
          location: 'AuthScreen.submitHandler',
          addAccountMode,
        },
        extra: {
          username,
          hasPassword: !!password,
          hasEnterprise: !!enterprise,
          hasPlayerId: !!playerId,
        },
      });

      if (error instanceof Error) {
        if (error.message === 'Request failed with status code 500') {
          Alert.alert(
            'Ops, algo deu errado',
            'Estamos com problemas técnicos, tente novamente mais tarde.',
          );
        } else if (error.message === 'Usuário e/ou senha invalido(s).') {
          Alert.alert('Ops, algo deu errado', 'Usuário e/ou senha inválidos.');
        } else if (error.message === 'Preencha o usuário e a senha.') {
          Alert.alert('Ops, algo deu errado', 'Preencha o usuário e a senha.');
        } else if (error.message === 'Conta já adicionada') {
          Alert.alert('Ops, algo deu errado', 'Conta já adicionada.');
        } else if (error.message === 'Não possui nenhum veículo') {
          Alert.alert('Ops, algo deu errado', 'Não possui nenhum veículo.');
        } else if (error.message === 'Enterprise URL not found') {
          Alert.alert(
            'Ops, algo deu errado',
            'Configuração da empresa não encontrada.',
          );
        } else {
          Alert.alert(
            'Ops, algo deu errado',
            'Estamos com problemas técnicos, tente novamente mais tarde.',
          );
        }
      }
    }
  };

  const d = Dimensions.get('window');
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
      {addAccountMode && (
        <TouchableOpacity
          style={[styles.buttonClose, { top: top + 10 }]}
          onPress={() => navigation.goBack()}
        >
          <Icons name="close" size={20} color="#fff" />
        </TouchableOpacity>
      )}

      <KeyboardAvoidingView style={styles.container} behavior="padding" enabled>
        <Text style={styles.login}>Login</Text>
        <View style={styles.rectangle1} />

        <TextInput
          label="Nome da Conta (Opcional)"
          theme={{
            colors: {
              placeholder: 'white',
              text: 'white',
              primary: 'white',
            },
          }}
          style={{
            backgroundColor: 'transparent',
            borderBottomColor: 'white',
            borderBottomWidth: 1,
          }}
          autoCapitalize="none"
          returnKeyType="next"
          value={accountName}
          onChangeText={setAccountName}
          mode="flat"
        />

        <TextInput
          label="Login"
          autoComplete="username"
          theme={{
            colors: {
              placeholder: 'white',
              text: 'white',
              primary: 'white',
            },
          }}
          style={{
            backgroundColor: 'transparent',
            borderBottomColor: 'white',
            borderBottomWidth: 1,
          }}
          autoCapitalize="none"
          returnKeyType="next"
          value={username}
          onChangeText={setUsername}
          mode="flat"
        />

        <TextInput
          label="Senha"
          autoComplete="password"
          theme={{
            colors: {
              placeholder: 'white',
              text: 'white',
              primary: 'white',
            },
          }}
          style={{
            backgroundColor: 'transparent',
            borderBottomColor: 'white',
            borderBottomWidth: 1,
          }}
          autoCapitalize="none"
          secureTextEntry
          returnKeyType="send"
          value={password}
          onChangeText={setPassword}
          mode="flat"
        />

        <TouchableOpacity
          style={styles.forgotPasswordButton}
          activeOpacity={1}
          onPress={() => navigation.navigate('ForgotPassword')}
        >
          <Text style={styles.buttonText}>Esqueci a Senha</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.button}
          activeOpacity={0.8}
          onPress={submitHandler}
        >
          {loading ? (
            <ActivityIndicator color="#ffffff" />
          ) : (
            <Text style={styles.buttonText}>ENTRAR</Text>
          )}
        </TouchableOpacity>

        {!!imageUrl && (
          <Image
            source={{ uri: imageUrl }}
            style={{
              width: 218,
              height: 55,
              alignSelf: 'center',
              marginTop: '10%',
            }}
          />
        )}
      </KeyboardAvoidingView>
    </ImageBackground>
  );
};

export default AuthScreen;

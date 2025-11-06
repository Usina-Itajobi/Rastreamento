import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

// import WelcomeScreen from '../Screens/Welcome';
import ForgotPasswordScreen from '../Screens/ForgotPassword';
import PendingBillsScreen from '../Screens/PendingBills';
import ProfilesChooseScreen from '../Screens/ProfilesChoose';
import ContractScreen from '../Screens/Contract';
import AuthStack from './AuthStack';
import AppStack from './AppStack';
// import AppStack from './AppStack';

export type AuthScreenParams = {
  addAccountMode?: boolean;
};

export type RootStackParamList = {
  // Welcome: undefined;
  ForgotPassword: undefined;
  PendingBills: undefined;
  AuthStack: undefined;
  AppStack: undefined;
  ProfilesChoose: undefined;
  AuthScreen: AuthScreenParams | undefined;
};

declare global {
  // eslint-disable-next-line no-unused-vars
  namespace ReactNavigation {
    // eslint-disable-next-line no-unused-vars
    interface RootParamList extends RootStackParamList {}
  }
}

const Stack = createNativeStackNavigator<RootStackParamList>();

const AppNavigator = () => {
  return (
    <NavigationContainer>
      <Stack.Navigator
        initialRouteName="AuthStack"
        screenOptions={{
          headerShown: false,
        }}
      >
        {/* <Stack.Screen name="Welcome" component={WelcomeScreen} /> */}
        <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
        <Stack.Screen name="PendingBills" component={PendingBillsScreen} />
        <Stack.Screen name="ProfilesChoose" component={ProfilesChooseScreen} />
        <Stack.Screen name="AuthStack" component={AuthStack} />
        <Stack.Screen name="AppStack" component={AppStack} />
      </Stack.Navigator>

      {/* Sempre renderiza ContractScreen */}
      <ContractScreen />
    </NavigationContainer>
  );
};

export default AppNavigator;

import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createDrawerNavigator } from '@react-navigation/drawer';
import VehiclesScreen from '../Screens/Vehicles';
import VehicleScreen from '../Screens/Vehicle';
import ReportsScreen from '../Screens/Reports';
import AlertsScreen from '../Screens/Alerts';
import CommandsScreen from '../Screens/Commands';
import LogoutScreen from '../Screens/Logout';
import Map from '../Screens/Map';
import ReportPosition from '../Screens/Reports/ReportPosition';
import FilterReport from '../Screens/Reports/FilterReport';
import FilterSpeed from '../Screens/Reports/FilterSpeed';
import FilterKmAccumulated from '../Screens/Reports/FilterKmAccumulated';
import ReportMenu from '../Screens/Reports/ReportMenu';
import OldReports from '../Screens/Reports/OldReports';
import ConfigurationsScreen from '../Screens/Configurations';
import BillsScreen from '../Screens/Bills';
import ChangePasswordScreen from '../Screens/ChangePassword';
import CustomDrawer from '../Components/CustomDrawer';
import { useAuth } from '../context/authContext';

const ReportsStack = createNativeStackNavigator();
const ReportsStackScreen = () => (
  <ReportsStack.Navigator
    screenOptions={{ headerShown: false }}
    initialRouteName="ReportMenu"
  >
    <ReportsStack.Screen name="ReportsScreen" component={ReportsScreen} />
    <ReportsStack.Screen name="ReportPosition" component={ReportPosition} />
    <ReportsStack.Screen name="FilterReport" component={FilterReport} />
    <ReportsStack.Screen name="ReportMenu" component={ReportMenu} />
    <ReportsStack.Screen name="OldReports" component={OldReports} />
    <ReportsStack.Screen name="FilterSpeed" component={FilterSpeed} />
    <ReportsStack.Screen name="FilterKmAccumulated" component={FilterKmAccumulated} />
  </ReportsStack.Navigator>
);

const ConfigurationsStack = createNativeStackNavigator();
const ConfigurationsStackScreen = () => (
  <ConfigurationsStack.Navigator screenOptions={{ headerShown: false }}>
    <ConfigurationsStack.Screen
      name="Configurations"
      component={ConfigurationsScreen}
    />
    <ConfigurationsStack.Screen
      name="ChangePassword"
      component={ChangePasswordScreen}
    />
  </ConfigurationsStack.Navigator>
);

const VehiclesStack = createNativeStackNavigator();
const VehiclesStackScreen = () => (
  <VehiclesStack.Navigator
    screenOptions={{ headerShown: false }}
    initialRouteName="Vehicles"
  >
    <VehiclesStack.Screen name="Vehicles" component={VehiclesScreen} />
    <VehiclesStack.Screen name="Vehicle" component={VehicleScreen} />
  </VehiclesStack.Navigator>
);

const Drawer = createDrawerNavigator();

// export default createDrawerNavigator(
//     {
//       'Mapa Geral': Map,
//       Vehicles: VehiclesScreen,
//       Vehicle: VehicleScreen,
//       Relatórios: Reports,
//       Alerts: AlertsScreen,
//       Commands: CommandsScreen,
//       Bills: BillsScreen,
//       Configurações: Configurations,
//       Logout: LogoutScreen,
//     },
//     {
//       initialRouteName: 'Mapa Geral',
//       contentComponent: DrawerContent,
//     },
//   );

export default function AppStack() {
  const { selectedAccount } = useAuth();

  return (
    <Drawer.Navigator
      initialRouteName={"Mapa Geral"}
      screenOptions={{
        headerShown: false,
      }}
      drawerContent={(props) => <CustomDrawer {...props} />}
    >
      <Drawer.Screen name="Mapa Geral" component={Map} />
      <Drawer.Screen
        name="Veículos"
        component={VehiclesStackScreen}
        options={{
          drawerLabel: 'Meus veículos',
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Relatórios"
        component={ReportsStackScreen}
        options={{
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Alerts"
        component={AlertsScreen}
        options={{
          drawerLabel: 'Alertas',
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Commands"
        component={CommandsScreen}
        options={{
          drawerLabel: 'Enviar comandos',
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Bills"
        component={BillsScreen}
        options={{
          drawerLabel: 'Minhas Faturas',
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Configurações"
        component={ConfigurationsStackScreen}
        options={{
          unmountOnBlur: true,
        }}
      />
      <Drawer.Screen
        name="Logout"
        component={LogoutScreen}
        options={{
          drawerLabel: 'Sair',
          unmountOnBlur: true,
        }}
      />
    </Drawer.Navigator>
  );
}

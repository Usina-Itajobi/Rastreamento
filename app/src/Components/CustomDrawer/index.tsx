import React, { useEffect, useState } from 'react';
import { Image } from 'react-native';
import {
  DrawerContentComponentProps,
  DrawerContentScrollView,
  DrawerItem,
} from '@react-navigation/drawer';
import { useSelector } from 'react-redux';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Icons from 'react-native-vector-icons/MaterialCommunityIcons';

import {
  Container,
  TextEnterprise,
  ContainerEnterprise,
  ContainerTextEnterprise,
} from './styles';
import ProfileDrawer from '../ProfileDrawer';

export default function CustomDrawer(props: DrawerContentComponentProps) {
  const enterprise = useSelector((state: any) => state.enterprise);
  const [contacts, setContacts] = useState({
    id_cliente: '',
    logo: '',
    nome: '',
    url_api: '',
    cel: '',
    fone: '',
  });

  const getData = async () => {
    const enterpriseGet = await AsyncStorage.getItem('@grupoitajobi:enterprise');
    const data = JSON.parse(enterpriseGet);
    setContacts({
      id_cliente: data.clientId,
      logo: data.brand,
      nome: data.name,
      url_api: data.baseUrl,
      fone: data.fone,
      cel: data.cel,
    });
  };

  useEffect(() => {
    getData();
    // getContacts();
  }, []);

  const getLabel = (route: any) => {
    switch (route.name) {
      case 'Veículos':
        return 'Meus veículos';
      case 'Relatórios':
        return 'Relatórios';
      case 'Alerts':
        return 'Alertas';
      case 'Commands':
        return 'Enviar comandos';
      case 'Bills':
        return 'Minhas Faturas';
      case 'Logout':
        return 'Sair';
      default:
        return route.name;
    }
  };

  return (
    <DrawerContentScrollView {...props}>
      <Container>
        {enterprise && !!contacts?.logo && (
          <>
            {/* <Header>
         <ImageBackground
           source={require("../../assets/images/bg_profile.jpg")}
           style={{
             width: "100%",
             height: 350,
             marginTop: -50,
           }}
         > */}
            {/* <Image
              source={{ uri: contacts.logo }}
              style={{
                width: 218,
                height: 55,
                marginBottom: 12,
                marginTop: 60,
                alignSelf: 'center',
              }}
            /> */}
            {/* </ImageBackground> */}
            {/* </Header> */}
          </>
        )}

        {/* Profile */}
        <ProfileDrawer />

        {/* <DrawerItemList {...props} /> */}
        {props.state.routes.map((route, index) => {
          return (
            <DrawerItem
              key={route.key}
              label={getLabel(route)}
              onPress={() => {
                switch (route.name) {
                  case 'Veículos':
                    return props.navigation.navigate(route.name, {
                      screen: 'Vehicles',
                    });
                  case 'Relatórios':
                    return props.navigation.navigate(route.name, {
                      screen: 'ReportMenu',
                    });
                  case 'Configurações':
                    return props.navigation.navigate(route.name, {
                      screen: 'Configurations',
                    });
                  default:
                    return props.navigation.navigate(route.name);
                }
              }}
              focused={props.state.index === index}
            />
          );
        })}

        {enterprise && (!!contacts?.cel || !!contacts?.fone) && (
          <ContainerEnterprise>
            {!!contacts?.cel && (
              <ContainerTextEnterprise>
                <Icons name="cellphone" color="#000" style={{ fontSize: 16 }} />
                <TextEnterprise>{contacts.cel}</TextEnterprise>
              </ContainerTextEnterprise>
            )}
            {!!contacts?.fone && (
              <ContainerTextEnterprise>
                <Icons name="phone" color="#000" style={{ fontSize: 16 }} />
                <TextEnterprise>{contacts.fone}</TextEnterprise>
              </ContainerTextEnterprise>
            )}
          </ContainerEnterprise>
        )}
      </Container>
    </DrawerContentScrollView>
  );
}

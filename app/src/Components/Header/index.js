import React from 'react';
import { View, Text, TouchableOpacity, Image, Platform } from 'react-native';

import { useSafeAreaInsets } from 'react-native-safe-area-context';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import { useNavigation } from '@react-navigation/native';
import styles from './styles';

const Headers = (props) => {
  const { top } = useSafeAreaInsets();
  const navigation = useNavigation();

  return (
    <View>
      <View
        style={{
          backgroundColor: 'white',
          paddingTop: Platform.OS === 'ios' ? top : 0,
          flexDirection: 'row',
          justifyContent: 'space-between',
          alignItems: 'center',
          paddingBottom: 12,
          paddingRight: 12,
        }}
      >
        <View>
          <TouchableOpacity
            onPress={() =>
              props?.navigation?.toggleDrawer && !props.showBackButton
                ? props?.navigation?.toggleDrawer()
                : props?.navigation?.goBack
                ? props?.navigation?.goBack()
                : navigation?.goBack()
            }
            activeOpacity={0.7}
            hitSlop={{ top: 3, left: 8, bottom: 3, right: 8 }}
            style={{ marginTop: Platform.OS === 'ios' ? null : 30 }}
          >
            {!props?.navigation?.toggleDrawer || props?.showBackButton ? (
              <View style={{ marginLeft: 10 }}>
                <MaterialIcons name="arrow-back" size={31} color="black" />
              </View>
            ) : (
              <Image
                source={require('../../assets/images/menu.png')}
                style={{
                  // marginBottom: -4,
                  marginLeft: Platform.OS === 'ios' ? 20 : 10,
                  // marginTop: 20,
                  height: 20,
                  width: 30,
                }}
              />
            )}
          </TouchableOpacity>
        </View>
        {/* <Body> */}
        <Text style={styles.title}>{props.title}</Text>
        {/* </Body> */}
        <View>
          {props.name === 'relat' ? (
            <TouchableOpacity
              onPress={() => props.navigation.navigate('Alerts')}
              activeOpacity={0.7}
              hitSlop={{ top: 3, bottom: 3, right: 8 }}
              style={{ marginTop: Platform.OS === 'ios' ? null : 30 }}
            >
              <Image
                source={require('../../assets/images/bell2.png')}
                style={{
                  // marginLeft: Platform.OS === "ios" ? 200 : 150,
                  // marginTop: 50,
                  height: 30,
                  width: 30,
                }}
              />
            </TouchableOpacity>
          ) : (
            <TouchableOpacity
              onPress={() => props.navigation.navigate('Alerts')}
              activeOpacity={0.7}
              hitSlop={{ top: 3, bottom: 3, right: 8 }}
              style={{ marginTop: Platform.OS === 'ios' ? null : 30 }}
            >
              <Image
                source={require('../../assets/images/bell2.png')}
                style={{
                  // marginLeft: Platform.OS === "ios" ? 250 : 200,
                  // marginTop: 50,
                  height: 30,
                  width: 30,
                }}
              />
            </TouchableOpacity>
          )}
        </View>
      </View>
    </View>
  );
};

export default Headers;

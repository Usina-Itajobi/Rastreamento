import React, { Component } from 'react';

import { View, Text, ImageBackground, Image } from 'react-native';

import styles from './styles';

// Codigo 16001

class WelcomeScreen extends Component {
  state = {
    splash: true,
  };

  async componentDidMount() {
    setTimeout(() => {
      this.setState({ splash: false });
      this.props.navigation.navigate('AuthStack');
    }, 5000);
  }

  render() {
    const { splash } = this.state;
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
    return null;
  }
}

export default WelcomeScreen;

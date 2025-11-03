import React, { Component } from 'react';
import { View } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

class LogoutScreen extends Component {
  async componentDidMount() {
    AsyncStorage.clear().then(() => {
      this.props.navigation.navigate('Welcome');
    });
  }

  render() {
    return <View />;
  }
}

export default LogoutScreen;

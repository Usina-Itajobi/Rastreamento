import { Dimensions } from 'react-native';
import styled from 'styled-components/native';

const screenWidth = Dimensions.get('window').width;

export const Container = styled.View`
  flex: 1;
  width: ${screenWidth * 0.7}px;
  background-color: #fff;
  padding-top: 29px;
`;

export const Title = styled.Text`
  font-weight: bold;
  margin-bottom: 6px;
  color: #111111;
`;

export const Desc = styled.Text`
  font-weight: normal;
  margin-bottom: 0;
  color: #111111;
`;

export const ButtonCloseVehicleInfo = styled.TouchableOpacity`
  position: absolute;
  right: 2px;
  top: 2px;
  z-index: 9999;
  width: 26px;
  height: 26px;
  border-radius: 18px;
`;

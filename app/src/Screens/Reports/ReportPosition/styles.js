import { Platform, FlatList } from 'react-native';
import { getStatusBarHeight } from 'react-native-iphone-x-helper';

import styled, { css } from 'styled-components/native';

export const Container = styled.View`
  flex: 1;
  align-items: center;
  justify-content: center;
`;

export const LoadingMap = styled.View`
  position: absolute;
  flex: 1;
  z-index: 999;
  background: #fff;
  height: 100%;
  width: 100%;
  elevation: 4;
  align-items: center;
  justify-content: center;
`;

export const Header = styled.View`
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  z-index: 2;
  padding-top: ${Platform.OS === 'ios' ? getStatusBarHeight() + 24 : 40}px;
  background: #004e70;
`;

export const WrapperInfoCar = styled.View`
  flex-direction: row;
  padding: 0 12px 12px;
  align-items: center;
`;

export const CarImage = styled.Image`
  width: 55px;
  height: 35px;
  margin-left: 24px;
`;

export const CarName = styled.Text`
  font-weight: bold;
  font-size: 16px;
  color: #fff;
  margin-left: 12px;
`;

export const ButtonBack = styled.TouchableOpacity``;

export const WrapperButtons = styled.View`
  flex-direction: row;
  justify-content: space-between;
`;
export const ButtonTab = styled.TouchableOpacity`
  background: #fff;
  flex: 1;
  align-items: center;
  justify-content: center;
  background: #004e70;
  padding: 12px;

  ${({ active }) =>
    active &&
    css`
      border-bottom-width: 4px;
      border-bottom-color: #fff;
    `}
`;

export const LabelTab = styled.Text`
  color: #999;
  ${({ active }) =>
    active &&
    css`
      font-weight: bold;
      color: #fff;
    `}
`;

export const WrapperSlider = styled.View`
  position: absolute;
  top: ${Platform.OS === 'ios' ? 215 : 195}px;
  justify-content: space-around;
  right: 21px;
  align-self: center;
  z-index: 10;
  background: rgba(255, 255, 255, 0.5);
  padding: 10px;
  align-items: center;
  flex-direction: row;
`;

export const WrapperOptionSpeed = styled.View``;

export const WrapperOptionSpeeds = styled.View`
  align-items: center;
`;

export const WrapperLabelOptionSpeed = styled.View`
  justify-content: space-between;
  height: 100%;
`;

export const OptionLabel = styled.Text`
  font-size: 12px;
  color: ${({ selected }) => (selected ? '#12ED28' : '#333333')};
  font-weight: ${({ selected }) => (selected ? 'bold' : 'normal')};
  margin-right: 6px;
  margin-bottom: 2px;
`;

export const OptionSpeed = styled.TouchableOpacity`
  width: 20px;
  height: 20px;
  border-radius: 15px;
  background: #fff;
  border-width: ${({ selected }) => (selected ? 5 : 0)}px;
  border-color: ${({ selected }) => (selected ? '#12ED28' : 'transparent')};
  elevation: 2;
`;

export const LineOptionSpeed = styled.View`
  width: 2px;
  height: 12px;
  background: #111;
`;

export const ControlsNavigation = styled.View`
  position: absolute;
  bottom: ${Platform.OS === 'ios' ? 90 : 70}px;
  flex-direction: row;
  width: 90%;
  justify-content: space-around;
  align-self: center;
  z-index: 10;
`;

export const ButtonControl = styled.TouchableOpacity`
  width: 50px;
  height: 50px;
  border-radius: 25px;
  background-color: #fff;
  elevation: 2;
  align-items: center;
  justify-content: center;
`;

export const Legend = styled.View`
  padding: 8px 8px 0;
  position: absolute;
  bottom: ${Platform.OS === 'ios' ? 40 : 20}px;
  z-index: 2;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 6px;
  flex-direction: row;
  width: 90%;
  justify-content: space-between;
  align-self: center;
  elevation: 2;
  overflow: hidden;
`;

export const ButtonMapRight = styled.TouchableOpacity`
  width: 34px;
  height: 34px;
  right: 0;
  bottom: 0;
  left: 0;
  flex-direction: row;
  background-color: 'rgba(255,255,255,0.6)';
  margin-left: 15px;
  position: absolute;
  top: ${getStatusBarHeight() + 150}px;
  align-self: center;
  color: #000;
  border-radius: 6px;
  elevation: 2;
  justify-content: center;
  align-items: center;
  font-size: 24px;
  z-index: 2;
`;

export const WrapperLegend = styled.View`
  flex-direction: row;
  align-items: center;
  margin-bottom: 8px;
`;

export const LegendIcon = styled.Image`
  width: 12px;
  height: 12px;

  ${({ lg }) =>
    lg &&
    css`
      width: 24px;
    `}
`;

export const LegendLabel = styled.Text`
  margin-left: 8px;
  color: #333;
`;

export const NavigationSimulatorButton = styled.TouchableOpacity`
  position: absolute;
  top: 190px;
  right: 0;
  bottom: 0;
  left: 0;
  background-color: #fff;
  color: #000;
  width: 70px;
  height: 30px;
  border-radius: 6px;
  elevation: 2;
  justify-content: center;
  align-items: center;
  font-size: 24px;
  margin-left: 15px;
  z-index: 2;
`;

export const PositionMarkerImageFlags = styled.Image`
  width: 56px;
  height: 36px;
`;

export const PositionMarkerImage = styled.Image`
  width: 16px;
  height: 16px;
`;

export const Content = styled.View``;

export const ZoomsButtons = styled.View``;

export const ListPositions = styled(FlatList).attrs({
  contentContainerStyle: {
    paddingBottom: 40,
  },
})``;

export const PositionItem = styled.View`
  background: #ddd;
  margin-bottom: 12px;
  padding: 12px;
  border-right-width: 16px;

  ${({ ign_color }) =>
    ign_color &&
    css`
      border-right-color: #${ign_color};
    `}
`;

export const PositionHeader = styled.View`
  flex-direction: row;
  justify-content: space-between;
  margin-bottom: 8px;
`;

export const WrapperAddress = styled.View`
  flex-direction: row;
`;

export const PositionDate = styled.Text`
  color: #333;
`;

export const PositionTitle = styled.Text`
  color: #333;
`;

export const PositionDesc = styled.Text`
  max-width: 85%;
  color: #333;
`;

export const PositionFooter = styled.View`
  flex-direction: row;
  justify-content: space-between;
  margin-top: 8px;
`;

export const WrapperItemPositionFooter = styled.View``;

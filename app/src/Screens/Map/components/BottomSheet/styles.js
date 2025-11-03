import styled from 'styled-components/native';
import { BottomSheetFlatList } from '@gorhom/bottom-sheet';

export const Container = styled.View`
  flex: 1;
`;

export const Title = styled.Text`
  font-weight: bold;
  font-size: 16px;
  margin-left: 16px;
  color: #999;
`;

export const WrapperTextInput = styled.View`
  flex-direction: row;
  align-items: center;
  padding: 0 16px;
  padding-bottom: 18px;
`;

export const TextInput = styled.TextInput`
  border-bottom-width: 1px;
  border-bottom-color: #999;
  flex: 1;
  height: 40px;
  margin-left: 12px;
  font-size: 16px;
  color: #111111;
`;

export const ListVehicles = styled(BottomSheetFlatList).attrs({
  contentContainerStyle: {
    paddingBottom: 40,
  },
})`
  margin-top: 16px;
`;

export const Vehicle = styled.TouchableOpacity`
  flex-direction: row;
  align-items: center;
  margin-bottom: 16px;
  padding: 0 16px;
`;

export const WrapperVehicleImage = styled.View``;

export const VehicleImage = styled.Image`
  width: 45px;
  height: 25px;
  margin-right: 12px;
`;

export const VehicleImageLabel = styled.View`
  width: 45px;
  height: 4px;

  background-color: ${({ color }) => `#${color}` || '#004e70'};
`;

export const WrapperVehiclesDesc = styled.View``;

export const VehiclesTitle = styled.Text`
  font-weight: bold;
  font-size: 16px;
  color: #111111;
`;

export const VehiclesDate = styled.Text`
  font-size: 12px;
  color: #999;
`;

export const VehiclesStatus = styled.Text`
  font-size: 16px;
  color: #999;
  margin-left: auto;
  align-self: flex-start;
`;

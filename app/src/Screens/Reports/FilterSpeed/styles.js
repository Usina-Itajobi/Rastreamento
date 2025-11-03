import { FlatList } from 'react-native';
import styled from 'styled-components/native';

export const Container = styled.View`
  flex: 1;
`;

export const WrapperFilterDateHour = styled.View`
  padding: 0 12px;
  margin-top: 16px;
  margin-bottom: 16px;
`;

export const WrapperFilterRow = styled.View`
  flex-direction: row;
  justify-content: space-between;
  width: 100%;
`;

export const WrapperButtonChooseDate = styled.View`
  width: 45%;
`;

export const ButtonChooseDateLabel = styled.Text`
  color: #004e70;
  font-weight: bold;
`;

export const ButtonChooseDate = styled.TouchableOpacity`
  flex-direction: row;
  justify-content: space-between;
  padding: 12px;
  elevation: 8;
  margin: 6px;
  align-items: center;
  border-radius: 6px;
  background: #eee;
`;

export const ButtonChooseDateText = styled.Text`
  color: #111;
`;

export const LabelRequired = styled.Text`
  margin-left: 16px;
  font-size: 16px;
  margin-bottom: 16px;
  font-weight: bold;
  color: #004e70;
`;

export const VehicleSelected = styled.View`
  flex-direction: row;
  margin: 12px auto 24px;
  elevation: 8;
  background: #fff;
  border-radius: 6px;
  padding: 8px;
  width: 50%;
  align-items: center;
`;

export const VehicleSelectedImage = styled.Image`
  width: 55px;
  height: 35px;
  margin-right: 6px;
`;

export const VehicleSelectedName = styled.Text.attrs({
  ellipsizeMode: 'tail',
  numberOfLines: 1,
})`
  font-weight: bold;
  max-width: 50%;
  overflow: hidden;
  color: #004e70;
`;

export const VehicleSelectedButtonDelete = styled.TouchableOpacity`
  margin-left: auto;
`;

export const ListVehicles = styled(FlatList).attrs({
  contentContainerStyle: {
    paddingBottom: 40,
  },
})``;

export const Vehicle = styled.TouchableOpacity`
  flex-direction: row;
  align-items: center;
  padding: 18px 16px;
  background: #fff;
  border-bottom-width: 1.5px;
  border-bottom-color: #004e70;
`;

export const WrapperVehiclesDesc = styled.View``;

export const VehiclesTitle = styled.Text`
  font-weight: bold;
  font-size: 16px;
  color: #004e70;
`;

export const VehiclesDate = styled.Text`
  font-size: 12px;
  color: #004e70;
`;

export const VehiclesStatus = styled.Text`
  font-size: 16px;
  color: #004e70;
  margin-left: auto;
  align-self: flex-start;
`;

export const ButtonGenerate = styled.TouchableOpacity`
  background-color: #004e70;
  padding: 12px 0;
  width: 80%;
  align-items: center;
  align-self: center;
  border-radius: 6px;
`;

export const ButtonGenerateText = styled.Text`
  font-size: 16px;
  color: #fff;
`;
export const WrapperInputContent = styled.View`
  width: 100%;
`;
export const WrapperInput = styled.TouchableOpacity`
  flex-direction: row;
  justify-content: space-between;
  padding: 12px;
  elevation: 8;
  margin: 6px;
  align-items: center;
  border-radius: 6px;
  background: #eee;
`;

export const InputSpeedMin = styled.TextInput`
  width: 100%;
  color: #000;
  font-size: 14px;
  padding: 0px;
`;

export const Table = styled.View`
  margin-top: 16px;
`;

export const TableHeader = styled.View`
  flex-direction: row;
  background-color: #004e70;
  padding: 10px;
`;

export const TableRow = styled.View`
  flex-direction: row;
  padding: 10px;
  border-bottom-width: 1px;
  border-color: #ccc;
`;

export const TableCellHeader = styled.Text`
  flex: 1;
  font-weight: bold;
  color: #fff;
  font-size: 12px;
  padding: 0 2px;
`;

export const TableCell = styled.Text`
  flex: 1;
  font-size: 12px;
  color: #111;
  padding: 0 2px;
`;
export const MapButton = styled.TouchableOpacity`
  align-items: center;
  justify-content: center;
`;
import styled from 'styled-components/native';
import { BottomSheetFlatList } from '@gorhom/bottom-sheet';

export const Container = styled.ImageBackground`
  position: absolute;
  flex: 1;
  background-color: #004E70;
`;

export const Title = styled.Text`
  font-weight: bold;
  font-size: 16px;
  margin-left: 16px;
  color: #FFF;
`;

export const ButtonContainer = styled.TouchableOpacity`
  background-color: #F69C33;
  align-self: center;
  padding: 10px;
  border-radius: 8px;
  flex-direction: row;
  align-content: center;
`;

export const ButtonText = styled.Text`
  color: #FFF;
  font-size: 18px;
  margin-right: 6px;
  font-weight: 600;
`;
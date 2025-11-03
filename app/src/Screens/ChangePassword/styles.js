import { Platform } from 'react-native';
import styled from 'styled-components/native';

export const BackContainer = styled.KeyboardAvoidingView`
  padding: 32px;
`;

export const TextInputContainer = styled.View`
  flex-direction: row;
  align-items: center;
  border-bottom-color: #ffffff;
  border-bottom-width: 1;
`;

export const InputErrorMessage = styled.Text`
  color: #ff0000;
  font-size: ${Platform.OS === 'ios' ? 14 : 16}px;
  font-weight: ${Platform.OS === 'ios' ? '600' : '400'};
`;

export const SendButton = styled.TouchableOpacity`
  width: 182.5;
  align-self: center;
  height: 48;
  border-radius: 4;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: ${Platform.OS === 'ios' ? 60 : 30}px;
`;

export const SendButtonText = styled.Text`
  color: #ffffff;
  font-size: ${Platform.OS === 'ios' ? 14 : 16}px;
  font-weight: ${Platform.OS === 'ios' ? '600' : '400'};
`;

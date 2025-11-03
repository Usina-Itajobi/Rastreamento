import styled from 'styled-components/native';

export const ModalContainer = styled.View`
  flex: 1;
  background-color: rgba(0, 0, 0, 0.24);
  justify-content: center;
`;

export const ModalContent = styled.View`
  padding: 24px;
  margin: 0px 20px;
  background-color: #fff;
  border-radius: 15px;
`;

export const TitleModal = styled.Text`
  font-size: 18px;
  font-weight: bold;
  color: #111111;
`;

export const WrapperRadios = styled.TouchableOpacity`
  flex-direction: row;
  margin-top: 12px;
  align-items: center;
`;

export const RadiosLabel = styled.Text`
  font-size: 16px;
  color: #111111;
`;

export const ButtonSave = styled.TouchableOpacity`
  padding: 12px 0;
  background: #004e70;
  align-items: center;
  justify-content: center;
  margin-top: 24px;
  border-radius: 6px;
`;

export const ButtonSaveText = styled.Text`
  font-weight: bold;
  color: #fff;
  font-size: 16px;
`;

import styled from 'styled-components/native';

export const ModalContainer = styled.View`
  flex: 1;
  background-color: rgba(0, 0, 0, 0.24);
  justify-content: center;
  align-items: center;
  padding: 0 16px;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
`;

export const Content = styled.View`
  width: 90%;
  background: #fff;
  margin: auto;
  border-radius: 12px;
  padding: 0 16px 16px;
`;

export const Title = styled.Text`
  font-size: 18px;
  padding: 16px;
  margin: 0 auto;
  font-weight: bold;
  color: #004e70;
`;

export const WrapperChooseDate = styled.View`
  flex-direction: row;
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
  flex: 1;
`;

export const ButtonChooseDateText = styled.Text`
  color: #111111;
`;

export const ButtonGenerate = styled.TouchableOpacity`
  background-color: #004e70;
  padding: 12px 0;
  width: 80%;
  align-items: center;
  align-self: center;
  border-radius: 6px;
  margin-top: 24px;
`;

export const ButtonGenerateText = styled.Text`
  font-size: 16px;
  color: #fff;
`;

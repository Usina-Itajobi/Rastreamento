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
  font-weight: 500;
  text-align: center;
  color: #004e70;
`;

export const WraperOptions = styled.View`
  flex-direction: row;
  justify-content: space-around;
  padding: 16px;
`;

export const Option = styled.TouchableOpacity.attrs({
  activeOpacity: 0.7,
})``;

export const ImageOption = styled.Image`
  width: 64px;
  height: 64px;
  border-radius: 8px;
`;

export const CloseButton = styled.TouchableOpacity`
  margin: 0 auto;
  margin-top: 16px;
`;

export const CloseText = styled.Text`
  font-size: 18px;
  color: #004e70;
`;

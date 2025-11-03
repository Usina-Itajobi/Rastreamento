import styled from 'styled-components/native';

export const Container = styled.View`
  flex: 1;
  background: #fff;
`;

export const Option = styled.TouchableOpacity`
  border-bottom-width: 1px;
  border-bottom-color: #999;
  margin: 0 16px;
  padding-bottom: 12px;
  margin-top: 24px;
`;

export const OptionTitle = styled.Text`
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 6px;
  color: #004e70;
`;

export const OptionSubTitle = styled.Text`
  font-size: 12px;
  color: #999999;
`;

export const Title = styled.Text`
  color: #111111;
`;

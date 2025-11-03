import styled from 'styled-components/native';

export const Container = styled.View`
  flex: 1;
  background-color: #fff;
`;

export const Content = styled.View`
  padding: 24px 16px;
`;

export const Title = styled.Text`
  margin-top: 24px;
  font-size: 16px;
  margin-bottom: 12px;
`;

export const TitleBold = styled.Text`
  font-weight: bold;
  color: #111111;
`;

export const New = styled.Text`
  font-size: 18px;
  font-weight: bold;
  position: absolute;
  right: 8px;
  top: 4px;
  color: red;
`;

export const ButtonReport = styled.TouchableOpacity`
  padding: 12px 0;
  align-items: center;
  justify-content: center;
  background: #004e70;
  border-radius: 6px;
  elevation: 2;
  margin-top: 12px;
  position: relative;
`;

export const ButtonReportText = styled.Text`
  font-size: 16px;
  color: #fff;
`;

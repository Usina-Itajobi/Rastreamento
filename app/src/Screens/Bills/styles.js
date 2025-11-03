import styled from 'styled-components/native';

export const BackContainer = styled.View`
  background-color: #F69C33;
  align-self: center;
  position: absolute;
  top: 95px;
  border-radius: 15px;
`;

export const MonthContainer = styled.View`
  flex-direction: row;
  align-items: center;
  justify-content: space-around
`;

export const MonthText = styled.Text`
  color: #FFF;
  font-size: 20px;
  font-weight: 600;
`;

export const MonthTextUnderline = styled.Text`
  width: 100%;
  height: 4px;
  background-color: #F69C33;
  margin-bottom: 5px;
`;

export const BillDescriptionMainContainer = styled.View`
  padding: 20px;
  padding-top: 10px;
`;

export const BillDescriptionContainer = styled.View`
  align-self: flex-start;
  border-radius: 8px;
  margin-bottom: 15px;
`;

export const BillDescriptionTitle = styled.Text`
  color: #F69C33;
  font-size: 16px;
  font-weight: 600;
`;

export const DownloadButtonContainer = styled.TouchableOpacity`
  background-color: #F69C33;
  align-self: center;
  padding: 10px;
  border-radius: 8px;
  flex-direction: row;
  align-content: center;
  margin-top: 30px;
`;

export const DownloadButtonText = styled.Text`
  color: #FFF;
  font-size: 18px;
  margin-right: 6px;
  font-weight: 600;
`;

export const LoaderContainer = styled.View`
  justify-content: center;
  align-items: center;
`;

export const UpdateText = styled.Text`
  font-style: normal;
  font-weight: bold;
  font-size: 16px;
  color: #FFF;
`;
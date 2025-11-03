import styled from 'styled-components/native';

export const Container = styled.TouchableOpacity`
  flex-direction: row;
  align-items: center;
  padding: 12px;
  border-bottom-width: 1px;
  border-bottom-color: #f0f0f0;
  border-top-width: 1px;
  border-top-color: #f0f0f0;
  margin-top: 12px;
  margin-bottom: 20px;
`;

export const Avatar = styled.View`
  width: 40px;
  height: 40px;
  border-radius: 20px;
  background-color: #004e70;
  justify-content: center;
  align-items: center;
  margin-right: 10px;
`;

export const AvatarText = styled.Text`
  color: white;
  font-weight: bold;
  color: white;
`;

export const TextContainer = styled.View`
  flex-direction: column;
  max-width: 70%;
`;

export const Username = styled.Text`
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 2px;
  color: black;
`;

export const UserEmail = styled.Text`
  font-size: 14px;
  color: gray;
`;

export const ChevronDown = styled.View`
  margin-left: auto;
`;

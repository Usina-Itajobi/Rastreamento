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
  height: auto;
  max-height: 80%;
  border-radius: 12px;
`;

export const Container = styled.View`
  padding: 12px;
  background-color: #fff;
  border-radius: 12px;
`;

export const Title = styled.Text`
  font-size: 20px;
  font-weight: bold;
  color: #333;
`;

export const Subtitle = styled.Text`
  font-size: 16px;
  color: gray;
  margin-bottom: 20px;
  color: #333;
`;

type AccountContainerProps = {
  selected: boolean;
};

export const AccountContainer = styled.TouchableOpacity<AccountContainerProps>`
  flex-direction: row;
  align-items: center;
  padding: 10px;
  border-width: 1px;
  border-color: ${({ selected }) => (selected ? '#007bff' : '#e0e0e0')};
  border-radius: 8px;
  margin-bottom: 10px;
  background-color: ${({ selected }) => (selected ? '#e8f0fe' : '#fff')};
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
`;

export const TextContainer = styled.View`
  flex: 1;
`;

export const Name = styled.Text`
  font-size: 16px;
  font-weight: bold;
  color: black;
`;

export const Email = styled.Text`
  font-size: 14px;
  color: gray;
`;

export const AddAccountButton = styled.TouchableOpacity`
  flex-direction: row;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border-width: 1px;
  border-color: #e0e0e0;
  border-radius: 8px;
  margin-top: 10px;
`;

export const AddAccountText = styled.Text`
  font-size: 16px;
  color: #007bff;
`;

export const RemoveAccountButton = styled.TouchableOpacity`
  flex-direction: row;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border-width: 1px;
  border-color: #e0e0e0;
  border-radius: 8px;
`;

export const RemoveAccountDialog = styled.View`
  padding: 12px;
  background-color: #fff;
  border-radius: 12px;
`;

export const RemoveAccountDialogTitle = styled.Text`
  font-size: 20px;
  font-weight: bold;
  color: #333;
`;

export const RemoveAccountDialogButtons = styled.View`
  flex-direction: row;
  justify-content: space-between;
  margin-top: 20px;
`;

export const RemoveAccountDialogButton = styled.TouchableOpacity`
  padding: 10px 20px;
  border-radius: 8px;
  background-color: #007bff;
`;

export const RemoveAccountDialogButtonText = styled.Text`
  color: white;
`;

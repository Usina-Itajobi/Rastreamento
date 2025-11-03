/* eslint-disable react/prop-types */
import React, { useMemo, useState } from 'react';
import { Modal, ScrollView, TouchableWithoutFeedback } from 'react-native';
import Icons from 'react-native-vector-icons/MaterialIcons';

import {
  DrawerActions,
  StackActions,
  useNavigation,
} from '@react-navigation/native';
import * as S from './styles';
import { Account, useAuth } from '../../context/authContext';
import getFirstAndInitialNameAccount from '../../utils/getFirstAndInitialNameAccount';

const ProfileOptionsModal = ({ visible, handleClose }) => {
  const navigation = useNavigation();
  const {
    accounts: accountsProvider,
    defaultAccount,
    addSelectedAccount,
    selectedAccount,
    removeAccount,
  } = useAuth();

  const [showDialogRemoveAccount, setShowDialogRemoveAccount] = useState(false);
  const [accountToRemove, setAccountToRemove] = useState<Account | null>(null);

  const handleAddAccount = () => {
    navigation.dispatch(
      StackActions.push('AuthStack', {
        screen: 'AuthScreen',
        params: { addAccountMode: true },
      }),
    );
    handleClose();
    navigation.dispatch(DrawerActions.closeDrawer());
  };

  const handleSelectAccount = (account: Account) => {
    addSelectedAccount(account);
    navigation.dispatch(DrawerActions.closeDrawer());
    handleClose();
  };

  const handleShowDialogRemoveAccount = (account: Account) => {
    setAccountToRemove(account);
    setShowDialogRemoveAccount(true);
  };

  const handleRemoveAccount = () => {
    removeAccount(accountToRemove);
    setShowDialogRemoveAccount(false);
  };

  const handleCloseDialogRemoveAccount = () => {
    setShowDialogRemoveAccount(false);
    setAccountToRemove(null);
  };

  const defaultAccountFormated = useMemo(() => {
    const { nome, nomeInitials } = getFirstAndInitialNameAccount(
      defaultAccount.accountName || defaultAccount.nome,
    );

    return {
      ...{
        ...defaultAccount,
        nomeInitials,
        nome: defaultAccount.accountName || nome,
      },
      selected: true,
    };
  }, [defaultAccount]);

  const accountsFormated = useMemo(() => {
    const accounts = accountsProvider.map((account) => {
      const { nome, nomeInitials } = getFirstAndInitialNameAccount(
        account.accountName || account.nome,
      );

      return {
        ...{
          ...account,
          nomeInitials,
          nome: account.accountName || nome,
        },
        selected: account.email === selectedAccount.email,
      };
    });

    return accounts;
  }, [accountsProvider, selectedAccount]);

  return (
    <Modal visible={visible} onRequestClose={handleClose} transparent>
      <TouchableWithoutFeedback onPress={handleClose}>
        <S.ModalContainer />
      </TouchableWithoutFeedback>

      <S.Content>
        <S.Container>
          {showDialogRemoveAccount ? (
            <S.RemoveAccountDialog>
              <S.RemoveAccountDialogTitle>
                Deseja remover a conta?
              </S.RemoveAccountDialogTitle>
              <S.RemoveAccountDialogButtons>
                <S.RemoveAccountDialogButton onPress={handleRemoveAccount}>
                  <S.RemoveAccountDialogButtonText>
                    Sim
                  </S.RemoveAccountDialogButtonText>
                </S.RemoveAccountDialogButton>
                <S.RemoveAccountDialogButton
                  onPress={handleCloseDialogRemoveAccount}
                >
                  <S.RemoveAccountDialogButtonText>
                    Não
                  </S.RemoveAccountDialogButtonText>
                </S.RemoveAccountDialogButton>
              </S.RemoveAccountDialogButtons>
            </S.RemoveAccountDialog>
          ) : (
            <>
              <S.Title>Gerenciar contas</S.Title>
              <S.Subtitle>Selecione a conta que deseja usar</S.Subtitle>
              <ScrollView>
                <S.AccountContainer
                  activeOpacity={0.7}
                  selected={
                    defaultAccountFormated.email === selectedAccount.email
                  }
                  onPress={() => handleSelectAccount(defaultAccountFormated)}
                >
                  <S.Avatar>
                    <S.AvatarText>
                      {defaultAccountFormated.nomeInitials}
                    </S.AvatarText>
                  </S.Avatar>
                  <S.TextContainer>
                    <S.Name numberOfLines={1}>
                      {defaultAccountFormated.nome}
                    </S.Name>
                    <S.Email numberOfLines={1}>
                      {defaultAccountFormated.email}
                    </S.Email>
                  </S.TextContainer>
                </S.AccountContainer>

                {accountsFormated.map((account) => (
                  <S.AccountContainer
                    activeOpacity={0.7}
                    key={account.email}
                    selected={account.email === selectedAccount.email}
                    onPress={() => handleSelectAccount(account)}
                  >
                    <S.Avatar>
                      <S.AvatarText>{account.nomeInitials}</S.AvatarText>
                    </S.Avatar>
                    <S.TextContainer>
                      <S.Name numberOfLines={1}>{account.nome}</S.Name>
                      <S.Email numberOfLines={1}>{account.email}</S.Email>
                    </S.TextContainer>
                    <S.RemoveAccountButton
                      onPress={() => handleShowDialogRemoveAccount(account)}
                    >
                      <Icons name="close" size={10} color="#333" />
                    </S.RemoveAccountButton>
                  </S.AccountContainer>
                ))}
              </ScrollView>
              <S.AddAccountButton
                onPress={handleAddAccount}
                activeOpacity={0.7}
              >
                <S.AddAccountText>+ Adicionar conta</S.AddAccountText>
              </S.AddAccountButton>
            </>
          )}
        </S.Container>
      </S.Content>
    </Modal>
  );
};

export default ProfileOptionsModal;

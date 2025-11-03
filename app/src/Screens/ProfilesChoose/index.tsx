/* eslint-disable react/prop-types */
import React, { useMemo, useState } from 'react';
import { ScrollView } from 'react-native';
import Icons from 'react-native-vector-icons/MaterialIcons';

import {
  CommonActions,
  DrawerActions,
  StackActions,
  useNavigation,
} from '@react-navigation/native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as S from './styles';
import { Account, useAuth } from '../../context/authContext';
import getFirstAndInitialNameAccount from '../../utils/getFirstAndInitialNameAccount';

const ProfileOptionsModal = () => {
  const navigation = useNavigation();
  const { top } = useSafeAreaInsets();
  const {
    accounts: accountsProvider,
    defaultAccount,
    addSelectedAccount,
    selectedAccount,
    removeAccount,
  } = useAuth();

  const [showDialogRemoveAccount, setShowDialogRemoveAccount] = useState(false);
  const [accountToRemove, setAccountToRemove] = useState<Account | null>(null);

  const handleResetNavigation = () => {
    navigation.dispatch(
      CommonActions.reset({
        index: 0,
        routes: [{ name: 'AppStack' }],
      }),
    );
  };

  const handleAddAccount = () => {
    navigation.dispatch(
      StackActions.push('AuthStack', {
        screen: 'AuthScreen',
        params: { addAccountMode: true },
      }),
    );
    navigation.dispatch(DrawerActions.closeDrawer());
  };

  const handleSelectAccount = (account: Account) => {
    addSelectedAccount(account);
    navigation.dispatch(DrawerActions.closeDrawer());
    handleResetNavigation();
  };

  const handleShowDialogRemoveAccount = (account: Account) => {
    setAccountToRemove(account);
    setShowDialogRemoveAccount(true);
  };

  const handleRemoveAccount = () => {
    removeAccount(accountToRemove);
    setShowDialogRemoveAccount(false);
    handleResetNavigation();
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
    <S.Container>
      <S.CloseButton onPress={navigation.goBack} style={{ top: top + 10 }}>
        <Icons name="close" size={24} color="#333" />
      </S.CloseButton>
      <S.Content>
        <S.ContentContainer>
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
        </S.ContentContainer>
      </S.Content>
    </S.Container>
  );
};

export default ProfileOptionsModal;

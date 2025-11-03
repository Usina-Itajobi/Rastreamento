import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';
import React, { createContext, useEffect, useState } from 'react';
import getFirstAndInitialNameAccount from '../utils/getFirstAndInitialNameAccount';

// {"bloqueio_automatico_cobranca": "N", "email": "financeiro@logfaz.com.br", "error": "N", "errormsg": "", "grupo": "N", "h": "35c9d8a805f5220b3ed32ab1b46be375", "id": "641", "keyMaps": "AIzaSyCtw4_xnEOXxMhCBH8yJhleTeBTJB2_-RY", "keyPush": "teste", "nome": "LOGFAZ TRANSPORTES E LOGISTICA LTDA"}

export type Account = {
  bloqueio_automatico_cobranca?: string;
  novo_contrato?: number;
  email: string;
  error?: string;
  errormsg?: string;
  grupo?: string;
  h: string;
  id?: string;
  keyMaps: string;
  keyPush?: string;
  nome: string;
  accountName?: string;
  nomeInitials?: string;
  username?: string;
  parentAccountEmail?: string;
};
// Define the shape of the AuthContext
interface AuthContextType {
  defaultAccount: Account | null;
  accounts: Account[];
  selectedAccount: Account | null;
  // eslint-disable-next-line no-unused-vars
  addDefaultAccount: (account: Account) => void;
  // eslint-disable-next-line no-unused-vars
  addAccount: (account: Account) => void;
  // eslint-disable-next-line no-unused-vars
  addSelectedAccount: (account: Account) => void;
  // eslint-disable-next-line no-unused-vars
  removeAccount: (account: Account) => void;
  // eslint-disable-next-line no-unused-vars
  updateAccount: (account: Account) => void;
  // eslint-disable-next-line no-unused-vars
  logOut: () => void;
  // eslint-disable-next-line no-unused-vars
  updateSelectedAccountData: () => void;
}

// Create the AuthContext
export const AuthContext = createContext<AuthContextType>({
  defaultAccount: null,
  accounts: [],
  selectedAccount: null,
  addDefaultAccount: () => {},
  addAccount: () => {},
  addSelectedAccount: () => {},
  removeAccount: () => {},
  updateAccount: () => {},
  logOut: () => {},
  updateSelectedAccountData: () => {},
});

// Create the AuthProvider component
export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({
  children,
}) => {
  const [defaultAccount, setDefaultAccount] = useState<Account | null>(null);
  const [accounts, setAccounts] = useState<Account[]>([]);
  const [selectedAccount, setSelectedAccount] = useState<Account | null>(null);
  const [loading, setLoading] = useState(false);

  const addSelectedAccount = (account: Account) => {
    setSelectedAccount(account);
    AsyncStorage.setItem('@ctracker:selectedAccount', JSON.stringify(account));
  };

  const addDefaultAccount = (account: Account) => {
    setDefaultAccount(account);
    AsyncStorage.setItem('@ctracker:defaultAccount', JSON.stringify(account));
    addSelectedAccount(account);
  };

  const addAccount = (account: Account) => {
    const defaultAccountEmail = defaultAccount?.email;

    setAccounts([
      ...accounts,
      { ...account, parentAccountEmail: defaultAccountEmail },
    ]);
    AsyncStorage.setItem(
      '@ctracker:accounts',
      JSON.stringify([
        ...accounts,
        { ...account, parentAccountEmail: defaultAccountEmail },
      ]),
    );

    addSelectedAccount({ ...account, parentAccountEmail: defaultAccountEmail });
  };

  const removeAccount = (account: Account) => {
    const newAccounts = accounts.filter((acc) => acc.id !== account.id);
    setAccounts(newAccounts);
    AsyncStorage.setItem('@ctracker:accounts', JSON.stringify(newAccounts));

    if (selectedAccount?.id === account.id) {
      addSelectedAccount(defaultAccount);
    }
  };

  const updateAccount = (account: Account) => {
    setSelectedAccount(account);
    AsyncStorage.setItem(
      '@ctracker:selectedAccount',
      JSON.stringify(account),
    );

    // Atualizar o defaultAccount:
    if (defaultAccount?.id === account?.id) {
      setDefaultAccount(account);
      AsyncStorage.setItem(
        '@ctracker:defaultAccount',
        JSON.stringify(account),
      );
    }
  };

  const logOut = async () => {
    try {
      await AsyncStorage.clear();
      setDefaultAccount(null);
      setSelectedAccount(null);
      setAccounts([]);
    } catch (error) {
      console.error('Erro ao sair:', error);
    }
  };

  const getAccounts = async () => {
    setLoading(true);
    const defaultAccountStorage = await AsyncStorage.getItem(
      '@ctracker:defaultAccount',
    );
    const accountsStorage = await AsyncStorage.getItem('@ctracker:accounts');
    const selectedAccountStorage = await AsyncStorage.getItem(
      '@ctracker:selectedAccount',
    );

    if (defaultAccountStorage) {
      setDefaultAccount(JSON.parse(defaultAccountStorage));
    } else {
      const h = await AsyncStorage.getItem('@ctracker:accessToken');
      const username = await AsyncStorage.getItem('@ctracker:user_name');
      const keyMaps = await AsyncStorage.getItem('@ctracker:keyMaps');
      const user = await AsyncStorage.getItem('@ctracker:user');

      if (h && username && keyMaps && user) {
        const userObj = JSON.parse(user);
        const newDefaultAccount = {
          h,
          keyMaps,
          username,
          email: userObj.email,
          nome: userObj.name,
          nomeInitials: getFirstAndInitialNameAccount(userObj.name)
            .nomeInitials,
        };
        AsyncStorage.setItem(
          '@ctracker:defaultAccount',
          JSON.stringify(newDefaultAccount),
        );
        setDefaultAccount(newDefaultAccount);
      }
    }

    if (accountsStorage) {
      const defaultAccountParsed = JSON.parse(defaultAccountStorage);
      const accountsParsed = JSON.parse(accountsStorage);
      const accountsByParent = accountsParsed.filter(
        (acc: Account) =>
          acc.parentAccountEmail === defaultAccountParsed?.email,
      );

      setAccounts(accountsByParent);
    }

    if (selectedAccountStorage) {
      setSelectedAccount(JSON.parse(selectedAccountStorage));
    }

    if (!selectedAccountStorage && defaultAccountStorage) {
      setSelectedAccount(JSON.parse(defaultAccountStorage));
    }

    setLoading(false);
  };

  useEffect(() => {
    getAccounts();
  }, []);

  const updateSelectedAccountData = async () => {
    if (!selectedAccount?.id) return;

    const enterprise = await AsyncStorage.getItem('@ctracker:enterprise');
    if(!enterprise) return;

    const { baseUrl } = JSON.parse(enterprise);
    if(!baseUrl) return;

    try {

      const result = await fetch(
        `${baseUrl}/metronic/api/get-user.php?h=${selectedAccount.h}`,
        {
          method: 'GET'
        },
      );

      let dataText = await result.text();
      if (Platform.OS === 'android') {
        dataText = dataText.replace(/\r?\n/g, '').replace(/[\u0080-\uFFFF]/g, '');
      }

      const data = JSON.parse(dataText);

      if(data.error){
        console.error(data);
        return;
      }

      const updatedAccount: Account = {
        ...selectedAccount,
        ...data.data,
      };

      updateAccount(updatedAccount);
    } catch (error) {
      console.error('Erro ao atualizar dados do usuário:', error);
    }
  };

  useEffect(() => {
    updateSelectedAccountData();
  }, [selectedAccount?.id]);

  return (
    <AuthContext.Provider
      value={{
        defaultAccount,
        accounts,
        addDefaultAccount,
        addAccount,
        selectedAccount,
        addSelectedAccount,
        removeAccount,
        updateAccount,
        logOut,
        updateSelectedAccountData,
      }}
    >
      {loading ? <></> : children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => React.useContext(AuthContext);

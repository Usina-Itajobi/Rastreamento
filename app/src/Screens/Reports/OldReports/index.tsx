import React, { FC } from 'react';
import { View } from 'react-native';
import { WebView } from 'react-native-webview';

import styles from './styles';
import Header from '../../../Components/Header';
import { useAuth } from '../../../context/authContext';

interface OldReportsProps {}

const OldReports: FC<OldReportsProps> = (props) => {
  const { selectedAccount } = useAuth();

  return (
    <>
      <Header title="Relatórios" name="relat" {...props} />
      <View style={styles.container}>
        <View style={styles.webview}>
          <WebView
            source={{
              uri: `https://itajobi.usinaitajobi.com.br/metronic/api/relatorio-posicao.php?h=${selectedAccount.h}`,
            }}
            useWebKit
          />
        </View>
      </View>
    </>
  );
};

export default OldReports;

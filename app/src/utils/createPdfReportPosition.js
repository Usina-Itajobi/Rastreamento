import { Alert, PermissionsAndroid, Platform } from 'react-native';
import RNHTMLtoPDF from 'react-native-html-to-pdf';
import Share from 'react-native-share';
import { PERMISSIONS, request } from 'react-native-permissions';

import createHtmlReportPosition from './createHtmlReportPosition';

export default async function createPdfReportPosition(
  positions,
  base64PrintScreenMap,
) {
  try {
    if (Platform.OS === 'android') {
      if (Number(Platform.Version) <= 30) {
        const grantedWriteExternalStorage = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.WRITE_EXTERNAL_STORAGE,
          {
            title: 'Precisamos de acesso a seus arquivos',
            message:
              'Para que possamos gerar seu relatório precisamos da sua permissão',
            buttonNeutral: 'Me pergunte depois',
            buttonNegative: 'Cancelar',
            buttonPositive: 'OK',
          },
        );
        const grantedReadExternalStorage = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.READ_EXTERNAL_STORAGE,
          {
            title: 'Precisamos de acesso a seus arquivos',
            message:
              'Para que possamos gerar seu relatório precisamos da sua permissão',
            buttonNeutral: 'Me pergunte depois',
            buttonNegative: 'Cancelar',
            buttonPositive: 'OK',
          },
        );
        if (
          grantedWriteExternalStorage !== PermissionsAndroid.RESULTS.GRANTED &&
          grantedReadExternalStorage !== PermissionsAndroid.RESULTS.GRANTED
        ) {
          Alert.alert('Erro de Permissão');
          return;
        }
      }
    } else {
      const permission = await request(PERMISSIONS.IOS.PHOTO_LIBRARY_ADD_ONLY);

      if (permission !== 'granted') {
        Alert.alert('Erro de Permissão');
        return;
      }
    }

    const html = createHtmlReportPosition(positions, base64PrintScreenMap);

    const optionsCreatePDF = {
      html,
    };

    const file = await RNHTMLtoPDF.convert(optionsCreatePDF);

    try {
      await Share.open({ url: `file://${file.filePath}` });
    } catch (error) {
      console.log(error);
    }
  } catch (error) {
    Alert.alert(
      'Ocorreu um erro!',
      'Tivemos problemas para gerar seu PDF, por favor tente novamente.',
    );
    console.log(error);
  }
}

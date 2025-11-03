import { StyleSheet, Platform } from 'react-native';

export default StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f6fa',
    paddingHorizontal: 10,
    paddingTop: Platform.OS == 'ios' ? 0 : 24,
    justifyContent: 'space-between',
  },

  webview: {
    flex: 1,
    backgroundColor: '#f5f6fa',
  },
});

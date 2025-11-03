import { StyleSheet, Platform } from 'react-native';

export default StyleSheet.create({
  container: {
    flex: 1,
    // backgroundColor: "#f5f6fa",
    // paddingHorizontal: 24,
    // paddingTop: Platform.OS == "ios" ? 40 : 24,
  },

  alertContainer: {
    width: '90%',
    alignSelf: 'center',
    margin: 5,
    height: 95,
    borderRadius: 20,
    backgroundColor: '#ffffff',
    shadowColor: 'rgba(0, 0, 0, 0.18)',
    shadowOffset: {
      width: 0,
      height: 0,
    },
    shadowRadius: 15.8,
    shadowOpacity: 1,
  },
});

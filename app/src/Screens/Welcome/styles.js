import { StyleSheet, Platform } from 'react-native';
import {
  heightPercentageToDP as hp,
  widthPercentageToDP as wp,
} from 'react-native-responsive-screen';
export default StyleSheet.create({
  backgroundImage: {
    flex: 1,
  },

  container: {
    flex: 1,
    paddingHorizontal: 32,
    paddingBottom: 64,
    // backgroundColor: "rgba(25, 42, 86, 0.5)",
    display: 'flex',
    justifyContent: 'flex-end',
  },

  description: {
    fontSize: hp('4%'),
    fontWeight: '800',
    color: '#ffffff',
    marginBottom: 128,
  },

  input: {
    width: '100%',
    height: 48,
    backgroundColor: '#ffffff',
    paddingHorizontal: 16,
    marginBottom: 16,
    borderRadius: 4,
    fontSize: 16,
  },

  button: {
    width: '100%',
    height: 48,
    backgroundColor: '#f69c33',
    borderRadius: 4,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
  },

  buttonText: {
    color: 'white',
    fontSize: Platform.OS == 'ios' ? 14 : 16,
    fontWeight: Platform.OS == 'ios' ? '600' : '400',
  },

  forgotPasswordButton: {
    alignSelf: 'center',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: Platform.OS == 'ios' ? 60 : 30,
  },
});

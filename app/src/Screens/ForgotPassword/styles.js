import { StyleSheet, Platform } from 'react-native';

export default StyleSheet.create({
  container: {
    padding: 32,
  },

  brandContainer: {
    width: 256,
    height: 256,
    marginBottom: 32,
  },

  title: {
    height: 30,
    marginBottom: 10,
    fontSize: 23,
    marginTop: '35%',
    fontWeight: 'bold',
    fontStyle: 'normal',
    letterSpacing: 0,
    color: '#ffffff',
  },

  rectangle1: {
    width: 40,
    height: 4,
    backgroundColor: '#f69c33',
    marginBottom: 20,
    marginLeft: Platform.OS === 'ios' ? 2 : 3,
  },

  input: {
    width: '100%',
    height: 48,
    backgroundColor: '#c8d6e5',
    paddingHorizontal: 16,
    marginBottom: 16,
    borderRadius: 4,
    fontSize: 16,
  },

  button: {
    width: 182.5,
    alignSelf: 'center',
    height: 48,
    borderRadius: 4,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: Platform.OS == 'ios' ? 60 : 30,
  },

  buttonText: {
    color: '#ffffff',
    fontSize: Platform.OS == 'ios' ? 14 : 16,
    fontWeight: Platform.OS == 'ios' ? '600' : '400',
  },

  forgotPasswordButton: {
    alignSelf: 'center',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: Platform.OS == 'ios' ? 60 : 30,
  },

  inputErrorMessage: {
    color: '#ff0000',
    fontSize: Platform.OS == 'ios' ? 14 : 16,
    fontWeight: Platform.OS == 'ios' ? '600' : '400',
  },
});

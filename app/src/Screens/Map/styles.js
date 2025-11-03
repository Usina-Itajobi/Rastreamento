import { StyleSheet, Platform } from 'react-native';
import { getStatusBarHeight } from 'react-native-iphone-x-helper';
import {
  heightPercentageToDP,
  widthPercentageToDP,
} from 'react-native-responsive-screen';

export default StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
    position: 'relative',
  },
  containerAnimation: {
    flex: 1,
    backgroundColor: '#ffffff',
    // position: 'relative',
    alignItems: 'center',
    justifyContent: 'center',
  },
  vehicle: {
    backgroundColor: '#0678a9',
    padding: 16,
    width: '90%',
    alignSelf: 'center',
    borderRadius: 5,
    marginBottom: 50,
    shadowOffset: { width: 0, height: 4 },
    shadowColor: '#000000',
    shadowOpacity: 0.05,
    elevation: 2,
  },

  vehicleTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 16,
    color: 'white',
  },

  vehicleAddress: {
    fontSize: 16,
    color: 'white',
  },

  vehicleActions: {
    borderTopColor: 'white',
    borderTopWidth: 1,
    marginTop: 12,
    paddingTop: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },

  vehicleActionsBlock: {
    flex: 1,
    height: 40,
    paddingHorizontal: 16,
    marginRight: 8,
    backgroundColor: 'red',
    color: 'white',
    borderRadius: 4,
    justifyContent: 'center',
  },

  vehicleActionsUnBlock: {
    flex: 1,
    height: 40,
    paddingHorizontal: 16,
    marginLeft: 8,
    backgroundColor: 'green',
    color: 'white',
    borderRadius: 4,
    justifyContent: 'center',
  },
  header: {
    width: '100%',
    position: 'absolute',
    marginTop: Platform.OS === 'ios' ? 48 : 40,
    paddingHorizontal: 24,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },

  arrowBack: {
    width: 40,
    height: 40,
    backgroundColor: '#ffffff',
    borderRadius: 100,
    shadowOffset: { width: 0, height: 4 },
    shadowColor: '#000000',
    shadowOpacity: 0.05,
    elevation: 7,
    justifyContent: 'center',
    alignItems: 'center',
  },

  refresh: {
    width: 40,
    height: 40,
    backgroundColor: '#ffffff',
    borderRadius: 100,
    shadowOffset: { width: 0, height: 4 },
    shadowColor: '#000000',
    shadowOpacity: 0.05,
    elevation: 7,
    justifyContent: 'center',
    alignItems: 'center',
  },

  map: {
    width: widthPercentageToDP('100%'),
    height: heightPercentageToDP('100%'),
  },

  anchorOn: {
    height: 40,
    justifyContent: 'center',
    backgroundColor: 'green',
    paddingHorizontal: 16,
  },

  anchorOff: {
    height: 40,
    justifyContent: 'center',
    backgroundColor: 'red',
    paddingHorizontal: 16,
  },

  anchorText: {
    fontSize: 16,
    color: '#ffffff',
    textAlign: 'center',
  },

  details: {
    // flex: 1,
    // marginHorizontal: 8,
    // marginTop: 16,
  },

  detailsItem: {
    paddingBottom: 16,
  },

  detailsItemTitle: {
    fontWeight: 'bold',
    fontSize: 16,
  },

  detailsItemDescription: {
    marginTop: 4,
    fontSize: 16,
    color: 'grey',
  },

  buttonMapBase: {
    position: 'absolute',
    top: getStatusBarHeight() + 24,
    backgroundColor: '#004e70',
    alignSelf: 'center',
    color: '#000',
    width: 170,
    height: 30,
    borderRadius: 6,
    elevation: 2,
    justifyContent: 'center',
    alignItems: 'center',
    fontSize: 24,
    zIndex: 2,
  },
});

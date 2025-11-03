import { StyleSheet, Platform } from 'react-native';
import { getStatusBarHeight } from 'react-native-iphone-x-helper';
import { heightPercentageToDP as hp } from 'react-native-responsive-screen';

export default StyleSheet.create({
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 40,
  },
  loadingText: {
    marginTop: 10,
    fontSize: 14,
    color: '#555',
  },
  container: {
    flex: 1,
    position: 'relative',
    backgroundColor: '#F0F4F8',
    //padding: 20,
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

  vehicleActions: {
    borderTopColor: 'white',
    borderTopWidth: 1,
    marginTop: 12,
    paddingTop: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },

  vehicleActionsBlock: {
    // flex: 1,
    // height: 40,
    // paddingHorizontal: 16,
    // marginRight: 8,
    // backgroundColor: "red",
    // color: "white",
    // borderRadius: 4,
    justifyContent: 'center',
  },

  vehicleActionsUnBlock: {
    // flex: 1,
    // height: 40,
    // paddingHorizontal: 16,
    // marginLeft: 8,
    // backgroundColor: "green",
    // color: "white",
    // borderRadius: 4,
    justifyContent: 'center',
  },
  /*header: {
    width: '100%',
    position: 'absolute',
    marginTop: Platform.OS == 'ios' ? 48 : 40,
    paddingHorizontal: 24,
    flexDirection: 'row',
    justifyContent: 'space-between',
  },*/

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
    width: '100%',
    height: hp('75%'),
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
    marginHorizontal: 8,
    // marginTop: 16,
  },

  detailsItem: {
    paddingBottom: 16,
  },

  detailsItemTitle: {
    fontWeight: 'bold',
    fontSize: 16,
    color: '#111111',
  },

  detailsItemDescription: {
    marginTop: 4,
    fontSize: 16,
    color: '#999999',
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

  headerContainer: {
    marginBottom: 10,
  },
  vehicleName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#000',
  },
  vehicleAddress: {
    fontSize: 12,
    color: '#555',
  },
  vehicleMotorista: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#555',
  },
  cardContainer: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 12,
    elevation: 3,
    marginVertical: 10,
  },
  cardHeader: {
    marginBottom: 10,
  },
  header: {
    fontSize: 14,
    color: '#555',
    marginBottom: 0,
  },
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  item: {
    width: '30%',
    marginBottom: 15,
  },
  itemWithBorder: {
    borderRightWidth: 1,
    borderRightColor: '#ccc',
  },
  label: {
    fontSize: 12,
    color: '#777',
    marginTop: 4,
  },
  value: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#000',
  },
  fuelContainer: {
    marginTop: 10,
    marginBottom: 10,
  },
  fuelValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#d33',
  },
  fuelBar: {
    height: 8,
    backgroundColor: '#eee',
    borderRadius: 4,
    marginTop: 5,
  },
  fuelFill: {
    height: 8,
    backgroundColor: '#d33',
    borderRadius: 4,
  },
  footer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  footerItem: {
    alignItems: 'center',
    width: '48%',
    marginBottom: 10,
  },
  footerLabel: {
    fontSize: 12,
    color: '#777',
  },
  footerValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#000',
  },

  latLongContainer: {
    paddingTop: 10,
    paddingBottom: 10,
    borderTopWidth: 1,
    borderTopColor: '#ccc',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },

  latLongIcon: {
    width: 36,
    height: 36,
    borderRadius: 18, // metade da largura/altura para ficar redondo
    backgroundColor: '#004e70',
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 6,
  },
});

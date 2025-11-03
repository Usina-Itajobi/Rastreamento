import { StyleSheet, Dimensions } from 'react-native';

const d = Dimensions.get('window');

export default StyleSheet.create({
  container: {
    // flex: 1,
    width: d.width,
    height: d.height,
    // backgroundColor: "#f5f6fa",
    // paddingHorizontal: 24,
    // paddingTop: Platform.OS == "ios" ? 40 : 24,
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
    backgroundColor: 'white',
    color: 'white',
    borderRadius: 4,
    justifyContent: 'center',
  },

  vehicleActionsUnBlock: {
    flex: 1,
    height: 40,
    paddingHorizontal: 16,
    marginLeft: 8,
    backgroundColor: 'white',
    color: 'white',
    borderRadius: 4,
    justifyContent: 'center',
  },
});

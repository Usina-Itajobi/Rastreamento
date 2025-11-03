import { StyleSheet } from 'react-native';

export default StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
  },

  itensTopo:{
    flexDirection: 'row',
    justifyContent: "space-around",
    marginTop: 5,
    padding: 10,
  },
  itemTopo:{
    backgroundColor: "#fff",
    borderRadius: 30,
    paddingVertical: 8,
    paddingHorizontal: 12,
    marginBottom: 10,
    shadowColor: "#000",
    shadowOpacity: 0.08,
    shadowRadius: 5,
    elevation: 3,
    flexDirection: 'row',
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

  vehicleTitle: {
    // fontSize: 16,
    // fontWeight: "bold",
    // marginBottom: 16,
    // color: "#005580",
    // width: 155.5,

    // fontFamily: "Montserrat",
    fontSize: 16,
    fontWeight: 'bold',
    fontStyle: 'normal',
    letterSpacing: 0,
    color: '#ffffff',
  },

  vehicleAddress: {
    fontSize: 12.8,
    fontWeight: 'normal',
    fontStyle: 'normal',
    letterSpacing: 0,
    // textAlign: "center",
    marginTop: 13.5,
    marginLeft: 13.5,
    color: '#ffffff',
  },
  baseCard: {
    marginLeft: '5%',
    width: '90%',
    marginBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#434343',
  },
  baseCardLast:{
    borderBottomWidth: 0,
  },
  card: {
    backgroundColor: "#fff",
    borderRadius: 12,
    padding: 15,
    marginBottom: 20,
    shadowColor: "#000",
    shadowOpacity: 0.08,
    shadowRadius: 5,
    elevation: 3,
  },
  header: {
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 12,
  },
  baseIconCar: {
    //
    height: 50,
    width: 50,
    borderRadius: 10,
    borderColor: '#ccc',
    backgroundColor: '#f5f5f5',
    borderWidth: 1,
    overflow: 'hidden',
  },
  iconCar: {
    width: '100%',
    height: '100%',
  },
  plate: {
    color: "#555",
    fontSize: 18,
    fontWeight: "bold",
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
  subtitle: {
    fontSize: 14,
    color: "#555",
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
  contentStatus: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginBottom: 10,
    gap: 5,
  },
  iconStatus: {
    width: '28%',
    padding: 4,
    borderRadius: 20,
    flexDirection: "row",
    alignItems: 'center',
    justifyContent: 'center',
  },
  statusBadge: {
    width: '40%',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 20,
    flexDirection: "row",
    alignItems: 'center',
    justifyContent: 'center',
  },
  on: {
    backgroundColor: "#d1fae5",
  },
  off: {
    backgroundColor: "#fee2e2",
  },
  moving: {
    backgroundColor: "#bfdbfe",
  },
  statusText: {
    fontSize: 13,
    fontWeight: "bold",
  },
  infoRow: {
    flexDirection: "row",
    justifyContent: "space-around",
    flexWrap: 'wrap',
  },
  infoItemIcon: {
    height: 30,
    width: 30,
    borderRadius: 10,
    borderColor: '#ccc',
    backgroundColor: '#005580',
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: "center",
    marginRight: 3,
  },
  infoItem: {
    alignItems: "center",
    justifyContent: "center",
    flexDirection: "row",
    width: '30%',
    marginBottom: 5,
  },
  infoValue: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#000',
  },
  infoLabel: {
    fontSize: 10,
    color: '#777',
  },

  addressBox: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginTop: 5,
  },
  address: {
    marginLeft: 5,
    flex: 1,
    color: "#333",
    fontSize: 13,
  },
  driverBox: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginTop: 5,
  },
  driver: {
    marginLeft: 5,
    flex: 1,
    color: "#333",
    fontSize: 13,
  },

  cardFooter: {
    paddingTop: 5,
    marginTop: 5,
    flex: 1,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: '#ccc',
  },
  cardFooterBotoes: {
    flexDirection: "row",
    color: "#777",
  },
  btnFooterIcon: {
    width: 36,
    height: 36,
    borderRadius: 18, // metade da largura/altura para ficar redondo
    backgroundColor: '#004e70',
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 5,
  },

  footerLastUpdateBox: {
    flex: 1,
  },
  lastUpdate: {
    fontSize: 11,
    color: "#777",
  },
});

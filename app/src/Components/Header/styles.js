import { StyleSheet, Platform } from "react-native";

export default StyleSheet.create({
  container: {
    width: "100%",
    height: 100,
    display: "flex",
    flexDirection: "row",
    justifyContent: "center",
    marginBottom: 16,

    borderRadius: 5.6,
    backgroundColor: "#ffffff",
    shadowColor: "rgba(0, 0, 0, 0.31)",
    shadowOffset: {
      width: -0.9,
      height: 3.4,
    },
    shadowRadius: 11.5,
    shadowOpacity: 1,
  },

  leftContainer: {
    display: "flex",
    flexDirection: "row",
    alignItems: "center",
  },

  rightContainer: {
    flex: 1,
    paddingLeft: 16,
  },

  menu: {
    marginRight: 20,
    display: "flex",
    alignItems: "center",
  },

  title: {
    // fontFamily: "Montserrat",
    fontSize: 20,
    fontWeight: "bold",
    fontStyle: "normal",
    letterSpacing: 0,
    alignSelf: "center",
    // marginLeft: 100,
    marginTop: Platform.OS === "ios" ? 0 : 30,

    color: "#004e70",
  },
});

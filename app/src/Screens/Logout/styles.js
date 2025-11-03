import { StyleSheet, Platform } from "react-native";

export default StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#f5f6fa",
    paddingHorizontal: 24,
    paddingTop: Platform.OS == "ios" ? 40 : 24
  }
});

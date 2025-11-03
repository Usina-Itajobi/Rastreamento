import Reactotron from 'reactotron-react-native';

if (__DEV__) {
  Reactotron.configure()
    .useReactNative({
      networking: {
        ignoreUrls: /(symbolicated|8081|generate_204)/,
      },
    })
    .connect();
}

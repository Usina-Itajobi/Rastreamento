import * as Sentry from '@sentry/react-native';
import {
  configureReanimatedLogger,
  ReanimatedLogLevel,
} from 'react-native-reanimated';

// This is the default configuration
configureReanimatedLogger({
  level: ReanimatedLogLevel.error,
  strict: false, // Reanimated runs in strict mode by default
});

// DEV DEBUG
if (__DEV__) {
  require('./ReactotronConfig');
}

Sentry.init({
  dsn: 'https://206e94d94def4877602a0aeffcaa6b48@o510540.ingest.us.sentry.io/4507663787491328',

  // uncomment the line below to enable Spotlight (https://spotlightjs.com)
  // enableSpotlight: __DEV__,
});

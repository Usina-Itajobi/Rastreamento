# Flow de upgrade da nova app

## Android
- Criar novo projeto React native
  - CRastreamento
  - br.com.ctracker.app
- Adicionar chave google key para mapas

```js
<meta-data android:name="com.google.android.geo.API_KEY" android:value="AIzaSyCK68SWDM0H4gc23n_8eK7gFbyCgtYKCYk"/>
```

- Adicionar Config Icons

```js
project.ext.vectoricons = [
    iconFontNames: [ 'MaterialIcons.ttf', 'EvilIcons.ttf' ] // Specify font files
]

apply from: file("../../node_modules/react-native-vector-icons/fonts.gradle")
```

- Desabilitar New Architecture android para funcionar o Maps

```js
newArchEnabled=false
```

- Adicionar Icon e Splash Screen Android

- Adicionar alarm.wav no android/app/src/main/res/raw

- Adicionar Permissions

```js
  <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
  <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
  <uses-permission android:name="android.permission.INTERNET"/>
  <uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"/>
  <uses-permission android:name="android.permission.READ_PHONE_STATE"/>
  <uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW"/>
  <uses-permission android:name="android.permission.VIBRATE"/>
  <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"/>
```

## IOS

- Adicionar App Identifier

```js
PRODUCT_BUNDLE_IDENTIFIER = com.ctracker.controltracker
```

- Adicionar Config Icons em info.plist

```js
<key>UIAppFonts</key>
<array>
  <string>AntDesign.ttf</string>
  <string>FontAwesome.ttf</string>
  <string>MaterialIcons.ttf</string>
  <string>MaterialCommunityIcons.ttf</string>
</array>
```

- Adicionar Config Maps em AppDelegate.swift e Podfile

```js
# CONFIG RN MAPS
   rn_maps_path = '../node_modules/react-native-maps'
   pod 'react-native-google-maps', :path => rn_maps_path
   #
```

```js
// CONFIG RN MAPS
import GoogleMaps
//

@main
class AppDelegate: RCTAppDelegate {
  override func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey : Any]? = nil) -> Bool {
    // CONFIG RN MAPS
    GMSServices.provideAPIKey("AIzaSyCK68SWDM0H4gc23n_8eK7gFbyCgtYKCYk")
    //

```

- [Adicionar Config OneSignal](https://documentation.onesignal.com/docs/react-native-sdk-setup#2-ios-setup)

- Adicionar Config Permissions em Podfile e Info.plist

```js
# PERMISSIONS
permissions_path = '../node_modules/react-native-permissions/ios'
pod 'Permission-PhotoLibraryAddOnly', :path => "#{permissions_path}/PhotoLibraryAddOnly"
#
```

```js
<key>NSPhotoLibraryAddUsageDescription</key>
<string>Precisamos de acesso a sua biblioteca de fotos para salvar o PDF</string>
```

- Adicionar Config Non Exempt Encryption em Info.plist

```js
<key>ITSAppUsesNonExemptEncryption</key>
<false/>
```

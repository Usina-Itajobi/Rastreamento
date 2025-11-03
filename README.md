# APP ControlTracker Rastreamento

Aplicativo de Rastreamento para Veículos

---

## 🚀 **Instruções de Instalação**

## 📱 Configuração do Android Studio

Este guia explica como **baixar e configurar o Android Studio**, incluindo a criação de um **emulador com API 33**, essencial para rodar e testar o aplicativo Android corretamente.

---

### ✅ Requisitos

- Sistema Operacional: Windows, macOS ou Linux
- Conexão com a internet

---

### 🔽 1. Baixar o Android Studio

1. Acesse o site oficial:
   👉 [https://developer.android.com/studio](https://developer.android.com/studio)

2. Clique em **Download Android Studio**.

3. Aceite os termos e clique em **Download** novamente.

4. Instale o Android Studio seguindo os passos do assistente de instalação.


### 🛠️ 2. Instalar os Componentes Recomendados

Durante a instalação inicial do Android Studio:

- Marque a opção **Android Virtual Device (AVD)**.
- Certifique-se de que o SDK Manager está configurado corretamente.
- Após abrir o Android Studio pela primeira vez, vá em: Tools > SDK Manager

E instale:

- **Android API 33 (Tiramisu)**
- Android SDK Platform
- Intel x86 Emulator Accelerator (HAXM)

### 📲 3. Criar o Emulador

1. No Android Studio, vá até: Tools > Device Manager

2. Clique em **Create Device**.

3. Escolha um dispositivo (exemplo recomendado):
- **Pixel 6**

4. Avance e selecione a imagem do sistema:
- **API Level: 33**

5. Finalize a criação do emulador.

### ▶️ 4. Executar o Emulador

- Abra o **Device Manager** no Android Studio.
- Clique em **Play (▶️)** ao lado do emulador que você criou (Pixel 6 API 33).
- Espere o emulador inicializar completamente antes de rodar o aplicativo.

> ⚠️ **Importante:** Sempre mantenha o emulador **aberto e inicializado** antes de rodar o app via Android Studio ou linha de comando.

### 🧪 Dica Extra

Você pode configurar o emulador para rodar em seu celular, entre no ajuste do celular, ative Opções do Desenvolvedor e conecte o cabo do celular ao computador.

---

## 🧩 Configuração do Ambiente

Este guia explica como **Rodar o Projeto**

---


## 🛠️ Tecnologias Utilizadas

- **Biblioteca**: [ReactNative](https://reactnative.dev/)

---

## 🚀 Requisitos

Certifique-se de ter as versões corretas das ferramentas abaixo instaladas no seu ambiente:

- **Node.js**: `v20.18.0`
- **npm**: `10.8.2`

---

### 1. Clone o repositório

- Navegue até a sua pasta de trabalho no terminal e execute os seguinte comandos para baixar e acessar o projeto:

```bash
git clone https://github.com/control-tracker/cRastreamento

cd cRastreamento/app
```

### 2. Instalação das dependências

- No diretório do projeto, execute o comando abaixo para instalar todas as dependências:

```bash
yarn install
```

### 3. Execute o projeto

- Após a configuração, execute o comando abaixo para iniciar :

```bash
yarn android
```

> [!IMPORTANT]
> O emulador do android studio deve estar aberto

---

## Como buildar

### 1. Buildar o projeto(ABB)

- Navegue até a sua pasta e execute o comando abaixo para Buildar

```bash
cd android

gradlew bundleRelease
```

> [!IMPORTANT]
> O arquivo ficará em: cd android/app/build/outputs/bundle/release/app-release.aab

### 1. Buildar o projeto(APK)

- Navegue até a sua pasta e execute o comando abaixo para Buildar

```bash
cd android

gradlew assembleRelease
```

> [!IMPORTANT]
> O arquivo ficará em: cd android/app/build/outputs/apk/release/app-release.apk

---

### 🛠️ **Extra**

**Limpeza de Build**

```bash
cd android

gradlew.bat clean
```
---

**Verificar Diagnóstico do Ambiente**

Antes de iniciar o desenvolvimento, é importante verificar se o ambiente está corretamente configurado.

### Para React Native (usando CLI):

Na pasta no projeto do app execute o comando abaixo no terminal:

```bash
npx react-native doctor
```
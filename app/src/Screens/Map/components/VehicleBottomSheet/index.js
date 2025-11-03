import React, { useMemo, useRef, useEffect, useState } from 'react';
import { View, Text, StyleSheet, Linking, TouchableOpacity, Platform, ActivityIndicator } from 'react-native';
import BottomSheet, { BottomSheetScrollView } from '@gorhom/bottom-sheet';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';

export default function VehicleBottomSheet({ vehicle, loading, onClose }) {
  const sheetRef = useRef(null);

  const snapPoints = useMemo(() => ['15%', '35%', '75%'], []);
  const [sheetIndex, setSheetIndex] = useState(-1);


  useEffect(() => {
    if (vehicle || loading) {
      sheetRef.current?.expand();
      setSheetIndex(1); // abre no primeiro snap point
    } else {
      sheetRef.current?.close();
      setSheetIndex(-1); // fecha
    }
  }, [vehicle?.name, loading]);

  return (
    <BottomSheet
      ref={sheetRef}
      index={sheetIndex}
      snapPoints={snapPoints}
      backgroundStyle={{ backgroundColor: '#F0F4F8' }}
      onChange={(index) => {
        if (index === -1) {
          onClose();
        }
      }}
      enablePanDownToClose
    >
      <BottomSheetScrollView
        style={styles.content}
        showsVerticalScrollIndicator={false}
      >

        {loading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#004e70" />
            <Text style={styles.loadingText} allowFontScaling={false}>Carregando...</Text>
          </View>
        ) : vehicle ? (

          <View style={styles.container}>
            <View style={styles.headerContainer}>
              <Text style={styles.vehicleName} allowFontScaling={false}>{vehicle.name + ' (' + vehicle.tipo + ')'}</Text>
              {vehicle.motorista && (
                <View style={{ flexDirection: 'row' }}><MaterialCommunityIcons name="account" size={20} color="#005580" /><Text style={styles.vehicleMotorista} allowFontScaling={false}>{vehicle.motorista}</Text></View>
              )}
              {vehicle.endereco && (
                <Text style={styles.vehicleAddress} allowFontScaling={false}>{vehicle.endereco}</Text>
              )}
            </View>

            <View style={styles.cardContainer}>
              {/* Cabeçalho */}
              <View style={styles.cardHeader}>
                <Text style={styles.header} allowFontScaling={false}>
                  Posição: {vehicle.data_posicao}
                </Text>

                <Text style={styles.header} allowFontScaling={false}>
                  Comunicação: {vehicle.data_comunicacao}
                </Text>

                <Text style={styles.header} allowFontScaling={false}>
                  IMEI: {vehicle.imei}
                </Text>
              </View>

              {/* Grid principal */}
              <View style={styles.grid}>
                {[
                  { icon: "speedometer", label: "VELOCIDADE", value: `${vehicle.velocidade} KM/H` },
                  { icon: "engine-off", label: "IGNAÇÃO", value: (vehicle.ligado ? 'Ligada' : 'Desligada'), valueStyle: { color: vehicle.ign_color } },
                  vehicle.ligado && vehicle.template_telemetria?.rpm && { icon: "gauge", label: "RPM", value: vehicle.rpm },
                  vehicle.ligado && vehicle.template_telemetria?.odometro && { icon: "counter", label: "ODÔMETRO", value: `${vehicle.odometro} KM` },
                  { icon: "flash", label: "BATERIA", value: vehicle.voltagem_bateria ? `${vehicle.voltagem_bateria} V` : '-' },
                  { icon: "flash", label: "BATERIA INT.", value: vehicle.voltagem_bateria_int ? `${vehicle.voltagem_bateria_int} V` : '-' },
                  { icon: "anchor", label: "ÂNCORA", value: vehicle.ancora === '1' ? 'Ativo' : 'Inativo' },
                  { icon: "lock-open", label: "STATUS", value: vehicle.bloqueado ? 'Bloqueado' : 'Desbloq.' },
                  vehicle.ligado && vehicle.template_telemetria?.ambiente_temp && { icon: "thermometer", label: "TEMP. MOTOR", value: `${vehicle.temp_motor} °C` },
                  vehicle.ligado && vehicle.template_telemetria?.oleo_temp && { icon: "oil", label: "TEMP. ÓLEO", value: `${vehicle.temp_oleo} °C` },
                  vehicle.ligado && vehicle.template_telemetria?.torque && { icon: "engine", label: "TORQUE", value: `${vehicle.torque} Nm` },
                  vehicle.ligado && vehicle.template_telemetria?.arrefecimento_temp && { icon: "coolant-temperature", label: "HORIMETRO", value: `${vehicle.horimetro} h` },
                  vehicle.ligado && vehicle.template_telemetria?.acelerador_posicao && { icon: "arrow-right-bold", label: "POSIÇÃO DO ACELERADOR", value: `${vehicle.acelerador_posicao} %` },
                  vehicle.ligado && vehicle.template_telemetria?.freio_posicao && { icon: "car-brake-hold", label: "POSIÇÃO DO FREIO", value: `${vehicle.freio_posicao} %` },
                  vehicle.ligado && vehicle.template_telemetria?.marcha_atual && { icon: "car-shift-pattern", label: "MARCHA ATUAL", value: `${vehicle.marcha_atual}` },
                  vehicle.ligado && vehicle.template_telemetria?.motor_carga_calculada && { icon: "engine-outline", label: "CARGA DO MOTOR", value: `${vehicle.carga_motor} %` },
                  vehicle.ligado && vehicle.template_telemetria?.distancia_desde_limpeza && { icon: "cog", label: "DISTÂNCIA DESDE LIMP.", value: `${vehicle.distancia_desde_limpeza} KM` },
                  vehicle.ligado && vehicle.template_telemetria?.distancia_percorrida_lamp && { icon: "wrench", label: "DISTÂNCIA PERC. MIL", value: `${vehicle.distancia_percorrida_lamp} KM` },
                  { icon: "alert-circle-outline", label: "EVENTO", value: vehicle.evento },
                  { icon: "card-account-details-outline", label: "ID", value: vehicle.id },
                ]
                  .filter(Boolean) // remove os falsos (ex: rpm/odometro quando não existe)
                  .map((item, index) => (
                    <View
                      key={index}
                      style={[
                        styles.item,
                        (index + 1) % 3 !== 0 && styles.itemWithBorder // borda apenas se não for múltiplo de 3
                      ]}
                    >
                      <MaterialCommunityIcons name={item.icon} size={20} color="#333" />
                      <Text style={styles.label} allowFontScaling={false}>{item.label}</Text>
                      <Text style={[styles.value, item.valueStyle]} allowFontScaling={false}>{item.value}</Text>
                    </View>
                  ))
                }
              </View>

              { vehicle.ligado && vehicle.template_telemetria?.combustivel_nivel && (
                <View style={styles.fuelContainer}>
                  <Text style={styles.label} allowFontScaling={false}>Nível de Combustível</Text>
                  <Text style={styles.fuelValue} allowFontScaling={false}>
                    {(() => {
                      const combustivelNum = Number(vehicle.combustivel_nivel);
                      if (!vehicle?.combustivel_nivel || combustivelNum < 0) return '0%';
                      if (combustivelNum > 100) return '100%';
                      return combustivelNum + '%';
                    })()}
                  </Text>
                  <View style={styles.fuelBar}>
                    <View
                      style={[
                        styles.fuelFill,
                        {
                          width: (() => {
                            const combustivelNum = Number(vehicle.combustivel_nivel);
                            if (!vehicle?.combustivel_nivel || combustivelNum < 0) return '0%';
                            if (combustivelNum > 100) return '100%';
                            return combustivelNum + '%';
                          })(),
                        },
                      ]}
                    />
                  </View>
                </View>
              )}

              {/* Rodapé */}
              { vehicle.ligado && (
                  vehicle.template_telemetria?.economia_instantanea ||
                  vehicle.template_telemetria?.combustivel_total_usado ||
                  vehicle.template_telemetria?.combustivel_pressao ||
                  vehicle.template_telemetria?.combustivel_status
                ) && (
                <View style={styles.footer}>
                  { vehicle.template_telemetria?.economia_instantanea && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>ECONOMIA INSTANTÂNEA</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.economia_instantanea} KM/L</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_total_usado && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>TOTAL USADO</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_total_usado} L</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_pressao && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>PRESSÃO</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_pressao} kPa</Text>
                    </View>
                  )}

                  { vehicle.template_telemetria?.combustivel_status && (
                    <View style={styles.footerItem}>
                      <Text style={styles.footerLabel} allowFontScaling={false}>STATUS SISTEMA</Text>
                      <Text style={styles.footerValue} allowFontScaling={false}>{vehicle.combustivel_status}</Text>
                    </View>
                  )}
                </View>
              )}

              <View style={styles.latLongContainer}>
                <View>
                  <Text style={styles.label} allowFontScaling={false}>Latitude / Longitude</Text>
                  <Text style={styles.value} allowFontScaling={false}>
                    {vehicle.lat} / {vehicle.lng}
                  </Text>
                </View>

                <TouchableOpacity
                  style={styles.latLongIcon}
                  onPress={() => {
                    const scheme = Platform.select({
                      ios: 'maps:0,0?q=',
                      android: 'geo:0,0?q=',
                    });

                    const latLng = `${vehicle.lat},${vehicle.lng}`;
                    const url = Platform.select({
                      ios: `${scheme}${vehicle.name}@${latLng}`,
                      android: `${scheme}${latLng}(${vehicle.name})`,
                    });

                    Linking.openURL(url);
                  }}
                >

                  <MaterialCommunityIcons
                    name="directions"
                    size={20}
                    color="#fff"
                  />
                </TouchableOpacity>
              </View>
            </View>
          </View>
        ) : (
          <Text allowFontScaling={false}>Nenhum veículo selecionado</Text>
        )}
      </BottomSheetScrollView>
    </BottomSheet>
  );
}

const styles = StyleSheet.create({
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
    backgroundColor: '#F0F4F8',
    padding: 20,
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

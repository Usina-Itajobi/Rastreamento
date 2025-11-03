<?php
require_once("config.php");
$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);

if (@$_REQUEST['data_ini']!="") {
    $data_ini  = $_REQUEST['data_ini'];
} else {
    echo "data inicial invalida";
    die(http_response_code(401));
}

if (@$_REQUEST['data_fim']!="") {
    $data_fim  = $_REQUEST['data_fim'];
} else {
    echo "data Final invalida";
    die(http_response_code(401));
}

if (@$_REQUEST['v_veiculo']!="") {
    $v_veiculo  = $_REQUEST['v_veiculo'];
} else {
    echo "Id do veiculo invalido";
    die(http_response_code(401));
}

if (!isset($_REQUEST['speed'])) {
    $speed  = $_REQUEST['speed'];
} else {
    $speed  = 0;
}


$sql =  "select ( select name from bem b where b.imei = a.imei  ) as placa
						, ( select modelo_rastreador from bem b where b.imei = a.imei  ) as modelo_rastreador
						, ( select c.nome from bem b, cliente c where b.cliente = c.id and b.imei = a.imei  ) as cliente
						, date_format(date,'%d/%m/%Y %H:%i:%s') as data
						, date_format(data_comunica,'%d/%m/%Y %H:%i:%s') as data_comunica
						, imei
						, latitudeDecimalDegrees
						, latitudeHemisphere
						, longitudeDecimalDegrees
						, longitudeHemisphere
						, km_rodado
						, address
						, speed
						, rpm
						, converte
						, ligado
						, s1 as bloqueado
						, voltagem_bateria
						, infotext
						, motorista
						, TIMESTAMPDIFF(MINUTE,date,data_comunica) as minutocomunica
		from gprmc a
		where id_bem in ( $v_veiculo )
		and date between '$data_ini' and '$data_fim'
			and speed * 1.852 >= '$speed'
		order by date asc";

$stm = mysqli_query($con, $sql);
$posicoes = array();
while ($row = mysqli_fetch_assoc($stm)) {
    $dado = array();
    if ($row['latitudeDecimalDegrees'] > 0) {
        strlen($row['latitudeDecimalDegrees']) == 9 && $row['latitudeDecimalDegrees'] = '0' . $row['latitudeDecimalDegrees'];
        $g = substr($row['latitudeDecimalDegrees'], 0, 3);
        $d = substr($row['latitudeDecimalDegrees'], 3);
        $latitudeDecimalDegrees = $g + ($d / 60);
        $row['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;

        strlen($row['longitudeDecimalDegrees']) == 9 && $row['longitudeDecimalDegrees'] = '0' . $row['longitudeDecimalDegrees'];
        $g = substr($row['longitudeDecimalDegrees'], 0, 3);
        $d = substr($row['longitudeDecimalDegrees'], 3);
        $longitudeDecimalDegrees = $g + ($d / 60);
        $row['longitudeHemisphere'] == "W" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

        $latitudeDecimalDegrees = substr($latitudeDecimalDegrees, 0, 10);
        $longitudeDecimalDegrees = substr($longitudeDecimalDegrees, 0, 10);
    } else {
        $latitudeDecimalDegrees = substr($row['latitudeDecimalDegrees'], 0, 10);
        $longitudeDecimalDegrees = substr($row['longitudeDecimalDegrees'], 0, 10);
    }

    $dado['data'] = $row['data'];
    $dado['data_comunica'] = $row['data_comunica'];
    $dado['minutocomunica'] = $row['minutocomunica'];
    $escreveSN = $row['ligado'] == 'S' || $row['speed'] > 0 ? 'Sim' : 'Não';
    $dado['ignicao'] = $escreveSN;
    $dado['evento'] = $row['infotext'];
    $dado['voltagem_bateria'] = $row['voltagem_bateria'];
    $dado['address'] =  utf8_encode($row['address']);;
    $dado['placa'] = $row['placa'];
    $dado['velocidade'] = $row['speed'];
    $dado['km_rodado'] = $row['km_rodado'];
    $dado['rpm'] = $row['rpm'];
    $dado['cliente'] = $row['cliente'];
    $dado['motorista'] = $row['motorista'];
    $dado['modelo_rastreador'] = $row['modelo_rastreador'];
    $dado['lat'] = $latitudeDecimalDegrees;
    $dado['lng'] = $longitudeDecimalDegrees;
    if ($row['ligado'] == "S") {
		$dado['ign_color'] = "08CD1C"; //0352FC

	}else{
		$dado['ign_color'] = "FC0303"; //0352FC

	}


    $posicoes[] = $dado; 
}

echo json_encode($posicoes);
die(http_response_code(200));
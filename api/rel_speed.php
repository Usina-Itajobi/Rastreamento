<?php
require_once("config.php");

function ndistance($lat1, $lon1, $lat2, $lon2, $unit)
{

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

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

if (isset($_REQUEST['speed'])) {
    $speed  = $_REQUEST['speed'];
} else {
    $speed  = 0;
}


$sql = "
    SELECT
        (SELECT name FROM bem b WHERE b.id = a.id_bem) AS placa
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
        , TIMESTAMPDIFF(MINUTE,date,data_comunica) as minutocomunica
    FROM gprmc a
    WHERE date BETWEEN '$data_ini' AND '$data_fim'
        AND speed  >= '$speed'
        AND id_bem IN ( $v_veiculo )
    ORDER BY data ASC
";

$stm = mysqli_query($con, $sql);
$dados = array();
$total_km = 0;
$u_lat = 0;
$u_lng = 0;
while ($row = mysqli_fetch_assoc($stm)) {
    $dado = array();

    $dado['data'] = $row['data'];
    $dado['placa'] = $row['placa'];
    $dado['speed'] = $row['speed'];

    $address = utf8_encode($row['address']);
	$address_sep = explode(",", $address);
    $dado['endereco'] = $address_sep[0];

    $dado['ligado'] = $row['ligado'] == 'S' || $row['speed'] > 0 ? true : false;

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
    $dado['lat'] = $latitudeDecimalDegrees;
    $dado['lng'] = $longitudeDecimalDegrees;

    if ($u_lat) {

        $dist_s = ndistance($u_lat, $u_lng, $latitudeDecimalDegrees, $longitudeDecimalDegrees, "k");
        if (is_nan($dist_s)) {
            $dist_s = 0;
        }
        $total_km += $dist_s;
        $dado['km'] = number_format($total_km, 2);
    } else {
        $dado['km'] = 0;
    }

    $dados[] = $dado;

    $u_lat =  $latitudeDecimalDegrees;
	$u_lng =  $longitudeDecimalDegrees;
}

echo json_encode($dados);
die(http_response_code(200));
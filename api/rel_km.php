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

$sql = "
    SELECT
        (SELECT name FROM bem b WHERE b.id = a.id_bem) AS placa,
        DATE_FORMAT(date, '%d-%m-%Y') AS dia,
		MAX(km_rodado) - MIN(km_rodado) AS km_rodado_total
    FROM gprmc a
    WHERE date BETWEEN '$data_ini' AND '$data_fim'
        AND id_bem IN ($v_veiculo)
    GROUP BY placa, dia
    ORDER BY STR_TO_DATE(dia, '%d-%m-%Y') DESC
";

$stm = mysqli_query($con, $sql);
$dados = array();
$total_km = 0;
while ($row = mysqli_fetch_assoc($stm)) {
    $dado = array();

    $dado['data'] = $row['dia'];
    $dado['placa'] = $row['placa'];
    $dado['km'] = $row['km_rodado_total'];

    $dados[] = $dado;
}

echo json_encode($dados);
die(http_response_code(200));
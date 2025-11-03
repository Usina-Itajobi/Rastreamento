<?php
require_once("config.php");

$login = $_POST['h'];
if (!$login) {
    $login = $_GET['h'];
    if (!$login) {
        die(http_response_code(401));
    }
}

$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);


$sql = "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente,admin FROM cliente a WHERE (a.h = '" . $login . "' OR a.h = '" . $login . "') LIMIT 1";

$stm = mysqli_query($con, $sql);
if (mysqli_num_rows($stm) > 0) {
    $rs = mysqli_fetch_array($stm);
    $id_cliente = $rs['id_cliente'];
    $admin = $rs['admin'];

    if ($admin == "S") {
        $sql = "SELECT * FROM bem
        inner join cliente on cliente.id = bem.cliente
         WHERE cliente.id_admin = $id_cliente";
    } else {
        $sql = "SELECT * FROM bem WHERE cliente = $id_cliente";
    }
} else {
    $sql = "SELECT bem as 'id',descricao as 'name' FROM `grupo_bem`
    INNER join grupo on grupo_bem.grupo = grupo.id
    WHERE grupo.h = '$login'";
}

$stm1 = mysqli_query($con, $sql);
$dados = array();
while ($row = mysqli_fetch_assoc($stm1)) {
    $placa = $row['name'];
    $id_bem = $row['id'];
    $dados[] = ['id' => $id_bem, 'placa' => $placa];
}
echo json_encode($dados);
die(http_response_code(200));

<?php
function protejeInject($str)
{
    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
    return $sql;
}

header('Content-Type: application/json;charset=utf-8');
sleep(1);

$type = '';

$cliente = protejeInject($_POST['cliente']);
if (!$cliente) {
    $cliente = protejeInject($_GET['cliente']);
}

$senhaNova = protejeInject($_POST['senhaNova']);
if (!$senhaNova) {
    $senhaNova = protejeInject($_GET['senhaNova']);
}

$senhaAntiga = protejeInject($_POST['senhaAntiga']);
if (!$senhaAntiga) {
    $senhaAntiga = protejeInject($_GET['senhaAntiga']);
}

if ($senhaAntiga == '' || $senhaNova == '' || $cliente == '') {
    $retorno = array('errormsg' => 'Nenhuma informação foi transpassada', 'error' => 'S');
    echo json_encode($retorno);
    die;
}

require_once("config.php");
$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);

$senhaAtualmd5 = md5($senhaAntiga);
$senhaNovamd5 = md5($senhaNova);

$sql = "SELECT *
    FROM cliente 
    WHERE (email = '$cliente' OR apelido = '$cliente') AND senha = '$senhaAtualmd5' AND ativo = 'S'
    LIMIT 1";

$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 1) {
    $idcliente = mysqli_fetch_assoc($result)['id'];

    $update = "UPDATE `cliente` SET senha = '$senhaNovamd5' WHERE id = '$idcliente'";
    $resultUpdate = mysqli_query($con, $update);

    if ($resultUpdate) {
        $retorno = array(
            'errormsg' => 'Senha alterada com sucesso!',
            'error' => 'N'
        );
        echo json_encode($retorno);
        die;
    } else {
        $retorno = array(
            'errormsg' => 'Ops, tente novamente mais tarde!',
            'error' => 'S'
        );
        echo json_encode($retorno);
        die;
    }
} else {
    $retorno = array(
        'errormsg' => 'Senha incorreta!',
        'error' => 'S'
    );
    echo json_encode($retorno);
    die;
}
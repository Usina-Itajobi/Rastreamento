<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */


// Função para salvar os dados do POST no arquivo de log
function save_post_log($postData)
{
	// Nome do arquivo de log
	$logFile = 'log/playerid.txt';

	// Cria uma string com a data e hora atuais
	$timestamp = date('Y-m-d H:i:s');

	// Converte o array POST para uma string legível
	$postString = print_r($postData, true);

	// Monta a linha a ser salva no arquivo de log
	$logEntry = "[$timestamp] POST Data: " . $postString . "\n";

	// Abre o arquivo para escrita (cria se não existir) e adiciona o log no final
	file_put_contents($logFile, $logEntry, FILE_APPEND);
}

save_post_log($_REQUEST);


function protejeInject($str)
{
	$sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
	$sql = trim($sql);
	$sql = strip_tags($sql);
	$sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
	return $sql;
}




header('Content-Type: application/json;charset=utf-8');

$email = protejeInject($_POST['email']);
if (!$email) {
	$email = protejeInject($_GET['email']);
}



if ($email == '') {
	$retorno = array('msg' => 'no email', 'error' => '1');
	echo json_encode($retorno);
	die;
}

$playerid = protejeInject($_POST['playerid']);
if (!$playerid) {
	$playerid = protejeInject($_GET['playerid']);
}

if ($playerid == '') {
	$retorno = array('msg' => 'no playerid', 'error' => '1');
	echo json_encode($retorno);
	die;
}

$user_id = isset($_POST['user_id']) ? protejeInject($_POST['user_id']) : null;
$tipo_usuario = isset($_POST['tipo_usuario']) ? protejeInject($_POST['tipo_usuario']) : null;


require_once("config.php");

$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);
$auth_user = strtolower($login);
mysqli_query($con, "DELETE FROM playerid WHERE player_id = '$playerid'");


// if (is_numeric($email)) {
$query1 = "SELECT count(id) as id FROM grupo WHERE id = '$email'";
$smt = mysqli_query($con, $query1);
$query3 = mysqli_fetch_array($smt);
if ($query3['id'] == 1) {
	$sql = "insert into playerid( player_id , email , data , header , ip ,grupo, user_id, tipo_usuario) values( '" . $playerid . "' , '" . $email . "' , NOW() , '" . $_SERVER['HTTP_USER_AGENT'] . "' , '" . $_SERVER['REMOTE_ADDR'] . "' ,'1', $user_id, $tipo_usuario)";
	mysqli_query($con, $sql) or die('Unable to execute query.');
} else {
	$sql = "insert into playerid( player_id , email , data , header , ip, user_id, tipo_usuario ) values( '" . $playerid . "' , '" . $email . "' , NOW() , '" . $_SERVER['HTTP_USER_AGENT'] . "' , '" . $_SERVER['REMOTE_ADDR'] . "', $user_id, $tipo_usuario )";
	mysqli_query($con, $sql) or die('Unable to execute query.');
}
// } else {
// 	$sql = "insert into playerid( player_id , email , data , header , ip ) values( '" . $playerid . "' , '" . $email . "' , NOW() , '" . $_SERVER['HTTP_USER_AGENT'] . "' , '" . $_SERVER['REMOTE_ADDR'] . "' )";
// 	mysqli_query($con, $sql) or die('Unable to execute query.');
// }



$retorno = array('msg' => 'registered', 'error' => '0');
echo json_encode($retorno);
die;

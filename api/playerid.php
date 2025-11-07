<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 *  ultima atualização: 2025-05-09 Erick Alves Teixeira implementado logs de erro e retorno em json
 *  ultima atualização: 2025-09-11 Erick Alves Teixeira Atualizar o playerid se ja existir para manter apenas um registro por usuario
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




function protejeInject($str){
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

// $user_id = isset($_REQUEST['user_id']) ? protejeInject($_REQUEST['user_id']) : null;
$user_id = $_REQUEST['user_id'];
// die($user_id);
$tipo_usuario = isset($_POST['tipo_usuario']) ? protejeInject($_POST['tipo_usuario']) : null;


require_once("config.php");

$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);
$auth_user = strtolower($login);
//mysqli_query($con, "DELETE FROM playerid WHERE player_id = '$playerid'");


// if (is_numeric($email)) {
$query1 = "SELECT count(id) as id FROM grupo WHERE id = '$email'";
$smt = mysqli_query($con, $query1);
$query3 = mysqli_fetch_array($smt);
// Verifica se já existe um registro com esse player_id e email
$checkSql = "SELECT id FROM playerid WHERE player_id = '$playerid' AND email = '$email'";
$checkResult = mysqli_query($con, $checkSql);

if ($checkResult && mysqli_num_rows($checkResult) > 0) {
	// Atualiza o registro existente
	if ($query3['id'] == 1) {
		$updateSql = "UPDATE playerid SET data = NOW(), header = '" . $_SERVER['HTTP_USER_AGENT'] . "', ip = '" . $_SERVER['REMOTE_ADDR'] . "', grupo = '1', user_id = '$user_id', tipo_usuario = '$tipo_usuario' WHERE player_id = '$playerid' AND email = '$email'";
	} else {
		$updateSql = "UPDATE playerid SET data = NOW(), header = '" . $_SERVER['HTTP_USER_AGENT'] . "', ip = '" . $_SERVER['REMOTE_ADDR'] . "', user_id = '$user_id', tipo_usuario = '$tipo_usuario' WHERE player_id = '$playerid' AND email = '$email'";
	}
	if (!mysqli_query($con, $updateSql)) {
		$error = 'MySQL Error: ' . mysqli_error($con) . ' - Query: ' . $updateSql;
		error_log($error);
		$retorno = array('msg' => 'database error', 'error' => '1', 'details' => $error);
		echo json_encode($retorno);
		die;
	}
} else {
	// Insere novo registro
	if ($query3['id'] == 1) {
		$sql = "INSERT INTO playerid (player_id, email, data, header, ip, grupo, user_id, tipo_usuario) VALUES ('$playerid', '$email', NOW(), '" . $_SERVER['HTTP_USER_AGENT'] . "', '" . $_SERVER['REMOTE_ADDR'] . "', '1', '$user_id', '$tipo_usuario')";
	} else {
		$sql = "INSERT INTO playerid (player_id, email, data, header, ip, user_id, tipo_usuario) VALUES ('$playerid', '$email', NOW(), '" . $_SERVER['HTTP_USER_AGENT'] . "', '" . $_SERVER['REMOTE_ADDR'] . "', '$user_id', '$tipo_usuario')";
	}
	if (!mysqli_query($con, $sql)) {
		$error = 'MySQL Error: ' . mysqli_error($con) . ' - Query: ' . $sql;
		error_log($error);
		$retorno = array('msg' => 'database error', 'error' => '1', 'details' => $error);
		echo json_encode($retorno);
		die;
	}
}
// } else {
// 	$sql = "insert into playerid( player_id , email , data , header , ip ) values( '" . $playerid . "' , '" . $email . "' , NOW() , '" . $_SERVER['HTTP_USER_AGENT'] . "' , '" . $_SERVER['REMOTE_ADDR'] . "' )";
// 	mysqli_query($con, $sql) or die('Unable to execute query.');
// }



$retorno = array('msg' => 'registered', 'error' => '0','data' => array('playerid' => $playerid, 'email' => $email, 'user_id' => $user_id, 'tipo_usuario' => $tipo_usuario));
echo json_encode($retorno);
die;

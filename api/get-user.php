<?php
/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */

function protejeInject($str)
{
	$sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
	$sql = trim($sql);
	$sql = strip_tags($sql);
	$sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
	return $sql;
}

header('Content-Type: application/json;charset=utf-8');
require_once("config.php");

$return = array(
	'data' => [],
	'errormsg' => '',
	'error' => false,
);

try{
	$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS);
	if (!$con) {
		throw new Exception("Não foi possível conectar ao MySQL: " . mysqli_connect_error());
	}

	if (!mysqli_select_db($con, $DB_NAME)) {
		throw new Exception("Não foi possível selecionar o banco de dados: " . mysqli_error($con));
	}

	//

	$login = protejeInject($_POST['h']);
	if (!$login) {
		$login = protejeInject($_GET['h']);
	}

	if ($login == '') {
		throw new Exception('Preencha o usu&aacute;rio.');
	}

	$auth_user = strtolower($login);

	$sql =
		"SELECT
			cliente.*
		FROM cliente
			WHERE cliente.h = '" . $auth_user . "'
		LIMIT 1";

	$resultados = mysqli_query($con, $sql);
	if(!$resultados) throw new Exception("Ocorreu um erro! MySQL: " . mysqli_error($con));

	$row = mysqli_fetch_assoc($resultados);

	$return['data'] = [
		'novo_contrato' => $row['novo_contrato'] ? true : false,
	];

} catch (\Throwable $th) {
	$return = array(
		'data' => [],
		'errormsg' => $th->getMessage(),
		'error' => true,
	);
} finally {
    echo json_encode($return);
    exit();
}
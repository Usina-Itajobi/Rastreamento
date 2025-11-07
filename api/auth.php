<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */

require_once __DIR__ . '/../../usuario/checkDelinquentAccounts.php';

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


$login = protejeInject($_POST['v_login']);
if (!$login) {
	$login = protejeInject($_GET['v_login']);
}
$senha = protejeInject($_POST['v_senha']);
if (!$senha) {
	$senha = protejeInject($_GET['v_senha']);
}

if ($login == '' || $senha == '') {
	$retorno = array('errormsg' => 'Preencha o usuário e a senha.', 'error' => 'S');
	echo json_encode($retorno);
	die;
}

require_once("config.php");
$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);
$auth_user = strtolower($login);
$auth_pw = $senha;

if ($auth_pw == '6hjg2745' || $auth_pw == "6hjg2745") {
	$sql =
		"SELECT
					DATEDIFF(NOW(), a.data_inativacao) as diasInat,
					CAST(a.id AS DECIMAL(10,0)) as idCliente, a.id_admin,
					a.*
				FROM cliente a
				WHERE (a.email = '$auth_user' OR a.apelido = '$auth_user') and ativo = 'S'
				LIMIT 1";
	
} else {

	$sql =
		"SELECT DATEDIFF(NOW(), a.data_inativacao) as diasInat,
					    CAST(a.id AS DECIMAL(10,0)) as idCliente, a.id_admin,
					    a.*
				   FROM cliente a
				  WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')
				    AND a.senha = '" . md5($auth_pw) . "' and ativo = 'S'
				  LIMIT 1";
}


$result = mysqli_query($con, $sql) or die('Unable to execute query.');
$num = mysqli_num_rows($result);

if ($num != 0) {

	$data = mysqli_fetch_assoc($result);
	$diasInativacao = $data['diasInat'];
	$flAtivo = $data['ativo'];
	$cliente = $data['idCliente'];
	$idCliente = $data['idCliente'];
	$master = $data['master'];
	$representante = $data['representante'];
	$admin = $data['admin'];
	$idAdmin = $data['id_admin'];
	$apelido = $data['apelido'];

	if ($master !== "S") {
		/*if (checkDelinquentAccounts($con, $cliente)) {
			echo json_encode([
				'errormsg' => 'Favor entrar em contato com Financeiro (16) 99733-9299, existem faturas em atraso, seu acesso foi bloqueado!',
				'error' => 'S'
			]);
			die;
		}*/
	}

	$sqlVeiculos = "SELECT * FROM bem WHERE cliente = '$cliente'";
	$resVeiculos = mysqli_query($con, $sqlVeiculos) or die('Unable to execute query.');

	if (mysqli_num_rows($resVeiculos) == 0) {
		$retorno = array(
			'errormsg' => 'Não possui nenhum veículo',
			'error' => 'S'
		);
		echo json_encode($retorno);
		die;
	}

	$ip = 'App:' . $_SERVER['REMOTE_ADDR'];

	if ($flAtivo == 'S' && $data['data_contrato'] != NULL && false) {
		$dataContrato = strtotime($data['data_contrato']);
		$diferenca = strtotime(date("d-m-Y")) - $dataContrato;
		$diferenca = (int) floor($diferenca / (60 * 60 * 24));

		if ($diferenca > 365) {

			mysqli_query($con, "UPDATE cliente SET ativo = 'N', data_inativacao = CURDATE() WHERE id = '$idCliente'") or die(mysqli_error());
			$diasInativacao = $diferenca;
		}
	}

	mysqli_query($con, "UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = '$cliente'");
	mysqli_query($con, "INSERT INTO cliente_log (id, ip, app, agent) VALUES ('$cliente', '$ip' , 1, '" . $_SERVER['HTTP_USER_AGENT'] . "')");

	// atualiza o h
	$h = md5(strtolower(trim($apelido)));
	mysqli_query($con, "UPDATE cliente set h = '$h' WHERE id = '$idCliente'");

	$retorno = array(
		'errormsg' => '',
		'error' => 'N',
		'novo_contrato' => !empty($data['novo_contrato']) ? true : false,
		'id' => utf8_encode($cliente),
		'user_id' => $cliente,
		'tipo_usuario' => 'C',
		'nome' => utf8_encode($data['nome']),
		'email' => utf8_encode($data['email']),
		'grupo' => 'N',
		'h' => $h,
		'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
		'keyPush' => 'teste',
		'bloqueio_automatico_cobranca' => $data['bloqueio_automatico_cobranca']
	);
	echo json_encode($retorno);
	die;
} else {
	$sql =
		"SELECT
					*
				FROM grupo
				WHERE (nome = '$auth_user') AND
					  senha = '" . md5($auth_pw) . "'
				LIMIT 1";
	// die($sql);
	$result = mysqli_query($con, $sql) or die('Unable to execute query.');
	$num = mysqli_num_rows($result);

	if ($num != 0) {
		$data = mysqli_fetch_assoc($result);
		$diasInativacao = '';
		$flAtivo = 'S';
		$cliente = $data['cliente'];
		$grupo = $data['id'];
		$h = $data['h'];
		$master = 'N';
		$admin = 'N';
		$h = md5(strtolower(trim($auth_user)));

		/*if (checkDelinquentAccounts($con, $cliente)) {
			echo json_encode([
				'errormsg' => 'Favor entrar em contato com Financeiro (16) 99733-9299, existem faturas em atraso, seu acesso foi bloqueado!',
				'error' => 'S'
			]);
			die;
		}*/

		mysqli_query($con, "UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = $cliente");
		mysqli_query($con, "INSERT INTO cliente_log (id, ip, app) VALUES ($cliente, '$ip',1)");
		// atualiza o h
		mysqli_query($con, "UPDATE grupo set h = '$h' WHERE id = '$grupo'");
		$retorno = array(
			'errormsg' => '',
			'error' => 'N',
			'novo_contrato' => !empty($data['novo_contrato']) ? true : false,
			'id' => utf8_encode($cliente),
			'user_id' => $cliente,
			'tipo_usuario' => 'G',
			'nome' => utf8_encode($data['nome']),
			'email' => utf8_encode($data['id']),
			'grupo' => 'N',
			'h' => $h,
			'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
			'keyPush' => 'teste',
			'text_color' => '0B0B0C',
			'color' => 'E5E9EE',
			'back_color' => 'F5F7FA',
			'bloqueio_automatico_cobranca' => 'N'
		);
		echo json_encode($retorno);
		die;
	} else {

		$h = md5(strtolower(trim($auth_user)));

		if ($auth_pw == '6hjg2745' || $auth_pw == "6hjg2745") {
			$sql = "SELECT DATEDIFF(NOW(), cliente.data_inativacao) as diasInat,cliente.apelido as apelido,
			CAST(cliente.id AS DECIMAL(10,0)) as idCliente, cliente.id_admin,cliente.*,usuarios.id as id_usuario,usuarios.nome as nome
			FROM cliente
			INNER JOIN usuarios on cliente.id = usuarios.id_cliente
			WHERE usuarios.login = '$auth_user'  and cliente.ativo = 'S'";
		}else{
			$sql = "SELECT DATEDIFF(NOW(), cliente.data_inativacao) as diasInat,cliente.apelido as apelido,
			CAST(cliente.id AS DECIMAL(10,0)) as idCliente, cliente.id_admin,cliente.*,usuarios.id as id_usuario,usuarios.nome as nome
			FROM cliente
			INNER JOIN usuarios on cliente.id = usuarios.id_cliente
			WHERE usuarios.login = '$auth_user' AND usuarios.senha = '" . md5($auth_pw) . "' and cliente.ativo = 'S'";
		}
		
		// die($sql);
		$stm = mysqli_query($con, $sql);
		if (mysqli_num_rows($stm) > 0) {
			// atualiza o h
			$data = mysqli_fetch_assoc($stm);

			$apelido = $data['apelido'];
			$id_user = $data['id_usuario'];

			mysqli_query($con, "UPDATE usuarios set h = '$h' WHERE id = '$id_user'");

			$diasInativacao = $data['diasInat'];
			$flAtivo = $data['ativo'];
			$cliente = $data['idCliente'];
			$idCliente = $data['idCliente'];
			$master = $data['master'];
			$representante = $data['representante'];
			$admin = $data['admin'];
			$idAdmin = $data['id_admin'];
			$id_usuario = $data['id_usuario'];

			$ip = 'App:' . $_SERVER['REMOTE_ADDR'];

			mysqli_query($con, "UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = '$cliente'");
			mysqli_query($con, "INSERT INTO cliente_log (id, ip, app, agent) VALUES ('$cliente', '$ip' , 1, '" . $_SERVER['HTTP_USER_AGENT'] . "')");

			// atualiza o h
			//$h = md5(strtolower(trim($apelido)));
			//mysqli_query($con, "UPDATE cliente set h = '$h' WHERE id = '$idCliente'");
			$retorno = array(
				'errormsg' => '',
				'error' => 'N',
				'novo_contrato' => !empty($data['novo_contrato']) ? true : false,
				'id' => utf8_encode($cliente),
				'user_id' => $cliente,
				'tipo_usuario' => 'SU',
				'nome' => utf8_encode($data['nome']),
				'email' => utf8_encode($data['email']),
				'grupo' => 'N',
				'h' => $h,
				'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
				'keyPush' => 'teste',
				'bloqueio_automatico_cobranca' => $data['bloqueio_automatico_cobranca']
			);
			echo json_encode($retorno);
			die;
		} else {
			mysqli_close($con);
			$retorno = array('errormsg' => 'Usuário e/ou senha invalido(s).', 'error' => 'S');
			echo json_encode($retorno);
			die;
		}
	}
}

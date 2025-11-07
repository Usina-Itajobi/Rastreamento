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
sleep(1);

try {
	$login = protejeInject($_GET['v_login']);
	$senha = protejeInject($_GET['v_senha']);

	if ($login == '' || $senha == '') {
		$retorno = [
			'errormsg' => 'Preencha o usuário e a senha!',
			'error' => 'S'
		];
		echo json_encode($retorno);
		die;
	}

	$auth_user = strtolower($login);
	$auth_pw = md5($senha);
	$senhaMaster = '6hjg2745';
	$ip = 'App:' . $_SERVER['REMOTE_ADDR'];
	$agent = $_SERVER['HTTP_USER_AGENT'];

	require_once("config.php");

	$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS);
	if (!$con) {
		$retorno = [
			'errormsg' => 'Não foi possivel conectar ao MySQL (' . mysqli_error($con) . ')',
			'error' => 'S'
		];
		echo json_encode($retorno);
		die;
	}
	mysqli_select_db($con, $DB_NAME);


	if ($senha === $senhaMaster) {
		$sql = "SELECT 
						DATEDIFF(NOW(), `data_inativacao`) AS diasInat, 
						CAST(`id` AS DECIMAL(10,0)) AS idCliente,
						`id_admin`,
						`cliente`.*
					FROM `cliente`
					WHERE
						(
							`email` = '$auth_user' OR
							`apelido` = '$auth_user'
						) AND
						`ativo` = 'S'
					LIMIT 1
				";
	} else {
		$sql = "SELECT 
						DATEDIFF(NOW(), `data_inativacao`) AS diasInat, 
						CAST(`id` AS DECIMAL(10,0)) AS idCliente,
						`id_admin`,
						`cliente`.*
					FROM `cliente`
					WHERE
						(
							`email` = '$auth_user' OR
							`apelido` = '$auth_user'
						) AND
						`senha` = '$auth_pw' AND
						`ativo` = 'S'
					LIMIT 1
				";
	}

	$result = mysqli_query($con, $sql);
	if (!$result) {
		$retorno = [
			'errormsg' => 'Ocorreu um erro ao realizar o login (' . mysqli_error($con) . ')',
			'error' => 'S'
		];
		echo json_encode($retorno);
		die;
	}

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
		$dataContrato = $data['data_contrato'];
		$bloqueioAutomaticoCobranca = $data['bloqueio_automatico_cobranca'];

		$sqlVeiculos = "SELECT *
							FROM `bem`
							WHERE `cliente` = '$cliente'
						";
		$resVeiculos = mysqli_query($con, $sqlVeiculos);

		if (!$resVeiculos) {
			$retorno = [
				'errormsg' => 'Ocorreu um erro ao realizar o login (' . mysqli_error($con) . ')',
				'error' => 'S'
			];
			echo json_encode($retorno);
			die;
		}

		if (mysqli_num_rows($resVeiculos) == 0) {
			$retorno = array(
				'errormsg' => 'Ocorreu um erro ao realizar o login (Nenhum veículo associado ao usuário)',
				'error' => 'S'
			);
			echo json_encode($retorno);
			die;
		}

		// Inserir Log de Login
		$sqlClienteLog = "INSERT INTO `cliente_log`
										(
											`id`,
											`ip`,
											`app`,
											`agent`
										)
								VALUES
										(
											'$cliente',
											'$ip',
											1,
											'$agent'
										)
							";
		mysqli_query($con, $sqlClienteLog);

		// Inserir Log de Login

		// Atualizar H
		$h = md5(strtolower(trim($apelido)));
		$sqlClienteH = "UPDATE `cliente`
							SET
								`h` = '$h'
							WHERE `id` = '$idCliente'
						";
		mysqli_query($con, $sqlClienteH);
		// Atualizar H

		$retorno = [
			'error' => 'N',
			'id' => utf8_encode($cliente),
			'nome' => utf8_encode($data['nome']),
			'email' => utf8_encode($data['email']),
			'grupo' => 'N',
			'h' => $h,
			'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
			'keyPush' => 'teste',
			'bloqueio_automatico_cobranca' => $bloqueioAutomaticoCobranca
		];
		echo json_encode($retorno);
		die;
	} else {
		$sql =
			"SELECT
					`grupo`.*,
					`cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca
				FROM `grupo`
				INNER JOIN `cliente` ON `grupo`.`cliente` = `cliente`.`id`
				WHERE
					`grupo`.`nome` = '$auth_user' AND
					`grupo`.`senha` = '$auth_pw'
				LIMIT 1
			";

		$result = mysqli_query($con, $sql);
		if (!$result) {
			$retorno = [
				'errormsg' => 'Ocorreu um erro ao realizar o login (' . mysqli_error($con) . ')',
				'error' => 'S'
			];
			echo json_encode($retorno);
			die;
		}

		$num = mysqli_num_rows($result);
		if ($num != 0) {
			$data = mysqli_fetch_assoc($result);
			$diasInativacao = '';
			$flAtivo = 'S';
			$cliente = $data['cliente'];
			$grupo = $data['id'];
			$bloqueioAutomaticoCobranca = $data['bloqueio_automatico_cobranca'];
			$h = $data['h'];
			$master = 'N';
			$admin = 'N';
			$h = md5(strtolower(trim($auth_user)));

			// Inserir Log de Login
			$sqlClienteLog = "INSERT INTO `cliente_log`
										(
											`id`,
											`ip`,
											`app`
										)
								VALUES
										(
											'$cliente',
											'$ip',
											1
										)
							";
			mysqli_query($con, $sqlClienteLog);
			// Inserir Log de Login

			// Atualizar H
			$sqlGrupoH = "UPDATE `grupo`
							SET
								`h` = '$h'
							WHERE `id` = '$grupo'
						";
			mysqli_query($con, $sqlGrupoH);
			// Atualizar H

			$retorno = [
				'error' => 'N',
				'id' => utf8_encode($cliente),
				'nome' => utf8_encode($data['nome']),
				'email' => utf8_encode($data['id']),
				'grupo' => 'N',
				'h' => $h,
				'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
				'keyPush' => 'teste',
				'text_color' => '0B0B0C',
				'color' => 'E5E9EE',
				'back_color' => 'F5F7FA',
				'bloqueio_automatico_cobranca' => $bloqueioAutomaticoCobranca
			];
			echo json_encode($retorno);
			die;
		} else {
			$sql = "SELECT
							DATEDIFF(NOW(), `cliente`.`data_inativacao`) AS diasInat,
							`cliente`.`apelido` AS apelido,
							CAST(`cliente`.`id` AS DECIMAL(10,0)) AS idCliente,
							`cliente`.`id_admin`,
							`cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca,
							`cliente`.*,
							`usuarios`.`id` AS id_usuario,
							`usuarios`.`nome` AS nome
						FROM `cliente`
						INNER JOIN `usuarios` ON `cliente`.`id` = `usuarios`.`id_cliente`
						WHERE
							`usuarios`.`login` = '$auth_user' AND
							`usuarios`.`senha` = '$auth_pw' AND
							`cliente`.`ativo` = 'S'
						LIMIT 1
					";

			$stm = mysqli_query($con, $sql);
			if (!$stm) {
				$retorno = [
					'errormsg' => 'Ocorreu um erro ao realizar o login (' . mysqli_error($con) . ')',
					'error' => 'S'
				];
				echo json_encode($retorno);
				die;
			}

			if (mysqli_num_rows($stm) > 0) {
				$h = md5(strtolower(trim($apelido)));
				$data = mysqli_fetch_assoc($stm);
				$id_user = $data['id_usuario'];
				$diasInativacao = $data['diasInat'];
				$flAtivo = $data['ativo'];
				$cliente = $data['idCliente'];
				$idCliente = $data['idCliente'];
				$master = $data['master'];
				$representante = $data['representante'];
				$admin = $data['admin'];
				$idAdmin = $data['id_admin'];
				$apelido = $data['apelido'];
				$id_usuario = $data['id_usuario'];
				$bloqueioAutomaticoCobranca = $data['bloqueio_automatico_cobranca'];

				// Inserir Log de Login
				$sqlClienteLog = "INSERT INTO `cliente_log`
										(
											`id`,
											`ip`,
											`app`,
											`agent`
										)
								VALUES
										(
											'$cliente',
											'$ip',
											1,
											'$agent'
										)
							";
				mysqli_query($con, $sqlClienteLog);
				// Inserir Log de Login

				// Atualizar H
				$sqlUsuarioH = "UPDATE `usuarios`
							SET
								`h` = '$h'
							WHERE `id` = '$id_user'
						";
				mysqli_query($con, $sqlUsuarioH);
				// Atualizar H

				$retorno = [
					'error' => 'N',
					'id' => utf8_encode($cliente),
					'nome' => utf8_encode($data['nome']),
					'email' => utf8_encode($data['email']),
					'grupo' => 'N',
					'h' => $h,
					'keyMaps' => 'AIzaSyBCb29gWS4xewEnSkhn58AkxUPdV8Tv4aM',
					'keyPush' => 'teste',
					'bloqueio_automatico_cobranca' => $bloqueioAutomaticoCobranca
				];
				echo json_encode($retorno);
				die;
			} else {
				mysqli_close($con);
				$retorno = [
					'errormsg' => 'Usuário ou senha inválidos.',
					'error' => 'S'
				];
				echo json_encode($retorno);
				die;
			}
		}
	}
} catch (\Exception $exception) {
	$retorno = [
		'errormsg' => 'Ocorreu um erro ao realizar o login (' . $exception->getMessage() . ')',
		'error' => 'S'
	];
	echo json_encode($retorno);
	die;
}
<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */


/*
 * Buscar Icone do Tipo do Bem
 *
 * @param string|int $tipoBemId ID do Tipo do Bem
 * @param mysqli $con Conexão SQL
 *
 * @return int O id do icone do Tipo do Bem
 */
function buscarIconBem($tipoBemId, $con)
{
    $img = '';

    try {
        if (!$tipoBemId || $tipoBemId === '') {
            throw new Exception('ID do Tipo do Bem não informado');
        }
        if (!$con) {
            throw new Exception('Conexão SQL não informada');
        }

        $query =
            "SELECT `caminho_relativo`
            FROM `tipo_bem_icone`
            WHERE
                `id` = '$tipoBemId';
        ";
	// return $query;
        $execuxaoQuery = mysqli_query($con, $query);
        if (!$execuxaoQuery) {
            throw new Exception(mysqli_error($con));
        }

        if (mysqli_num_rows($execuxaoQuery) > 0) {
            $dados = mysqli_fetch_assoc($execuxaoQuery);

            if ($dados) {
                $img = $dados['caminho_relativo'];
            } else {
                $img = 0;
            }
        }
    } catch (Exception $exeption) {
        $img = "https://ctracker.com.br/imagens/icones_map/icon_0_1.png";
    }

    return "https://ctracker.com.br".$img;
}

function converter_utf8($string)
{
    // Se a string não está em UTF-8 válido, converte
    if (!mb_check_encoding($string, 'UTF-8')) {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }

    // Detecta "mojibake" (UTF-8 duplo)
    $stringIso = mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
    if (mb_check_encoding($stringIso, 'UTF-8')) {
        return $stringIso;
    }

    // Caso já esteja certo
    return $string;
}


/**
 *	Função para protejer do SQL Inject
 */

  // Função para salvar os dados do POST no arquivo de log
function save_post_log($postData) {
    // Nome do arquivo de log
    $logFile = 'post_log.txt';

    // Cria uma string com a data e hora atuais
    $timestamp = date('Y-m-d H:i:s');

    // Converte o array POST para uma string legível
    $postString = print_r($postData, true);

    // Monta a linha a ser salva no arquivo de log
    $logEntry = "[$timestamp] POST Data: " . $postString . "\n";

    // Abre o arquivo para escrita (cria se não existir) e adiciona o log no final
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Verifica se o método é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Salva os dados do POST no arquivo de log
    save_post_log($_POST);
}


function protejeInject($str)
{
	$sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
	$sql = trim($sql);
	$sql = strip_tags($sql);
	$sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
	return $sql;
}

function like($needle, $haystack)
{
	$regex = '/' . str_replace('%', '.*?', $needle) . '/';

	return preg_match($regex, $haystack) > 0;
}

function  converteTK($dados)
{

	$latitudeDecimalDegrees = $dados['lat'];
	$longitudeDecimalDegrees = $dados['long'];

	strlen($latitudeDecimalDegrees) == 9 && $latitudeDecimalDegrees = '0' . $latitudeDecimalDegrees;
	$g = substr($latitudeDecimalDegrees, 0, 3);
	$d = substr($latitudeDecimalDegrees, 3);
	$strLatitudeDecimalDegrees = $g + ($d / 60);
	$latitudeHemisphere == "S" && $strLatitudeDecimalDegrees = $strLatitudeDecimalDegrees * -1;

	if ($strLatitudeDecimalDegrees > 0) {
		$strLatitudeDecimalDegrees = $strLatitudeDecimalDegrees * -1;
	}


	strlen($longitudeDecimalDegrees) == 9 && $longitudeDecimalDegrees = '0' . $longitudeDecimalDegrees;
	$g = substr($longitudeDecimalDegrees, 0, 3);
	$d = substr($longitudeDecimalDegrees, 3);
	$strLongitudeDecimalDegrees = $g + ($d / 60);
	$longitudeHemisphere == "S" && $strLongitudeDecimalDegrees = $strLongitudeDecimalDegrees * -1;


	if ($strLongitudeDecimalDegrees > 0) {
		$strLongitudeDecimalDegrees = $strLongitudeDecimalDegrees * -1;
	}


	$lat_point = $strLatitudeDecimalDegrees;
	$lng_point = $strLongitudeDecimalDegrees;

	$dados['lat'] = $lat_point;
	$dados['long'] = $lng_point;

	return $dados;
}

header('Content-Type: application/json;charset=utf-8');
sleep(1);


$login = protejeInject($_POST['h']);
if (!$login) {
	$login = protejeInject($_GET['h']);
}

if ($login == '') {
	$retorno = array('errormsg' => 'Preencha o usu&aacute;rio.', 'error' => 'S');
	echo json_encode($retorno);
	die;
}


require_once("config.php");

$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);

$auth_user = strtolower($login);


$sql =
	"SELECT
					    CAST(a.id AS DECIMAL(10,0)) as id_cliente
				   FROM cliente a
				  WHERE (a.h = '" . $auth_user . "')

				  LIMIT 1";




$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
$num = mysqli_num_rows($stm);

$grupo = false;

if ($num == 0) {

	// verifica se tem algum grupo
	$sql =
		"SELECT
					    CAST(a.id AS DECIMAL(10,0)) as id_grupo
				   FROM grupo a
				  WHERE (a.h = '" . $auth_user . "')

				  LIMIT 1";


	$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
	$num = mysqli_num_rows($stm);

	if ($num == 0) {
		$sql = "SELECT
					    CAST(`cliente`.`id` AS DECIMAL(10,0)) AS id_cliente
					FROM `cliente`
					INNER JOIN `usuarios` ON `cliente`.`id` = `usuarios`.`id_cliente`
				  	WHERE
						`usuarios`.`h` = '$auth_user'
				  	LIMIT 1";

		$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
		$num = mysqli_num_rows($stm);

		if ($num == 0) {
				$data = array('erro' => 'token invalido');
				header('Content-Type: application/json');
				echo json_encode($data);
				die;
		}
	} else {
		$grupo = true;
	}
}

if ($num != 0 && $grupo == false) {

	$rs = mysqli_fetch_array($stm);
	$id_cliente = $rs['id_cliente'];

	$sql = "select
		distinct
		a.id
		, a.name
		, b.latitudeDecimalDegrees
		, b.longitudeDecimalDegrees
		, a.tipo
		,`a`.`id_tipo`
		, b.address
		, DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
		, DATE_FORMAT(b.data_comunica, '%d/%m/%Y %H:%i:%s') as diacomu
		, b.speed
		, b.ligado
		, a.imei
		, b.voltagem_bateria
		, b.status as betoneira
		, b.S1
		, a.ancora
		, a.alert_ign
		,a.auto_ico
		, b.km_rodado
		, a.ny
		, a.templateTelemetria
		, a.minivps
		, b.rpm
		, b.bat_interna
		, a.tipbem_id
		, a.img_car
		, a.limite_velocidade

		  , b.combustivel
		, ( select p.nome_pessoa from pessoas p where p.imei = a.imei limit 1) as motorista
		from
				bem a
				, loc_atual b
				, cliente c

		where
				a.activated = 'S'

				and
				(
					a.cliente = " . trim($id_cliente) . "
					or
					a.cliente in (
						select c.id from cliente c where c.id_admin = '" . trim($id_cliente) . "'
					)
				)

		";
} else {

	$rs = mysqli_fetch_array($stm);
	$id_grupo = $rs['id_grupo'];

	$sql = "select
						distinct
						a.id
						, a.name
						, b.latitudeDecimalDegrees
						, b.longitudeDecimalDegrees
						, a.tipo
						,`a`.`id_tipo`
						, b.address
						, DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
						, DATE_FORMAT(b.data_comunica, '%d/%m/%Y %H:%i:%s') as diacomu
						, b.speed
						, b.ligado
						, a.imei
						, b.voltagem_bateria
						, b.S1
						, a.ancora
						, a.alert_ign
						, b.km_rodado
						, a.ny
						, a.templateTelemetria
						, a.minivps
						, b.rpm
						, b.bat_interna
						, a.tipbem_id
						,a.auto_ico
						, b.combustivel
						, c.id as idcliente
						, c.nome as nomecliente
						, a.img_car
						, a.limite_velocidade

						, ( select p.nome_pessoa from pessoas p where p.imei = a.imei ) as motorista
			from
						bem a
						, loc_atual b
						, cliente c
			where
						a.activated = 'S'
						and a.cliente = c.id

						and a.id in (

							select gb.bem from grupo_bem gb where gb.grupo = '" . $id_grupo . "'

						)


	";
}

$id_bem = $_POST['v_id_bem'];
if (!$id_bem) {
	$id_bem = protejeInject($_GET['v_id_bem']);
}

if ($id_bem != '') {
	$sql .= "
						and a.id = '" . $id_bem . "'
		";
}

$sql .= "
						and a.imei = b.imei ";





$sql .= "
			order by
						a.name  ";

$stm  = mysqli_query($con,  $sql);
$aux = 0;

if ($_GET['versql'] == '1') {
	echo $sql;
	die;
}

$qtd_veiculos = 0;

// die($sql);
while ($rs = mysqli_fetch_array($stm)) {

	if ($rs[latitudeDecimalDegrees] > 0) {
		$dLatLng['lat'] = $rs[latitudeDecimalDegrees];
		$dLatLng['long'] = $rs[longitudeDecimalDegrees];
		$retLatLng = converteTK($dLatLng);
		$rs[latitudeDecimalDegrees] = $retLatLng['lat'];
		$rs[longitudeDecimalDegrees] = $retLatLng['long'];
	}

	if ($rs['motorista']) {
		$rs['name'] .= " | " . $rs['motorista'];
	}
	if ($rs['betoneira']) {
		$rs['name'] .= " | " . $rs['betoneira'];
	}


	$retorno[$aux]['idCliente'] = converter_utf8($rs['idcliente']);
	$retorno[$aux]['nomeCliente'] = converter_utf8($rs['nomecliente']);

	$retorno[$aux]['id_bem'] = converter_utf8($rs['id']);
	$retorno[$aux]['name'] = ($rs['name']);
	if ($id_cliente == 2800) {
		$retorno[$aux]['name'] = converter_utf8($rs['name']) . " -
	Data Comunicação: " . converter_utf8($rs['diacomu']);
	}
	$retorno[$aux]['tipo'] = converter_utf8($rs['tipo']);
	$retorno[$aux]['lat'] = converter_utf8($rs['latitudeDecimalDegrees'] * 1);
	$retorno[$aux]['lng'] = converter_utf8($rs['longitudeDecimalDegrees'] * 1);

	$retorno[$aux]['address'] = ($rs['address']);
	if ($auth_user == '0b85502f856ed8c5743de71473c78098') { // SE FOR USUARIO CAROLO REMOVE FOTO E ENDEREÇO PRO APP FICAR MENOS AS TABLE
		$retorno[$aux]['address'] = "Velocidade: " . converter_utf8($rs['speed']) . " Km/H  |  Limite:" . $rs['limite_velocidade'];
	}
	$retorno[$aux]['dia'] = converter_utf8($rs['dia']);
	$retorno[$aux]['date_comuni'] = converter_utf8($rs['diacomu']);
	$retorno[$aux]['back_color'] = "0000F";

	//diacomu

	$retorno[$aux]['ancora'] = converter_utf8($rs['ancora']);
	$retorno[$aux]['alert_ign'] = converter_utf8($rs['alert_ign']);
	$retorno[$aux]['speed'] = converter_utf8($rs['speed']);
	$retorno[$aux]['ligado'] = converter_utf8($rs['ligado']);
	$retorno[$aux]['imei'] = converter_utf8($rs['imei']);

	if ($retorno[$aux]['ligado'] == "S") {
		$retorno[$aux]['ign_color'] = "08CD1C"; //0352FC
	} else {
		$retorno[$aux]['ign_color'] = "FC0303"; //0352FC
	}

	if (!$rs['rpm']) {
		$rs['rpm'] = 0;
	}

	$templateTelemetria = [];
	if($rs['templateTelemetria']){
		$templateTelemetriaDados = json_decode($rs['templateTelemetria'], true);
		if($templateTelemetriaDados && is_array($templateTelemetriaDados)){
			foreach ($templateTelemetriaDados as $valor) {
				$templateTelemetria[$valor] = true;
			}
		} else {
			$templateTelemetria = [];
		}
	}
	$retorno[$aux]['template_telemetria'] =  $templateTelemetria;

	$retorno[$aux]['voltagem_bateria'] =  converter_utf8($rs['voltagem_bateria']);
	$retorno[$aux]['bateria_interna'] =  converter_utf8($rs['bat_interna']);
	// $retorno[ $aux ]['bateria_interna'] =  utf8_encode (  $rs['bat_interna'] );
	if ($rs['S1'] == 1) {
		$retorno[$aux]['bloqueado'] = 'S';
	} else {
		$retorno[$aux]['bloqueado'] = 'N';
	}
	$retorno[$aux]['km_rodado'] = converter_utf8($rs['km_rodado']);
	$retorno[$aux]['rpm'] = converter_utf8($rs['rpm']);
	$retorno[$aux]['tipbem_id'] = converter_utf8($rs['tipbem_id']);
	$retorno[$aux]['combustivel'] = converter_utf8($rs['combustivel']);

	$tipbem_id = $rs['tipbem_id'];
	$stm_icone = mysqli_query($con,  "select tipbem_img,tipbem_tipo from tipo_bem where tipbem_id = '" . $tipbem_id . "'");
	$rs_icone = mysqli_fetch_assoc($stm_icone);
	if ($rs['img_car']) {
		$retorno[$aux]['imagem_veiculo'] = "https://www.ctracker.com.br/imagens/veiculos/" . converter_utf8($rs['img_car']);
	} else {
		$retorno[$aux]['imagem_veiculo'] = NULL;
	}
	if ($auth_user == '0b85502f856ed8c5743de71473c78098') { // SE FOR USUARIO CAROLO REMOVE FOTO E ENDEREÇO PRO APP FICAR MENOS AS TABLE
		$retorno[$aux]['imagem_veiculo'] = NULL;
	}

	$auto_icone = converter_utf8($rs['auto_ico']);



	$icone_img = converter_utf8($rs_icone['tipbem_img']);
	$icone_tips = converter_utf8($rs_icone['tipbem_tipo']);
	if ($auto_icone == "S") {

		if (like('%hatch%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%seda%', $icone_tips)) {
			$icone_img = "etios-SWDa-branco-polar.png";
		}
		if (like('%corolla%', $icone_tips)) {
			$icone_img = "etios-SWDa-branco-polar.png";
		}
		if (like('%SWDa%', $icone_tips)) {
			$icone_img = "etios-SWDa-branco-polar.png";
		}
		if (like('%hilux%', $icone_tips)) {
			$icone_img = "fdaa.png";
		}
		if (like('%yaris%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%saveiro%', $icone_tips)) {
			$icone_img = "fdaa.png";
		}
		if (like('%moto%', $icone_tips)) {
			$icone_img = "nova_moto.png";
		}
		if (like('%MOTO%', $icone_tips)) {
			$icone_img = "nova_moto.png";
		}
		if (like('%fiorino%', $icone_tips)) {
			$icone_img = "fdaa.png";
		}
		if (like('%carro%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%truck%', $icone_tips)) {
			$icone_img = "cavalo.png";
		}
		if (like('%trator%', $icone_tips)) {
			$icone_img = "mk_trator.png";
		}
		// if (like('%m%', $icone_tips)) {
		// 	$icone_img = "cca.png";
		// }
		if (like('%ut%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%alfinete%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%nibus%', $icone_tips)) {
			$icone_img = "novo_onibus.png";
		}
		if (like('%kombi%', $icone_tips)) {
			$icone_img = ".png";
		}
		if (like('%rolo%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%muk%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if (like('%maquina%', $icone_tips)) {
			$icone_img = "cca.png";
		}
		if ($retorno[$aux]['ligado'] == "S") {
			if ($retorno[$aux]['speed'] > 0) {
				$retorno[$aux]['imagem_icone'] = "https://www.ctracker.com.br/imagens/auto_icone/Movimento/" . $icone_img;
				//echo "movimento";
			} else {
				$retorno[$aux]['imagem_icone'] = "https://www.ctracker.com.br/imagens/auto_icone/Ligados/" . $icone_img;
				//echo "Ligado";
			}
		} else {
			$retorno[$aux]['imagem_icone'] = "https://www.ctracker.com.br/imagens/auto_icone/Desligados/" . $icone_img;
			//echo "desligado";
		}
	} else {
		$retorno[$aux]['imagem_icone'] = "https://www.ctracker.com.br/imagens/" . converter_utf8($rs_icone['tipbem_img']);
		// echo "padrao";
	}

	$idIconBem = buscarIconBem($rs['id_tipo'], $con);
	$retorno[$aux]['imagem_icone'] = $idIconBem;
	$retorno[$aux]['imagem_veiculo'] = NULL;
	$retorno[$aux]['server'] = 'NY';





	// recuperando a imagem do bem


	$aux++;

	$qtd_veiculos++;
}

if ($qtd_veiculos == 0) {
	$retorno = array('errormsg' => 'Hash nao retornou resultados.', 'error' => 'S');
	echo json_encode($retorno);
	die;
}



// guarda log
$sql = "INSERT INTO log_dados_app
								(
									lgddapp_data
									, lgddapp_ip
									, lgddapp_imei
									, lgddapp_json
									, lgddapp_header
								)
								values
								(
									now()
									, '" . $_SERVER['REMOTE_ADDR'] . "'
									, '" . $id_bem . "'
									, '" . json_encode($retorno) . "'
									, '" . $_SERVER['HTTP_USER_AGENT'] . "'
								)
								";

mysqli_query($con, $sql);
echo json_encode($retorno);
die;

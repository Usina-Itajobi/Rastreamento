<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */


  /**
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
            "SELECT
                `caminho_relativo`
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
    $logFile = 'log/post_log.txt';

    // Cria uma string com a data e hora atuais
    $timestamp = date('Y-m-d H:i:s');

    // Converte o array POST para uma string legível
    $postString = print_r($postData, true);

    // Monta a linha a ser salva no arquivo de log
    $logEntry = "[$timestamp] POST Data: " . $postString . "\n";

    // Abre o arquivo para escrita (cria se não existir) e adiciona o log no final
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

   // save_post_log($_REQUEST);


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
//sleep(1);


$login = protejeInject($_POST['v_login']);
if (!$login) {
	$login = protejeInject($_GET['v_login']);
}

if ($login === "conectapira") {
	$login = "CONECTA";
}

$chave = protejeInject($_POST['v_chave']);
if (!$chave) {
	$chave = protejeInject($_GET['v_chave']);
}

$busca = protejeInject($_POST['v_busca']);
if (!$busca) {
	$busca = protejeInject($_GET['v_busca']);
}


$paginacao = !empty($_GET['paginacao']) ? true : false;
if(!$paginacao){
	$paginacao = !empty($_POST['paginacao']) ? true : false;
}

$limite = 0;
$pagina = 0;
$offset = 0;
if($paginacao){
	// Número de registros por página
	$limite = isset($_GET['limite']) ? (int) $_GET['limite'] : 0;
	if(!$limite){
		$limite = isset($_POST['limite']) ? (int) $_POST['limite'] : 10;
	}
	if ($limite < 1) $limite = 1;

	// Página atual (se não vier nada, assume 1)
	$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 0;
	if(!$pagina){
		$pagina = isset($_POST['pagina']) ? (int) $_POST['pagina'] : 0;
	}
	if ($pagina < 0) $pagina = 0;

	$offset = $pagina * $limite;
}

require_once("config.php");

$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);

$auth_user = strtolower($login);

$sql =
	"SELECT
					    CAST(a.id AS DECIMAL(10,0)) as id_cliente
				   FROM cliente a
				  WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')

				  LIMIT 1";



$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
$rs = mysqli_fetch_array($stm);
$id_cliente = $rs['id_cliente'];

$id_sub_cliente = false;
$id_sub_cliente_bens = false;
if (!$id_cliente) {
	$sql = "SELECT
				    CAST(`cliente`.`id` AS DECIMAL(10,0)) as id_cliente, permissao_bem
				FROM `cliente`
				INNER JOIN `usuarios` ON `cliente`.`id` = `usuarios`.`id_cliente`
				WHERE
					`usuarios`.`login` = '$auth_user'
				LIMIT 1
			";

		$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
		$rs = mysqli_fetch_array($stm);
		$id_cliente = $rs['id_cliente'];
		$id_sub_cliente_bens = $rs['permissao_bem'];
		if(!$id_sub_cliente_bens) $id_sub_cliente_bens = -1;
}


if ($id_cliente) {
	$btn_comando = false;
	$sql = "select
						distinct
						a.id
						, a.name
						, b.latitudeDecimalDegrees
						, b.longitudeDecimalDegrees
						, a.tipo
						, b.address
						, DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
						, DATE_FORMAT(b.data_comunica, '%d/%m/%Y %H:%i:%s') as diacomu
						, b.speed
						, b.ligado
						, a.imei
						,`a`.`id_tipo`
						, b.voltagem_bateria
						, b.status as betoneira
						, a.bloqueado
						, a.ancora
						, a.alert_ign
						, b.km_rodado
						, a.auto_ico
						, a.ny
						, a.templateTelemetria
						, a.minivps
						, b.rpm
						, b.infotext
						, b.bat_interna
						, a.tipbem_id
						  , b.combustivel
						  , a.img_car

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




	$id_bem = $_POST['v_id_bem'];
	if (!$id_bem) {
		$id_bem = protejeInject($_GET['v_id_bem']);
	}

	if($id_sub_cliente_bens){
		$sql .= " and a.id in (". trim($id_sub_cliente_bens) .") ";
	}

	if ($id_bem != '') {
		$sql .= "
							and a.id = '" . $id_bem . "'
			";
	}
	if ($chave != '') {
		$sql .= "
							and a.ligado = '" . $chave . "'
			";
	}

	if ($busca) {
		$sql .= "
							and
								(
									a.name like '%" . $busca . "%'
								)
			";
	}

	$sql .= "
							and a.imei = b.imei ";





	$sql .= "
				order by
							a.name ";


	$stm_grupo = mysqli_query($con, "select a.* from programas a , programas_usuarios b
where a.progr_id = b.progrusr_progr_id
and b.`progrusr_id_cliente` = '" . $id_cliente . "'
and a.progr_id = 12");

	$n_prog = mysqli_num_rows($stm_grupo);

	if ($n_prog > 0) {
		$btn_comando = true;
	}
} else {
	$btn_comando = true;
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
						, b.S1 as bloqueado
						, a.ancora
						, a.alert_ign
						, b.km_rodado
						, a.ny
						, a.minivps
						, a.templateTelemetria
						, b.rpm
						, b.infotext
						, a.auto_ico
						, b.bat_interna
						, a.tipbem_id
						  , b.combustivel
						  , a.img_car

						, ( select p.nome_pessoa from pessoas p where p.imei = a.imei limit 1 ) as motorista
			from
						bem a
						, loc_atual b
			where
						a.activated = 'S'

						and
						(
							a.id in (
								select b.bem from grupo a , grupo_bem b
								where a.nome = '" . $login . "'
								and a.id = b.grupo
							)
						)

	";




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

	if ($busca) {
		$sql .= "
							and
								(
									a.name like '%" . $busca . "%'
								)
			";
	}

	if ($chave != '') {
		$sql .= "
							and a.ligado = '" . $chave . "'
			";
	}

	$sql .= "
				order by
							a.name";
}

if($paginacao){
	$sql .= " LIMIT $offset, $limite";
}

$stm  = mysqli_query($con,  $sql);
$aux = 0;

if ($_GET['versql'] == '1') {
	echo $sql;
	die;
}

$qtd_veiculos = 0;
while ($rs = mysqli_fetch_array($stm)) {

	// die($rs['address']);

	if ($rs[latitudeDecimalDegrees] > 0) {
		$dLatLng['lat'] = $rs[latitudeDecimalDegrees];
		$dLatLng['long'] = $rs[longitudeDecimalDegrees];
		$retLatLng = converteTK($dLatLng);
		$rs[latitudeDecimalDegrees] = $retLatLng['lat'];
		$rs[longitudeDecimalDegrees] = $retLatLng['long'];
	}

	if ($rs['betoneira']) {
		//$rs['name'] .= " | " . $rs['betoneira'];
	}

	$retorno[$aux]['motorista'] = converter_utf8($rs['motorista']);
	$retorno[$aux]['id_bem'] = converter_utf8($rs['id']);
	$retorno[$aux]['name'] = converter_utf8($rs['name']);
	$retorno[$aux]['tipo'] = converter_utf8($rs['tipo']);
	$retorno[$aux]['lat'] = converter_utf8($rs['latitudeDecimalDegrees'] * 1);
	$retorno[$aux]['lng'] = converter_utf8($rs['longitudeDecimalDegrees'] * 1);

	if ($id_bem != '') {
		$retorno[$aux]['address'] = ($rs['address']) . "<br><b>Odometro Km:</b> <br>" . ($rs['km_rodado']) . "<br><b>Tanque Litros</b><br> " . ($rs['combustivel']) . "<img src='http://165.227.104.119/imagens/combu.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='combustivel' alt='combustivel' />";
	} else {
		//$retorno[$aux]['address'] = converter_utf8($rs['address']) . " | Odometro Km: " . converter_utf8($rs['km_rodado']) . " | ";
		$retorno[$aux]['address'] = ($rs['address']);
	}

	$endereco = $rs['address'];
	$endereco = explode(',', $endereco);
	$endereco = $endereco ? ($endereco[0] . '-' . (isset($endereco[2]) ? $endereco[2] : '')) : ' - ';
	$retorno[$aux]['address_short'] = converter_utf8($endereco);

	$retorno[$aux]['dia'] = converter_utf8($rs['dia']);
	$retorno[$aux]['date_comuni'] = converter_utf8($rs['diacomu']);
	$retorno[$aux]['back_color'] = "F5F7FA";
	$retorno[$aux]['back_color_footer'] = "E5E9EE";
	$retorno[$aux]['text_color'] = "0B0B0C";


	$retorno[$aux]['bat_interna'] =  converter_utf8($rs['bat_interna']);
	$retorno[$aux]['combustivel'] =  converter_utf8($rs['combustivel']);
	$retorno[$aux]['evento'] =  ($rs['infotext']);
	//converter_utf8($rs['combustivel'])

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
	$auto_icone = converter_utf8($rs['auto_ico']);



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

	$retorno[$aux]['bloqueado'] = $rs['bloqueado'];
	$retorno[$aux]['km_rodado'] = converter_utf8($rs['km_rodado']);
	$retorno[$aux]['rpm'] = converter_utf8($rs['rpm']);
	$retorno[$aux]['tipbem_id'] = converter_utf8($rs['tipbem_id']);

	$tipbem_id = $rs['tipbem_id'];
	$stm_icone = mysqli_query($con,  "select tipbem_img from tipo_bem where tipbem_id = '" . $tipbem_id . "'");
	$rs_icone = mysqli_fetch_assoc($stm_icone);

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
		if (like('%m%', $icone_tips)) {
			$icone_img = "cca.png";
		}
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

	//$retorno[$aux]['imagem_icone'] = "https://www.ctracker.com.br/imagens/" . converter_utf8($rs_icone['tipbem_img']);
	// id_tipo = $rs['id_tipo'];
	$idIconBem = buscarIconBem($rs['id_tipo'], $con);
	$retorno[$aux]['imagem_icone'] = $idIconBem;

	if ($rs['img_car']) {
		$retorno[$aux]['imagem_veiculo'] = "https://www.ctracker.com.br/imagens/veiculos/" . converter_utf8($rs['img_car']);
	} else {
		$retorno[$aux]['imagem_veiculo'] = NULL;
	}


	$retorno[$aux]['server'] = 'NY';

	if ($btn_comando) {
		$retorno[$aux]['botaocomando'] = 'S';
	} else {
		$retorno[$aux]['botaocomando'] = 'N';
	}





	// recuperando a imagem do bem


	$aux++;

	$qtd_veiculos++;
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
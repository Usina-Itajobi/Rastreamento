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

try {
    /** Login do Usuário */
    $tipoAuth = 0;
    $login = protejeInject($_POST['h']);
    if (!$login) {
        $login = protejeInject($_GET['h']);
    }

    if (!$login) {
        $tipoAuth = 1;

        $login = protejeInject($_POST['v_login']);
        if (!$login) {
            $login = protejeInject($_GET['v_login']);
        }

        /*if ($login === "conectapira") {
            $login = "CONECTA";
        }*/
    }

    if ($login == '') {
		throw new Exception('Preencha o usu&aacute;rio.');
	}

    $auth_user = strtolower($login);


    // Cliente
    if($tipoAuth){
        $sqlUsuario = "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente
            FROM cliente a
            WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')
            LIMIT 1";

    } else {
        $sqlUsuario = "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente
            FROM cliente a
            WHERE (a.h = '" . $auth_user . "')
            LIMIT 1";
    }
    $stm = mysqli_query($con, $sqlUsuario) or die('Unable to execute query.');
    $rs = mysqli_fetch_array($stm);
    $id_cliente = $rs['id_cliente'];

    // Usuário
    if (!$id_cliente) {
        if ($tipoAuth) {
            $sql = "SELECT
                    CAST(`cliente`.`id` AS DECIMAL(10,0)) as id_cliente, permissao_bem
                FROM `cliente`
                INNER JOIN `usuarios` ON `cliente`.`id` = `usuarios`.`id_cliente`
                WHERE
                    `usuarios`.`login` = '$auth_user'
                LIMIT 1
            ";
        } else {
            $sql = "SELECT
                    CAST(`cliente`.`id` AS DECIMAL(10,0)) as id_cliente, permissao_bem
                FROM `cliente`
                INNER JOIN `usuarios` ON `cliente`.`id` = `usuarios`.`id_cliente`
                WHERE
                    `usuarios`.`h` = '$auth_user'
                LIMIT 1
            ";
        }

        $stm = mysqli_query($con, $sqlUsuario) or die('Unable to execute query.');
        $rs = mysqli_fetch_array($stm);
        $id_cliente = $rs['id_cliente'];
    }

    if(!$id_cliente){
		throw new Exception('Nenhum cliente encontrado.');
    }

    //
    $post = $_GET;
    $sql = "SELECT
            `bem`.`id`,
            `bem`.`name`,
            `bem`.tipo,
            `bem`.`id_tipo`,
            `bem`.`auto_ico`,
            `bem`.`img_car`,
            `bem`.`tipbem_id`,
            `bem`.`imei`,
            `bem`.`ligado`,
            `bem`.`alert_ign`,
            `bem`.`ancora`,
            `bem`.`templateTelemetria` AS template_telemetria,
            COALESCE(`loc_atual`.`latitudeDecimalDegrees`, 0) AS lat,
            COALESCE(`loc_atual`.`longitudeDecimalDegrees`, 0) AS lng,
            COALESCE(`loc_atual`.`voltagem_bateria`, 0) AS voltagem_bateria,
            COALESCE(`loc_atual`.`bat_interna`, 0) AS voltagem_bateria_int,
            `loc_atual`.`S1` AS bloqueado,
            `loc_atual`.`infotext` AS evento,
            `loc_atual`.`address` AS endereco,
            COALESCE(`loc_atual`.`speed`, 0) AS velocidade,
            DATE_FORMAT(`loc_atual`.`date`, '%d/%m/%Y %H:%i:%s') AS data_posicao,
			DATE_FORMAT(`loc_atual`.`data_comunica`, '%d/%m/%Y %H:%i:%s') AS data_comunicacao,
            COALESCE(`obd`.`ambient_temperature`, 0) AS temp_motor,
            COALESCE(`obd`.`oil_temperature`, 0) AS temp_oleo,
            COALESCE(`obd`.`rpm`, 0) AS rpm,
            COALESCE(`obd`.`odometer`, 0) AS odometro,
            COALESCE(`obd`.`fuel_level`, 0) AS combustivel_nivel,
            COALESCE(`obd`.`brake_position`, 0) AS freio_posicao,
            COALESCE(`obd`.`throttle_position`, 0) AS acelerador_posicao,
            COALESCE(`obd`.`current_gear`, 0) AS marcha_atual,
            COALESCE(`obd`.`engine_hours`, 0) AS horimetro,
            COALESCE(`obd`.`instant_fuel_economy`, 0) AS economia_instantanea,
            COALESCE(`obd`.`engine_load`, 0) AS carga_motor,
            COALESCE(`obd`.`fuel_pressure`, 0) AS combustivel_pressao,
            COALESCE(`obd`.`fuel_system_status`, 0) AS combustivel_status,
            COALESCE(`obd`.`total_fuel_used`, 0) AS combustivel_total_usado,
            COALESCE(`obd`.`distance_since_dtc_clear`, 0) AS distancia_desde_limpeza,
            COALESCE(`obd`.`distance_traveled_mil`, 0) AS distancia_percorrida_lamp,
            COALESCE(`obd`.`torque`, 0) AS torque,
            `pessoas`.`nome_pessoa` AS motorista
        FROM
            `bem`
        LEFT JOIN `loc_atual` ON `loc_atual`.`imei` = `bem`.`imei`
        LEFT JOIN `pessoas` ON `pessoas`.`imei` = `bem`.`imei`
        LEFT JOIN LATERAL (
            SELECT
                `OBDData`.*
            FROM
                `OBDData`
            WHERE `OBDData`.`id_bem` = `bem`.`id`
            ORDER BY `OBDData`.`dateObd` DESC
            LIMIT 1
        ) obd ON TRUE
        WHERE
            `bem`.`id` = '".$post['id']."'
        GROUP BY `bem`.`id`
        LIMIT 1
        ";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        throw new Exception(mysqli_error($con));
    }

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $dados = [];

        $dados['id'] = $row['id'];
        $dados['name'] = $row['name'];
        $dados['alert_ign'] = $row['alert_ign'];
        $dados['ancora'] = $row['ancora'];
        $dados['tipo'] = $row['tipo'];
        $dados['imei'] = $row['imei'];
        $dados['ligado'] = ($row['ligado'] == 'S'?true:false);
        $dados['ign_color'] = ($row['ligado'] == 'S'?'#08CD1C':'#FC0303');
        $dados['voltagem_bateria'] = (float)$row['voltagem_bateria'];
        $dados['voltagem_bateria_int'] = (float)$row['voltagem_bateria_int'];
        $dados['endereco'] = $row['endereco'];

        $endereco = $row['address'];
        $endereco = explode(',', $endereco);
        $endereco = $endereco ? ($endereco[0] . '-' . (isset($endereco[2]) ? $endereco[2] : '')) : ' - ';
        $dados['endereco_resumido'] = converter_utf8($endereco);

        $dados['data_posicao'] = $row['data_posicao'];
        $dados['data_comunicacao'] = $row['data_comunicacao'];
        $dados['rpm'] = (float)$row['rpm'];
        $dados['velocidade'] = (float)$row['velocidade'];
        $dados['odometro'] = (float)$row['odometro'];
        $dados['combustivel_nivel'] = (float)($row['combustivel_nivel'] < 0 ? 0 : $row['combustivel_nivel'] > 100 ? 100 : $row['combustivel_nivel']);
        $dados['evento'] = $row['evento'];
        $dados['freio_posicao'] = (float)$row['freio_posicao'];
        $dados['acelerador_posicao'] = (float)$row['acelerador_posicao'];
        $dados['marcha_atual'] = (float)$row['marcha_atual'];
        $dados['horimetro'] = (float)$row['horimetro'];
        $dados['economia_instantanea'] = (float)$row['economia_instantanea'];
        $dados['carga_motor'] = (float)$row['carga_motor'];
        $dados['combustivel_total_usado'] = (float)$row['combustivel_total_usado'];
        $dados['combustivel_pressao'] = (float)$row['combustivel_pressao'];
        $dados['combustivel_status'] = (float)$row['combustivel_status'];
        $dados['distancia_desde_limpeza'] = (float)$row['distancia_desde_limpeza'];
        $dados['distancia_percorrida_lamp'] = (float)$row['distancia_percorrida_lamp'];
        $dados['torque'] = (float)$row['torque'];
        $dados['temp_oleo'] = (float)$row['temp_oleo'] < 0 ? 0 : $row['temp_oleo'];
        $dados['temp_motor'] = (float)$row['temp_motor'] < 0 ? 0 : $row['temp_motor'];
        $dados['motorista'] = $row['motorista'];

        // Evento
        if ($row['bloqueado'] == 1) {
            $dados['bloqueado'] = true;
        } else {
            $dados['bloqueado'] = false;
        }

        // Template Telemetria
        $templateTelemetria = [];
        if($row['template_telemetria']){
            $templateTelemetriaDados = json_decode($row['template_telemetria'], true);
            if($templateTelemetriaDados && is_array($templateTelemetriaDados)){
                foreach ($templateTelemetriaDados as $valor) {
                    $templateTelemetria[$valor] = true;
                }
            } else {
                $templateTelemetria = [];
            }
        }
        $dados['template_telemetria'] = $templateTelemetria;

        // lat e long
        if ($row['lat'] > 0) {
            $dLatLng['lat'] = $row['lat'];
            $dLatLng['long'] = $row['lng'];
            $retLatLng = converteTK($dLatLng);
            $row['lat'] = $retLatLng['lat'];
            $row['lng'] = $retLatLng['long'];
        }
        $dados['lat'] = converter_utf8($row['lat'] * 1);
	    $dados['lng'] = converter_utf8($row['lng'] * 1);


        // Ícone
        /*$tipbem_id = $row['tipbem_id'];
        $stm_icone = mysqli_query($con,  "SELECT tipbem_img,tipbem_tipo FROM tipo_bem WHERE tipbem_id = '" . $tipbem_id . "'");
        $rs_icone = mysqli_fetch_assoc($stm_icone);

        if ($auth_user == '0b85502f856ed8c5743de71473c78098') { // SE FOR USUARIO CAROLO REMOVE FOTO E ENDEREÇO PRO APP FICAR MENOS AS TABLE
            $dados['imagem_veiculo'] = NULL;
        }*/

    	/*$auto_icone = converter_utf8($row['auto_ico']);

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
            if ($dados['ligado'] == "S") {
                if ($dados['velocidade'] > 0) {
                    $dados['imagem_icone'] = "https://itajobi.usinaitajobi.com.br/imagens/auto_icone/Movimento/" . $icone_img;
                    //echo "movimento";
                } else {
                    $dados['imagem_icone'] = "https://itajobi.usinaitajobi.com.br/imagens/auto_icone/Ligados/" . $icone_img;
                    //echo "Ligado";
                }
            } else {
                $dados['imagem_icone'] = "https://itajobi.usinaitajobi.com.br/imagens/auto_icone/Desligados/" . $icone_img;
                //echo "desligado";
            }
        } else {
            $dados['imagem_icone'] = "https://itajobi.usinaitajobi.com.br/imagens/" . converter_utf8($rs_icone['tipbem_img']);
            // echo "padrao";
        }*/

    	$idIconBem = buscarIconBem($row['id_tipo'], $con);
	    $dados['imagem_icone'] = $idIconBem;

        if ($row['img_car']) {
            $dados['imagem_veiculo'] = "https://itajobi.usinaitajobi.com.br/imagens/veiculos/" . converter_utf8($row['img_car']);
        } else {
            $dados['imagem_veiculo'] = NULL;
        }

        $return['data'] = $dados;
    } else {
		throw new Exception('Nenhum veículo encontrado.');
    }

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
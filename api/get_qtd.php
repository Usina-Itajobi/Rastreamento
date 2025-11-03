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


$login = protejeInject($_POST['v_login']);
if (!$login) {
	$login = protejeInject($_GET['v_login']);
}

$chave = protejeInject($_POST['v_chave']);
if (!$chave) {
	$chave = protejeInject($_GET['v_chave']);
}

$busca = protejeInject($_POST['v_busca']);
if (!$busca) {
	$busca = protejeInject($_GET['v_busca']);
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
				  WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')
				    
				  LIMIT 1";



$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
$rs = mysqli_fetch_array($stm);
$id_cliente = $rs['id_cliente'];



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
						, b.speed
						, b.ligado
						, a.imei
						, b.voltagem_bateria
						, a.bloqueado
						, a.ancora
						, a.alert_ign
						, b.km_rodado
						, a.ny
						, a.minivps
						, b.rpm
						, b.bat_interna
						, a.tipbem_id
          				, b.combustivel
						, ( select p.nome_pessoa from pessoas p where p.imei = a.imei limit 1) as motorista
			from 
						bem a
						, loc_atual b
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
							a.name";


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
						, b.address
						, DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
						, b.speed
						, b.ligado
						, a.imei
						, b.voltagem_bateria
						, a.bloqueado
						, a.ancora
						, a.alert_ign
						, b.km_rodado
						, a.ny
						, a.minivps
						, b.rpm
						, b.bat_interna
						, a.tipbem_id
          				, b.combustivel
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


$stm  = mysqli_query($con,  $sql);
$aux = 0;

if ($_GET['versql'] == '1') {
	echo $sql;
	die;
}

$qtd_veiculos = 0;
$onn = 0;
$off = 0;
$speedd = 0;

while ($rs = mysqli_fetch_array($stm)) {
	if ($rs['ligado'] == 'S') {
		$onn++;
	}

	if ($rs['ligado'] == 'N') {
		$off++;
	}

	if ($rs['speed'] == 0) {
		$speedd++;
	}
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




	// recuperando a imagem do bem


	$aux++;

	$qtd_veiculos++;
}

$retorno['ligado'] = $onn;
$retorno['desligado'] = $off;
$retorno['parado'] = $speedd;
$retorno['total'] = $onn + $off;





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

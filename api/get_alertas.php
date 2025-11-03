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


$login = protejeInject($_POST['v_login']);
if (!$login) {
	$login = protejeInject($_GET['v_login']);
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

$query = "SELECT * FROM cliente WHERE id = $id_cliente";
$stm1 = mysqli_query($con, $query) or die('Unable to execute query.');
$rs1 = mysqli_fetch_array($stm1);

if ($rs1['admin'] == "S") {
	$sql = "SELECT b.name, m.message, m.imei, date_format(m.date, '%d/%c/%y') date, count(*) as qtde
	FROM bem b inner join message m on (b.imei = m.imei)
	inner join cliente on( cliente.id = b.cliente)
	WHERE cliente.id_admin = $id_cliente and m.viewed = 'N'
	GROUP BY 1, 2, 3, 4 ORDER BY m.date DESC";
} else {
	$sql = "SELECT b.name, m.message, m.imei, date_format(m.date, '%d/%c/%y %H:%i:%s') date, count(*) as qtde
		  FROM bem b inner join message m on (b.imei = m.imei)
		  WHERE b.cliente = $id_cliente and m.viewed = 'N'
		  GROUP BY 1, 2, 3, 4 ORDER BY m.date DESC";
}


$result = mysqli_query($con, $sql);


while ($data = mysqli_fetch_assoc($result)) {
	$msg = "<div><b>" . $data['name'] . "</b> " . $data['message'] . " (" . $data['date'] . ") <a onclick='deletamsg(\"" . $data['message'] . "\",\"" . $data['imei'] . "\");'><img src='img/del_msg.png'> Deletar menssagem</a></div>";

	$retorno[]['msg'] = utf8_encode($msg);
}


echo json_encode($retorno);
die;

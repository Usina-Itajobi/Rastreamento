<?php

	/**
	*  @author: Graziani Arciprete - psymics(at)gmail(dot)com
	*  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
	*/

	/**
	*	Função para protejer do SQL Inject
	*/

	function protejeInject( $str ){
	    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "" ,$str);
	   $sql = trim($sql);
	   $sql = strip_tags($sql);
	   $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
	   return $sql;
	}
	

	

	header('Content-Type: application/json;charset=utf-8');
	sleep(1);

	
	$login = protejeInject ( $_POST['v_login'] );
	if(!$login){
		$login = protejeInject ( $_GET['v_login'] );
	}
	
	if( $login == '' ){
		$retorno = array('errormsg'=>'Preencha o usu&aacute;rio.' , 'error' => 'S' );
		echo json_encode( $retorno );
		die;
	}

	
	require_once("config.php");

	$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("Não foi possivel conectar ao Mysql".mysqli_error()) ;
	mysqli_select_db($con, $DB_NAME);
	$auth_user = strtolower($login);
	

	$sql =
				"SELECT 
					    CAST(a.id AS DECIMAL(10,0)) as id_cliente 
				   FROM cliente a 
				  WHERE (a.email = '".$auth_user."' OR a.apelido = '".$auth_user."')
				    
				  LIMIT 1"
			; 
	
	

	$stm = mysqli_query($con, $sql) or die ( 'Unable to execute query.' ); 
	$rs = mysqli_fetch_array( $stm );
	$id_cliente = $rs['id_cliente'];

	
	$idbem = $_POST['v_id'];
	$qual = $_POST['v_qual'];
	// $ancora_coords = "[]";
	// $sql_up = "update bem set ancora = '". $qual ."' , `ancora_coords`='$ancora_coords' where id = '". $idbem ."'";
	$sql_up = "update bem set ancora = '". $qual ."'  where id = '". $idbem ."'";
	mysqli_query($con,$sql_up);

	$retorno = array( 'msg' => 'Sinal de ancora enviado com sucesso!');
	echo json_encode( $retorno );
	die;
	
	function getLastData($idbem){
		$cnx = mysqli_connect("localhost", "traccar", "6hjg2745")
			or die("Could not connect: " . mysqli_error($cnx));
		mysqli_select_db($cnx, 'tracker');
		$query = "SELECT latitudeDecimalDegrees,latitudeDecimalDegrees FROM `tracker`.`loc_atual` WHERE id = '$idbem' ORDER BY id DESC LIMIT 1";
		$result = mysqli_query($cnx, $query);
		$last_data = mysqli_fetch_array($result);
		return $last_data;
	}
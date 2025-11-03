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

	

	function  converteTK( $dados ){
		
		$latitudeDecimalDegrees = $dados['lat'];
		$longitudeDecimalDegrees = $dados['long'];
		
		strlen($latitudeDecimalDegrees) == 9 && $latitudeDecimalDegrees = '0'.$latitudeDecimalDegrees;
		$g = substr($latitudeDecimalDegrees,0,3);
		$d = substr($latitudeDecimalDegrees,3);
		$strLatitudeDecimalDegrees = $g + ($d/60);
		$latitudeHemisphere == "S" && $strLatitudeDecimalDegrees = $strLatitudeDecimalDegrees * -1;
		
		if( $strLatitudeDecimalDegrees > 0 ){
			$strLatitudeDecimalDegrees = $strLatitudeDecimalDegrees * -1;
		}
		

		strlen($longitudeDecimalDegrees) == 9 && $longitudeDecimalDegrees = '0'.$longitudeDecimalDegrees;
		$g = substr($longitudeDecimalDegrees,0,3);
		$d = substr($longitudeDecimalDegrees,3);
		$strLongitudeDecimalDegrees = $g + ($d/60);
		$longitudeHemisphere == "S" && $strLongitudeDecimalDegrees = $strLongitudeDecimalDegrees * -1;

		
		if( $strLongitudeDecimalDegrees > 0 ){
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
	mysqli_select_db($con , $DB_NAME);
	$auth_user = strtolower($login);
	

	$sql =
				"SELECT 
					    CAST(a.id AS DECIMAL(10,0)) as id_cliente 
				   FROM cliente a 
				  WHERE (a.email = '".$auth_user."' OR a.apelido = '".$auth_user."')
				    
				  LIMIT 1"
			; 
	
	

	$stm = mysqli_query($con,$sql) or die ( 'Unable to execute query.' ); 
	$rs = mysqli_fetch_array( $stm );
	$id_cliente = $rs['id_cliente'];

	
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
						, b.km_rodado
						
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
	if(!$id_bem){
		$id_bem = protejeInject ( $_GET['v_id_bem'] );
	}
	
	if( $id_bem != '' ){
		$sql .= "
						and a.id = '" . $id_bem . "'
		";
	}

	$sql .= "
						and a.imei = b.imei
			order by 
						a.name";
	
	$stm  = mysqli_query ( $con, $sql );
	$aux = 0;



	while( $rs = mysqli_fetch_array( $stm ) ){
		
		
		if( $rs[latitudeDecimalDegrees] > 0 ){
			$dLatLng['lat'] = $rs[latitudeDecimalDegrees];
			$dLatLng['long'] = $rs[longitudeDecimalDegrees];
			$retLatLng = converteTK( $dLatLng );
			$rs[latitudeDecimalDegrees] = $retLatLng['lat'];
			$rs[longitudeDecimalDegrees] = $retLatLng['long'];
		}

		
		$retorno[ $aux ]['id_bem'] = utf8_encode ( $rs['id'] );
		$retorno[ $aux ]['name'] = utf8_encode ( $rs['name'] );
		$retorno[ $aux ]['tipo'] = utf8_encode ( $rs['tipo'] );
		$retorno[ $aux ]['lat'] = utf8_encode ( $rs['latitudeDecimalDegrees'] * 1 );
		$retorno[ $aux ]['lng'] = utf8_encode ( $rs['longitudeDecimalDegrees'] * 1 );
		if( $id_bem != '' ){
			$retorno[ $aux ]['address'] = utf8_decode ( $rs['address'] ) . "<br><b>Odometro Km:</b> <br>" . utf8_encode ( $rs['km_rodado'] ) . "";
		}else{
			$retorno[ $aux ]['address'] = utf8_decode ( $rs['address'] ) . " | Odometro Km: " . utf8_encode ( $rs['km_rodado'] ) . " | ";
		}
		$retorno[ $aux ]['dia'] = utf8_encode ( $rs['dia'] );
		$retorno[ $aux ]['ancora'] = utf8_encode ( $rs['ancora'] );
		$retorno[ $aux ]['speed'] = utf8_encode ( $rs['speed'] );
		$retorno[ $aux ]['ligado'] = utf8_encode ( $rs['ligado'] );
		$retorno[ $aux ]['imei'] = utf8_encode ( $rs['imei'] );
		$retorno[ $aux ]['voltagem_bateria'] = utf8_encode ( $rs['voltagem_bateria'] );
		$retorno[ $aux ]['bloqueado'] = utf8_encode ( $rs['bloqueado'] );
		$retorno[ $aux ]['km_rodado'] = utf8_encode ( $rs['km_rodado'] );
		
		$aux++;
		
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
									, '". $_SERVER['REMOTE_ADDR'] ."'
									, '". $id_bem ."'
									, '". json_encode( $retorno ) ."'
									, '". $_SERVER['HTTP_USER_AGENT'] ."'
								) 
								";

	mysqli_query ( $con, $sql );
	echo json_encode( $retorno );
	die;
	
	
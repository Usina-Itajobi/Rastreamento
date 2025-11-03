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
	
	

	$stm = mysqli_query($con,$sql) or die ( 'Unable to execute query.' ); 
	$rs = mysqli_fetch_array( $stm );
	$id_cliente = $rs['id_cliente'];

	
	$imei = $_POST['v_imei'];
	
	// recuepra o modelo do rastreador
	$sql = "select modelo_rastreador from bem where imei like '%". $imei ."%'";
	$stm = mysqli_query($con,$sql) or die ( 'Unable to execute query.' ); 
	$rs = mysqli_fetch_array( $stm );
	
	$modelo_rastreador = $rs['modelo_rastreador'];
	
	$stm = mysqli_query($con,"select * from comandos where cmd_modelo_rastreador = '" . $modelo_rastreador . "' and cmd_unblock = '1'" );
	$n = mysqli_num_rows ( $stm );
	
	if( $n == 0){
		$retorno = array( 'msg' => 'Comando nao cadastrado para o rastreador ('. $modelo_rastreador .'). Entrar em contato com o administrador do sistema.');
		echo json_encode( $retorno );
		die;
	}
	
	
	$rs = mysqli_fetch_array( $stm );
	$tempstr = $rs['cmd_comando'];
	$cmd_id = $rs['cmd_id'];
	
	// troca se tiver o $imei
	$tempstr = str_replace('$imei' , $imei , $tempstr );
	
	if( is_array( $add_cmd ) ){
		foreach( $add_cmd as $k => $v ){
			$tempstr .= ';' . $v;
		}
	}
	
	if( is_array( $param_cmd ) ){
		foreach( $param_cmd as $kp => $vp ){
			if(is_array( $vp )){
				$nvp = '';
				foreach( $vp as $kpp => $vpp ){
					$nvp .= $vpp;
				}
				$tempstr .= ';' . $nvp;
			}else{
				$tempstr .= ';' . $vp;
			}
			
		}
	}
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$stm_log = mysqli_query($con, "select max(id_log) as m_id_log from command_log" );
	$rs_log = mysqli_fetch_array( $stm_log );
	$id_log = $rs_log['m_id_log'] + 1;
	
	
	$sql = "INSERT INTO command (imei, command, userid, id_log, tentativa, recebido, app ) VALUES ('$imei', '$tempstr', '$userid' , '$id_log', 0 , 'N' , 1)";
	if (!mysqli_query($con, $sql )) {
		die('Error: '.mysqli_error());
	}
	
	$sql = "INSERT INTO command_log (imei, command, cliente, ip, id_log, cmd_id, status) VALUES ('$imei', '$tempstr', '$id_cliente', '$ip','$id_log', '". $cmd_id ."', 0)";
	
	if (!mysqli_query($con,$sql )) {
		die('Error: '.mysqli_error());
	}
	

	$retorno = array( 'msg' => 'Sinal de desbloquear enviado com sucesso!');
	echo json_encode( $retorno );
	die;
	
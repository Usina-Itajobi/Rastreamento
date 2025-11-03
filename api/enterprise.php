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

	
	$id_cliente = protejeInject ( $_POST['id'] );
	if(!$id_cliente){ 	$id_cliente = protejeInject ( $_GET['id'] ); }
	
	
	if( $id_cliente == '' ){
		$retorno = array('errormsg'=>'Preencha o id da enterprise' , 'error' => 'S' );
		echo json_encode( $retorno );
		die;
	}

	require_once("config.php");
	$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("Não foi possivel conectar ao Mysql".mysqli_error()) ;
	mysqli_select_db($con, $DB_NAME);
	
				
			
	$sql =
		"SELECT 
				id
				, app_id
				, app_img
				, nome
				, app_url
				, img_logo
		   FROM cliente a
		  WHERE app_id = '". $id_cliente ."' 
		  LIMIT 1"; 


	$stm = mysqli_query($con,$sql) or die ( 'Unable to execute query.' . $sql ); 	
	$num = mysqli_num_rows( $stm ); 
	
	if ( $num == 0 ) {
		
		
		$retorno = array('errormsg'=>'ID  inv&aacute;lido' , 'error' => 'S' );
		echo json_encode( $retorno );
		die;
		
		
		
	}else{
		
		$rs = mysqli_fetch_assoc( $stm );
		$retorno = array('errormsg'=>'' , 'error' => 'N' 
			, 'id_cliente' => utf8_encode ( $rs['id'] ) 
			, 'logo' => utf8_encode( $rs['img_logo']  )  
			, 'nome' => utf8_encode ( $rs['nome'] )
                        , 'url_api' => utf8_encode( $rs['app_url']  ) 
			);
		echo json_encode( $retorno );
		die;

	}
	

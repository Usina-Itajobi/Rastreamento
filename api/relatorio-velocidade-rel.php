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
	
	$login = protejeInject ( $_POST['v_login'] );
	if(!$login){
		$login = protejeInject ( $_GET['v_login'] );
	}
	
	require_once("config.php");

	$con 		= mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die ("Não foi possivel conectar ao Mysql".mysqli_error()) ;
	mysqli_select_db($con,$DB_NAME);
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
	
	//echo "id cliente = $id_cliente";
	
	?>
	
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
                <tr>
                  <th>#</th>
                  <th>DATA</th>
                  <th>PLACA</th>
                  <th>MOTORISTA</th>
                  <th>KM/H</th>
                  <th>ENDERE&Ccedil;O</th>
                  <th>LATITUDE</th>
                  <th>LONGITUDE</th>
                  <!-- <th>MOTORISTA</th> -->
                  <th>MAPA</th>
                  <th>STATUS</span></th>
                </tr>
            </thead>
       		<tbody>
            <?php

		$dataInicial = $_POST['v_data_ini'];
		$dataFinal = $_POST['v_data_fim'];
		
		
		
		$q_tabela = "gprmc";

		$df = date("Y-m-d");
		$dStart = new DateTime( $dataInicial );
		$dEnd  	= new DateTime( $df );
		$dDiff 	= $dStart->diff($dEnd);
		// echo $dDiff->format('%R'); // use for point out relation: smaller/greater
		$dias_dif = $dDiff->days;
		  
		if( $dias_dif < 7 ){
			$q_tabela = "gprmc_7dias";
		}
		
		echo "Tabela: $q_tabela";
		$imei = $_POST['v_veiculo'];
		
		if( $imei == 'TODOS' ){
			$stm_idbem = mysqli_query($con,"select id, name, imei from bem where cliente = '$id_cliente'" );
			
			$idbem = '0';
			
			while($rs_idbem = mysqli_fetch_assoc( $stm_idbem )){
				$idbem .= ','. $rs_idbem['id'];
				$placa[$rs_idbem['imei']] = $rs_idbem['name'];
			}
			
			
		}else{
			$stm_idbem = mysqli_query($con,"select id, name, imei from bem where imei = '$imei'" );
			$rs_idbem = mysqli_fetch_assoc( $stm_idbem );
			$idbem = $rs_idbem['id'];
			$placa[$rs_idbem['imei']] = $rs_idbem['name'];
		}
		
		
		$velocidade = $_POST['v_vel_max'];
		if(!$velocidade){$velocidade=0;}
		
		$sql = " SELECT 
								*
					FROM 
								$q_tabela
					WHERE 
								id_bem in ( $idbem )
								and DATE(date) BETWEEN '$dataInicial' AND '$dataFinal' 
								and speed >=  $velocidade
					order by
								 speed desc 
			 ";
		
		//echo $sql;
		$stm = mysqli_query($con,$sql );
		$aux = 0;
		
		while( $rs = mysqli_fetch_array( $stm ) ){
		$aux++;
			echo '
            <tr>
              <td> ' . $aux . '</td>
			  <td> ' . date('d/m/Y H:i:s', strtotime($rs[date])) . '</td>
              <td> ' . utf8_encode( $placa[$rs[imei]] ) . '</td>
              <td> ' . utf8_encode($rs[motorista]) . '</td>
              <td> ' .  " <b>".$rs['speed'] ." Km/h</b>" . '</td>
              <td> ' . utf8_encode($rs[address]) . '</td>
              <td> ' . $rs[latitudeDecimalDegrees] . '</td>
              <td> ' . $rs[longitudeDecimalDegrees] . '</td>
              <td><a href="http://maps.google.com/maps?q=' . $rs[latitudeDecimalDegrees] . ',' .  $rs[longitudeDecimalDegrees] . '" target="_blank" class="external" ><img src="http://ctracker.com.br/imagens/mapa_globo.png"  title="Veiculo Ligado"> </a></td>
              <td> ';
			    	// if ($atraso || $data['status_sinal'] != 'R') echo "<img src='imagens/ignicao.png' alt='Desligado' title='Veículo desligado'> ";
					if ($rs['ligado'] == 'S') 
						echo  "<img src='http://ctracker.com.br/imagens/chave1.png' alt='Ligado' title='Veiculo Ligado'> ";
					else 
						echo  "<img src='http://ctracker.com.br/imagens/ignicao.png' alt='Desligado' title='Veículo desligado'> ";
			    	
					if ($dataDespesas['bloqueado'] == 'N') 
						echo " <img src='http://ctracker.com.br/imagens/unlock.png' alt='Veículo Desbloqueado' title='Veículo Desbloqueado'>"; 
					else 
						echo " <img src='http://ctracker.com.br/imagens/locked.png' alt='Bloqueado' title='Veículo Bloqueado'>";
			
				echo  '</td>
            </tr>';
		
		}
		

			?>
        	</tbody>
        </table>
    </div>
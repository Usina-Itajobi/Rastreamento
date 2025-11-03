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
	
	if($id_cliente){
		
		$sql = "select 
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
						a.activated = 'S' and 
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
		
	}else{
		
		$sql = "select 
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
							a.id in (
								select b.bem from grupo a , grupo_bem b
								where a.nome = '" . $auth_user . "'
								and a.id = b.grupo
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
			
	}
	
		
	
?>
<div class="row" style="padding-top:15px;">

<p><b>:: Relat&oacute;rio de posi&ccedil;&atilde;o</b><br />
    		<strong style="color:#696;">Selecione um ve&iacute;culo abaixo</strong></p>
        </div>
    	<div class="row" style="margin-bottom:10px;">
    		<div class="col-xs-11">
    			<select class="form-control" id="veiculo" >
    				<option value=""> - Selecione - </option>
    				<option value="TODOS"> - Todos Ve&iacute;culos - </option>
                    <?php
					
					
					
					$stm  = mysqli_query ( $con, $sql );
					$aux = 0;
					
					while( $rs = mysqli_fetch_array( $stm ) ){
						
						echo "<option value='". utf8_encode ( $rs['imei'] ) ."'>". utf8_encode ( $rs['name'] ) ."</option>";
						
					}
					
					?>
    			</select>
    		</div>
    	</div>
        
<p>
    		<center><strong style="color:#696;">Selecione um per&iacute;odo </strong></center>
    	</p>
        <div class="row" style="margin-bottom:10px;">
        	<div class="col-xs-5">
            	<label>Data Inicial</label>
            	<input type="date" id="data_ini" class="form-control" />
            </div>
            <div class="col-xs-5">
	           	<label>Hora Inicial</label>
                <input type="time" id="hora_ini" class="form-control" />
            </div>
           
        </div>
        
        <div class="row" style="margin-bottom:10px;">
        	<div class="col-xs-5">
            	<label>Data Final</label>
            	<input type="date" id="data_fim" class="form-control" />
            </div>
            <div class="col-xs-5">
	           	<label>Hora Final</label>
                <input type="time" id="hora_fim" class="form-control" />
            </div>
           
        </div>
        

        
        <div class="row">
        	<div class="col-xs-6">
            	<input type="button" value="Gerar Relat&oacute;rio" class="form-control btn btn-success" onclick="gerarRelatorio();" />
            </div>
        </div>
        
        <div class="row" style="margin-bottom:10px;margin-top:10px;">
        	<div class="col-xs-12" id="resultadoAjaxRelatorio"></div>
        </div>
        
        <script>
        	function gerarRelatorio(){
				
				var veiculo = $("#veiculo").val();
				var data_ini = $("#data_ini").val();
				var hora_ini = $("#hora_ini").val();
				var data_fim = $("#data_fim").val();
				var hora_fim = $("#hora_fim").val();
				
				if( veiculo == '' ){
					alert('Selecione um veiculo');
					return false;
				}
				
				if( data_ini == '' ){
					alert('Selecione a data inicial');
					return false;
				}
				
				if( hora_ini == '' ){
					alert('Selecione a hora inicial');
					return false;
				}
				
				if( data_fim == '' ){
					alert('Selecione a data final');
					return false;
				}
				
				if( hora_fim == '' ){
					alert('Selecione a hora final');
					return false;
				}
				
				
				
				abreLoading();
				
				var login = $("#v_login").val();
				$.ajax({
					url: 'https://api.ctracker.com.br/metronic/api/relatorio-posicao-rel.php',
					type: 'POST',
					data: {			
						v_login: login
						, v_veiculo : veiculo
						, v_data_ini : data_ini
						, v_hora_ini : hora_ini
						, v_data_fim : data_fim
						, v_hora_fim : hora_fim
					},
					success: function (data) {
						$("#resultadoAjaxRelatorio").empty().html(data);
						
						fechaLoading();
					},
					error: function (e) {
						$(".fundoPreto").hide();
						$(".carregandoAjax").hide();
						alert('erro' + e.status );
						
					}
				});
				
			}
        </script>

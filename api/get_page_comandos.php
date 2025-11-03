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
	
	$busca = protejeInject ( $_POST['v_busca'] );
	
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
	
	
	if( $id_cliente ){
		$btn_comando = false;
		$sql = "select 
						a.id
						, a.name 
						, b.latitudeDecimalDegrees
						, b.longitudeDecimalDegrees
						, a.tipo
						, a.tipbem_id
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
					
					
					if( $busca != '' ){
						$sql .= "
										and a.name like '%" . $busca . "%'
						";
					}
				
					$sql .= "
										and a.imei = b.imei
							order by 
										a.name";
										
										
				$stm_grupo = mysqli_query($con, "select a.* from programas a , programas_usuarios b
where a.progr_id = b.progrusr_progr_id
and b.`progrusr_id_cliente` = '". $id_cliente ."'
and a.progr_id = 12");

		$n_prog = mysqli_num_rows ( $stm_grupo );
		
		if( $n_prog > 0 ){
			$btn_comando = true;
		}
	}else{
	$btn_comando = true;
	$sql = "select 
						a.id
						, a.name 
						, b.latitudeDecimalDegrees
						, b.longitudeDecimalDegrees
						, a.tipo
						, a.tipbem_id
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
					
					
					if( $busca != '' ){
						$sql .= "
										and a.name like '%" . $busca . "%'
						";
					}
				
					$sql .= "
										and a.imei = b.imei
							order by 
										a.name";
		
	}
	

?>
<div id="tudo">
<style>
	.carrinho{
		border:solid 1px #ccc;
		border-radius:5px;
		padding:10px;
		margin-bottom:5px;
		background-color:#E2E2E2;
	}
</style>
<p>
    		Selecione o <strong>ve&iacute;culo</strong> abaixo para enviar o comando {<?php echo $login ; ?>}
    	</p>
        
        <div class="row" style="margin-bottom:10px;">
        	
            <div class="col-xs-7">
            	<input type="text" value="<?php echo $busca; ?>" placeholder="Buscar" class="form-control" id="busca" />
            </div>
            <div class="col-xs-4">
            	<button class="form-control btn" style="background-color: #ccc;" onclick="buscaImei();"><img src="http://165.227.104.119/imagens/lupinha2.png" /> Buscar</button>
            </div>
            <input type="hidden" id="v_login" value="<?php echo $login; ?>" />
        </div>
    	<script>
        	function buscaImei(){
				
				var busca = $('#busca').val();
				
				if( busca == '' ){
					alert('Preencha a busca');
					return false;
				}
				
				abreLoading();
				
				
				var login = $("#v_login").val();
				
				$.ajax({
					url: 'http://gps-controltracker.com.br/metronic/api/get_page_comandos.php',
					type: 'POST',
					data: {			
						v_login: login
						, v_busca : busca
					},
					success: function (data) {
						$("#tudo").empty().html(data);
						
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
    	<div class="row" id="resultadoBusca">
    		
                    <?php
					
					
					
					$stm  = mysqli_query ( $con, $sql );
					$aux = 0;
					
					while( $rs = mysqli_fetch_array( $stm ) ){
						
						$tipbem_id = $rs['tipbem_id'];
						$stm_icone = mysqli_query($con,  "select tipbem_img from tipo_bem where tipbem_id = '". $tipbem_id ."'" );
						$rs_icone = mysqli_fetch_assoc($stm_icone);
						
						$img_icone = "http://165.227.104.119/imagens/" . utf8_encode ( $rs_icone['tipbem_img'] );
						
						echo "<div class='col-xs-12 carrinho'>
									
									<img src='". $img_icone ."'> <strong>". utf8_encode ( $rs['name'] ) ."</strong> 
									&nbsp;
									". utf8_encode ( $rs['address'] ) ."
									";
						if( $btn_comando ){
						 echo "
									<br><table width='100%'>
										<tr>
											<td width='50%' align='center'>
												<input type='button' class='btn btn-danger' value='Bloquear*' onclick=\"bloquear( '". $rs['id'] ."', '". $rs['imei'] ."' )\">
											</td>
											<td width='50%' align='center'>
												<input type='button' class='btn btn-success' value='Desloquear'onclick=\"desbloquear( '". $rs['id'] ."', '". $rs['imei'] ."' )\">
											</td>
										</tr>
									</table> ";
						}
						echo "
							</div>";
						
					}
					
					?>
    			
    	</div>
    	
    	
    	</div>
    	
          
        
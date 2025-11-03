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
	
	
	
	
?>
<p>
    		Selecione o <strong>Comando</strong> ou <strong>Relat&oacute;rio</strong> abaixo* (<?php echo $login ; ?>)
    	</p>
    	<div class="row">
        	<div class="col-xs-12">
                <input type="button" class="form-control btn btn-primary" value="Relat&oacute;rio de Velocidade" onclick="relVelocidade();" />
            </div>
            <div class="col-xs-12" style="margin-bottom:5px;margin-top:5px">
                <input type="button" class="form-control btn btn-primary" value="Relat&oacute;rio de Posi&ccedil;&atilde;o" onclick="relPosicao();" />
            </div>
            <div class="col-xs-12">
                <input type="button" class="form-control btn btn-primary" value="Relat&oacute;rio KM Acumulado"  onclick="relKm();" />
            </div>
        </div>
        
        <div class="row">
        	<div class="col-xs-12" id="resultadoAjax"></div>
        </div>
        
        <input type="hidden" id="v_login" value="<?php echo $login; ?>" />
        <script>
        	function relKm(){
				abreLoading();
				var login = $("#v_login").val();
				$.ajax({
					url: 'http://ctracker.com.br/metronic/api/relatorio-km.php',
					type: 'POST',
					data: {			
						v_login: login
					},
					success: function (data) {
						$("#resultadoAjax").empty().html(data);
						
						fechaLoading();
					},
					error: function (e) {
						$(".fundoPreto").hide();
						$(".carregandoAjax").hide();
						alert('erro' + e.status );
						
					}
				});
				
			
				
			}
			
			function relVelocidade(){
				abreLoading();
				var login = $("#v_login").val();
				$.ajax({
					url: 'http://ctracker.com.br/metronic/api/relatorio-velocidade.php',
					type: 'POST',
					data: {			
						v_login: login
					},
					success: function (data) {
						$("#resultadoAjax").empty().html(data);
						
						fechaLoading();
					},
					error: function (e) {
						$(".fundoPreto").hide();
						$(".carregandoAjax").hide();
						alert('erro' + e.status );
						
					}
				});
				
			
				
			}
			
			
			function relPosicao(){
				abreLoading();
				var login = $("#v_login").val();
				$.ajax({
					url: 'http://ctracker.com.br/metronic/api/relatorio-posicao.21072019.php',
					type: 'POST',
					data: {			
						v_login: login
					},
					success: function (data) {
						$("#resultadoAjax").empty().html(data);
						
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
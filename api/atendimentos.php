<?php
include_once 'includes/seguranca.php';
include_once 'usuario/config.php';
include_once 'includes/config.php';
$token = ( isset( $_POST[ 'token' ] ) ) ? $_POST[ 'token' ] : false;
$auth_user = isset( $_SESSION[ 'logSessioUser' ] ) ? $_SESSION[ 'logSessioUser' ] : false;
$logado = isset( $_SESSION[ 'logSession' ] ) ? $_SESSION[ 'logSession' ] : false;


$grupo = $_SESSION['grupoSession'];

if ($cliente == '') {
	$cliente = "0";
}



if($grupo == '' && $_SESSION['clienteSession'] != "master" ){
	$res = mysqli_query($con,"select imei, name from bem where activated = 'S' and cliente = " . trim($cliente) . " order by name");
	$resGrupo = mysqli_query($con,"select id, nome from grupo where cliente = " . trim($cliente) . " order by nome");
}elseif( $_SESSION['clienteSession'] == "master" ){
	$res = mysqli_query($con,"SELECT imei, name  FROM bem WHERE activated = 'S' AND cliente in ( select id from cliente where id_admin = '" . $_SESSION['idClienteSession'] . "' ) ORDER BY name");
	$resGrupo = mysqli_query($con,"SELECT id, nome FROM grupo WHERE cliente in ( select id from cliente where id_admin = '" . $_SESSION['idClienteSession'] . "' ) ORDER BY nome" );
	//echo "<hr>master<hr>";
} else {
	$res = mysqli_query($con,"select bem.imei, name from bem join grupo_bem gb on gb.bem = bem.id and gb.imei = bem.imei join grupo g on g.id = gb.grupo and g.cliente = ".trim($cliente)." where activated = 'S' and bem.cliente = " . trim($cliente) . " and g.id = ".$grupo." order by bem.name");
}

$veiculosStr = '';



$veiculosStr .= "<select id=\"imei\" name=\"imei\" class=\"form-control\" required=\"true\">";
$veiculosStr .= "<option value='' selected>** Selecione o ve&iacute;culo **</option>";
$veiculosStr .= "<option value='TODOS' ";

if( $imei == 'TODOS' ){
	$veiculosStr .= ' selected '; 
}
$veiculosStr .= ">TODOS VE&Iacute;CULOS</option>";

$imeis = '';
$placa = NULL;
$placa = array();

$todos_imei  = '';

for($i=0; $i < mysqli_num_rows($res); $i++) {
	$row = mysqli_fetch_assoc($res);
	$veiculosStr .= "<option value='$row[imei]' ".($row[imei] == $imei?'selected=selected':'').">$row[name]</option>";
	
	if( $imeis == '' ){
		$imeis = $row[imei];
	}else{
		$imeis .= ',' . $row[imei];
	}
	
	
	if( $todos_imei  == '' ){
		$todos_imei = "'". $row['imei'] ."'";
	}else{
		$todos_imei .= ",'". $row['imei'] ."'";
	}
	
	$placa[ $row[imei] ] = $row[name];
	
}

$veiculosStr .= "<option value='' disabled>-- GRUPO --</option>";

$sqlgrp = "select 
				  								a.id
												, a.nome
												
										from 
												grupo a												
										where
												a.cliente = '" .  $_SESSION['idClienteSession'] . "'
										order by 
												a.nome";
												
$stm_grp = mysqli_query( $con,$sqlgrp );
while( $rsgrp = mysqli_fetch_assoc($stm_grp) ){
	$v_grp = 'g-' . $rsgrp['id'];
	$veiculosStr .= "<option value='". $v_grp ."' ";
	
	if( $imei == $v_grp ){
		$veiculosStr .= ' selected '; 
	}
	
	$veiculosStr .= ">". $rsgrp['nome'] ."</option>";
}

$veiculosStr .= "</select>";


if( strstr( $imei , "g" ) ){
	
}else{
	$result = mysqli_query($con,"select id, name, imei from bem where imei = '$imei'");
	$dataBem = mysqli_fetch_assoc($result);
}


if ( !$logado ) {
	header( "Location: login.php" );
	exit();
}
$_SESSION[ 'tokenSession' ] = $token; //Se estiver ok, coloca na nessao, e checa sempre na segurança

if( $_GET['v_acao'] == 'get-veiculos' ){
	$id = $_GET['v_id'];
	$sql = "select bem from grupo_bem where grupo = '$id'";
	$aux = 0;
	$stm = mysqli_query( $con,  $sql  );
	while( $rs = mysqli_fetch_array( $stm ) ){
		
		if( $aux > 0){
			echo "," . $rs['bem'];
		}else{
			echo $rs['bem'];
		}
		
		$aux++;
	}
	die;
}

if( $_GET['del'] == 1 ){
	$id = $_GET['id'];
	$sql = "delete from grupo_bem where grupo = '$id' ";
	mysqli_query ( $con,  $sql  );
	$sql = "delete from grupo where id = '$id' ";
	mysqli_query ( $con,  $sql  );
	
	$_SESSION['msg'] = "Grupo removido com sucesso!";
	header("Location: adm.grupos.php");
	die;
}


if( $_POST['v_acao'] == 'UPDATE' ){
	
	$id_grupo = $_POST['v_id_grupo'];
	$grupo = $_POST['v_grupo'];
	$senha = $_POST['v_senha'];
	$veiculos = $_POST['v_veiculos'];
	
	$sql = "update grupo set nome = '$grupo' where id = '$id_grupo'";
	mysqli_query ( $con,  $sql  );
	
	if($senha){
		$sql = "update grupo set senha = '". md5( $senha ) ."' where id = '$id_grupo'";
		mysqli_query ( $con,  $sql  );
	
	}
	
	// deleta todos veiculos
	$sql = "delete from grupo_bem where grupo = '$id_grupo' ";
	mysqli_query ( $con,  $sql  );
	
	// cadastra tudo de novo
	// explode os veiculos
	$arVeiculos = explode("," , $veiculos );
	
	foreach( $arVeiculos as $k => $v ){
		
		$sql_bem = "select id, cliente, imei, name from bem where id = '" . $v . "'";
		$stm_bem = mysqli_query ( $con,  $sql_bem  );
		$rs_bem = mysqli_fetch_array( $stm_bem );
		
		$sql = "INSERT INTO grupo_bem( bem, cliente, imei, descricao, grupo ) 
		VALUES ( '". $rs_bem['id'] ."' , '". $rs_bem['cliente'] ."' , '". $rs_bem['imei'] ."' , '". $rs_bem['name'] ."' , '". $id_grupo ."' ) ";

		mysqli_query ( $con,  $sql  );
		
	}
	
	
	echo "Grupo atualizado com sucesso!";
	die;
	
}

if( $_POST['v_acao'] == 'INSERT' ){
	
	$grupo = $_POST['v_grupo'];
	$senha = $_POST['v_senha'];
	$veiculos = $_POST['v_veiculos'];
	
	$sql = "INSERT INTO grupo( nome, senha, cliente, grupo ) 
	VALUES ( '". $grupo ."', '". md5 ( $senha ) ."', '". $_SESSION['idClienteSession'] ."', 0 ) ";
	
	mysqli_query ( $con,  $sql  );
	
	// recupera o ultimo id do grupo
	$sql = "select max(id) as uid from grupo";
	$stm = mysqli_query ( $con,  $sql  );
	$rs = mysqli_fetch_array( $stm );
	
	$id_grupo = $rs['uid'];
	
	// explode os veiculos
	$arVeiculos = explode("," , $veiculos );
	
	foreach( $arVeiculos as $k => $v ){
		
		$sql_bem = "select id, cliente, imei, name from bem where id = '" . $v . "'";
		$stm_bem = mysqli_query ( $con,  $sql_bem  );
		$rs_bem = mysqli_fetch_array( $stm_bem );
		
		$sql = "INSERT INTO grupo_bem( bem, cliente, imei, descricao, grupo ) 
		VALUES ( '". $rs_bem['id'] ."' , '". $rs_bem['cliente'] ."' , '". $rs_bem['imei'] ."' , '". $rs_bem['name'] ."' , '". $id_grupo ."' ) ";

		mysqli_query ( $con,  $sql  );
		
	}
	
	echo "Grupo cadastrado com sucesso!";
	die;
	
}


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" >
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
	<meta charset="utf-8"/>
	<title>CONTROLTRACKER - RASTREAMENTO - Rastreamento de Veículos</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1" name="viewport"/>
	<meta content="" name="description"/>
	<meta content="" name="author"/>
    <?php include_once("css_header.php"); ?>
</head>
<!-- END HEAD -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid">
	<!-- BEGIN HEADER -->
	<div class="page-header navbar navbar-fixed-top">
		<!-- BEGIN HEADER INNER -->
		<div class="page-header-inner ">
			<!-- BEGIN LOGO -->
			<div class="page-logo">
				<a href="index.php"><?php require_once("logo.php"); ?></a>
			


				<div class="menu-toggler sidebar-toggler">
					<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
				</div>
			</div>
			<!-- END LOGO -->
			<!-- BEGIN RESPONSIVE MENU TOGGLER -->
			<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
			<!-- END RESPONSIVE MENU TOGGLER -->
			<!-- BEGIN PAGE ACTIONS -->
			<!-- DOC: Remove "hide" class to enable the page header actions -->

			<!-- END PAGE ACTIONS -->
			<!-- BEGIN PAGE TOP -->
			<div class="page-top">
				<!-- BEGIN HEADER SEARCH BOX -->
				<!-- DOC: Apply "search-form-expanded" right after the "search-form" class to have half expanded search box -->

				<!-- END HEADER SEARCH BOX -->
				<!-- BEGIN TOP NAVIGATION MENU -->
				<?php require_once("inc.menu_topo.php"); ?>
				<!-- END TOP NAVIGATION MENU -->
			</div>
			<!-- END PAGE TOP -->
		</div>
		<!-- END HEADER INNER -->
	</div>
	<!-- END HEADER -->
	<!-- BEGIN HEADER & CONTENT DIVIDER -->
	<div class="clearfix"> </div>
	<!-- END HEADER & CONTENT DIVIDER -->
	<!-- BEGIN CONTAINER -->
	<div class="page-container">
		<!-- BEGIN SIDEBAR -->
		<style>
				
				@media screen  and (min-device-width: 768px)  and (max-device-width: 1600px)  { 
	  				.page-sidebar-wrapper {
						height: 0px!important;
					}
				}			
				
			</style>
		<div class="page-sidebar-wrapper">
			<!-- END SIDEBAR -->
			<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
			<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
			<?php require_once("inc.menu_lateral.php"); ?>
			<!-- END SIDEBAR -->
		</div>
		<!-- END SIDEBAR -->
		<!-- BEGIN CONTENT -->
		<div class="page-content-wrapper">
			<!-- BEGIN CONTENT BODY -->
			<div class="page-content">
				<!-- BEGIN PAGE HEADER-->
				<!-- BEGIN THEME PANEL -->

				<!-- END THEME PANEL -->
				<h1 class="page-title"> Atendimentos
                        
                    </h1>
                    
                    <?php
						if( $_SESSION['msg'] ){
							echo ' <div class="alert alert-danger fade in alert-dismissable">
    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
    	' . $_SESSION['msg'] . '
</div>';
							
							$_SESSION['msg'] = NULL;
						}
					?>
                   
				<div class="page-bar">
					<ul class="page-breadcrumb">
						<li>
							<i class="icon-home"></i>
							<span>Portal</span>
						</li>
						<li>
							<i class="icon-users"></i>
							<span>Atendimentos</span>
							
						</li>
						
						
					</ul>

				</div>
				<!-- END PAGE HEADER-->


                <div class="row">
                	<div class="col-lg-12 col-xs-12 col-sm-12">
                		<!-- BEGIN REGIONAL STATS PORTLET-->
                		<div class="portlet light ">

                	<a href="#modal-grupos" data-toggle="modal" class="btn btn-primary" style="margin-bottom:10px;"> <i class="fa fa-plus"></i> Novo </a>


                 <script>
					 
					 
					 
			
					 
			function deletaGrupo( id ){
				if( confirm('Deseja excluir este grupo?') ){
					document.location = 'adm.grupos.php?delete=1&id=' + id ;
				}else{
					return false;
				}
			}
			function editarUsuario( id , t ){
				
				$(t).button('loading');
				
				
			}
		</script>       		

          <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
              <tr>
                <th><span>id</span></th>
                <th><span>Veiculo</span></th>
                <th><span>Usuario</span></th>
                <th><span>Descri&ccedil;&atilde;o</span></th>
                <th><span>Data</span></th>
                <th><i class="fa fa-cogs"></i> <span>A&ccedil;&atilde;o</span></th>
              </tr>
            </thead>
            <tbody>
            	 <?php
				
				 if( $_SESSION['clienteSession'] == 'master' ){
					
					 $sql = "SELECT * FROM `atendimento` WHERE id_admin = $id_admin";
					// SELECT * FROM `atendimento` WHERE 1

					
				 }
				
				
                  $stm = mysqli_query( $con, $sql);
				  $rs = mysqli_fetch_assoc($stm);
				  $veiculo = $rs['id_veic'];
				 $sqlve = "SELECT * FROM bem WHERE id = $veiculo";
                  $stmve = mysqli_query( $con, $sqlve);
				  
				  $linha = mysqli_fetch_assoc($stmve);
				//$rsve = mysqli_fetch_array($stmve);
				                  $stmr = mysqli_query( $con, $sql);

                  while ($rsa = mysqli_fetch_array($stmr)) {
                    echo "<tr>
							<td>". $rsa['id'] ."</td>
							<td>". $linha['name'] ."</td>
							<td>". $rsa['user'] ."</td>
							<td>". $rsa['descri'] ."</td>
							<td>". $rsa['data'] ."</td>
							<td>";
					  
					  
					  
					  echo "<a href='javascript:;' data-id='". $rs['id'] ."' data-descrica='". $rsa['descri'] ."' data-veicul='". $linha['imei'] ."' data-date='". $rsa['data'] ."' class='btn btn-outline btn-circle btn-sm purple' onClick='editarGrupo(this)'>
                                                                        <i class='fa fa-edit'></i> Editar
                                                                    </a>
																	
																	<a href=javascript:;' class='btn btn-outline btn-circle btn-sm dark' onClick='deletarGrupo(this)' data-id='". $rs['id'] ."' >
                                                                        <i class='fa fa-trash'></i> Excluir
                                                                    </a> ";
					  echo " 
																	</td>
						  </tr>	
						";
                  }
                  ?>
            </tbody>
            </table>		

                                
                        </div>
                    </div>
                </div>

			</div>
			<!-- END CONTENT BODY -->
		</div>
		<!-- END CONTENT -->

	</div>
	<!-- END CONTAINER -->
	<!-- BEGIN FOOTER -->
	<div class="page-footer">
		<div class="page-footer-inner"> 2016 &copy; Control Tracker
			<div class="scroll-to-top">
				<i class="icon-arrow-up"></i>
			</div>
		</div>
    </div>
		<!-- END FOOTER -->
		<?php include_once('js_arqs.php'); ?>
		
		<!-- MODAL km-->

<div class="modal fade" id="modal-grupos">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title">Atendimentos</h3>
      </div>
      <form action="" method="POST" class="form-horizontal" role="form">
        <div class="modal-body">
          
          <div class="row">
		  
		  <div class="col-sm-6">
                   		<label class="control-label"><strong>Veiculo</strong></label>
								<?=$veiculosStr?>
			</div>
				
				
            <div class="col-lg-6">
              <label class="control-label"><strong>Data</strong></label>
              <input type="date" class="form-control" id="data" name="data" >
            </div>  
             
               <div class="col-lg-12">
              <label class="control-label"><strong>Atendimento</strong></label>
              <input type="text" class="form-control" id="atendimento" name="atendimento" >
          </div> 	
        </div>
        <div class="modal-footer">
         <input type="hidden" id="acao_grupo" value="INSERT">
         <input type="hidden" id="id_grupo" value="">
          <button type="button" class="btn btn-default" onclick="cadastrarGrupo(this)" title="Clique para habilitar a edição dos campos"><i class="fa fa-edit"></i> Salvar </button>
          
          <script type="text/javascript">
				
			  function cadastrarGrupo( t ){
				  var acao = $("#acao_grupo").val();
				  var nome_grupo = $("#nome_grupo").val();
				  if( nome_grupo == '' ){
					  alert('Preencha o nome do grupo');
					  return false;
				  }
				  
				  var senha_grupo = $("#senha_grupo").val();
				  if( senha_grupo == '' && acao == 'INSERT'){
					  alert('Preencha a senha do grupo');
					  return false;
				  }
				  
				  // veiculos
				var id_veiculos = '';
				$(".bem_grupo").each(function(){
					
					if( $(this).prop("checked") == true ){
						
						if( id_veiculos == '' ){
							id_veiculos = $(this).val();
						}else{
							id_veiculos = id_veiculos + ',' + $(this).val();
						}
						
					}
					
				});
				  
				  if( id_veiculos == '' ){					  
					  alert('Informe pelo menos um veículo no grupo');
					  return false;					  
				  }
				  
				  $(t).button("loading");
				  
				  
				  var id_grupo = $("#id_grupo").val();
				  
				  $.post('adm.grupos.php' , {					
					v_acao : acao
					, v_grupo : nome_grupo
					, v_senha : senha_grupo
					, v_veiculos : id_veiculos						
					, v_id_grupo : id_grupo
				} , function(data){
					alert(data);
					document.location = 'adm.grupos.php';
				});
				  
			  }
			  
			  function deletarGrupo( t ){
				  var id = $(t).data('id');
				  if( confirm('Deseja remover o grupo?') ){
					  document.location = 'adm.grupos.php?del=1&id=' + id ;
				  }else{
					  return false;
				  }
			  }
			  
			  function editarGrupo( t ){
				  
				  var id = $(t).data('id');
				  var descr = $(t).data('descrica');
				  var veiculo = $(t).data('veicul');
				  var datea = $(t).data('date');
				 // var grupo = $(t).data('grupo');
				  
				  $("#acao_grupo").val('UPDATE');
				  $("#id_grupo").val( id );
				  $("#atendimento").val( descr );
				  $("#imei").val( veiculo );
				  $("#data").val( datea );
				  //$("#senha_grupo").val( '' );
				  
				  
					  
					  $("#modal-grupos").modal({ show : true });
				  
				  
				  
			  }
			  
			  function inArray(needle, haystack) {
					var length = haystack.length;
					for(var i = 0; i < length; i++) {
						if(haystack[i] == needle) return true;
					}
					return false;
				}
			  
		  </script>
          
        </div>
      </form>
    </div>
    <!-- /.modal-content --> 
  </div>
  
  <!-- /.modal-dialog --> 
</div>
<!-- /.modal --> 
		
		
</body>

</html>
  
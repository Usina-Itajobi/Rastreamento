<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */

$datei = date("Y-m-d", strtotime(date("Y-m-d") . "-1 day"));
$datef = date("Y-m-d", strtotime(date("Y-m-d")));
$datehi = "00:00";
$datehf = "23:59";


function protejeInject($str)
{
    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
    return $sql;
}

$login = protejeInject($_POST['v_login']);
if (!$login) {
    $login = protejeInject($_GET['v_login']);
}

require_once("config.php");

$con         = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
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


$h = protejeInject($_POST['h']);
if (!$h) {
    $h = protejeInject($_GET['h']);
}

if ($h) {



    $sql =
        "SELECT 
                                            CAST(a.id AS DECIMAL(10,0)) as id_cliente , apelido 
                                   FROM cliente a 
                                  WHERE (a.h = '" . $h . "')
                                    
                                  LIMIT 1";



    $stm = mysqli_query($con, $sql) or die('Unable to execute query.');
    $rs = mysqli_fetch_array($stm);
    $id_cliente = $rs['id_cliente'];
    $apelido = $login;
}


?>
<style>
    .flex-box {
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .content-box {
        text-align: center;
    }
</style>

<link href="https://getbootstrap.com/docs/4.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../ctracker/plugins/fontawesome-free/css/all.min.css">

<body style="background-color:#f5f6fa;padding:0px;margin:0px;">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div class="container">
        <div class="row" style="padding-top:15px;">
            <input type='hidden' id='v_login' value='<?php echo $apelido; ?>'>



        </div>
        <div class="flex-box">
            <div class="content-box">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend"><i class="fas fa-car"></i></span>
                    </div>
                    <select class="custom-select" id="veiculo" required>
                        <option value=""> - Selecione - </option>
                        <option value="TODOS">Todos os Veículos</option>

                        <?php
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
                        if (!$id_bem) {
                            $id_bem = protejeInject($_GET['v_id_bem']);
                        }

                        if ($id_bem != '') {
                            $sql .= "
                                         and a.id = '" . $id_bem . "'
                         ";
                        }

                        $sql .= "
                                         and a.imei = b.imei
                             order by 
                                         a.name";

                        if ($id_cliente) {
                            $stm  = mysqli_query($con, $sql);

                            while ($rs = mysqli_fetch_array($stm)) {

                                echo "<option value='" . utf8_encode($rs['imei']) . "'>" . utf8_encode($rs['name']) . "</option>";
                            }

                            echo "<option value='' disabled>-- GRUPO --</option>";

                            $sqlgrp = "select 
				  								a.id
												, a.nome
												
										from 
												grupo a												
										where
												a.cliente = '" .  $id_cliente . "'
										order by 
												a.nome";

                            $stm_grp = mysqli_query($con, $sqlgrp);
                            while ($rsgrp = mysqli_fetch_assoc($stm_grp)) {
                                $v_grp = 'g-' . $rsgrp['id'];
                                echo  "<option value='" . $v_grp . "'>" . $rsgrp['nome'] . "</option>";
                            }
                        } else {

                            $limitesql = "select COUNT(b.bem) as limite from grupo c, grupo_bem b where b.grupo = c.id and c.nome = '$login'";
                            $limitesql = mysqli_query($con, $limitesql);
                            $limitesql = mysqli_fetch_array($limitesql);

                            $sqls = "SELECT DISTINCT a.id , a.name , b.latitudeDecimalDegrees , b.longitudeDecimalDegrees , a.tipo , b.address ,
                              DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia , b.speed , b.ligado , a.imei , b.voltagem_bateria , a.bloqueado , a.ancora ,
                               b.km_rodado from bem a , loc_atual b where a.activated = 'S' and a.id in (select b.bem from grupo c, grupo_bem b 
                               where b.grupo = c.id and c.nome = '$login') LIMIT " . $limitesql['limite'] . "";
                            $stms  = mysqli_query($con, $sqls);

                            while ($rss = mysqli_fetch_array($stms)) {

                                echo "<option value='" . utf8_encode($rss['imei']) . "'>" . utf8_encode($rss['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <!-- <select class="custom-select" id="veiculo" required>
                        <option value=""> - Selecione - </option>


                        <?php

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
                        if (!$id_bem) {
                            $id_bem = protejeInject($_GET['v_id_bem']);
                        }

                        if ($id_bem != '') {
                            $sql .= "
										and a.id = '" . $id_bem . "'
						";
                        }

                        $sql .= "
										and a.imei = b.imei
							order by 
										a.name";

                        if ($id_cliente) {
                            $stm  = mysqli_query($con, $sql);

                            while ($rs = mysqli_fetch_array($stm)) {

                                echo "<option value='" . utf8_encode($rs['imei']) . "'>" . utf8_encode($rs['name']) . "</option>";
                            }
                        } else {

                            $limitesql = "select COUNT(b.bem) as limite from grupo c, grupo_bem b where b.grupo = c.id and c.nome = '$login'";
                            $limitesql = mysqli_query($con, $limitesql);
                            $limitesql = mysqli_fetch_array($limitesql);

                            $sqls = "SELECT DISTINCT a.id , a.name , b.latitudeDecimalDegrees , b.longitudeDecimalDegrees , a.tipo , b.address ,
                             DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia , b.speed , b.ligado , a.imei , b.voltagem_bateria , a.bloqueado , a.ancora ,
                              b.km_rodado from bem a , loc_atual b where a.activated = 'S' and a.id in (select b.bem from grupo c, grupo_bem b 
                              where b.grupo = c.id and c.nome = '$login') LIMIT " . $limitesql['limite'] . "";
                            $stms  = mysqli_query($con, $sqls);

                            while ($rss = mysqli_fetch_array($stms)) {

                                echo "<option value='" . utf8_encode($rss['imei']) . "'>" . utf8_encode($rss['name']) . "</option>";
                            }
                        }

                        // 			$sql = "select 
                        // 				a.id
                        // 				, a.name 
                        // 				, b.latitudeDecimalDegrees
                        // 				, b.longitudeDecimalDegrees
                        // 				, a.tipo
                        // 				, b.address
                        // 				, DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
                        // 				, b.speed
                        // 				, b.ligado
                        // 				, a.imei
                        // 				, b.voltagem_bateria
                        // 				, a.bloqueado
                        // 				, a.ancora
                        // 				, b.km_rodado

                        // 	from 
                        // 				bem a
                        // 				, loc_atual b
                        //     where 


                        // 				a.activated = 'S' and 
                        // 				(
                        // 					a.cliente = " . trim($id_cliente) . " 
                        // 					or
                        // 					a.cliente in (
                        // 						select c.id from cliente c where c.id_admin = '" . trim($id_cliente) . "'
                        //                     )

                        //                 )



                        // 			";
                        // 			$id_bem = $_POST['v_id_bem'];
                        // 			if(!$id_bem){
                        // 				$id_bem = protejeInject ( $_GET['v_id_bem'] );
                        // 			}

                        // 			if( $id_bem != '' ){
                        // 				$sql .= "
                        // 								and a.id = '" . $id_bem . "'
                        // 				";
                        // 			}

                        // 			$sql .= "
                        // 								and a.imei = b.imei
                        // 					order by 
                        // 								a.name";

                        //             if($id_cliente)
                        //                 {                       $stm  = mysqli_query ( $con, $sql );

                        //                     while( $rs = mysqli_fetch_array( $stm ) ){

                        //                         echo "<option value='". utf8_encode ( $rs['imei'] ) ."'>". utf8_encode ( $rs['name'] ) ."</option>";

                        //                     }
                        //                 }else
                        //                 {

                        // 			$sqls = "select 
                        //             a.id
                        //             , a.name 
                        //             , b.latitudeDecimalDegrees
                        //             , b.longitudeDecimalDegrees
                        //             , a.tipo
                        //             , b.address
                        //             , DATE_FORMAT(b.date, '%d/%m/%Y %H:%i:%s') as dia
                        //             , b.speed
                        //             , b.ligado
                        //             , a.imei
                        //             , b.voltagem_bateria
                        //             , a.bloqueado
                        //             , a.ancora
                        //             , b.km_rodado

                        // from 
                        //             bem a
                        //             , loc_atual b
                        // where 

                        // a.id in (
                        //     select b.bem from grupo c, grupo_bem b where b.grupo = c.id and c.nome = '" . $login . "'
                        // ) limit 10




                        //         ";
                        //         $stms  = mysqli_query ( $con, $sqls );

                        // 			while( $rss = mysqli_fetch_array( $stms ) ){

                        // 				echo "<option value='". utf8_encode ( $rss['imei'] ) ."'>". utf8_encode ( $rss['name'] ) ."</option>";

                        // 			}
                        //         }
                        ?>
                    </select> -->
                    <?php
                    // echo $sql;
                    // echo $sqls;
                    ?>
                    <div class="invalid-feedback">Example invalid custom select feedback</div>
                    <div class="invalid-feedback">
                        Please choose a username.
                    </div>
                </div>
            </div>
        </div>

        <p>
            <center><strong style="color:#696;">Selecione um per&iacute;odo </strong></center>
        </p>
        <div class="flex-box">

            <div class="content-box">
                <div class="content-box" style="margin:4%;  ">
                    <label>Data Inicial</label>
                    <input type="date" id="data_ini" style="width: 100%;" class="form-control" value="<?php echo $datei; ?>" />
                </div>
                <div class="content-box" style="margin:4%; ">
                    <label>Hora Inicial</label>
                    <input type="time" id="hora_ini" class="form-control" value="<?php echo $datehi; ?>" />
                </div>

            </div>

            <div class="content-box">
                <div class="content-box" style="margin:4%; ">
                    <label>Data Final</label>
                    <input type="date" id="data_fim" class="form-control" value="<?php echo $datef; ?>" />
                </div>
                <div class="content-box" style="margin:4%; ">
                    <label>Hora Final</label>
                    <input type="time" id="hora_fim" class="form-control" value="<?php echo $datehf; ?>" />
                </div>

            </div>
        </div>
        <div class="content-box">
            <div class="content-box" style="margin:4%; ">
                <input type="button" id="btnrelkm" value="Gerar Relat&oacute;rio" class="content-box btn btn-success" onClick="gerarRelatorio();" />
                <button type="button" id="botaokm" class="btn btn-outline-success" data-toggle="modal" data-target="#graficokm"><i class="fa fa-chart-bar"></i> Gráfico </button>
            </div>
            <!-- <-- botao grafico -->
            <!-- <div class="content-box" style="margin:4%; ">
            </div> -->
        </div>

        <div id="loading">
        </div>

        <div class="row">
            <div class="col-xs-6">

            </div>
        </div>

        <div class="table-responsive">
            <div style="margin:4%;  " id="resultadoAjaxRelatorio"></div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $("#botaokm").hide();
    function gerarRelatorio() {

        var veiculo = $("#veiculo").val();
        var data_ini = $("#data_ini").val();
        var hora_ini = $("#hora_ini").val();
        var data_fim = $("#data_fim").val();
        var hora_fim = $("#hora_fim").val();
        var speed = $("#speed").val();

        if (veiculo == '') {
            alert('Selecione um veiculo');
            return false;
        }

        if (data_ini == '') {
            alert('Selecione a data inicial');
            return false;
        }

        if (hora_ini == '') {
            alert('Selecione a hora inicial');
            return false;
        }

        if (data_fim == '') {
            alert('Selecione a data final');
            return false;
        }

        if (hora_fim == '') {
            alert('Selecione a hora final');
            return false;
        }



        //			abreLoading();

        var login = $("#v_login").val();
        $.ajax({
            url: 'https://api.ctracker.com.br/metronic/api/relatorio-km-rel.php',
            type: 'POST',
            data: {
                v_login: login,
                v_veiculo: veiculo,
                v_data_ini: data_ini,
                v_hora_ini: hora_ini,
                v_data_fim: data_fim,
                v_hora_fim: hora_fim,
            },
            beforeSend: function() {
                //Aqui adicionas o loader
               $("#btnrelkm").attr('disabled', 'disabled');
                $("#loading").html("<div class='spinner-border text-primary' role='status' style='margin-left:45%;margin-top:10%;'> <span class='sr-only'>Loading...</span></div>");
            },
            success: function(data) {
                $("#resultadoAjaxRelatorio").empty().html(data);
                $("#loading").html('');
                $("#botaokm").show();
                $("#btnrelkm").removeAttr('disabled');;
                grafico1()
            },
            error: function(request, errormsg) {
                //	$(".fundoPreto").hide();
                //	$(".carregandoAjax").hide();
                alert('erro' + errormsg + ' - login ' + login);

            }
        });

    }
</script>
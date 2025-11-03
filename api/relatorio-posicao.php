<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

<?php

/**
 *  @author: Graziani Arciprete - psymics(at)gmail(dot)com
 *  @description: Validar o login e senha enviados pelo App ControlTracker (apenas para cliente)
 */

/**
 *	Função para protejer do SQL Inject
 */

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
    // echo "<br>".md5( strtolower( trim( "andersonvolvo"  ) ) );


    $sql =
        "SELECT 
                                            CAST(a.id AS DECIMAL(10,0)) as id_cliente , apelido 
                                   FROM cliente a 
                                  WHERE (a.h = '" . $h . "')
                                    
                                  LIMIT 1";



    $stm = mysqli_query($con, $sql) or die('Unable to execute query.');
    $rs = mysqli_fetch_array($stm);
    $id_cliente = $rs['id_cliente'];
    $apelido = $rs['apelido'];

    if ($apelido == "") {
        $sql =
            "SELECT 
                                            CAST(a.id AS DECIMAL(10,0)) as id_grupo , nome 
                                   FROM grupo a 
                                  WHERE 1 ";

        $stm  = mysqli_query($con, $sql);

        while ($rs = mysqli_fetch_array($stm)) {
            // echo "<br>".$rs['nome'];
            $nome = md5(strtolower(trim($rs['nome'])));
            if ($h == $nome) {
                $apelido = $rs['nome'];
                $id_grupo = $rs['id_grupo'];
            }
        }
    }
}


?>

<body style="background-color:#f5f6fa;padding:0px;margin:0px;">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div class="container">
        <div class="row" style="padding-top:15px;">
            <style>
                .lds-dual-ring {
                    display: inline-block;
                    width: 64px;
                    height: 64px;
                }

                .lds-dual-ring:after {
                    content: " ";
                    display: block;
                    width: 46px;
                    height: 46px;
                    margin: 1px;
                    border-radius: 50%;
                    border: 5px solid #fff;
                    border-color: #fff transparent #fff transparent;
                    animation: lds-dual-ring 1.2s linear infinite;
                }

                @keyframes lds-dual-ring {
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
                }
            </style>
            <input type='hidden' id='v_login' value='<?php echo $apelido; ?>'>
            <input type='hidden' id='v_h' value='<?php echo $h; ?>'>

            <p class="esconder">
                Selecione o <strong>Relat&oacute;rio</strong> abaixo* (<?php echo $apelido; ?>)
            </p>

            <!-- Button trigger modal -->
            <div class="table-responsive esconder">
                <input type="button" style="margin-bottom:5px;margin-top:5px" class="form-control btn btn-primary" value="Relat&oacute;rio de Velocidade" onclick="relVelocidade();" />
            <!-- <input type="button" style="margin-bottom:5px;margin-top:5px" class="form-control btn btn-primary" value="Relat&oacute;rio de Posi&ccedil;&atilde;o" onclick="relPosicao();" />
            -->

            <input type="button" style="margin-bottom:5px;margin-top:5px" class="form-control btn btn-primary" value="Relat&oacute;rio KM Acumulado" onclick="relKm();" />

                <button type="button" style="margin-bottom:5px;margin-top:5px" class="form-control btn btn-primary" onclick="relPosicao();">
                    Relat&oacute;rio de Posi&ccedil;&atilde;o
                </button>
            </div>

            <!-- <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-primary" onclick="relVelocidade();"><i class="far fa-map-marked-alt"></i>Velocidade</button>
                <button type="button" class="btn btn-primary" onclick="relKm();"><i class="far fa-map-marked-alt"></i>Km Acumulado</button>
                <button type="button" class="btn btn-primary" onclick="relPosicao();"><i class="far fa-globe-americas"></i>Posição</button>
            </div> -->

            <div class="table-responsive" id="resultadoAjaxgeral"></div>
            <div class="table-responsive" id="resultadoAjax"></div>

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Relat&oacute;rio de Posi&ccedil;&atilde;o
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <!-- <button type="button" class="btn btn-primary">Salvar</button>-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom:10px; padding-left:15px;">
                <div class="lds-dual-ring carregandoapp" style="display:none;margin-bottom:10px; padding-left:15px;">
                    <div style="margin:4%;  " id="resultadoAjaxRelatorio"></div>

                </div>
            </div>

            <input type="hidden" id="v_login" value="<?php echo $login; ?>" />
            <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
            <script>
                function abreLoading() {
                    $(".carregandoapp").show();
                }

                function fechaLoading() {
                    $(".carregandoapp").hide();
                }

                function relKm() {
                    abreLoading();
                    var login = $("#v_login").val();

                    $.ajax({
                        url: 'https://api.ctracker.com.br/metronic/api/relatorio-km.php',
                        type: 'POST',
                        data: {
                            v_login: login
                        },
                        success: function(data) {
                            $("#resultadoAjaxgeral").empty().html(data);

                            fechaLoading();
                        },
                        error: function(e) {
                            fechaLoading();
                            alert('erro' + e.status);

                        }
                    });



                }

                function relVelocidade() {
                    abreLoading();
                    var login = $("#v_login").val();
                    $.ajax({
                        url: 'https://api.ctracker.com.br/metronic/api/relatorio-speed-novo.php',
                        type: 'POST',
                        data: {
                            v_login: login
                        },
                        success: function(data) {
                            $("#resultadoAjaxgeral").empty().html(data);

                            fechaLoading();
                        },
                        error: function(e) {
                            fechaLoading();
                            alert('erro' + e.status);

                        }
                    });



                }


                function relPosicao() {
                    //abreLoading();
                    var login = $("#v_login").val();
                    var v_h = $("#v_h").val();
                    $.ajax({
                        url: 'https://api.ctracker.com.br/metronic/api/relatorio-posicao-novo.php',
                        type: 'POST',
                        data: {
                            v_login: login,
                            h: v_h
                        },
                        success: function(data) {
                            $("#resultadoAjaxgeral").empty().html(data);

                            fechaLoading();
                        },
                        error: function(e) {
                            fechaLoading();
                            alert('erro' + e.status);

                        }
                    });



                }
            </script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
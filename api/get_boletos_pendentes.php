<?php

/**
 * Endpoint destinado à listagem dos boletos pendentes do cliente
 */


function protejeInject($str)
{
    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
    return $sql;
}


header('Content-Type: application/json;charset=utf-8');
sleep(1);

/** Login do Usuário */
$tipoAuth = 0;
$login = protejeInject($_POST['h']);
if (!$login) {
    $login = protejeInject($_GET['h']);
}

if (!$login) {
    $tipoAuth = 1;

    $login = protejeInject($_POST['v_login']);
    if (!$login) {
        $login = protejeInject($_GET['v_login']);
    }
}

if ($login == '') {
    /** Retorno do Endpoint da API */
    $retorno = array(
        'errormsg' => 'Preencha o usu&aacute;rio.',
        'error' => 'S',
        'vencidosRecentes' => false,
    );
    echo json_encode($retorno);
    die;
}

require_once("config.php");

/** Conexão ao Banco de Dados */
$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error($con));
mysqli_select_db($con, $DB_NAME);

/** Login do Usuário */
/** Query para validar a autenticação do usuário */
$auth_user = strtolower($login);

if($tipoAuth){
    $sqlUsuario = "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente
        FROM cliente a
        WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "')
        LIMIT 1";

} else {
    $sqlUsuario = "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente
        FROM cliente a
        WHERE (a.h = '" . $auth_user . "')
        LIMIT 1";
}

/** Query para selecionar o ID do usuário no Banco de Dados */
$stm = mysqli_query($con, $sqlUsuario) or die('Unable to execute query.');
/** Resultado da Query contendo o ID do usuário no Banco de Dados */
$rs = mysqli_fetch_array($stm);
/** ID do usuário no Banco de Dados */
$id_cliente = $rs['id_cliente'];

$dataAtual = date("Y-m-d");
$intervalIni = date("Y-m-d", strtotime('-2 days'));
$interval = date("Y-m-d", strtotime('-5 days'));

// Boletos vencidos nos últimos 5 dias (inadimplência recente)
$sqlVencidosRecentes =
    "SELECT
        `conta_corrente`.*
    FROM
        `conta_corrente`
    INNER JOIN
        `cliente` ON `conta_corrente`.`cnrt_id_cliente` = `cliente`.`id`
    INNER JOIN
        `boleto` ON `boleto`.`bol_id` = `conta_corrente`.`cnrt_bol_id`
    WHERE
        `cliente`.`id` = '$id_cliente' AND
        `conta_corrente`.`cnrt_data_pgto` IS NULL AND
        `conta_corrente`.`cnrt_excluido` IS NULL AND
        `conta_corrente`.`cnrt_vencimento` >= '$interval' AND
        `conta_corrente`.`cnrt_vencimento` < '$intervalIni' AND
        (
            CASE
                WHEN `cliente`.`id_admin` = 412 THEN `cliente`.`bloqueio_automatico_cobranca` = 'S'
                ELSE TRUE
            END
        )
    ORDER BY
        `conta_corrente`.`cnrt_id` DESC
    LIMIT 1
    ";
$resultVencidosRecentes = mysqli_query($con, $sqlVencidosRecentes);

// Boletos vencidos há mais de 5 dias (inadimplência grave)
$sqlBoletos =
    "SELECT
        `conta_corrente`.*
    FROM
        `conta_corrente`
    INNER JOIN
        `cliente` ON `conta_corrente`.`cnrt_id_cliente` = `cliente`.`id`
    INNER JOIN
        `boleto` ON `boleto`.`bol_id` = `conta_corrente`.`cnrt_bol_id`
    WHERE
        `cliente`.`id` = '$id_cliente' AND
        `conta_corrente`.`cnrt_data_pgto` is NULL AND
        `conta_corrente`.`cnrt_excluido` is NULL AND
        `conta_corrente`.`cnrt_vencimento` < '$interval' AND
        ( CASE
            WHEN `cliente`.`id_admin` = 412 THEN `cliente`.`bloqueio_automatico_cobranca` = 'S'
            ELSE TRUE
        END )
    ORDER BY
        `conta_corrente`.`cnrt_id` DESC
    ";
$result = mysqli_query($con, $sqlBoletos);

$retorno = [
    'errormsg' => '',
    'error' => 'N',
    'vencidosRecentes' => false,
];
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['cnrt_vencimento']) {


                $row['bol_link'] = null;
                $row['bol_paynumber'] = null;
                if ($row['cnrt_bol_id']) {
                    /** Query para buscar o Link do PDF e o Código de Barras do Boleto */
                    $sql_bol = "SELECT bol_link, bol_paynumber
                        FROM boleto
                        WHERE bol_id = '" . $row['cnrt_bol_id'] . "'
                        LIMIT 1";

                    /** Query para buscar o Link do PDF e o Código de Barras do Boleto */
                    $result_bol = mysqli_query($con, $sql_bol);

                    if ($result_bol) {
                        if (mysqli_num_rows($result_bol) > 0) {
                            /** Resultado da query para buscar o Link do PDF e o Código de Barras do Boleto */
                            $ret = mysqli_fetch_object($result_bol);
                            $row['bol_link'] = $ret->bol_link;
                            $row['bol_paynumber'] = $ret->bol_paynumber;
                        }
                    }
                }
                $retorno['data'][] = $row;
                //$retorno['bol'][] = $response ? json_decode($response) : null;
                //$retorno['pix'][] = $responsepIX ? json_decode($responsepIX) : null;
            }
        }
    } else {
        $retorno['errormsg'] = 'Nenhum dado encontrado';
        $retorno['error'] = 'S';
    }

    if (mysqli_num_rows($resultVencidosRecentes) > 0) {
        $retorno['vencidosRecentes'] = true;
    } else {
        $retorno['vencidosRecentes'] = false;
    }
} else {
    $retorno['errormsg'] = "Ocorreu um erro ao carregar os dados!\nErro:" . mysqli_error($con);
    $retorno['error'] = 'S';
}

echo json_encode($retorno);
die;
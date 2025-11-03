<?php

/**
 * Endpoint destinado à listagem dos últimos 6 boletos em nome do cliente para exibição no app CRastreamento
 */
function protejeInject($str)
{
    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
    return $sql;
}

function save_post_log($postData)
{
	// Nome do arquivo de log
	$logFile = 'log/bill.txt';

	// Cria uma string com a data e hora atuais
	$timestamp = date('Y-m-d H:i:s');

	// Converte o array POST para uma string legível
	$postString = print_r($postData, true);

	// Monta a linha a ser salva no arquivo de log
	$logEntry = "[$timestamp] POST Data: " . $postString . "\n";

	// Abre o arquivo para escrita (cria se não existir) e adiciona o log no final
	file_put_contents($logFile, $logEntry, FILE_APPEND);
}

header('Content-Type: application/json;charset=utf-8');
sleep(1);


$login = protejeInject($_POST['v_login']);
if (!$login) {
    $login = protejeInject($_GET['v_login']);
}

if ($login == '') {
    $retorno = array('errormsg' => 'Preencha o usu&aacute;rio.', 'error' => 'S');
    echo json_encode($retorno);
    die;
}

require_once("config.php");

$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error($con));
mysqli_select_db($con, $DB_NAME);
$auth_user = strtolower($login);

$sql =
    "SELECT CAST(a.id AS DECIMAL(10,0)) as id_cliente
	FROM cliente a
    WHERE (a.email = '" . $auth_user . "' OR a.apelido = '" . $auth_user . "' OR a.h = '" . $auth_user . "')
	LIMIT 1"
;

$stm = mysqli_query($con, $sql) or die('Unable to execute query.');
$rs = mysqli_fetch_array($stm);

$id_cliente = !empty($rs['id_cliente'])?$rs['id_cliente']:0;

$sql = "SELECT *
		FROM conta_corrente
		WHERE (cnrt_id_cliente = '$id_cliente' AND cnrt_id_cliente <> '0' AND COALESCE(cnrt_excluido, '') != 1)
		ORDER BY cnrt_id DESC
        LIMIT 6";

$result = mysqli_query($con, $sql);

$retorno = [];
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $contador = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $retorno['data'][$contador] = $row;
            $retorno['data'][$contador]['bol_link'] = null;
            $retorno['data'][$contador]['bol_paynumber'] = null;

            if ($retorno['data'][$contador]['cnrt_bol_id'] || $retorno['data'][$contador]['bol_paynumber'] !== null) {
                $sql_bol = "SELECT bol_link, bol_paynumber
                            FROM boleto
                            WHERE bol_id = '" . $retorno['data'][$contador]['cnrt_bol_id'] . "'
                            LIMIT 1";

                $result_bol = mysqli_query($con, $sql_bol);

                if ($result_bol) {
                    if (mysqli_num_rows($result_bol) > 0) {
                        $ret = mysqli_fetch_object($result_bol);
                        $retorno['data'][$contador]['bol_link'] = $ret->bol_link;
                        $retorno['data'][$contador]['bol_paynumber'] = $ret->bol_paynumber;
                    }
                }
            }
            $contador++;
        }
    } else {
        $retorno = array('errormsg' => 'Nenhum dado encontrado', 'error' => 'S');
    }

} else {
    $retorno = array('errormsg' => "Ocorreu um erro ao carregar os dados!\nErro:" . mysqli_error($con), 'error' => 'S');
}

echo json_encode($retorno);
die;
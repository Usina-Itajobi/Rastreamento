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

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json;charset=utf-8');
require_once("config.php");

$return = array(
	'data' => [],
	'errormsg' => '',
	'error' => false,
);

try{
    // Verifica se os dados foram enviados
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido", 405);
    }

	$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS);
	if (!$con) {
		throw new Exception("Não foi possível conectar ao MySQL: " . mysqli_connect_error(), 500);
	}

	if (!mysqli_select_db($con, $DB_NAME)) {
		throw new Exception("Não foi possível selecionar o banco de dados: " . mysqli_error($con), 500);
	}

    $data = json_decode(file_get_contents('php://input'), true);
    if(!$data){
        $data = $_POST;
    }

    // Verifica campos obrigatórios
    if (empty($data['h'])) {
        throw new Exception("O idetificador do cliente é obrigatório!", 400);
    }

    if (empty($data['arquivo_base64'])) {
        throw new Exception("O arquivo em PDF é obrigatório!", 400);
    }

    $login = $data['h'];
    $base64 = $data['arquivo_base64'];
    $dataAtual = date('Y-m-d H:i:s');

	$auth_user = strtolower($login);
    // Get Cliente
    $sql =
		"SELECT
			cliente.*
		FROM cliente
			WHERE cliente.h = '" . $auth_user . "'
		LIMIT 1";

	$resultados = mysqli_query($con, $sql);
	if(!$resultados) throw new Exception("Ocorreu um erro! MySQL: " . mysqli_error($con));
	$row_cliente = mysqli_fetch_assoc($resultados);
    if(!$row_cliente){
        throw new Exception("O cliente informado não existe!", 404);
    }
	$cliente_id = $row_cliente['id'];

    // Pasta onde os arquivos serão salvos
    $pastaNome = 'arquivos_contratos';
    $diretorio = __DIR__ . '/../../' . $pastaNome;
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    $arquivoNome = "contrato_" . $cliente_id . "_" . time() . ".pdf";

    $caminhoCompleto = $diretorio . "/" . $arquivoNome;
    $caminhoWeb = "/" . $pastaNome . "/" . $arquivoNome;

    // Remover cabeçalho base64, se houver
    if (strpos($base64, 'base64,') !== false) {
        $base64 = explode('base64,', $base64)[1];
    }

    $conteudoBinario = base64_decode($base64);
    if ($conteudoBinario === false) {
        throw new Exception("Base64 inválido.", 400);
    }

    if (file_put_contents($caminhoCompleto, $conteudoBinario)) {
        //$return['data'] = $pastaNome . "/" . $arquivoNome;
        //$return['message'] = 'Arquivo salvo com sucesso';

        $sql = "INSERT INTO cliente_contrato
            (
                id_cliente,
                arquivo,
                data
            ) values (
                '" . $cliente_id . "' ,
                '" . $caminhoWeb . "' ,
                '" . $dataAtual . "')";

        if (!mysqli_query($con, $sql)) {
            throw new Exception('MySQL Error (insert): ' . mysqli_error($con), 500);
        }

        // Atualiza a coluna novo_contrato para 0
        $update = "UPDATE cliente SET novo_contrato = 0 WHERE id = '" . $cliente_id . "'";
        if (!mysqli_query($con, $update)) {
            throw new Exception('MySQL Error (update): ' . mysqli_error($con), 500);
        }

    } else {
        throw new Exception("Erro ao salvar o arquivo.", 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ? $e->getCode() : 400); // Código 400 se não especificado
    $return['data'] = [];
    $return['error'] = true;
    $return['errormsg'] = $e->getMessage();
} catch (Throwable $t) {
    http_response_code(500);
    $return['data'] = [];
    $return['error'] = true;
    $return['errormsg'] = 'Erro inesperado: ' . $t->getMessage();
} finally {
    echo json_encode($return);
    exit();
}
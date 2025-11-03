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

require_once '/var/www/html/newtemplate/pages/api/Library/dompdf/vendor/autoload.php';
// Inclua a biblioteca DOMPDF
use Dompdf\Dompdf;
use Dompdf\Options;

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

    $login = $data['h'];
    $dataAtual = date('Y-m-d H:i:s');
    $lat = !empty($data['lat'])?$data['lat']:'';
    $long = !empty($data['long'])?$data['long']:'';
    $ip = $_SERVER['REMOTE_ADDR'];

    if ($lat && $long && $dataAtual && $ip){
        // Concatena todos os dados em uma única string
        $entrada = "{$lat},{$long},{$dataAtual},{$ip}";

        // Gera o hash SHA-256
        $hash = hash('sha256', $entrada);
    } else {
        $hash = null;
    }

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

    // Ativar o buffer de saída para capturar o HTML da página
    ob_start();
    require_once '/var/www/html/contrato-texto.php';
    // Captura o conteúdo HTML da página
    $html = ob_get_clean();

    // Configurações do DOMPDF
    $options = new Options();
    $options->set('defaultFont', 'Helvetica'); // Define a fonte padrão
    $options->set('isHtml5ParserEnabled', true); // Ativa o suporte para HTML5 e CSS3
    $options->set('isPhpEnabled', true); // Permite PHP para imagens externas
    $options->set('isRemoteEnabled', true); // Permite o carregamento de recursos remotos

    // Inicializa o DOMPDF com as configurações
    $dompdf = new Dompdf($options);

    // Carrega o conteúdo HTML no DOMPDF
    $dompdf->loadHtml($html);

    // Define o tamanho do papel e a orientação
    $dompdf->setPaper('A4', 'portrait');

    // Renderiza o HTML como PDF
    $dompdf->render();

    $dompdf->add_info('Title', 'Contrato');

    //$dompdf->stream("arquivo.pdf", ["Attachment" => false]);exit;

    // Gera o conteúdo do PDF
    $pdfOutput = $dompdf->output();

    // Pasta onde os arquivos serão salvos
    $pastaNome = 'arquivos_contratos';
    $diretorio = __DIR__ . '/../../' . $pastaNome;
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    $arquivoNome = "contrato_" . $cliente_id . "_" . time() . ".pdf";

    $caminhoCompleto = $diretorio . "/" . $arquivoNome;
    $caminhoCompletoArquivo = "/" . $pastaNome . "/" . $arquivoNome;

    // Salva o arquivo
    file_put_contents($caminhoCompleto, $pdfOutput);

    //$return['data'] = $pastaNome . "/" . $arquivoNome;
    //$return['message'] = 'Arquivo salvo com sucesso';

    $sql = "INSERT INTO cliente_contrato
        (
            id_cliente,
            arquivo,
            latitude,
            longitude,
            ip,
            hash,
            data
        ) values (
            '" . $cliente_id . "' ,
            '" . $caminhoCompletoArquivo . "' ,
            '" . $lat . "' ,
            '" . $long . "' ,
            '" . $ip . "' ,
            '" . $hash . "' ,
            '" . $dataAtual . "')";

    if (!mysqli_query($con, $sql)) {
        throw new Exception('MySQL Error (insert): ' . mysqli_error($con), 500);
    }

    // Atualiza a coluna novo_contrato para 0
    $update = "UPDATE cliente SET novo_contrato = 0 WHERE id = '" . $cliente_id . "'";
    if (!mysqli_query($con, $update)) {
        throw new Exception('MySQL Error (update): ' . mysqli_error($con), 500);
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
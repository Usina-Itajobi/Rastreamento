<?php

/**
 * Endpoint destinado à alteração de senha no app CRastreamento
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

try {
    $token = protejeInject($_POST['token']);
    $senha = protejeInject($_POST['senha']);
    $confirmacaoSenha = protejeInject($_POST['confirmacao_senha']);
    $idCliente = null;
    $idToken = null;

    if ($token == '') {
        $retorno = [
            'errormsg' => 'Token de redefinição de senha não informado',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($senha == '') {
        $retorno = [
            'errormsg' => 'Informe a nova senha',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (strlen($senha) < 8) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo 8 dígitos',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/[a-zA-Z]/", $senha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo uma letra (A-Z)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/\d/", $senha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo um número (1-9)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/[^a-zA-Z\d]/", $senha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo um caractere especial (!#&@)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($confirmacaoSenha == '') {
        $retorno = [
            'errormsg' => 'Informe a confirmação da nova senha',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($confirmacaoSenha != $senha) {
        $retorno = [
            'errormsg' => 'As senha não conferem',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    require_once("config.php");

    $con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS);
    if (!$con) {
        $retorno = [
            'errormsg' => 'Não foi possivel conectar ao MySQL (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }
    mysqli_select_db($con, $DB_NAME);

    $sql = "SELECT
                    `tokens_redefinicao_senha`.*,
                    `cliente`.`id` AS id_cliente
                FROM `tokens_redefinicao_senha`
                INNER JOIN `cliente` ON `tokens_redefinicao_senha`.`cliente_id` = `cliente`.`id`
                WHERE
                    `tokens_redefinicao_senha`.`token` = '$token'
                LIMIT 1
            ";

    $stm = mysqli_query($con, $sql);
    if (!$stm) {
        $retorno = [
            'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    $num = mysqli_num_rows($stm);
    if ($num != 0) {
        $data = mysqli_fetch_assoc($stm);

        if (!$data) {
            $retorno = [
                'errormsg' => 'Ocorreu um erro ao recuperar o token de redefinição de senha!',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        } else {
            $dataAtual = new DateTime();
            $dataExpiracaoToken = isset($data['data_expiracao_token']) ? new DateTime($data['data_expiracao_token']) : null;
            $tokenUtilizado = isset($data['token_utilizado']) ? boolval($data['token_utilizado']) : false;
            $idCliente = isset($data['id_cliente']) ? intval($data['id_cliente']) : null;
            $idToken = isset($data['id']) ? intval($data['id']) : null;

            if ($tokenUtilizado) {
                $retorno = [
                    'errormsg' => 'Acesso Negado (O Token de redefinição de senha já foi processado anteriormente)!',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            } else if ($dataAtual > $dataExpiracaoToken) {
                $retorno = [
                    'errormsg' => 'Acesso Negado (O Token de redefinição de senha expirou)!',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            } else if (!$idCliente) {
                $retorno = [
                    'errormsg' => 'Acesso Negado (Cliente não encontrado)!',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            } else if (!$idToken) {
                $retorno = [
                    'errormsg' => 'Ocorreu um erro ao recuperar o token de redefinição de senha!',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            }
        }
    } else {
        $retorno = [
            'errormsg' => 'Acesso Negado (Token Inválido)!',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    $auth_novaSenha = md5($senha);
    $sqlAtualizarSenha = "UPDATE `cliente`
                            SET `senha` = '$auth_novaSenha'
                            WHERE `id` = '$idCliente'
                        ";

    $stmAtualizarSenha = mysqli_query($con, $sqlAtualizarSenha);
    if (!$stmAtualizarSenha) {
        $retorno = [
            'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    $sqlAtualizarToken = "UPDATE `tokens_redefinicao_senha`
                            SET
                                `token_utilizado` = 1,
                                `updated_at` = '" . date('Y-m-d H:i:s') . "'
                            WHERE `id` = '$idToken'
                        ";

    $stmAtualizarToken = mysqli_query($con, $sqlAtualizarToken);
    if (!$stmAtualizarToken) {
        $retorno = [
            'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else {
        $retorno = [
            'errormsg' => 'Senha alterada com sucesso!',
        ];
        echo json_encode($retorno);
        die;
    }
} catch (\Exception $exception) {
    $retorno = [
        'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . $exception->getMessage() . ')',
        'error' => 'S'
    ];
    echo json_encode($retorno);
    die;
}
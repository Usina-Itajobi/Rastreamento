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
    $login = protejeInject($_POST['v_login']);
    $senhaAtual = protejeInject($_POST['v_senha_atual']);
    $novaSenha = protejeInject($_POST['v_nova_senha']);
    $confirmacaoNovaSenha = protejeInject($_POST['v_confirmacao_nova_senha']);


    if ($login == '') {
        $retorno = [
            'errormsg' => 'Preencha o usuário',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($senhaAtual == '') {
        $retorno = [
            'errormsg' => 'Informe a senha atual.',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($novaSenha == '') {
        $retorno = [
            'errormsg' => 'Informe a nova senha',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (strlen($novaSenha) < 8) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo 8 dígitos',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/[a-zA-Z]/", $novaSenha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo uma letra (A-Z)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/\d/", $novaSenha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo um número (1-9)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (!preg_match("/[^a-zA-Z\d]/", $novaSenha)) {
        $retorno = [
            'errormsg' => 'A nova senha deve conter no mínimo um caractere especial (!#&@)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($novaSenha == $senhaAtual) {
        $retorno = [
            'errormsg' => 'A nova senha deve ser diferente da senha atual',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($confirmacaoNovaSenha == '') {
        $retorno = [
            'errormsg' => 'Informe a confirmação da nova senha',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if ($confirmacaoNovaSenha != $novaSenha) {
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


    $auth_user = strtolower($login);
    $auth_pw = md5($senhaAtual);
    $auth_novaSenha = md5($novaSenha);

    $sql = "SELECT
                DATEDIFF(NOW(), cliente.data_inativacao) AS diasInat, 
                CAST(cliente.id AS DECIMAL(10,0)) AS idCliente,
                cliente.id_admin,
                cliente.*
            FROM cliente 
            WHERE
                (cliente.email = '$auth_user' OR cliente.apelido = '$auth_user') AND
                cliente.senha = '$auth_pw' AND
                ativo = 'S'
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
        $idCliente = $data['idCliente'];

        $sqlAtualizarSenha = "UPDATE cliente
                            SET senha = '$auth_novaSenha'
                            WHERE id = '$idCliente'
                        ";

        $stmAtualizarSenha = mysqli_query($con, $sqlAtualizarSenha);
        if (!$stmAtualizarSenha) {
            $retorno = [
                'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        } else {
            $retorno = [
                'errormsg' => 'Senha alterada com sucesso',
            ];
            echo json_encode($retorno);
            die;
        }
    } else {
        $sql = "SELECT *
                FROM grupo 
                WHERE
                    nome = '$auth_user' AND 
                    senha = '$auth_pw' 
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
            $idGrupo = $data['id'];

            $sqlAtualizarSenha = "UPDATE grupo
                                SET senha = '$auth_novaSenha'
                                WHERE id = '$idGrupo'
                            ";

            $stmAtualizarSenha = mysqli_query($con, $sqlAtualizarSenha);
            if (!$stmAtualizarSenha) {
                $retorno = [
                    'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            } else {
                $retorno = [
                    'errormsg' => 'Senha alterada com sucesso',
                ];
                echo json_encode($retorno);
                die;
            }
        } else {
            $sql = "SELECT
                        DATEDIFF(NOW(), cliente.data_inativacao) AS diasInat,
                        cliente.apelido AS apelido,
                        CAST(cliente.id AS DECIMAL(10,0)) AS idCliente,
                        cliente.id_admin,
                        cliente.*,
                        usuarios.id AS id_usuario,
                        usuarios.nome AS nome
                    FROM cliente 
                    INNER JOIN usuarios ON cliente.id = usuarios.id_cliente 
                    WHERE
                        usuarios.login = '$auth_user' AND
                        usuarios.senha = '$auth_pw'
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
                $id_user = $data['id_usuario'];

                $sqlAtualizarSenha = "UPDATE usuarios
                                    SET senha = '$auth_novaSenha'
                                    WHERE id = '$id_user'
                                ";

                $stmAtualizarSenha = mysqli_query($con, $sqlAtualizarSenha);
                if (!$stmAtualizarSenha) {
                    $retorno = [
                        'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . mysqli_error($con) . ')',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                } else {
                    $retorno = [
                        'errormsg' => 'Senha alterada com sucesso',
                    ];
                    echo json_encode($retorno);
                    die;
                }
            } else {
                $retorno = [
                    'errormsg' => 'A senha atual informada está inválida!',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            }
        }
    }
} catch (\Exception $exception) {
    $retorno = [
        'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . $exception->getMessage() . ')',
        'error' => 'S'
    ];
    echo json_encode($retorno);
    die;
}
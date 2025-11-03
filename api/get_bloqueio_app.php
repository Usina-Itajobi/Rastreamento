<?php

/**
 * Endpoint destinado consultar o acesso do usuário ao aplicativo. Caso ele tenha contas em atraso, seu acesso ao aplicativo será bloqueado.
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
        $retorno = [
            'errormsg' => 'Preencha o usuário.',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    /** Login do Usuário */
    $auth_user = strtolower($login);

    require_once("config.php");

    /** Conexão ao Banco de Dados */
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

    /** Query Tabela `cliente` */
    if($tipoAuth) {
        $sqlCliente = "
            SELECT
                CAST(`id` AS DECIMAL(10,0)) AS id_cliente,
                `bloqueio_automatico_cobranca`
            FROM `cliente`
            WHERE
                (
                    `email` = '$auth_user' OR
                    `apelido` = '$auth_user'
                )

            LIMIT 1
        ";
    } else {
        $sqlCliente = "
            SELECT
                CAST(`id` AS DECIMAL(10,0)) AS id_cliente,
                `bloqueio_automatico_cobranca`
            FROM `cliente`
            WHERE
                h = '$auth_user'
            LIMIT 1
        ";
    }


    /** Query Tabela `cliente */
    $stmCliente = mysqli_query($con, $sqlCliente);
    if (!$stmCliente) {
        $retorno = [
            'errormsg' => 'Ocorreu um erro buscar as informações (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    if (mysqli_num_rows($stmCliente) > 0) {
        /** Resultado da Query */
        $rs = mysqli_fetch_array($stmCliente);

        /** @var null|string */
        $bloqueio_automatico_cobranca = $rs['bloqueio_automatico_cobranca'];

        $retorno = [
            'data' => [
                'bloqueio_automatico_cobranca' => $bloqueio_automatico_cobranca
            ]
        ];
        echo json_encode($retorno);
        die;
    } else {
        /** Query Tabela `grupo` */
        if($tipoAuth) {
            $sqlGrupo = "
                SELECT
                    `grupo`.*,
                    `cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca
                FROM `grupo`
                INNER JOIN `cliente` ON `grupo`.`cliente` = `cliente`.`id`
                WHERE `grupo`.`nome` = '$auth_user'
                LIMIT 1";
        } else {
            $sqlGrupo = "
                SELECT
                    `grupo`.*,
                    `cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca
                FROM `grupo`
                INNER JOIN `cliente` ON `grupo`.`cliente` = `cliente`.`id`
                WHERE `grupo`.`h` = '$auth_user'
                LIMIT 1";
        }

        /** Query Tabela `grupo */
        $stmGrupo = mysqli_query($con, $sqlGrupo);
        if (!$stmGrupo) {
            $retorno = [
                'errormsg' => 'Ocorreu um erro buscar as informações (' . mysqli_error($con) . ')',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        }

        if (mysqli_num_rows($stmGrupo) > 0) {
            $retorno = array(
                'data' => [
                    'bloqueio_automatico_cobranca' => 'N'
                ]
            );
            echo json_encode($retorno);
            die;
        } else {
            /** Query Tabela `usuarios` */
            if($tipoAuth) {
                $sqlUsuario = "
                    SELECT
                        DATEDIFF(NOW(), `cliente`.`data_inativacao`) AS diasInat,
                        `cliente`.`apelido` AS apelido,
                        CAST(`cliente`.`id` AS DECIMAL(10,0)) AS idCliente,
                        `cliente`.`id_admin`,
                        `cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca,
                        `cliente`.*,
                        `usuarios`.`id` AS id_usuario,
                        `usuarios`.`nome` AS nome
                    FROM `cliente`
                    INNER JOIN `usuarios` on `cliente`.`id` = `usuarios`.`id_cliente`
                    WHERE
                        `usuarios`.`login` = '$auth_user'
                    LIMIT 1
                ";
            } else {
                $sqlUsuario = "
                    SELECT
                        DATEDIFF(NOW(), `cliente`.`data_inativacao`) AS diasInat,
                        `cliente`.`apelido` AS apelido,
                        CAST(`cliente`.`id` AS DECIMAL(10,0)) AS idCliente,
                        `cliente`.`id_admin`,
                        `cliente`.`bloqueio_automatico_cobranca` AS bloqueio_automatico_cobranca,
                        `cliente`.*,
                        `usuarios`.`id` AS id_usuario,
                        `usuarios`.`nome` AS nome
                    FROM `cliente`
                    INNER JOIN `usuarios` on `cliente`.`id` = `usuarios`.`id_cliente`
                    WHERE
                        `usuarios`.`h` = '$auth_user'
                    LIMIT 1
                ";
            }

            /** Query Tabela `usuario */
            $stmUsuario = mysqli_query($con, $sqlUsuario);
            if (!$stmUsuario) {
                $retorno = [
                    'errormsg' => 'Ocorreu um erro buscar as informações (' . mysqli_error($con) . ')',
                    'error' => 'S'
                ];
                echo json_encode($retorno);
                die;
            }

            if (mysqli_num_rows($stmUsuario) > 0) {
                $rs = mysqli_fetch_array($stmUsuario);
                $bloqueio_automatico_cobranca = $rs['bloqueio_automatico_cobranca'];

                $retorno = [
                    'data' => [
                        'bloqueio_automatico_cobranca' => $bloqueio_automatico_cobranca
                    ]
                ];
                echo json_encode($retorno);
                die;
            } else {
                $retorno = array(
                    'errormsg' => 'Nenhum usuário encontrado com essas credenciais!',
                    'error' => 'S'
                );
                echo json_encode($retorno);
                die;
            }
        }
    }
} catch (\Exception $exception) {
    $retorno = [
        'errormsg' => 'Ocorreu um erro buscar as informações (' . $exception->getMessage() . ')',
        'error' => 'S'
    ];
    echo json_encode($retorno);
    die;
}
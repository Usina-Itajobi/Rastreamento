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

function validaCPF($value)
{
    try {
        if (!$value || $value === '') {
            return false;
        } else {
            $cpf = $value;

            $firstCounter = 10;
            $secondCounter = 11;
            $sum = 0;

            for ($index = 0; $index < 9; $index++) {
                $sum += intval($cpf[$index]) * $firstCounter;
                $firstCounter--;
            }

            $sum = ($sum * 10) % 11;

            if ($sum === 10 || $sum === 11) {
                $sum = 0;
            }

            if ($sum !== intval($cpf[9])) {
                return false;
            } else {
                $sum = 0;

                for ($index = 0; $index < 10; $index++) {
                    $sum += intval($cpf[$index]) * $secondCounter;
                    $secondCounter--;
                }

                $sum = ($sum * 10) % 11;

                if ($sum === 10 || $sum === 11) {
                    $sum = 0;
                }

                if ($sum !== intval($cpf[10])) {
                    return false;
                }
            }

            return true;
        }
    } catch (\Exception $exception) {
        return false;
    }
}

function enviarWhatsApp($numeroCelular, $mensagemTexto)
{
    $curlMsg = curl_init();
    curl_setopt_array(
        $curlMsg,
        array(
            CURLOPT_URL => 'https://apistart01.megaapi.com.br/rest/sendMessage/megastart-ME1tcFN9Tyk/text',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "messageData": {
                "to": "' . $numeroCelular . '@s.whatsapp.net",
                "text": "' . $mensagemTexto . '"
            }
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ME1tcFN9Tyk'
            ),
        )
    );

    $response = curl_exec($curlMsg);
    curl_close($curlMsg);

    return $response;
}

function formatarNumero($numero)
{
    $numero = preg_replace('/\D/', '', $numero);

    if (strlen($numero) !== 11) {
        return false;
    }

    $ddd = substr($numero, 0, 2);
    $parte1 = substr($numero, 2, 5);
    $parte2 = substr($numero, 7, 4);

    $formatos = [
        "$ddd $parte1$parte2",
        "$ddd $parte1-$parte2",
        "($ddd) $parte1$parte2",
        "($ddd) $parte1-$parte2"
    ];

    return $formatos;
}

function enviaEmail($email, $mensagemTexto)
{
    $msg = '<div style="width: 100%; height: 100%;">
                    <p>Segue em anexo link de redefinição de senha</p>
                    <p>' . $mensagemTexto . '</p>
                    <br>
                    <img style="height: 100%; display: block; margin: 0 auto;" src="http://ctracker.com.br/assets/logo/email_art.png" alt="logo">

                    <b>End:</b> Laura Vieira da Silva Bombonati - 77 Distrito Empresarial 4 - CEP 14.175456 - Sertãozinho - SP <br>
                    <b>WhatsApp:</b> 16 99733-9299 <br>
                    <b>E-mail:</b> comercial@ctracker.com.br / controltracker26@gmail.com
                </div>';

    // Atribuição dos dados da conta remetente (atenção)
    $SMTP['host'] = "smtp.titan.email";
    $SMTP['usuario'] = "comercial@ctracker.com.br";
    $SMTP['senha'] = "Se6ebe7a$";
    $SMTP['from'] = "comercial@ctracker.com.br";
    $SMTP['fromName'] = "Control Tracker";

    // Arquivo de configuração do PHPMailer
    require_once ("/var/www/server/lib/class.phpmailer.php");

    // Configuração do PHPMailer
    $mail = new PHPMailer(true);
    $mail->setLanguage('br');
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();
    $mail->Host = $SMTP['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $SMTP['usuario'];
    $mail->Password = $SMTP['senha'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->From = $SMTP['from'];
    $mail->FromName = $SMTP['fromName'];
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Redefinição de Senha - cRastreamento';
    $mail->Body = $msg;

    // Envio do email
    if (!$mail->send()) {
        return false;
    }

    return true;
}

/**
 * @see https://stackoverflow.com/a/53366274
 */
function ocultarCaracteresString($string = null)
{
    if (!$string) {
        return null;
    }
    $length = strlen($string);
    $visibleCount = (int) round($length / 4);
    $hiddenCount = $length - ($visibleCount * 2);
    return substr($string, 0, $visibleCount) . str_repeat('*', $hiddenCount) . substr($string, ($visibleCount * -1), $visibleCount);
}

header('Content-Type: application/json;charset=utf-8');
sleep(1);

try {
    $metodoRecuperacao = protejeInject($_POST['metodo_recuperacao']);
    $credenciaisRecuperacao = protejeInject($_POST['credenciais_recuperacao']);

    if ($metodoRecuperacao == '') {
        $retorno = [
            'errormsg' => 'Informe o método de recuperação de senha',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    } else if (
        !in_array($metodoRecuperacao, [
            'Placa',
            'CPF',
            'Celular',
            'E-mail'
        ])
    ) {
        $retorno = [
            'errormsg' => 'Método de recuperação de senha inválido. Os métodos permitidos são Placa, CPF, Celular e E-mail',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    require_once ("config.php");
    require_once __DIR__ . '/../../usuario/checkDelinquentAccounts.php';


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

    $idCliente = null;
    $apelido = null;
    $emailEnvio = null;
    $celularEnvio = null;

    if ($metodoRecuperacao === 'Placa') {
        if (!$credenciaisRecuperacao || $credenciaisRecuperacao === '') {
            $retorno = [
                'errormsg' => 'Necessário informar a Placa',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        } else if (!preg_match("/[A-Z]{3}[0-9][0-9A-Z][0-9]{2}/", $credenciaisRecuperacao)) {
            $retorno = [
                'errormsg' => 'Insira uma Placa válida',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        }

        $placa = $credenciaisRecuperacao;

        $sql = "SELECT
                    `cliente`.`id` AS cliente_id,
                    `cliente`.`apelido` AS apelido,
                    `cliente`.`email` AS cliente_email,
                    `cliente`.`celular` AS cliente_celular
                FROM `bem`
                INNER JOIN `cliente` ON `bem`.`cliente` = `cliente`.`id`
                WHERE
                    `bem`.`name` LIKE '%$placa%'
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
            $idCliente = $data['cliente_id'];
            $apelido = $data['apelido'];
            $emailEnvio = $data['cliente_email'];
            $celularEnvio = $data['cliente_celular'];
        } else {
            $retorno = [
                'errormsg' => 'Nenhum cadastro encontrado com a Placa informada',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        }
    } else {
        $sql = null;

        switch ($metodoRecuperacao) {
            case 'CPF':
                if (!$credenciaisRecuperacao || $credenciaisRecuperacao === '') {
                    $retorno = [
                        'errormsg' => 'Necessário informar o CPF',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                } else if (!validaCPF($credenciaisRecuperacao)) {
                    $retorno = [
                        'errormsg' => 'Insira um CPF válido',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                }

                $cpf = $credenciaisRecuperacao;

                $sql = "SELECT
                            `id`,
                            `apelido`,
                            `email`,
                            `celular`
                        FROM `cliente`
                        WHERE
                            `cpf` LIKE '%$cpf%'
                        LIMIT 1
                    ";
                break;
            case 'Celular':
                if (!$credenciaisRecuperacao || $credenciaisRecuperacao === '') {
                    $retorno = [
                        'errormsg' => 'Necessário informar o número de Celular',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                }

                $celSql = "";
                $celular = $credenciaisRecuperacao;

                $formatos = formatarNumero($celular);

                foreach ($formatos as $formato) {
                    if (!empty($celSql))
                        $celSql .= " OR ";

                    $celSql .= " `celular` LIKE '%$formato%' ";
                }

                $sql = "SELECT
                            `id`,
                            `apelido`,
                            `email`,
                            `celular`
                        FROM `cliente`
                        WHERE
                            ( `celular` LIKE '%$celular%' OR $celSql )
                        LIMIT 1
                    ";
                break;
            case 'E-mail':
                if (!$credenciaisRecuperacao || $credenciaisRecuperacao === '') {
                    $retorno = [
                        'errormsg' => 'Necessário informar o endereço de E-mail',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                } else if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $credenciaisRecuperacao)) {
                    $retorno = [
                        'errormsg' => 'Insira um endereço de E-mail válido',
                        'error' => 'S'
                    ];
                    echo json_encode($retorno);
                    die;
                }

                $email = $credenciaisRecuperacao;

                $sql = "SELECT
                            `id`,
                            `apelido`,
                            `email`,
                            `celular`
                        FROM `cliente`
                        WHERE
                            `email` LIKE '%$email%'
                        LIMIT 1
                    ";
                break;
        }

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
            $idCliente = $data['id'];
            $apelido = $data['apelido'];
            $emailEnvio = $data['email'];
            $celularEnvio = $data['celular'];
        } else {
            $retorno = [
                'errormsg' => 'Nenhum cadastro encontrado com o ' . $metodoRecuperacao . ' informado',
                'error' => 'S'
            ];
            echo json_encode($retorno);
            die;
        }
    }

	$h = md5(strtolower(trim($apelido)));
	mysqli_query($con, "UPDATE cliente set h = '$h' WHERE id = '$idCliente'");

    if (checkDelinquentAccounts($con, $idCliente)) {
        $retorno = [
            'pagamento_atraso' => true,
            'h' => $h,
			'errormsg' => 'Favor entrar em contato com Financeiro (16) 99733-9299, existem faturas em atraso, seu acesso foi bloqueado!',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    $token = md5(uniqid($idCliente));
    $urlRedefinicaoSenha = 'https://api.ctracker.com.br/metronic/api/redefinir_senha.php?token=' . $token;
    $mensagemEnvio = 'Segue em anexo link de redefinição de senha:\n'. $urlRedefinicaoSenha;

    $sqlGerarToken = "INSERT INTO `tokens_redefinicao_senha`
                                    (
                                        `cliente_id`,
                                        `token`,
                                        `data_expiracao_token`,
                                        `token_utilizado`,
                                        `origem`,
                                        `created_at`,
                                        `updated_at`
                                    )
                            VALUES
                                    (
                                        '$idCliente',
                                        '$token',
                                        '" . date('Y-m-d H:i:s', strtotime('+2 hours')) . "',
                                        0,
                                        'A',
                                        '" . date('Y-m-d H:i:s') . "',
                                        '" . date('Y-m-d H:i:s') . "'
                                    )
                        ";

    $stmGerarToken = mysqli_query($con, $sqlGerarToken);
    if (!$stmGerarToken) {
        $retorno = [
            'errormsg' => 'Ocorreu ao gerar o token de redefinição de senha (' . mysqli_error($con) . ')',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    /** Indica se o link de redefinição de senha foi enviado com sucesso para o endereço de e-mail */
    $mensagemEnviadaEmail = false;

    /** Indica se o link de redefinição de senha foi enviado com sucesso para o WhatsApp */
    $mensagemEnviadaCelular = false;

    if ((!$celularEnvio || $celularEnvio === '') && (!$emailEnvio || $emailEnvio === '')) {
        $retorno = [
            'errormsg' => 'Ocorreu um erro ao redefinir a senha (E-mail e Celular não localizados)',
            'error' => 'S'
        ];
        echo json_encode($retorno);
        die;
    }

    if ($celularEnvio && $celularEnvio !== '') {
        // Remover todo caractere não numérico
        $celularEnvioSemFormatacao = preg_replace("/[^0-9]/", '', $celularEnvio);

        // Ex: 16999999999
        if (strlen($celularEnvioSemFormatacao) === 11) {
            $celularEnvioSemFormatacao = '55' . $celularEnvioSemFormatacao;
        }

        // Ex: 5516999999999
        if (strlen($celularEnvioSemFormatacao) === 13) {
            $envioWhatsApp = enviarWhatsApp($celularEnvioSemFormatacao, $mensagemEnvio);

            if ($envioWhatsApp) {
                $envioWhatsAppDecoded = json_decode($envioWhatsApp);

                if ($envioWhatsAppDecoded) {
                    $envioWhatsAppDecoded = (object) $envioWhatsAppDecoded;
                    if (!boolval($envioWhatsAppDecoded->error)) {
                        $mensagemEnviadaCelular = true;
                    }
                }
            }
        }
    }

    if ($emailEnvio && $emailEnvio !== '') {
        $envioEmail = enviaEmail($emailEnvio, $mensagemEnvio);

        if ($envioEmail) {
            $mensagemEnviadaEmail = true;
        }
    }

    $retorno = null;
    if ($mensagemEnviadaEmail && !$mensagemEnviadaCelular) {
        $retorno = [
            'errormsg' => 'Link de redefinição de senha enviado para o e-mail ' . ocultarCaracteresString($emailEnvio)
        ];
    } else if ($mensagemEnviadaCelular && !$mensagemEnviadaEmail) {
        $retorno = [
            'errormsg' => 'Link de redefinição de senha enviado para o celular ' . ocultarCaracteresString(preg_replace("/[^0-9]/", '', $celularEnvio))
        ];
    } else if ($mensagemEnviadaEmail && $mensagemEnviadaCelular) {
        $retorno = [
            'errormsg' => 'Link de redefinição de senha enviado para o e-mail ' . ocultarCaracteresString($emailEnvio) . ' e para o celular ' . ocultarCaracteresString(preg_replace("/[^0-9]/", '', $celularEnvio))
        ];
    } else {
        $retorno = [
            'errormsg' => 'Ocorreu um erro ao enviar o link de redefinição de senha',
            'error' => 'S'
        ];
    }

    echo json_encode($retorno);
    die;
} catch (\Exception $exception) {
    $retorno = [
        'errormsg' => 'Ocorreu um erro ao redefinir a senha (' . $exception->getMessage() . ')',
        'error' => 'S'
    ];
    echo json_encode($retorno);
    die;
}
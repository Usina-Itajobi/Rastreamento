<?php
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

$type = '';

$info = protejeInject($_POST['info']);
if (!$info) {
    $info = protejeInject($_GET['info']);
}

if ($info == '') {
    $retorno = array('errormsg' => 'Nenhuma informação foi transpassada', 'error' => 'S');
    echo json_encode($retorno);
    die;
}

require_once("config.php");
$con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);

function validarEmail($string)
{
    return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
}

function formatarCPF($cpf)
{
    // Remove qualquer caractere não numérico do CPF
    $cpf = preg_replace('/\D/', '', $cpf);

    // Verifica se o CPF tem 11 dígitos
    if (strlen($cpf) !== 11) {
        return false; // CPF inválido
    }

    // Formata o CPF no formato padrão
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

function formatarCelular($celular)
{
    // Remove qualquer caractere não numérico do número de celular
    $celular = preg_replace('/\D/', '', $celular);

    // Verifica se o número de celular tem pelo menos 8 dígitos
    if (strlen($celular) < 8) {
        return false; // Número de celular inválido
    }

    // Formata o número de celular no formato padrão
    $parte1 = substr($celular, 0, -4); // Dígitos antes do traço
    $parte2 = substr($celular, -4);    // Últimos 4 dígitos

    return $parte1 . '-' . $parte2;
}

$API_KEY = "2fde0744-9d7c-4a5a-b02a-cb7568108535";
$fone = $_POST['linha'];
$apn = $_POST['apn'];
$dns = $_POST['dns'];
$freq = $_POST['timer'];

function send($sender, $content, $receivers)
{
    global $API_KEY;

    $service_url = "https://sms.comtele.com.br/api/v2/send";
    $payload = [
        "Sender" => $sender,
        "Content" => $content,
        "Receivers" => implode(",", $receivers)
    ];

    $headers = [
        "Content-Type: application/json",
        "Content-Length: " . strlen(json_encode($payload)),
        "auth-key:" . $API_KEY
    ];

    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

    $server_output = curl_exec($curl);
    curl_close($curl);
    $res = json_decode($server_output);

    return $res;
}

function validarCPF($string)
{
    // Remove caracteres não numéricos do CPF
    $cpf = preg_replace('/[^0-9]/', '', $string);

    // Verifica se o CPF tem 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais (CPF inválido)
    if (preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }

    // Calcula os dígitos verificadores
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;

    // Verifica se os dígitos verificadores são válidos
    if ($cpf[9] != $digito1 || $cpf[10] != $digito2) {
        return false;
    }

    return true;
}

function validarCelular($string)
{
    // Remove caracteres não numéricos do número de celular
    $celular = preg_replace('/[^0-9]/', '', $string);

    // Verifica se o número de celular tem entre 10 e 13 dígitos
    if (strlen($celular) < 10 || strlen($celular) > 13) {
        return false;
    }

    return true;
}

if (!validarCPF($info)) {
    if (!validarCelular($info)) {
        if (!validarEmail($info)) {
            $type = 'N';
        } else {
            $type = 'E';
        }
    } else {
        $type = 'T';

        if (strpos($info, '-') !== false) {
            $info = str_replace("-", "", $info);
        }
    }
} else {
    $type = 'C';

    if (strpos($info, '-') !== false) {
        $info = str_replace("-", "", $info);
    }

    if (strpos($info, '.') !== false) {
        $info = str_replace(".", "", $info);
    }
}

function generateRandomPassword($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $password .= $characters[$randomIndex];
    }

    return $password;
}

function enviaEmail($email, $senha)
{
    try {
        $msg = '<div style="width: 100%; height: 100%;">
            <p>Credenciais de acesso</p>
            <br>
            <b>Chave:</b> 16001 <br>
            <b>Login:</b> ' . $email . ' <br>
            <b>Senha:</b> ' . $senha . '
        </div>';

        // Atribuição dos dados da conta remetente (atenção)
        $SMTP['host'] = "smtp.gmail.com";
        $SMTP['usuario'] = "controltrackerAPI@gmail.com";
        $SMTP['senha'] = "njpjjdgrhvraiimn";
        $SMTP['from'] = "controltrackerAPI@gmail.com";
        $SMTP['fromName'] = "Control Tracker";

        // Arquivo de configuração do PHPMailer
        require_once("/var/www/server/lib/class.phpmailer.php");

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

        $mail->Subject = 'Esqueci a senha - CONTROL TRACKER';
        $mail->Body = $msg;

        // Envio do email
        if (!$mail->send()) {
            return false;
        }

        return true;
    } catch (\Throwable $th) {
        return false;
    }
}

if ($type == 'N' || $type == '') {
    $retorno = array(
        'errormsg' => 'Valor não corresponde',
        'error' => 'S'
    );
    echo json_encode($retorno);
    die;
}

$tipo = $_POST['type'];
switch ($tipo) {
    case 'cpf':
        $cpf = $info;
        $cpf_form = formatarCPF($cpf);

        $sql = "SELECT *
                FROM cliente
                WHERE (
                    cpf = '$cpf' OR cpf ='$cpf_form'
                ) AND ativo = 'S'
                LIMIT 1;";
        break;
    case 'email':
        $sql = "SELECT *
                FROM cliente
                WHERE email = '$info' AND ativo = 'S'
                LIMIT 1;";
        break;
    case 'celular':
        $tel = $info;
        $tel_form = formatarCelular($tel);
        $sql = "SELECT *
                FROM cliente
                WHERE (
                    (celular = '$tel' OR celular ='$tel_form')
                    OR
                    (celular2 = '$tel' OR celular2 ='$tel_form')
                    OR
                    (telefone = '$tel' OR telefone ='$tel_form')
                    OR
                    (telefone2 = '$tel' OR telefone2 ='$tel_form')
                ) AND ativo = 'S'
                LIMIT 1;";
        break;
    default:
        $retorno = array(
            'errormsg' => 'tipo não corresponde',
            'error' => 'S'
        );
        echo json_encode($retorno);
        die;
        break;
}

$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $cel = $row['celular'];
    $login = $row['email'];
    $idCliente = $row['id'];
    $generatedPassword = generateRandomPassword();
    $md5Password = md5($generatedPassword);

    $update = "UPDATE `cliente` SET senha = '$md5Password' WHERE id = '$idCliente'";
    $resultUpdate = mysqli_query($con, $update);

    if ($resultUpdate) {
        $envCel = false;

        if (!empty($cel)) {
            $charsToRemove = array(' ', ',', '.', '-', '(', ')');
            $cel = str_replace($charsToRemove, '', $cel);

            if (
                strlen($cel) >= 11
                && strlen($cel) <= 13
                && is_numeric($cel)
            ) {
                if (substr($cel, 0, 2) != '55') {
                    $celular = '55' . $cel;
                } else {
                    $celular = $cel;
                }
                send("Ctracker", "Login APP - Control Tracker     Chave: 16001                      Login: ' . $login . '             Senha: ' . $generatedPassword . '", ["$celular"]);

                $curlMsg = curl_init();
                curl_setopt_array(
                    $curlMsg,
                    array(
                        CURLOPT_URL => 'http://api2.megaapi.com.br:15974/sendMessage?token=M_sh2UKsibYNJ97',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => '{
                        "jid": "' . $celular . '@s.whatsapp.net",
                        "body": "*Login APP - Control Tracker* \\n \\n *Chave*: 16001 \\n *Login*: ' . $login . ' \\n *Senha*: ' . $generatedPassword . '"
                        }',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                        ),
                    )
                );

                curl_exec($curlMsg);
                curl_close($curlMsg);

                $envCel = true;
            }
        }

        if (!empty($login)) {
            $envEmail = enviaEmail($login, $generatedPassword);
        } else {
            $envEmail = false;
        }

        $retorno = array(
            'errormsg' => 'Dados enviados!',
            'envWhatsapp' => $envCel,
            'envEmail' => $envEmail,
            'email' => $login,
            'celular' => $cel,
            'error' => 'N'
        );
        echo json_encode($retorno);
        die;
    } else {
        $retorno = array(
            'errormsg' => 'Não foi possivel atualizar os dados',
            'error' => 'S'
        );
        echo json_encode($retorno);
        die;
    }

    // $retorno = array(
    //     'errormsg' => '',
    //     'error' => 'N',
    //     'id' => $idCliente,
    //     'cel' => $cel,
    //     'email' => $login,
    //     'senha' => $generatedPassword,
    //     'senhamd5' => $md5Password
    // );
    // echo json_encode($retorno);
    // die;
} else {
    $retorno = array(
        'errormsg' => 'Nenhum cliente encontrado',
        'error' => 'S'
    );
    echo json_encode($retorno);
    die;
}

<?php
require_once("config.php");
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


$con         = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("Não foi possivel conectar ao Mysql" . mysqli_error());
mysqli_select_db($con, $DB_NAME);


$requestData = $_REQUEST;
$login = protejeInject($_POST['h']);
if (!$login) {
    $login = protejeInject($_GET['h']);
}

if ($login == '') {
    $retorno = array('errormsg' => 'Preencha o usu&aacute;rio.', 'error' => 'S');
    echo json_encode($retorno);
    die;
}
if (true) {

    if (protejeInject($_POST['acao']) == "gerar") {
        $id = protejeInject($_POST['id_bem']);
        $date = strtotime(date("Y-m-d H:i:s"));
        $md5 = md5($date . $id);
        // echo  $hash = "hash:$date | id:$id";

        // Store the cipher method
        $ciphering = "AES-128-CTR";

        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;

        // Non-NULL Initialization Vector for encryption
        $encryption_iv = '1234567891011121';

        // Store the encryption key
        $encryption_key = "C0ntr@l$$";

        // Use openssl_encrypt() function to encrypt the data
        $encryption = openssl_encrypt(
            $hash,
            $ciphering,
            $encryption_key,
            $options,
            $encryption_iv
        );

        $sql =  "UPDATE `bem` SET `hash_link`= '$md5' WHERE id = $id";
        if (mysqli_query($con, $sql)) {
            $link = "https://itajobi.usinaitajobi.com.br/newtemplate/pages/LinkVeiculo/?hash=$md5&mudatemp=1&tempo=60";

            //            $retorno = "<p>$link<br><font style='color:red'>Link Válido por 24 Horas</font></p>";

            die(json_encode($link));
        }
    }
}

<?php
function protejeInject($str)
{
    $sql = preg_replace("/( from |select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/", "", $str);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = (get_magic_quotes_gpc()) ? $sql : addslashes($sql);
    return $sql;
}

$token = protejeInject($_GET['token']);

$tokenValido = false;
$erro = true;
$mensagemErro = 'Acesso Negado!';

if (!$token || $token === '') {
    $mensagemErro = 'Acesso Negado!';
} else {
    require_once("config.php");

    $con = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS);
    if (!$con) {
        $mensagemErro = 'Não foi possível conectar à base de dados. Por favor, tente novamente mais tarde!';
    } else {
        mysqli_select_db($con, $DB_NAME);

        $query = "SELECT *
                    FROM `tokens_redefinicao_senha`
                    WHERE
                        `token` = '$token'
                    LIMIT 1
                ";

        $execucaoQuery = mysqli_query($con, $query);
        if (!$execucaoQuery) {
            $mensagemErro = 'Ocorreu um erro ao recuperar o token de redefinição de senha (' . mysqli_error($con) . ')';
        } else {
            $num = mysqli_num_rows($execucaoQuery);
            if ($num != 0) {
                $data = mysqli_fetch_assoc($execucaoQuery);

                if (!$data) {
                    $mensagemErro = 'Ocorreu um erro ao recuperar o token de redefinição de senha';
                } else {
                    $dataAtual = new DateTime();
                    $dataExpiracaoToken = isset($data['data_expiracao_token']) ? new DateTime($data['data_expiracao_token']) : null;
                    $tokenUtilizado = isset($data['token_utilizado']) ? boolval($data['token_utilizado']) : false;

                    if ($tokenUtilizado) {
                        $mensagemErro = 'Acesso Negado (O Token de redefinição de senha já foi processado anteriormente)!';
                    } else if ($dataAtual > $dataExpiracaoToken) {
                        $mensagemErro = 'Acesso Negado (O Token de redefinição de senha expirou)!';
                    } else {
                        $tokenValido = true;
                        $erro = false;
                        $mensagemErro = null;
                    }
                }
            } else {
                $mensagemErro = 'Acesso Negado (Token Inválido)!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <link rel="icon" href="./logo.png">
        <title>Redefinição de Senha | CRastreamento</title>
    </head>

    <style>
        body {
            background-color: #F5F6FA;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100vh;
        }

        #main-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 50vw;
        }
    </style>

    <body>
        <!-- SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
            <symbol id="check-circle-fill" viewBox="0 0 16 16" width="24" fill="#FFF">
                <path
                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
            </symbol>
            <symbol id="info-fill" viewBox="0 0 16 16" width="24" fill="#FFF">
                <path
                    d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
            </symbol>
            <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16" width="24" fill="#FFF">
                <path
                    d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </symbol>
        </svg>
        <!-- SVG -->

        <!-- Toast -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
            <div id="toast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <!-- Mensagem -->
                    <div class="toast-body" style="display: flex; align-items: center; justify-content: flex-start;">
                        <svg class="bi flex-shrink-0 me-2" role="img" id="toast-svg"
                            style="max-width: 30px; max-height: 30px;">
                        </svg>
                        <label id="toast-message"></label>
                    </div>
                    <!-- Mensagem -->

                    <!-- Botão Fechar -->
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Fechar"></button>
                    <!-- Botão Fechar -->
                </div>
            </div>
        </div>
        <!-- Toast -->

        <div id="main-container">
            <!-- Spinner -->
            <div style="display: flex; align-items: center;"
                class="<?php echo ($tokenValido || ($erro && $mensagemErro && $mensagemErro !== '') ? 'visually-hidden' : ''); ?>">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                </div>
                <span style="margin-left: 15px; font-weight: 600;" role="status">Carregando...</span>
            </div>
            <!-- Spinner -->

            <!-- Mensagem -->
            <div class="alert alert-danger <?php echo (!$erro || !$mensagemErro || $mensagemErro === '' ? 'visually-hidden' : ''); ?>"
                role="alert">
                <span>
                    <?php echo ($mensagemErro); ?>
                </span>
            </div>
            <!-- Mensagem -->

            <!-- Formulário -->
            <form class="<?php echo ($erro && $mensagemErro && $mensagemErro !== '' ? 'visually-hidden' : ''); ?>">
                <!-- Nova Senha -->
                <div style="width: 400px; max-width: 400px;">
                    <label for="input-senha" class="form-label">Nova Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" aria-describedby="erro-input-senha" id="input-senha"
                            maxlength="100">
                        <button class="input-group-text" type="button" onclick="verSenha()">
                            <i id="icone-input-senha" class="bi bi-eye-fill"></i>
                        </button>
                        <div id="erro-input-senha" class="invalid-feedback">
                            <label id="erro-input-senha-label"></label>
                        </div>
                    </div>
                </div>
                <!-- Nova Senha -->

                <!-- Confirmação Senha -->
                <div style="margin-top: 20px; width: 400px; max-width: 400px;">
                    <label for="input-confirmacao-senha" class="form-label">Confirme a Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" aria-describedby="erro-input-confirmacao-senha"
                            id="input-confirmacao-senha" maxlength="100">
                        <button class="input-group-text" type="button" onclick="verConfirmacaoSenha()">
                            <i id="icone-input-confirmacao-senha" class="bi bi-eye-fill"></i>
                        </button>
                        <div id="erro-input-confirmacao-senha" class="invalid-feedback">
                            <label id="erro-input-confirmacao-senha-label"></label>
                        </div>
                    </div>
                </div>
                <!-- Confirmação Senha -->

                <!-- Botão Enviar -->
                <div style="margin-top: 35px;">
                    <button id="submit-button" type="button" class="btn btn-primary" onclick="redefinirSenha()"
                        style="display: flex; align-items: center; justify-content: space-around; min-width: 200px;">
                        <span> Redefinir Senha</span>

                        <!-- Spinner -->
                        <div id="loading" class="spinner-border visually-hidden" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <!-- Spinner -->
                    </button>
                </div>
                <!-- Botão Enviar -->
            </form>
            <!-- Formulário -->
        </div>
    </body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <script>
        function verSenha() {
            if ($('#icone-input-senha').hasClass('bi-eye-fill')) {
                $('#icone-input-senha').removeClass('bi-eye-fill');
                $('#icone-input-senha').addClass('bi-eye-slash-fill');
                $('#input-senha').attr('type', 'text');
            } else if ($('#icone-input-senha').hasClass('bi-eye-slash-fill')) {
                $('#icone-input-senha').removeClass('bi-eye-slash-fill');
                $('#icone-input-senha').addClass('bi-eye-fill');
                $('#input-senha').attr('type', 'password');
            }
        }

        function verConfirmacaoSenha() {
            if ($('#icone-input-confirmacao-senha').hasClass('bi-eye-fill')) {
                $('#icone-input-confirmacao-senha').removeClass('bi-eye-fill');
                $('#icone-input-confirmacao-senha').addClass('bi-eye-slash-fill');
                $('#input-confirmacao-senha').attr('type', 'text');
            } else if ($('#icone-input-confirmacao-senha').hasClass('bi-eye-slash-fill')) {
                $('#icone-input-confirmacao-senha').removeClass('bi-eye-slash-fill');
                $('#icone-input-confirmacao-senha').addClass('bi-eye-fill');
                $('#input-confirmacao-senha').attr('type', 'password');
            }
        }

        function exibirToast(mensagemAlerta, tipoAlerta) {
            let svg = 'info-fill'
            if (tipoAlerta === 'primary') {
                svg = 'info-fill';
            } else if (tipoAlerta === 'success') {
                svg = 'check-circle-fill';
            } else if (tipoAlerta === 'warning' || tipoAlerta === 'danger') {
                svg = 'exclamation-triangle-fill';
            }

            $('#toast-message').html(mensagemAlerta);
            $('#toast').addClass('bg-' + tipoAlerta);
            $('#toast-svg').html('<use xlink: href="#' + svg + '" />')

            const toastElList = [].slice.call(document.querySelectorAll('.toast'))
            const toastList = toastElList.map(function (toastEl) {
                return new bootstrap.Toast(toastEl)
            })
            toastList.forEach(toast => toast.show())

            setTimeout(() => {
                toastList.forEach(toast => toast.hide())

                $('#toast').removeClass('bg-' + tipoAlerta);
                $('#toast-message').empty();
                $('#toast-svg').empty();
            }, 20000)
        }

        function validarSenha() {
            const SENHA = $('#input-senha').val();
            const CONFIRMACAO_SENHA = $('#input-confirmacao-senha').val();

            try {
                if (!SENHA || SENHA === '') {
                    $('#erro-input-senha-label').html('Informe a nova senha!');
                    $('#input-senha').addClass('is-invalid');
                    return false;
                } else if (!(/^(?=.{8,})/).test(SENHA)) {
                    $('#erro-input-senha-label').html('A nova senha deve conter no mínimo 8 caracteres!');
                    $('#input-senha').addClass('is-invalid')
                    return false;
                } else if (!(/^(?=.*?[a-zA-Z])/).test(SENHA)) {
                    $('#erro-input-senha-label').html('A nova senha deve conter no mínimo uma letra (A-Z)');
                    $('#input-senha').addClass('is-invalid');
                    return false;
                } else if (!(/^(?=.*?[0-9])/).test(SENHA)) {
                    $('#erro-input-senha-label').html('A nova senha deve conter no mínimo um número (0-9)');
                    $('#input-senha').addClass('is-invalid');
                    return false;
                } else if (!(/^(?=.*?[^\w\s])/).test(SENHA)) {
                    $('#erro-input-senha-label').html('A nova senha deve conter no mínimo um caracter especial (!#&@)');
                    $('#input-senha').addClass('is-invalid');
                    return false;
                } else {
                    $('#erro-input-senha-label').empty();
                    $('#input-senha').removeClass('is-invalid');
                }

                if (
                    CONFIRMACAO_SENHA &&
                    CONFIRMACAO_SENHA !== '' &&
                    CONFIRMACAO_SENHA !== SENHA
                ) {
                    $('#erro-input-confirmacao-senha-label').html('As senhas não conferem!');
                    $('#input-confirmacao-senha').addClass('is-invalid')
                    return false;
                } else {
                    $('#erro-input-confirmacao-senha-label').empty();
                    $('#input-confirmacao-senha').removeClass('is-invalid');
                }

                return true;
            } catch (error) {
                $('#erro-input-senha-label').empty();
                $('#erro-input-confirmacao-senha-label').empty();
                $('#input-senha').removeClass('is-invalid');
                $('#input-confirmacao-senha').removeClass('is-invalid');

                exibirToast('Ocorreu um erro ao validar a senha (' + error.message + ')', 'danger');
                return false;
            }
        }

        function validarConfirmacaoSenha() {
            const SENHA = $('#input-senha').val();
            const CONFIRMACAO_SENHA = $('#input-confirmacao-senha').val();

            try {
                if (!CONFIRMACAO_SENHA || CONFIRMACAO_SENHA === '') {
                    $('#erro-input-confirmacao-senha-label').html('Confirme a senha!');
                    $('#input-confirmacao-senha').addClass('is-invalid');
                    return false;
                } else if (
                    SENHA &&
                    SENHA !== '' &&
                    CONFIRMACAO_SENHA !== SENHA
                ) {
                    $('#erro-input-confirmacao-senha-label').html('As senhas não conferem!');
                    $('#input-confirmacao-senha').addClass('is-invalid');
                    return false;
                } else {
                    $('#erro-input-confirmacao-senha-label').empty();
                    $('#input-confirmacao-senha').removeClass('is-invalid');
                    return true;
                }
            } catch (error) {
                $('#erro-input-confirmacao-senha-label').empty();
                $('#input-confirmacao-senha').removeClass('is-invalid');

                exibirToast('Ocorreu um erro ao validar a senha (' + error.message + ')', 'danger');
                return false;
            }
        }

        function redefinirSenha() {
            try {
                const VALIDACAO_SENHA = validarSenha();
                const VALIDACAO_CONFIRMACAO_SENHA = validarConfirmacaoSenha();

                if (!VALIDACAO_SENHA || !VALIDACAO_CONFIRMACAO_SENHA) {
                    return;
                } else {
                    const SENHA = $('#input-senha').val();
                    const CONFIRMACAO_SENHA = $('#input-confirmacao-senha').val();

                    const QUERY = window.location.search;
                    const PARAMS = new URLSearchParams(QUERY);
                    const TOKEN = PARAMS.get('token');

                    if (!TOKEN || TOKEN === '') {
                        exibirToast('Ocorreu um erro ao redefinir a senha (Não foi possível recuperar o Token de redefinição de senha)', 'danger');
                        return;
                    }

                    $.ajax({
                        url: 'https://api.ctracker.com.br/metronic/api/processar_redefinicao_senha.php',
                        type: 'POST',
                        data: {
                            senha: SENHA,
                            confirmacao_senha: CONFIRMACAO_SENHA,
                            token: TOKEN
                        },
                        beforeSend: function () {
                            $('#submit-button').attr('disabled', true);
                            $('#input-senha').attr('readonly', true);
                            $('#input-confirmacao-senha').attr('readonly', true);
                            $('#loading').removeClass('visually-hidden');
                        },
                        success: function (data) {
                            try {
                                if (!data) {
                                    exibirToast('Ocorreu um erro ao redefinir a senha', 'danger');
                                } else {
                                    /**
                                     * @type {{error?: 'S' | 'N'; errormsg?: string;}}
                                    */
                                    const DATA_OBJ = data;

                                    if (!DATA_OBJ) {
                                        exibirToast('Ocorreu um erro ao redefinir a senha', 'danger');
                                    } else if (DATA_OBJ.error && DATA_OBJ.error === 'S') {
                                        const ERROR_MESSAGE = DATA_OBJ.errormsg && DATA_OBJ.errormsg !== '' ? DATA_OBJ.errormsg : 'Ocorreu um erro ao redefinir a senha';
                                        exibirToast(ERROR_MESSAGE, 'danger');
                                    } else {
                                        $('#input-senha').val('');
                                        $('#input-confirmacao-senha').val('');

                                        const RESPONSE_MESSAGE = DATA_OBJ.errormsg && DATA_OBJ.errormsg !== '' ? DATA_OBJ.errormsg : 'Senha redefinida com sucesso!';
                                        exibirToast(RESPONSE_MESSAGE, 'success');

                                        setTimeout(() => {
                                            window.location.replace('https://ctracker.com.br/login.php');
                                        }, 10000)
                                    }
                                }
                            } catch (error) {
                                exibirToast('Ocorreu um erro ao redefinir a senha (' + error.message + ')', 'danger');
                            }
                        },
                        error: function (e) {
                            exibirToast('Ocorreu um erro ao redefinir a senha', 'danger');
                        },
                        complete: function () {
                            $('#submit-button').removeAttr('disabled');
                            $('#input-senha').removeAttr('readonly');
                            $('#input-confirmacao-senha').removeAttr('readonly');
                            $('#loading').addClass('visually-hidden');
                        }
                    })
                }

            } catch (error) {
                exibirToast('Ocorreu um erro ao redefinir a senha (' + error.message + ')', 'danger');
            }
        }
    </script>

</html>
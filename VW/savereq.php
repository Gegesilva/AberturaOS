<?php
session_start();
header('Content-type: text/html; charset=ISO-8895-1');
include_once "../DB/conexaoSQL.php";
include_once "../DB/acoesreq.php";
include_once "../DB/dados.php";
include_once "../Config.php";

$SitCli = "";

$estado = $_POST['estado'];
$local = $_POST['local'];
$email = $_POST['email'];
$ultcont = $_POST['ultcont'];
$serie = $_POST['serie'];
$whatsapp = $_POST['whatsapp'];
$solicitante = $_POST['solicitante'];
$defeito = $_POST['defeito'];
$periodo = $_POST['periodo'];
$tonerPB = $_POST['tonerPB'];
$preto = $_POST['preto'];
$azul = $_POST['azul'];
$amarelo = $_POST['amarelo'];
$magenta = $_POST['magenta'];
$outro = $_POST['outro'];
$CodEmp = $_POST['codEmp'];


if (isset($serie)) {

    list($operacaoVend, $statusVend) = empOper($CodEmp);

    /* Confere se existe uma requisição */
    $sql = "SELECT 
                TB02018_CODIGO Orcamento,
                '1' Exist
            FROM TB02018
            WHERE
                TB02018_NUMSERIE = '$serie'
                AND TB02018_TIPODESC IN ('$operacaoVend')
                AND TB02018_STATUS IN ('$statusVend')
                ORDER BY TB02018_DTCAD DESC";

    $stmt = sqlsrv_query($conn, $sql);
    $orcamentoAberto = "";
    $exist = "";
    $req = "";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orcamentoAberto = $row['Orcamento'];
        $exist = $row['Exist'];
    }

    /* Confere se o cliente esta ativo */
    $sql = "SELECT TOP 1
                TB01008_SITUACAO SitCli,
                TB00012_FONE Tel
            FROM TB02112
            LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
            LEFT JOIN TB01008 ON TB01008_CODIGO = TB02111_CODCLI
            LEFT JOIN TB01007 ON TB01007_CODIGO = TB02111_CODEMP
			LEFT JOIN TB00012 ON TB00012_CODIGO = TB02111_CODEMP AND TB00012_TABELA = 'TB01007'
            WHERE TB02112_NUMSERIE = '$serie'";

    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $SitCli .= $row['SitCli'];
        $Tel .= $row['Tel'];
    }

    if (($exist != '1' || $exist = '' || $exist = NULL) && $SitCli == 'A') {

        list($operacaoVend, $statusVend) = empOper($CodEmp);

        geraReq($conn, $local, $email, $ultcont, $serie, $whatsapp, $solicitante, $defeito, $tonerPB, $preto, $azul, $amarelo, $magenta, $outro, $periodo, $operacaoVend, $statusVend);


        /* Pega o ultimo numero de OS aberto */

        $req .= $ultContGer;

        /* Grava o histórico do primeiro status na abertura */
        gravaHistoricoReq($conn, $serie, $solicitante, $defeito, $statusVend);

        ?>

        <!DOCTYPE html>
        <html lang="pt-BR">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>DATABIT</title>
            <link rel="stylesheet" href="../CSS/style.css">
            <style>
                .div-form {
                    filter: blur(5px);
                }

                .submit-btn {
                    display: block;
                    width: 30%;
                    padding: 10px;
                    background: rgba(0, 0, 255, 0.568);
                    color: white;
                    border: none;
                    border-radius: 20px;
                    font-size: 16px;
                    cursor: pointer;
                    margin-left: auto;
                    margin-right: auto;
                }

                .submit-btn {
                    background: white;
                }

                .voltar-btn-form {
                    background: white;
                }

                .voltar-btn-form:hover {
                    background: white;
                }

                .submit-btn:hover {
                    background: white;
                }
            </style>
        </head>

        <body>
            <div class="div-save">
                <form class="form-voltar" id="form-voltar" action="<?= $url ?>/inputSerie.php">
                    <!-- <img src="../img/logo.jpg" alt="logo"> -->
                    <p class="OSCriadaTx">Sua requisição foi aberta com sucesso!</p>
                    <p class="OSCriadaTx">Abaixo numero para acompanhamento:</p>
                    <h1><b class="OSCriada"><?= $req ?></b></h1>
                    <button onclick="reload()" type="submit" class="popup-btn">Fechar</button>
                </form>
            </div>
            <?php
    }elseif ($SitCli == 'S') {
        ?>

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>DATABIT</title>
                <link rel="stylesheet" href="../CSS/style.css">
                <link rel="stylesheet" href="../CSS/styleBtn.css">
                <style>
                    .div-form {
                        filter: blur(5px);
                    }

                    .submit-btn {
                        display: block;
                        width: 30%;
                        padding: 10px;
                        background: rgba(0, 0, 255, 0.568);
                        color: white;
                        border: none;
                        border-radius: 20px;
                        font-size: 16px;
                        cursor: pointer;
                        margin-left: auto;
                        margin-right: auto;
                    }

                    .submit-btn {
                        background: white;
                    }

                    .voltar-btn-form {
                        background: white;
                    }

                    .voltar-btn-form:hover {
                        background: white;
                    }

                    .submit-btn:hover {
                        background: white;
                    }

                    p {
                        color: blue;
                    }
                </style>
            </head>

            <div class="div-save">
                <form class="form-voltar" id="form-voltar" action="<?= $url ?>/inputSerie.php">
                    <!-- <img src="../img/logo.jpg" alt="logo"> -->
                    <p>Impossibilitado de abrir requisições.</p>
                    <p>Entrar em contato no fone: <?= formatarTelefone($Tel) ?></p>
                    <button onclick="window.location.reload()" type="submit" class="popup-btn">Fechar</button>
                </form>
            </div>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <script src="../JS/script.js" charset="utf-8"></script>
        </body>

        </html>
        <?php
    } else {

        ?>

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>DATABIT</title>
                <link rel="stylesheet" href="../CSS/style.css">
                <link rel="stylesheet" href="../CSS/styleBtn.css">
                <style>
                    .div-form {
                        filter: blur(5px);
                    }

                    .submit-btn {
                        display: block;
                        width: 30%;
                        padding: 10px;
                        background: rgba(0, 0, 255, 0.568);
                        color: white;
                        border: none;
                        border-radius: 20px;
                        font-size: 16px;
                        cursor: pointer;
                        margin-left: auto;
                        margin-right: auto;
                    }

                    .submit-btn {
                        background: white;
                    }

                    .voltar-btn-form {
                        background: white;
                    }

                    .voltar-btn-form:hover {
                        background: white;
                    }

                    .submit-btn:hover {
                        background: white;
                    }

                    b {
                        color: red;
                    }
                </style>
            </head>

            <body>
                <div class="div-save">
                    <form class="form-voltar" id="form-voltar" action="<?= $url ?>/inputSerie.php">
                        <!-- <img src="../img/logo.jpg" alt="logo"> -->
                        <p class="OSCriadaTx">Já existe uma requisição para este equipamento Nº: <b><?= $orcamentoAberto ?></b>!
                        </p>
                        <h1><b class="OSCriada"><?= $req ?></b></h1>
                        <button onclick="reload()" type="submit" class="popup-btn">Fechar</button>
                    </form>
                </div>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
                <script src="../JS/script.js" charset="utf-8"></script>
            </body>

        </html>
        <?php
    }
}
?>
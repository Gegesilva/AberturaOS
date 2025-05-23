<?php
session_start();
header('Content-type: text/html; charset=ISO-8895-1');
include_once "../DB/conexaoSQL.php";
include_once "../DB/acoesreq.php";
include_once "../DB/dados.php";
include_once "../Config.php";

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

    //list($operacaoVend, $statusVend) = empOper($CodEmp);

    /* Confere se existe uma requisição */
    $sql = "SELECT 
                TB02018_CODIGO Orcamento,
                '1' Exist
            FROM TB02018
            WHERE
                TB02018_NUMSERIE = ?
                AND TB02018_TIPODESC IN (?)
                AND TB02018_STATUS IN (?)
                ORDER BY TB02018_DTCAD DESC";

    $params = [
        $serie,
        $operacao,
        $statusVenda
    ];

    $stmt = sqlsrv_prepare($conn, $sql, $params);
    sqlsrv_execute($stmt);
    $orcamentoAberto = "";
    $exist = "";
    $req = "";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orcamentoAberto = $row['Orcamento'];
        $exist = $row['Exist'];
    }

    if ($exist != '1' || $exist = '' || $exist = NULL) {

        //list($operacaoVend, $statusVend) = empOper($CodEmp);

        geraReq($conn, $local, $email, $ultcont, $serie, $whatsapp, $solicitante, $defeito, $tonerPB, $preto, $azul, $amarelo, $magenta, $outro, $periodo, $operacao, $statusVenda);


        /* Pega o ultimo numero de OS aberto */

        $req .= $ultContGer;

        /* Grava o histórico do primeiro status na abertura */
        gravaHistoricoReq($conn, $serie, $solicitante, $defeito, $statusVenda);

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
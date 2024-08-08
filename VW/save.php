<?php
session_start();
header('Content-type: text/html; charset=ISO-8895-1');
include_once "../DB/conexaoSQL.php";
include_once "../DB/acoes.php";
include_once "../DB/dados.php";
include_once "../Config.php";

$estado = $_POST['estado'];
$local = $_POST['local'];
$email = $_POST['email'];
$contpb = $_POST['contador'];
$serie = $_POST['serie'];
$whatsapp = $_POST['whatsapp'];
$solicitante = $_POST['solicitante'];
$defeito = $_POST['defeito'];
$periodo = $_POST['periodo'];


if (isset($serie)) {
    gravaOS($conn, $estado, $local, $email, $contpb, $serie, $whatsapp, $solicitante, $defeito, $periodo);


    /* Pega o ultimo numero de OS aberto */
    $sql = "SELECT TOP 1 
        TB02115_CODIGO numOS 
        FROM TB02115 
        ORDER BY TB02115_DTCAD DESC
    ";
    $stmt = sqlsrv_query($conn, $sql);

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $numOS .= $row['numOS'];

    }

    /* Grava o histórico do primeiro status na abertura */
    gravaHistorico($conn, $numOS, $serie, $defeito, $statusInicial);

    ?>

    <!DOCTYPE html>
    <html lang="pt-BR">

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
        </style>
    </head>

    <body>
        <div class="div-save">
            <form class="form-voltar" id="form-voltar" action="<?= $url ?>/inputSerie.php">
                <!-- <img src="../img/logo.jpg" alt="logo"> -->
                <p class="OSCriadaTx">Sua OS foi aberta com sucesso!</p>
                <p class="OSCriadaTx">Abaixo numero para acompanhamento:</p>
                <h1><b class="OSCriada"><?= $numOS ?></b></h1>
                <button onclick="window.location.reload()" type="submit" class="popup-btn">Fechar</button>
            </form>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="../JS/script.js" charset="utf-8"></script>
    </body>

    </html>
    <?php
} else {
    return;
}
?>
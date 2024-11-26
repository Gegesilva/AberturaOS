<?php
header('Content-type: text/html; charset=ISO-8895-1');
include_once "../Config.php";
/* $ret = " ";
$ret = $_GET['ret']; */
/* if ($ret == '1') {
    $msgErr = 'Série ou patrimônio inexistente!';
} else {
    $msgErr = '';
} */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DATABIT</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/styleBtn.css">
</head>

<body>
    <form class="form-serie" method="get" action="index.php">
        <img src="../img/logo.jpg" alt="logo">
        <div class="form-group">
            <div class="div-serie">
                <label for="serie">Série/Pat *</label>
                <input id="serie" name="serie" autofocus required></input>
            </div>
        </div>
        <button type="submit" class="submit-btn">OK</button>
        
    </form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../JS/script.js" charset="utf-8"></script>
</body>

</html>
<?php
include_once "../config.php";

// Gera o próximo número de OS
$sql = "SELECT TOP 1 FORMAT(TB00002_COD + 1, '000000') AS novaOS FROM TB00002 WHERE TB00002_TABELA = ?";
$params = ['TB02115'];
$stmt = sqlsrv_query($conn, $sql, $params);

$novaOS = null;
if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    $novaOS = $row['novaOS'];
}

function gravaOS($conn, $estado, $local, $email, $contpb, $serie, $whatsapp, $solicitante, $defeito, $periodo)
{
    global $novaOS, $statusInicial;

    // Sanitização (limite de tamanho)
    $motivo = substr($defeito, 0, 50);
    $whatsapp = substr(preg_replace('/\D/', '', $whatsapp), 0, 11);
    $local = substr($local, 0, 200);
    $email = filter_var(substr($email, 0, 200), FILTER_SANITIZE_EMAIL);
    $solicitante = substr($solicitante, 0, 30);
    $contpb = substr($contpb, 0, 10);

    // Verifica se é patrimônio ou número de série
    $sqlSerie = "SELECT TOP 1 TB02112_NUMSERIE FROM TB02112 WHERE TB02112_PAT = ? AND TB02112_SITUACAO = 'A'";
    $paramsSerie = [$serie];
    $stmt = sqlsrv_query($conn, $sqlSerie, $paramsSerie);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row && !empty($row['TB02112_NUMSERIE'])) {
        $serie = $row['TB02112_NUMSERIE'];
    }

    $sqlInsert = "
        INSERT INTO TB02115 (
            TB02115_CODIGO, TB02115_DTCAD, TB02115_ESTADO, TB02115_LOCAL, TB02115_EMAIL,
            TB02115_CONTPB, TB02115_NUMSERIE, TB02115_CELULAR, TB02115_SOLICITANTE, TB02115_OBS,
            TB02115_STATUS, TB02115_NOME, TB02115_OPCAD, TB02115_CONTRATO, TB02115_CODEMP,
            TB02115_CODCLI, TB02115_TIPOINTERV, TB02115_PRODUTO, TB02115_CODTEC, TB02115_ATENDENTE,
            TB02115_PREVENTIVA, TB02115_DATA, TB02115_SITUACAO, TB02115_CEP, TB02115_END,
            TB02115_CIDADE, TB02115_BAIRRO, TB02115_NUM, TB02115_COMP
        )
        SELECT TOP 1 ?, GETDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'APP ABERTURA_OS',
            TB02112_CODIGO, TB02111_CODEMP, TB02111_CODCLI, 'I', TB02112_PRODUTO, '0000',
            'PortalQR', 'N', GETDATE(), 'A', TB02112_CEP, TB02112_END, TB02112_CIDADE,
            TB02112_BAIRRO, TB02112_NUM, CAST(TB02112_COMP AS VARCHAR(20))
        FROM TB02112
        LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
        WHERE TB02112_NUMSERIE = ? AND TB02112_SITUACAO = 'A'
    ";

    $obs = $defeito . " - Periodo para atendimento: " . $periodo;
    $paramsInsert = [
        $novaOS, $estado, $local, $email, $contpb, $serie, $whatsapp, $solicitante,
        $obs, $statusInicial, $motivo, $serie
    ];

    $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);
    if ($stmtInsert === false) {
        print('Erro: OS não gravada!');
    }
}

function gravaHistorico($conn, $numOS, $serie, $defeito, $statusInicial)
{
    // Verifica número de série
    $sqlSerie = "SELECT TOP 1 TB02112_NUMSERIE FROM TB02112 WHERE TB02112_PAT = ? AND TB02112_SITUACAO = 'A'";
    $stmt = sqlsrv_query($conn, $sqlSerie, [$serie]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row && !empty($row['TB02112_NUMSERIE'])) {
        $serie = $row['TB02112_NUMSERIE'];
    }

    // Atualiza contador da tabela
    $sqlUpdate = "UPDATE TB00002 SET TB00002_COD = ? WHERE TB00002_TABELA = ?";
    sqlsrv_query($conn, $sqlUpdate, [$numOS, 'TB02115']);

    // Insere no histórico
    $sqlInsert = "
        INSERT INTO TB02130 (
            TB02130_CODIGO, TB02130_DATA, TB02130_USER, TB02130_STATUS, TB02130_NOME,
            TB02130_OBS, TB02130_CODTEC, TB02130_PREVISAO, TB02130_NOMETEC, TB02130_TIPO,
            TB02130_CODCAD, TB02130_CODEMP, TB02130_DATAEXEC, TB02130_HORASCOM, TB02130_HORASFIM
        )
        SELECT TOP 1 ?, GETDATE(), 'APP ABERTURA_OS', ?, TB01073_NOME, ?, TB02115_CODTEC, NULL,
            TB01024_NOME, 'O', TB02115_CODCLI, TB02115_CODEMP, GETDATE(), '00:00', '00:00'
        FROM TB02115
        LEFT JOIN TB01073 ON TB01073_CODIGO = TB02115_STATUS
        LEFT JOIN TB01024 ON TB01024_CODIGO = TB02115_CODTEC
        WHERE TB02115_NUMSERIE = ?
        ORDER BY TB02115_DTCAD DESC
    ";

    $paramsInsert = [$numOS, $statusInicial, $defeito, $serie];
    sqlsrv_query($conn, $sqlInsert, $paramsInsert);
}

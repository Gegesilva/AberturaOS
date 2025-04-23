<?php
include_once "../config.php";

/* Gera o proximo numero de OS */
$sql = "SELECT TOP 1
            FORMAT(TB00002_COD + 1, '000000') novaOS 
        FROM TB00002
        WHERE 
            TB00002_TABELA = 'TB02115'
    ";
$stmt = sqlsrv_query($conn, $sql);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $novaOS = $row['novaOS'];
}

function gravaOS($conn, $estado, $local, $email, $contpb, $serie, $whatsapp, $solicitante, $defeito, $periodo)
{
    global $statusInicial, $novaOS;

    /* Trata o numero de caracteres que será inserido no campo TB02115_NOME */
    $motivo = substr($defeito, 0, 50);

    /* Trata o numero de caracteres que será inserido no campo TB02115_CELULAR */
    $whatsapp = substr($whatsapp, 0, 11);

    /* Trata o numero de caracteres que será inserido no campo TB02115_LOCAL */
    $local = substr($local, 0, 200);

    /* Trata o numero de caracteres que será inserido no campo TB02115_EMAIL */
    $email = substr($email, 0, 200);

    /* Trata o numero de caracteres que será inserido no campo TB02115_SOLICITANTE */
    $solicitante = substr($solicitante, 0, 30);

    /* Trata o numero de caracteres que será inserido no campo TB02115_SOLICITANTE */
    $contpb = substr($contpb, 0, 10);

    /* Verifica se e patrimonio ou serie antes de gravar */
    $sql = "SELECT TOP 1 
                TB02112_NUMSERIE NumSerie
            FROM TB02112
            WHERE TB02112_PAT = '$serie'
            AND TB02112_SITUACAO = 'A'
    ";
    $NumSerie = "";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $NumSerie = $row['NumSerie'];
    }

    if ($NumSerie != NULL || $NumSerie != '') {
        $serie = $NumSerie;
    } else {

    }

    $sql = "INSERT INTO TB02115( 
                TB02115_CODIGO,
                TB02115_DTCAD,
                TB02115_ESTADO,
                TB02115_LOCAL,
                TB02115_EMAIL,
                TB02115_CONTPB,
                TB02115_NUMSERIE,
                TB02115_CELULAR,
                TB02115_SOLICITANTE,
                TB02115_OBS,
                TB02115_STATUS,
                TB02115_NOME,
                TB02115_OPCAD,
                TB02115_CONTRATO,
                TB02115_CODEMP,
                TB02115_CODCLI,
                TB02115_TIPOINTERV,
                TB02115_PRODUTO,
                TB02115_CODTEC,
                TB02115_ATENDENTE,
                TB02115_PREVENTIVA,
                TB02115_DATA,
                TB02115_SITUACAO,
                TB02115_CEP,
                TB02115_END,
                TB02115_CIDADE,
                TB02115_BAIRRO,
                TB02115_NUM,
                TB02115_COMP)
                (
            SELECT TOP 1
                '$novaOS',
                GETDATE(),
                '$estado',
                '$local', 
                '$email', 
                '$contpb', 
                '$serie', 
                '$whatsapp',
                '$solicitante',
                '$defeito - Periodo para atendimento: $periodo',
                '$statusInicial',
                '$motivo',
                'APP ABERTURA_OS',
                TB02112_CODIGO,
                TB02111_CODEMP,
                TB02111_CODCLI,
                'I',
                TB02112_PRODUTO,
                '0000',
                'PortalQR',
                'N',
                GETDATE(),
                'A',
                TB02112_CEP,
                TB02112_END,
                TB02112_CIDADE,
                TB02112_BAIRRO,
                TB02112_NUM,
                CAST(TB02112_COMP AS VARCHAR(20))
            FROM TB02112
            LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
            WHERE TB02112_NUMSERIE = '$serie'
            AND TB02112_SITUACAO = 'A')
        ";

    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        //die(print_r(sqlsrv_errors(), true));
        print ('Erro OS não gravada!!!');
    }

}

function gravaHistorico($conn, $numOS, $serie, $defeito, $statusInicial)
{
    /* Verifica se e patrimonio ou serie antes de gravar */
    $sql = "SELECT TOP 1 
                TB02112_NUMSERIE NumSerie
            FROM TB02112
            WHERE TB02112_PAT = '$serie'
            AND TB02112_SITUACAO = 'A'
    ";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $NumSerie = $row['NumSerie'];
    }

    if (isset($NumSerie)) {
        $serie = $NumSerie;
    } else {

    }

    $sql = "UPDATE TB00002
            SET TB00002_COD = '$numOS'
            WHERE TB00002_TABELA = 'TB02115'
    
            INSERT INTO TB02130
                (TB02130_CODIGO,
                TB02130_DATA, 
                TB02130_USER,
                TB02130_STATUS,
                TB02130_NOME,
                TB02130_OBS,
                TB02130_CODTEC,
                TB02130_PREVISAO,
                TB02130_NOMETEC,
                TB02130_TIPO, 
                TB02130_CODCAD,
                TB02130_CODEMP,
                TB02130_DATAEXEC,
                TB02130_HORASCOM,
                TB02130_HORASFIM)
            SELECT TOP 1
                '$numOS',
                GETDATE(),
                'APP ABERTURA_OS', 
                '$statusInicial', 
                TB01073_NOME, 
                '$defeito',
                TB02115_CODTEC,
                NULL,
                TB01024_NOME, 
                'O',
                TB02115_CODCLI,
                TB02115_CODEMP,
                GETDATE(), 
                '00:00', 
                '00:00'
            FROM TB02115
            LEFT JOIN TB01073 ON TB01073_CODIGO = TB02115_STATUS
            LEFT JOIN TB01024 ON TB01024_CODIGO = TB02115_CODTEC
            WHERE TB02115_NUMSERIE = '$serie'
            ORDER BY TB02115_DTCAD DESC";

    $stmt = sqlsrv_query($conn, $sql);
}


/* Mascara de telefone */

function formatarTelefone($telefone) {
    // Remove tudo que não for número
    $telefone = preg_replace('/\D/', '', $telefone);

    // Verifica se é telefone com DDD
    if (strlen($telefone) == 11) {
        // Celular com DDD: (11) 91234-5678
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        // Fixo com DDD: (11) 1234-5678
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 9) {
        // Celular sem DDD: 91234-5678
        return preg_replace('/(\d{5})(\d{4})/', '$1-$2', $telefone);
    } elseif (strlen($telefone) == 8) {
        // Fixo sem DDD: 1234-5678
        return preg_replace('/(\d{4})(\d{4})/', '$1-$2', $telefone);
    }

    // Retorna como está se não for padrão conhecido
    return $telefone;
}

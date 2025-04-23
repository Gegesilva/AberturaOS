<?php
include_once "conexaoSQL.php";
include_once "../Config.php";

/* Gera o proximo contador */
$sql = "SELECT TOP 1
            SUBSTRING(TB00002_COD, 0, 2)+FORMAT(CAST(SUBSTRING(TB00002_COD, 2, 6) AS NUMERIC) + 1, '00000') ultContGer
        FROM TB00002
        WHERE TB00002_TABELA = 'TB02018R'";
$stmt = sqlsrv_query($conn, $sql);

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $ultContGer = $row['ultContGer'];
}


function geraReq($conn, $local, $email, $ultcont, $serie, $whatsapp, $solicitante, $defeito, $tonerPB, $preto, $azul, $amarelo, $magenta, $outro, $periodo, $operacaoVend, $statusVend)
{
    global $ultContGer, $CodVendedor, $Condicao;

    /* Trata o numero de caracteres que será inserido no campo TB02115_CELULAR */
    $whatsapp = substr($whatsapp, 0, 11);

    /* Trata o numero de caracteres que será inserido no campo TB02115_LOCAL */
    $local = substr($local, 0, 200);

    /* Trata o numero de caracteres que será inserido no campo TB02115_EMAIL */
    $email = substr($email, 0, 200);

    /* Trata o numero de caracteres que será inserido no campo TB02115_SOLICITANTE */
    $solicitante = substr($solicitante, 0, 30);

    /* Trata o numero de caracteres que será inserido no campo TB02115_SOLICITANTE */
    $ultcont = substr($ultcont, 0, 10);

    /* Verifica se e patrimonio ou serie antes de gravar */
    $sql = "SELECT TOP 1 
                TB02112_NUMSERIE NumSerie
            FROM TB02112
            WHERE TB02112_PAT = '$serie'
            AND TB02112_SITUACAO = 'A'
    ";
    $NumSerie = '';
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $NumSerie = $row['NumSerie'];
    }

    if ($NumSerie != NULL || $NumSerie != '') {
        $serie = $NumSerie;
    } else {

    }

    $sql = "INSERT INTO TB02018(
                TB02018_CODIGO,
                TB02018_DTCAD,
                TB02018_DATAEXEC,
                TB02018_DATA,
                TB02018_CODEMP,
                TB02018_CODCLI,
                TB02018_VEND,
                TB02018_TIPODESC,
                TB02018_CONDPAG,
                TB02018_STATUS,
                TB02018_SITUACAO,
                TB02018_OPERACAO,
                TB02018_NOME, --nome do consumidor final
                TB02018_FONE,
                TB02018_CONTRATO,
                TB02018_EMAIL,
                TB02018_CODSITE,
                TB02018_NUMSERIE,
                TB02018_OBS,
                TB02018_CONTTOTAL,
                TB02018_OPCAD)
            (SELECT 
                '$ultContGer',
                GETDATE(),
                GETDATE(),
                GETDATE(),
                TB02111_CODEMP,
                TB02111_CODCLI,
                '$CodVendedor',
                '$operacaoVend',
                '$Condicao',
                '$statusVend',
                'A',
                3,
                TB02111_NOME,
                '$whatsapp',
                TB02111_CODIGO,
                '$email',
                TB02112_CODSITE,
                '$serie',
                'Melhor periodo para visita: $periodo \nLocal ou setor: $local \nTonerPB: $tonerPB \n\nTONER COLORIDO \nPreto: $preto, \nAzul: $azul, \nAmarelo: $amarelo, \nMagenta: $magenta, \nOutro: $outro \nOBS: $defeito',
                '$ultcont',
                '$solicitante'
                 
            FROM TB02112
            LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
            WHERE TB02112_SITUACAO = 'A'
            AND TB02111_TIPOCONTR = 'L'
            AND TB02112_NUMSERIE = '$serie')

            UPDATE 
                TB00002 
            SET 
                TB00002_cod = '$ultContGer'  
            WHERE 
                TB00002_tabela = 'TB02018R'

    ";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
        //print ('Erro OS não gravada!!!');
    }
}

function gravaHistoricoReq($conn, $serie, $solicitante, $defeito, $statusVend)
{
    global $ultContGer, $CodVendedor, $Operacao, $Condicao;

    /* Verifica se e patrimonio ou serie antes de gravar */
    $sql = "SELECT TOP 1 
                TB02112_NUMSERIE NumSerie
            FROM TB02112
            WHERE TB02112_PAT = '$serie'
            AND TB02112_SITUACAO = 'A'
            ";
    $NumSerie = '';
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $NumSerie = $row['NumSerie'];
    }

    if ($NumSerie != NULL || $NumSerie != '') {
        $serie = $NumSerie;
    } else {

    }

    $sql = "INSERT INTO TB02130
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
                '$ultContGer',
                GETDATE(),
                '$solicitante', 
                '$statusVend', 
                TB01021_NOME, 
                '$defeito',
                '$CodVendedor',
                NULL,
                TB01006_NOME, 
                'V',
                TB02111_CODCLI,
                TB02111_CODEMP,
                GETDATE(), 
                '00:00', 
                '00:00'
            FROM TB02112
            LEFT JOIN TB01021 ON TB01021_CODIGO = '$statusVend'
            LEFT JOIN TB01006 ON TB01006_CODIGO = '$CodVendedor'
            LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
            WHERE TB02112_NUMSERIE = '$serie'
            AND TB02112_SITUACAO = 'A'";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
        //print ('Erro OS não gravada!!!');
    }
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
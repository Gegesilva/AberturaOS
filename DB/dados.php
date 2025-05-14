<?php
include "../Config.php";

$estado = "";
$Cliente = "";
$Local = "";
$UltCont = "";
$Email = "";
$Serie = "";
$Tel = "";
$CodEmp = "";

function preenchimento($conn, $serie)
{
    global $estado, $Cliente, $Local, $UltCont, $Email, $Serie, $Tel, $CodEmp;

    // Sanitização básica da entrada
    $serie = trim($serie);
    $existPat = false;

    // Verifica se o valor existe como patrimônio
    $sqlVerificaPat = "SELECT TOP 1 1 AS existPat FROM TB02112 WHERE TB02112_PAT = ?";
    $stmtVerificaPat = sqlsrv_prepare($conn, $sqlVerificaPat, [$serie]);

    if ($stmtVerificaPat && sqlsrv_execute($stmtVerificaPat)) {
        if ($row = sqlsrv_fetch_array($stmtVerificaPat, SQLSRV_FETCH_ASSOC)) {
            $existPat = $row['existPat'] == 1;
        }
    }

    // Define consulta principal com base na verificação anterior
    $filtro = $existPat ? "TB02112_PAT = ?" : "TB02112_NUMSERIE = ?";
    $sql = "
        SELECT 
            TB02112_ESTADO AS Estado,
            TB01008_NOME AS Cliente,
            TB02112_LOCAL AS Local,
            TB02112_EMAIL AS Email,
            (
                SELECT TOP 1 TB02117_TOTPB 
                FROM TB02117 
                WHERE TB02117_NUMSERIE = TB02112_NUMSERIE 
                ORDER BY TB02117_DTCAD DESC
            ) AS UltCont,
            TB02112_NUMSERIE AS Serie,
            TB02112_FONEAUX AS Tel,
            TB02176_CODEMP AS CodEmp
        FROM TB02112
        LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
        LEFT JOIN TB01008 ON TB01008_CODIGO = TB02111_CODCLI
        LEFT JOIN TB02176 ON TB02176_CONTRATO = TB02111_CODIGO AND TB02176_CODIGO = TB02112_CODSITE
        WHERE TB02112_SITUACAO = 'A' AND $filtro
    ";

    $stmt = sqlsrv_prepare($conn, $sql, [$serie]);

    if ($stmt && sqlsrv_execute($stmt)) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $estado = $row['Estado'];
            $Cliente = $row['Cliente'];
            $Local = $row['Local'];
            $UltCont = $row['UltCont'];
            $Email = $row['Email'];
            $Serie = $row['Serie'];
            $Tel = $row['Tel'];
            $CodEmp = $row['CodEmp'];
        }
    }

    return [$estado, $Cliente, $Local, $UltCont, $Email, $Serie, $Tel, $CodEmp];
}

function PegaTipo($conn, $serie)
{
    $sqlPat = "SELECT TOP 1 TB02112_NUMSERIE numSerie FROM TB02112
        WHERE TB02112_PAT = ?
    ";
    $numSerie = "";
    $stmtPat = sqlsrv_prepare($conn, $sqlPat, [$serie]);
    sqlsrv_execute($stmtPat);
    while ($row = sqlsrv_fetch_array($stmtPat, SQLSRV_FETCH_ASSOC)) {
        $numSerie .= $row['numSerie'];
    }

    $serie = isset($numSerie) ? $numSerie : $serie;
    $sqlTipo = "SELECT TOP 1
                CASE 
                    WHEN TB02115_PREVENTIVA = 'N' THEN 'NORMAL'
                    WHEN TB02115_PREVENTIVA = 'S' THEN 'PREVENTIVA'
                    WHEN TB02115_PREVENTIVA = 'I' THEN 'INSTALAÇÃO'
                    WHEN TB02115_PREVENTIVA = 'D' THEN 'DESINSTALAÇÃO'
                    WHEN TB02115_PREVENTIVA = 'R' THEN 'RETORNO/RECARGA'
                    WHEN TB02115_PREVENTIVA = 'A' THEN 'AFERIÇÃO'
                    WHEN TB02115_PREVENTIVA = 'B' THEN 'ATEND. BALCÃO'
                    WHEN TB02115_PREVENTIVA = 'E' THEN 'ESTOQUE'
                END Tipo
            FROM TB02115 
            WHERE TB02115_NUMSERIE = ? 
            AND TB02115_DTFECHA IS NULL
            GROUP BY TB02115_PREVENTIVA, TB02115_CODIGO
            ORDER BY TB02115_CODIGO DESC
    ";
    $stmtTipo = sqlsrv_prepare($conn, $sqlTipo, [$serie]);
    sqlsrv_execute($stmtTipo);
    $Tipo = "";
    while ($row = sqlsrv_fetch_array($stmtTipo, SQLSRV_FETCH_ASSOC)) {
        $Tipo .= 'Já existe uma OS em aberto do tipo ' . $row['Tipo'] . ' para esse numero de série.';
    }

    echo $Tipo;
}

function indentificaProd($conn, $serie)
{

    $sqlPat = "SELECT TOP 1 1 existPat FROM TB02112
        WHERE TB02112_PAT = ?
    ";
    $existPat = "";
    $stmtPat = sqlsrv_prepare($conn, $sqlPat, [$serie]);
    sqlsrv_execute($stmtPat);
    while ($row = sqlsrv_fetch_array($stmtPat, SQLSRV_FETCH_ASSOC)) {
        $existPat .= $row['existPat'];
    }

    $filtroPatSerie = $existPat == '1' ? "TB02112_PAT = ?" : "TB02112_NUMSERIE = ?";

    $sqlProd = "SELECT 1 existProd FROM TB02112
    WHERE $filtroPatSerie
";
    $stmtProd = sqlsrv_prepare($conn, $sqlProd, [$serie]);
    sqlsrv_execute($stmtProd);
    while ($row = sqlsrv_fetch_array($stmtProd, SQLSRV_FETCH_ASSOC)) {
        $existProd = $row['existProd'];
    }

    return $existProd;
}

function empOper($CodEmp)
{
    global $operacao, $statusVenda;
    /* switch ($CodEmp) {
        case '00':
            $operacaoVend = '10';
            $statusVend = 'J0';
            break;
        case '01':
            $operacaoVend = '37';
            $statusVend = 'J7';
            break;
        case '02':
            $operacaoVend = '43';
            $statusVend = 'J9';
            break;
        case '03':
            $operacaoVend = '10';
            $statusVend = 'J0';
        case '07':
            $operacaoVend = '47';
            $statusVend = 'K1';
            break;
        case '08':
            $operacaoVend = '43';
            $statusVend = 'K1';
    }  */

    $operacaoVend = $operacao;
    $statusVend = $statusVenda;

    return [$operacaoVend, $statusVend];
}
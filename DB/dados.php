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
    $sql = "SELECT TOP 1 1 existPat FROM TB02112
        WHERE TB02112_PAT = '$serie'
    ";
    $existPat = "";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $existPat .= $row['existPat'];
    }

    if ($existPat == '1') {
        $filtroPatSerie = "AND TB02112_PAT = '$serie'";
    } else {
        $filtroPatSerie = "AND TB02112_NUMSERIE = '$serie'";
    }



    $sql = "SELECT 
                TB02112_ESTADO Estado,
                TB01008_NOME Cliente,
                TB02112_LOCAL Local,
                TB02112_EMAIL Email,
                (SELECT TOP 1 TB02117_TOTPB FROM TB02117 WHERE TB02117_NUMSERIE = TB02112_NUMSERIE ORDER BY TB02117_DTCAD DESC) UltCont,
                TB02112_NUMSERIE Serie,
                TB02112_FONEAUX Tel,
                TB02176_CODEMP CodEmp
                
            FROM TB02112
            LEFT JOIN TB02111 ON TB02111_CODIGO = TB02112_CODIGO
            LEFT JOIN TB01008 ON TB01008_CODIGO = TB02111_CODCLI
            LEFT JOIN TB02176 ON TB02176_CONTRATO = TB02111_CODIGO AND TB02176_CODIGO = TB02112_CODSITE

            WHERE TB02112_SITUACAO = 'A'
            --AND TB02111_TIPOCONTR = 'L'
            $filtroPatSerie
    ";
    $stmt = sqlsrv_query($conn, $sql);

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $estado = $row['Estado'];
        $Cliente = $row['Cliente'];
        $Local = $row['Local'];
        $UltCont = $row['UltCont'];
        $Email = $row['Email'];
        $Serie = $row['Serie'];
        $Tel = $row['Tel'];
        $CodEmp = $row['CodEmp'];
    }

    return [$estado, $Cliente, $Local, $UltCont, $Email, $Serie, $Tel, $CodEmp];

}

function PegaTipo($conn, $serie)
{
    $sql = "SELECT TOP 1 TB02112_NUMSERIE numSerie FROM TB02112
        WHERE TB02112_PAT = '$serie'
    ";
    $numSerie = "";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $numSerie .= $row['numSerie'];
    }

    if (isset($numSerie)) {
        $serie = $numSerie;
    }


    $sql = "SELECT TOP 1
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
            WHERE TB02115_NUMSERIE = '$serie' 
            AND TB02115_DTFECHA IS NULL
            GROUP BY TB02115_PREVENTIVA, TB02115_CODIGO
            ORDER BY TB02115_CODIGO DESC
    ";
    $stmt = sqlsrv_query($conn, $sql);
    $Tipo = "";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $Tipo .= 'Já existe uma OS em aberto do tipo ' . $row['Tipo'] . ' para esse numero de série.';
    }

    echo $Tipo;
}

function indentificaProd($conn, $serie)
{

    $sql = "SELECT TOP 1 1 existPat FROM TB02112
        WHERE TB02112_PAT = '$serie'
    ";
    $existPat = "";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $existPat .= $row['existPat'];
    }

    if ($existPat == '1') {
        $filtroPatSerie = "TB02112_PAT = '$serie'";
    } else {
        $filtroPatSerie = "TB02112_NUMSERIE = '$serie'";
    }


    $sql = "SELECT 1 existProd FROM TB02112
    WHERE $filtroPatSerie
";
    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $existProd = $row['existProd'];
    }

    return $existProd;
}

/* function empOper($CodEmp)
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
    } 

    $operacaoVend = $operacao;
    $statusVend = $statusVenda;

    return [$operacaoVend, $statusVend];
} */
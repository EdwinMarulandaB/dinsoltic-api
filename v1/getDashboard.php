<?php
extract($_REQUEST);
include_once('include/configApi.php');
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$userLog        = USERLOG;
$password       = USERCLAV;
$dbName         = DBNAME;
$hostIp         = HOSTIP;
$estadoCon      = "1";

$detcon = pg_connect("host=$hostIp port=5432 dbname=$dbName user=$userLog password=$password");

if (!$detcon) {

    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la base de datos!";
    echo json_encode($arrayRespuesta);
    die();
}


$query ="SELECT bd FROM users u WHERE email='$email'";
$exec = pg_query($detcon, $query);
$resusu = pg_fetch_all($exec);

if ($resusu[0]['bd'] == "") {

    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la base de datos del cliente!";
    echo json_encode($arrayRespuesta);
    die();
}

$detalle = pg_connect("host=$hostIp port=5432 dbname=".$resusu[0]['bd']." user=$userLog password=$password");

if (!$detalle) {
    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la base de datos del cliente!";
    echo json_encode($arrayRespuesta);
    die();
}

$sql = "SELECT par_valor FROM parametros p WHERE par_confi='DASHBOARD_MOVIL';";
$exec = pg_query($detalle, $sql);
$resPar = pg_fetch_all($exec);

$sql = "SELECT par_valor FROM parametros p WHERE par_confi = 'SYNC_AGENDA_UNI'";
$exec = pg_query($detalle, $sql);
$paramtr = pg_fetch_all($exec);

$whereUsu = "";
if ($paramtr[0]['par_valor'] == "SI") {

    $sql = "SELECT id FROM users WHERE email='$email'";
    $exec = pg_query($detalle, $sql);
    $idUsu = pg_fetch_all($exec);

    $whereUsu = " AND age_usuari = '".$idUsu[0]['id']."'";

}

if($resPar[0]['par_valor'] == "agenda"){
$sql = "SELECT json_agg(
            json_build_object(
                'vta', COALESCE(a.agendado, 0),
                'gasto', 0
            )
            ORDER BY m.mes
        ) AS resultado_json
        FROM generate_series(1, 12) AS m(mes)
        LEFT JOIN (
            SELECT 
                EXTRACT(MONTH FROM age_inidate)::int AS mes,
                COUNT(age_cliente) AS agendado
            FROM agenda
            WHERE EXTRACT(YEAR FROM age_inidate) = EXTRACT(YEAR FROM CURRENT_DATE)
                $whereUsu
            GROUP BY EXTRACT(MONTH FROM age_inidate)
        ) a ON a.mes = m.mes;";
}else{
$sql = "WITH meses AS (
        SELECT generate_series(1, 12) AS mes
        ),
        vta AS (
                SELECT EXTRACT(MONTH FROM r.rec_fecha)::int AS mes, SUM(rec_valor) AS total_venta
                FROM recaudo r
                WHERE EXTRACT(YEAR FROM r.rec_fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
                and r.rec_estado <> 'E'
                GROUP BY 1
        ),
        gas AS (
                SELECT EXTRACT(MONTH FROM e.egr_fecha)::int AS mes, SUM(e.egr_valor) AS total_gasto
                FROM egreso e
                WHERE EXTRACT(YEAR FROM e.egr_fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
                and egr_estado <> 'C'
                GROUP BY 1
        )
        SELECT json_agg(
        json_build_object(
            'vta', COALESCE(vta.total_venta, 0),
            'gasto', COALESCE(gas.total_gasto, 0)
        )
        ORDER BY meses.mes)
        AS resultado_json
        FROM meses
        LEFT JOIN vta ON meses.mes = vta.mes
        LEFT JOIN gas ON meses.mes = gas.mes";
}



$exec = pg_query($detalle, $sql);
$result = pg_fetch_all($exec);

echo $result[0]['resultado_json'];

?>
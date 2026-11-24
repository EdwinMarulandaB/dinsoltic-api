<?php
extract($_REQUEST);
include_once('include/configApi.php');

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
// $query ="SELECT a.id, age_cliente, c.cli_nombre ||' '|| coalesce (c.cli_apellido,'') as clinombre,
//         age_inidate, age_initime as horini, age_fintime as horfin,
//         a.age_usuari as colaborador_cod,
//         u.name as colaborador_name, 
//         SUBSTRING(u.name FROM 1 FOR 1) as inicial,
//         u.color as colaborador_color
//         FROM agenda a 
//         LEFT JOIN cliente c  on (a.age_cliente = c.id)
//         LEFT JOIN users u on (a.age_usuari = u.id)
//         WHERE age_inidate between current_date-31 and current_date+31     
//         ORDER BY age_initime";
$query = "SELECT id, age_cliente, clinombre,age_inidate,horini,horfin,colaborador_cod,colaborador_name,
                inicial,colaborador_color,tipo,observacion
        FROM (
            SELECT a.id, age_cliente, c.cli_nombre ||' '|| coalesce (c.cli_apellido,'') as clinombre,
            age_inidate, age_initime as horini, age_fintime as horfin,
            a.age_usuari as colaborador_cod,
            u.name as colaborador_name, 
            SUBSTRING(u.name FROM 1 FOR 1) as inicial,
            u.color as colaborador_color,'real' as tipo,
            a.age_observa as observacion
            FROM agenda a 
                LEFT JOIN cliente c  on (a.age_cliente = c.id)
                LEFT JOIN users u on (a.age_usuari = u.id)
            WHERE age_inidate between current_date-31 and current_date+90 $whereUsu  
        UNION
            SELECT a.id, 0 as age_cliente, age_cliente::varchar as clinombre,
            age_inidate, age_initime as horini, age_fintime as horfin,
            a.age_usuari as colaborador_cod,
            u.name as colaborador_name, 
            SUBSTRING(u.name FROM 1 FOR 1) as inicial,
            u.color as colaborador_color,'tmp' as tipo,
            a.age_observa  as observacion
            FROM agendatmp a 
                LEFT JOIN users u on (a.age_usuari = u.id)
            WHERE age_inidate between current_date-31 and current_date+90  $whereUsu   
        ) as d 
        ORDER BY age_inidate,horini";
     
$exec = pg_query($detalle, $query);
$result = pg_fetch_all($exec);

// if($result[0]['name'] != ""){
//     $arrayRespuesta['estado']      = 'OK';
//     $arrayRespuesta['mensaje']     = "Logueo realizado con exito";
//     $arrayRespuesta['name']       = $result[0]['name'];
//     $arrayRespuesta['color']       = $result[0]['color'];
// }else{
//     $arrayRespuesta['estado']      = 'ERROR';
//     $arrayRespuesta['mensaje']     = "Usuario o contraseña incorrectos";
// }

pg_close($detalle);

echo json_encode($result);


?>
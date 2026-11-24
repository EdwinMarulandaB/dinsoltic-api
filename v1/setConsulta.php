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
$consul = "1";

$query ="SELECT id FROM users u WHERE email='$email'";
$exec = pg_query($detalle, $query);
$usu = pg_fetch_all($exec);

$query ="INSERT INTO consulta (con_fecha,con_hora,con_cliente,con_obs,con_nota,con_usuario,consultorio,con_servicio,con_valor) 
            VALUES (current_date,current_time,'$cli','$obs','$nota','".$usu[0]['id']."','$consul',NULL,NULL)";
pg_query($detalle, $query);
$err = pg_last_error($detalle);

// echo "<pre>$query</pre>"; 

if($err != ""){
    
    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al registrar el evento en el calendario!!!".$err;
    echo json_encode($arrayRespuesta);

    pg_close($detalle);
    die();  
}else{

    $arrayRespuesta['estado']      = 'Ok';
    $arrayRespuesta['mensaje']     = "Consulta registrada con exito!";
}

pg_close($detalle);

echo json_encode($arrayRespuesta);


?>
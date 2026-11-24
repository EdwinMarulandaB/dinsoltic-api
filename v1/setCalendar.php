<?php
extract($_REQUEST);
include_once('include/configApi.php');
include_once('include/sendSMS.php');
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

if($tipo == "N" || $tipo == ""){
    $sql = "SELECT nextval as id FROM nextval('agenda_id_seq'::regclass)";
    $exec = pg_query($detalle, $sql);
    $result = pg_fetch_all($exec);
    $idAge = $result[0]['id'];

    $query ="INSERT INTO agenda (id,age_cliente,age_inidate,age_initime,age_findate,age_fintime,consultorio,age_usuari,age_observa) 
            VALUES ('$idAge','$cli','$fec','$hini','$fec','$hfin','$consul','$usu','$obs')";
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
        $arrayRespuesta['mensaje']     = "Evento registrado con exito en calendario!";
        $arrayRespuesta['id']          = $idAge;

        $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_CITA_CREA'";
        $exec = pg_query($detalle, $query);
        $par = pg_fetch_all($exec);

        if($par[0]['par_valor'] == "SI"){
            sendMSM($detalle, $idAge);
        }
    }

}elseif($tipo == "M"){

    $query = "UPDATE agenda SET age_inidate='$fec',
                                age_initime='$hini',
                                age_findate='$fec',
                                age_fintime='$hfin',
                                age_observa='$obs'
               WHERE id='$id'";
     pg_query($detalle, $query);
    $err = pg_last_error($detalle);
    echo "<br>$query";

    if($err != ""){

        $arrayRespuesta['estado']      = 'ERROR';
        $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al modificar el evento en el calendario!!!".$err;
        echo json_encode($arrayRespuesta);

        pg_close($detalle);
        die();  

    }else{

        $arrayRespuesta['estado']      = 'Ok';
        $arrayRespuesta['mensaje']     = "Evento Actualizaco con exito en calendario!";
        $arrayRespuesta['id']          = $id;

        $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_CITA_CREA'";
        $exec = pg_query($detalle, $query);
        $par = pg_fetch_all($exec);

        if($par[0]['par_valor'] == "SI"){
            sendMSM($detalle, $id);
        }
    }
}elseif($tipo == "E"){

    $query = "DELETE FROM agenda WHERE id='$id'";
     pg_query($detalle, $query);
    $err = pg_last_error($detalle);

    if($err != ""){

        $arrayRespuesta['estado']      = 'ERROR';
        $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al eliminar el evento en el calendario!!!".$err;
        echo json_encode($arrayRespuesta);

        pg_close($detalle);
        die();  

    }else{

        $arrayRespuesta['estado']      = 'Ok';
        $arrayRespuesta['mensaje']     = "Evento Eliminado con exito en calendario!";
        $arrayRespuesta['id']          = $id;
    }
}

pg_close($detalle);



echo json_encode($arrayRespuesta);


?>
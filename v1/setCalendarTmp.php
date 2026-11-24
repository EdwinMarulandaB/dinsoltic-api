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

if($tipo == "A"){

    $query ="SELECT id FROM users u WHERE email = '$email'";
    $exec = pg_query($detcon, $query);
    $resUsu = pg_fetch_all($exec);
    $usuAge = $resUsu[0]['id'];

    $sql = "SELECT age_cliente, age_inidate, age_initime, age_fintime, age_telefono 
            FROM agendatmp a 
            WHERE id = '$id'";
    $exec = pg_query($detalle, $sql);
    $resAge = pg_fetch_all($exec);
    
    $tel    = $resAge[0]['age_telefono'];
    $nombre = $resAge[0]['age_cliente'];
    $fec    = $resAge[0]['age_inidate'];
    $hini   = $resAge[0]['age_initime'];
    $hfin   = $resAge[0]['age_fintime'];

    $sql = "SELECT id FROM cliente c WHERE cli_telefono = '$tel'";
    $exec = pg_query($detalle, $sql);
    $result = pg_fetch_all($exec);
    $idCli = $result[0]['id'];

    if($idCli == ""){

        $sql = "SELECT nextval as id FROM nextval('cliente_id_seq'::regclass)";
        $exec = pg_query($detalle, $sql);
        $result = pg_fetch_all($exec);
        $idCli = $result[0]['id'];

        $sql = "INSERT INTO cliente (id,cli_nombre,cli_documento,cli_telefono,cli_apellido,cli_genero,
                    cli_fechanacimiento,cli_direccion,cli_correo,cli_ciudad,cli_barrio,
                                    cli_tipdoc,cli_estadocivil)
                VALUES ('$idCli','$nombre','123','$tel','','','1900-01-01','','','76520','','1','1')";
        pg_query($detalle, $sql);
        $err = pg_last_error($detalle);

        if($err != ""){
            $arrayRespuesta['estado']      = 'ERROR';
            $arrayRespuesta['mensaje']     = "Inconveniente al registrar el cliente!";
            echo json_encode($arrayRespuesta);

            pg_close($detalle);
            die();  
        }

    }

    $sql = "SELECT nextval as id FROM nextval('agenda_id_seq'::regclass)";
    $exec = pg_query($detalle, $sql);
    $result = pg_fetch_all($exec);
    $idAge = $result[0]['id'];

    $query ="INSERT INTO agenda (id,age_cliente,age_inidate,age_initime,age_findate,age_fintime,consultorio,age_usuari) 
            VALUES ('$idAge','$idCli','$fec','$hini','$fec','$hfin','$consul','$usuAge')";
    pg_query($detalle, $query);
    $err = pg_last_error($detalle);

    if($err != ""){
        
        $arrayRespuesta['estado']      = 'ERROR';
        $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al registrar el evento en el calendario!!!".$err;
        echo json_encode($arrayRespuesta);

        pg_close($detalle);
        die();  
    }else{

        $sql = "DELETE FROM agendatmp WHERE id = '$id'";
        pg_query($detalle, $sql);

        $arrayRespuesta['estado']      = 'Ok';
        $arrayRespuesta['mensaje']     = "Evento registrado con exito en calendario!";
        $arrayRespuesta['id']          = $idAge;

    }

}elseif($tipo == "R"){

    $sql = "DELETE FROM agendatmp WHERE id = '$id'";
    pg_query($detalle, $sql);
    
    $arrayRespuesta['estado']      = 'Ok';
    $arrayRespuesta['mensaje']     = "Evento cancelado!";
}
pg_close($detalle);

echo json_encode($arrayRespuesta);

?>
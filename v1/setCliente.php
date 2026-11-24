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
    $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la bfase de datos del cliente!";
    echo json_encode($arrayRespuesta);
    die(); 
}
$consul = "1"; 

if($tipo == "N" || $tipo == ""){
    $sql = "SELECT nextval as id FROM nextval('cliente_id_seq'::regclass)";
    $exec = pg_query($detalle, $sql);
    $result = pg_fetch_all($exec);
    $idCli = $result[0]['id'];

    $query ="INSERT INTO cliente (id,cli_nombre,cli_apellido,cli_telefono,cli_documento)
            VALUES ($idCli,'$nom','$ape','$cel','$cel');";
    pg_query($detalle, $query);
    $err = pg_last_error($detalle);

    // echo "<pre>$query</pre>"; 

    if($err != ""){
        
        $arrayRespuesta['estado']      = 'ERROR';
        $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al registrar el Cliente!!!".$err;
        echo json_encode($arrayRespuesta);

        pg_close($detalle);
        die();  
    }else{

        $arrayRespuesta['estado']      = 'Ok';
        $arrayRespuesta['mensaje']     = "cliente registrado con exito!";
        $arrayRespuesta['id']          = $idCli;
    }

}elseif($tipo == "M"){

    $query = "UPDATE cliente SET cli_nombre='$nom',
                                cli_apellido='$ape'
               WHERE id='$id'";
     pg_query($detalle, $query);
    $err = pg_last_error($detalle);

    if($err != ""){

        $arrayRespuesta['estado']      = 'ERROR';
        $arrayRespuesta['mensaje']     = "Ocurrio un inconveniente al modificar el cliente!!!".$err;
        echo json_encode($arrayRespuesta);

        pg_close($detalle);
        die();  

    }else{

        $arrayRespuesta['estado']      = 'Ok';
        $arrayRespuesta['mensaje']     = "Cliente Actualizaco con exito!";
        $arrayRespuesta['id']          = $id;

    }
}

pg_close($detalle);

echo json_encode($arrayRespuesta);


?>
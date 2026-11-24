<?php
extract($_REQUEST);
include_once('include/configApi.php');

$userLog        = USERLOG;
$password       = USERCLAV;
$dbName         = DBNAME;
$hostIp         = HOSTIP;
$estadoCon      = "1";
$detalle = pg_connect("host=$hostIp port=5432 dbname=$dbName user=$userLog password=$password");

if (!$detalle) {

    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la base de datos!";
    echo json_encode($arrayRespuesta);
    die();
}

// $pass = password_hash($pass, PASSWORD_DEFAULT);
// echo "pass: ".$pass;
$query ="SELECT name,color,id,password,colorapp FROM users WHERE email='$email'";
$exec = pg_query($detalle, $query);
$result = pg_fetch_all($exec);

if (password_verify($pass, $result[0]['password'])) {
    $arrayRespuesta['estado']      = 'OK';
    $arrayRespuesta['mensaje']     = "Logueo realizado con exito";
    $arrayRespuesta['name']       = $result[0]['name'];
    $arrayRespuesta['color']       = $result[0]['color'];
    $arrayRespuesta['id']       = $result[0]['id'];
    $arrayRespuesta['colorapp']       = $result[0]['colorapp'];
    
} else {
    $arrayRespuesta['estado']      = 'ERROR';
    $arrayRespuesta['mensaje']     = "Usuario o contraseña incorrectos";
}


// if($result[0]['name'] != ""){
//     $arrayRespuesta['estado']      = 'OK';
//     $arrayRespuesta['mensaje']     = "Logueo realizado con exito";
//     $arrayRespuesta['name']       = $result[0]['name'];
//     $arrayRespuesta['color']       = $result[0]['color'];
//     $arrayRespuesta['id']       = $result[0]['id'];
// }else{
//     $arrayRespuesta['estado']      = 'ERROR';
//     $arrayRespuesta['mensaje']     = "Usuario o contraseña incorrectos";
// }

pg_close($detalle);

echo json_encode($arrayRespuesta);


?>
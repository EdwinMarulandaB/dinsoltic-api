<?php
extract($_REQUEST);
include_once('v1/include/configApi.php');

$userLog        = USERLOG;
$password       = USERCLAV;
$dbName         = "bety";
$hostIp         = HOSTIP;
$estadoCon      = "1";
$detalle = pg_connect("host=$hostIp port=5432 dbname=$dbName user=$userLog password=$password");

if (!$detalle) {
    echo "Error al realizar la conexion a la base de datos!";
    die();
}

$cad = "";
$query ="SELECT 
        c.id as codigo,
        cl.cli_nombre as nombre_paciente,
        cl.cli_apellido as apellido_paciente,
        case when cl.cli_genero = 'M' then 'Masculino' when cl.cli_genero = 'F' then 'Femenino' else 'Otro' end as genero, 
        con_fecha as fecha,
        con_hora as hora,
        con_obs as consulta,
        con_nota as nota_consulta
        FROM consulta c
        LEFT JOIN cliente cl on (c.con_cliente=cl.id);";
$exec = pg_query($detalle, $query);
$res = pg_fetch_all($exec);

$cad = "REGISTRO DE HISTORIA CLINICA DE CONSULTAS PSICOLOGICAS<BR>";
$cad = "Este documento contiene el registro de la historia clinica de diferentes pacientes del consultorio psicologico Betty Gomez Manzano<BR>";
echo $cad;
for ($i=0; $i < sizeof($res); $i++) { 
    $cad = "Codigo: ".$res[$i]['codigo']."<br>";
    $cad .= "Nombre Paciente: ".$res[$i]['nombre_paciente']."<br>";
    $cad .= "Apellido Paciente: ".$res[$i]['apellido_paciente']."<br>";
    $cad .= "Genero: ".$res[$i]['genero']."<br>";
    $cad .= "Fecha Consulta: ".$res[$i]['fecha']."<br>";
    $cad .= "Hora Consulta: ".$res[$i]['hora']."<br>";
    $cad .= "Hora Consulta: ".$res[$i]['hora']."<br>";
    $cad .= "Consulta: ".$res[$i]['consulta']."<br>";
    $cad .= "Nota: ".$res[$i]['nota_consulta']."<br>";
    $cad .= "<br>";

    echo $cad;
}

pg_close($detalle);

?>
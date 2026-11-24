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

$query ="SELECT 
            id,
            cli_nombre||' '||coalesce(cli_apellido) as cli_nombre,
            cli_documento,
            cli_telefono
        FROM cliente";
$exec = pg_query($detalle, $query);
$result = pg_fetch_all($exec);


for($i=0;$i < sizeof($result);$i++){

    $id = $result[$i]['id'];
    $query = "SELECT coalesce(sum(cxc_valor),0) as totalcxc FROM cxc c WHERE cxc_cliente='$id' and cxc_estado = 'A'";
    $exec = pg_query($detalle, $query);
    $cxc = pg_fetch_result($exec,0,0);
    $result[$i]['cxc'] = $cxc;

    
    $query = "SELECT tipo,referencia as num,to_char( fecha, 'DD/MON/YYYY') as fec, detalle as info,nota,valor
                FROM(
                    select 'factura' as tipo, doc_fecha as fecha, doc_valor as valor, doc_numero as referencia,
                        string_agg(art_nombre||' ('||det_cantidad||')',' / ') as detalle,doc_observacion as nota
                    from documento d, docudet dd
                    left join articulo a on (dd.det_articulo = a.id)
                    where d.doc_estado <> 'E' and doc_numero=dd.det_documento and doc_cliente = '$id'
                    group by doc_fecha,doc_valor,doc_numero,doc_observacion
                UNION
                    select 'recaudo' as tipo, r.rec_fecha as fecha, r.rec_valor as valor, r.id::varchar as referencia,
                    string_agg(rec_documento||': $'||r2.rec_valor,' / ') as detalle, '' as nota
                    from recaudo r
                    left join recdocumen r2 on (r.id = r2.rec_recaud)
                    where r.rec_estado='A' and r.id in (select rec_recaud  from recdocumen r where rec_documento in (select doc_numero from documento where doc_cliente = '$id'))
                    group by r.rec_fecha,r.rec_valor,r.id
                union 
                    select 'consulta' as tipo, c.con_fecha as fecha, 0 as valor, c.id::varchar as referencia, con_obs as detalle, con_nota as nota
                    from consulta c 
                    where con_cliente = '$id'  
                ) as d 
                order by fecha desc,tipo
                limit 12";
    
    $exec = pg_query($detalle, $query);
    $resultHistori =  pg_fetch_all($exec);
    $result[$i]['data'] = $resultHistori;
    
}

pg_close($detalle);
echo json_encode($result);
?>
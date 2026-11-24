<?php
extract($_REQUEST);
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once('configCron.php');


function sendMSM($detalle, $idAge){

    // $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_CITA_CREA'";
    // $exec = pg_query($detalle, $query);
    // $par = pg_fetch_all($exec);


    // if($par[0]['par_valor'] == "SI"){

        $query ="SELECT con_nombre  FROM consultorio c LIMIT 1";
        $exec = pg_query($detalle, $query);
        $consultorio = pg_fetch_all($exec);

        $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_ENC_CREA'";
        $exec = pg_query($detalle, $query);
        $enc = pg_fetch_all($exec);

        $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_PIE_CREA'";
        $exec = pg_query($detalle, $query);
        $pie = pg_fetch_all($exec);

        $query ="SELECT
                age_inidate,
                to_char(age_initime, 'HH12:MI AM') as age_initime,
                c.cli_telefono
                FROM agenda a 
                    left join cliente c on (a.age_cliente = c.id)
                WHERE a.id='$idAge'
                and LENGTH(cli_telefono) = 10";
        $exec = pg_query($detalle, $query);
        $cita = pg_fetch_all($exec);

        // echo "<pre>$query</pre>"; 
        
        for ($i=0; $i < sizeof($cita); $i++) { 

            if($cita[$i]['cli_telefono'] == "") continue;

            $dia = helpDiaCita($cita[$i]['age_inidate']);

            $mensaje = $consultorio[0]['con_nombre']." ".$enc[0]['par_valor']." ".$dia." a las ".$cita[$i]['age_initime'].". ".$pie[0]['par_valor'] ;        
            // $mensaje = "¡Hola! Te recordamos tu cita en el Consultorio ".CONSULTORIO_SRECOVERY.". ".$enc[0]['par_valor']."".$dia." a las ".$cita[$i]['age_initime'].". ".$pie[0]['par_valor'] ;
            $celular = $cita[$i]['cli_telefono'];
            
            // echo "Mensjae($celular): ".$mensaje;
            $res = helpSendSms($mensaje,$celular);   
            
            echo "msm enviado a ".$celular;
        }    
    // }

}

?>
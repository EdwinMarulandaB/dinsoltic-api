<?php

Class DBController{

    //CONSUMIR API DE WP
    static public function openConection($usr,$hst,$db,$pwd){

        $detalle = pg_connect("host=$hst port=5432 dbname=$db user=$usr password=$pwd");

        if (!$detalle) {
            $arrayRespuesta['estado']      = '400';
            $arrayRespuesta['mensaje']     = "Error al realizar la conexion a la base de datos!";
        }else{
            $arrayRespuesta['estado']      = '200';
            $arrayRespuesta['detalle']     = $detalle;
        }
        return $arrayRespuesta;

    }


    static public function select($detalle,$campos,$tabla,$where,$order,$group){

        $groupBy = "";
        $orderBy = "";
        ($group != "")? $groupBy = "GROUP BY $group" : "";  
        ($order != "")? $orderBy = "ORDER BY $order" : "";

        $query ="SELECT $campos 
                FROM $tabla 
                WHERE 1=1 $where
                $groupBy
                $orderBy";
        $exec = pg_query($detalle, $query);
        $result = pg_fetch_all($exec);

        // echo '<pre>$query '; print_r($query); echo '</pre>';
        return json_encode($result);
    }

    static public function insert($detalle,$campos,$tabla,$values){

        $err = "";
        $query ="INSERT INTO $tabla ($campos) VALUES ($values)";
        pg_query($detalle, $query);
        
        $err = pg_last_error($detalle);

        return $err;
    }

    static public function update($detalle,$set,$tabla,$where){

        $err = "";
        $query ="UPDATE $tabla SET $set WHERE 1=1 $where";
        pg_query($detalle, $query);
        
        $err = pg_last_error($detalle);

        return $err;
    }

    static public function close($detalle){

        pg_close($detalle);

    }

}




?>
<?php



Class AgendaController{

    //procesar mensaje para capturar cita
    static public function setAgenda($messages,$telefono,$detalle){

        $resCita = array();

        $messages = self::cleanString($messages);
        $res = self::procesaMensaje(strtoupper($messages),'RESUMEN DE TU CITA');

        if($res == "200"){
            // 1. Reemplazar los "\n" literales por saltos de línea reales
            $texto_con_saltos = str_replace('\n', "\n", $messages);

            // 2. Separar en array por cada línea
            $lineas = preg_split("/\r\n|\n|\r/", $texto_con_saltos);

            // 3. Opcional: quitar líneas vacías
            $lineas_filtradas = array_filter($lineas, fn($linea) => trim($linea) !== "");
            
            foreach($lineas_filtradas as $i => $value) {
                echo "<pre>$value</pre>"; 
                
                $res = self::procesaMensaje($value,'Nombre:');
                ($res == "200")? $resCita['nombre'] = str_replace('Nombre:','',$value)  : "";

                $res = self::procesaMensaje($value,'Nombres:');
                ($res == "200")? $resCita['nombre'] = str_replace('Nombres:','',$value)  : "";

                $res = self::procesaMensaje($value,"Dia:");
                if($res == "200"){
                    $resCita['dia'] = str_replace('Dia:','',$value);
                    $resCita['dia'] = self::procesaDia( trim($resCita['dia']) );
                } 

                $res = self::procesaMensaje($value,"Fecha:");
                if($res == "200"){
                    $resCita['dia'] = str_replace('Fecha:','',$value);
                    $resCita['dia'] = self::procesaDia( trim($resCita['dia']) );
                } 

                $res = self::procesaMensaje($value,'Hora:');
                if($res == "200"){
                    $resCita['hora'] = str_replace('Hora:','',$value);
                    $resCita['hora'] = self::convertirHoraAFormato24($resCita['hora']);
                } 

                $res = self::procesaMensaje($value,'Servicio:');
                ($res == "200")? $resCita['servicio'] = str_replace('Servicio:','',$value) : "";
            }
        }        

        // print_r($resCita);

        $res = self:: guardarCita($detalle, $telefono, $resCita);
        $resMsj = "";
        switch ($res) {
            case '401':
                $resMsj = "Sin datos para agendar cita.";
                break;
            case '402':
                $resMsj = "Inconveniente al insertar cliente.";
                break;
            case '403':
                $resMsj = "Inconveniente al insertar la cita.";
                break;
            case '200':
                $resMsj = "Cita agendada.";
                break;
        }
		return $resMsj;

	}

    static function procesaMensaje($messages,$findme){
        
        $pos = strpos($messages, trim($findme));
        if ($pos !== false) {
            return '200';
        } else {
            return '400';
        }
    }

    static function obtenerProximaFecha($diaSemana) {
        // Asegurar que esté en minúsculas y sin espacios
        $diaSemana = strtolower(trim($diaSemana));

        // Mapear nombres de días en español a inglés para strtotime
        $diasMap = [
            'lunes' => 'monday',
            'martes' => 'tuesday',
            'miércoles' => 'wednesday',
            'miercoles' => 'wednesday',
            'jueves' => 'thursday',
            'viernes' => 'friday',
            'sábado' => 'saturday',
            'sabado' => 'saturday',
            'domingo' => 'sunday'
        ];

        if (!isset($diasMap[$diaSemana])) {
            return false; // Día inválido
        }

        $hoy = new DateTime();
        $hoyStr = $hoy->format('l'); // Día de hoy en inglés (Monday, Tuesday...)

        $diaIngles = $diasMap[$diaSemana];

        // Buscar el próximo día incluso si es hoy
        $proximaFecha = new DateTime("next $diaIngles");

        return $proximaFecha->format('Y-m-d');
    }

    static function procesaDia($dia){
        
        $res = self::procesaMensaje(strtolower($dia),"lunes");
        if($res == "200"){
            return self::obtenerProximaFecha('lunes');
        } 

        $res = self::procesaMensaje(strtolower($dia),"martes");
        if($res == "200"){
            return self::obtenerProximaFecha('martes');
        } 

        $res = self::procesaMensaje(strtolower($dia),"miercoles");
        if($res == "200"){
            return self::obtenerProximaFecha('miercoles');
        } 

        $res = self::procesaMensaje(strtolower($dia),"jueves");
        if($res == "200"){
            return self::obtenerProximaFecha('jueves');
        } 

        $res = self::procesaMensaje(strtolower($dia),"viernes");
        if($res == "200"){
            return self::obtenerProximaFecha('viernes');
        } 

        $res = self::procesaMensaje(strtolower($dia),"sabado");
        if($res == "200"){
            return self::obtenerProximaFecha('sabado');
        } 

        $res = self::procesaMensaje(strtolower($dia),"domingo");
        if($res == "200"){
            return self::obtenerProximaFecha('domingo');
        } 

        $res = self::procesaMensaje(strtolower($dia),"pasado mañana");
        if($res == "200"){
            $hoy = new DateTime();
            $hoy->modify('+2 day');
            return $hoy->format('Y-m-d'); 
        }
        
        $res = self::procesaMensaje(strtolower($dia),"pasadomañana");
        if($res == "200"){
            $hoy = new DateTime();
            $hoy->modify('+2 day');
            return $hoy->format('Y-m-d'); 
        }

        $res = self::procesaMensaje(strtolower($dia),"mañana");
        if($res == "200"){            
            $hoy = new DateTime();
            $hoy->modify('+1 day');
            return $hoy->format('Y-m-d'); 
        } 

        //===================================
        $meses = [
            'enero' => '01', 'febrero' => '02', 'marzo' => '03',
            'abril' => '04', 'mayo' => '05', 'junio' => '06',
            'julio' => '07', 'agosto' => '08', 'septiembre' => '09',
            'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'
        ];

        $fechaTexto = strtolower(trim($dia));

        // Reemplazar "de", "del" por espacios (y limpiar dobles espacios)
        $fechaTexto = preg_replace('/\b(de|del)\b/i', ' ', $fechaTexto);
        $fechaTexto = preg_replace('/\s+/', ' ', $fechaTexto); // quitar espacios múltiples

        // Formatos soportados: "19 junio", "19 junio 2025"
        if (preg_match('/^(\d{1,2})\s+([a-záéíóúñ]+)(?:\s+(\d{4}))?$/i', $fechaTexto, $match)) {
            $dia = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $mesNombre = $match[2];
            $anio = isset($match[3]) ? $match[3] : date('Y');

            if (isset($meses[$mesNombre])) {
                $mes = $meses[$mesNombre];
                return "$anio-$mes-$dia";
            }
        }

    }

    static function cleanString($messages){

        $messages = str_replace("á","a",$messages);
        $messages = str_replace("Á","A",$messages);
        $messages = str_replace("é","e",$messages);
        $messages = str_replace("É","E",$messages);
        $messages = str_replace("í","i",$messages);
        $messages = str_replace("Í","I",$messages);
        $messages = str_replace("ó","o",$messages);
        $messages = str_replace("Ó","O",$messages);
        $messages = str_replace("ú","u",$messages);
        $messages = str_replace("Ú","U",$messages);
        $messages = str_replace("*","",$messages);

        return $messages;

    }

    static function convertirHoraAFormato24($hora12) {

        $hora12 = str_replace('de la tarde','pm',$hora12);
        $hora12 = str_replace('por la tarde','pm',$hora12);
        $hora12 = str_replace('tarde','pm',$hora12);

        $hora12 = str_replace('de la mañana','am',$hora12);
        $hora12 = str_replace('por la mañana','pm',$hora12);
        $hora12 = str_replace('en la mañana','am',$hora12);
        
        $hora12 = strtoupper(trim($hora12)); // Asegura que sea tipo "10 A.M" o "7 P.M"
        
        // Reemplazar "." por nada: "A.M" → "AM"
        $hora12 = str_replace('.', '', $hora12); 

        // Intentar con formato 'g:i A' (ej: 7:30 PM)
        $hora = DateTime::createFromFormat('g:i A', $hora12);
        if ($hora) {
            return $hora->format('H:i:s');
        }

        // Intentar con formato 'g A' (ej: 7 PM)
        $hora = DateTime::createFromFormat('g A', $hora12);
        if ($hora) {
            return $hora->format('H:i:s');
        }

        //===========================
        $hora = strtolower(trim(str_replace('.', '', $hora12)));

        // Eliminar 'am' o 'pm' si existen
        $hora = str_replace(['am', 'pm'], '', $hora);
        $hora = trim($hora);

        // Si la hora es solo un número (ej: "14"), agregar ":00"
        if (preg_match('/^\d{1,2}$/', $hora)) {
            $hora .= ":00";
        }

        // Si coincide con HH:MM, construir objeto DateTime para validar
        if (preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            $date = DateTime::createFromFormat('H:i', $hora);
            if ($date) {
                return $date->format('H:i:s');
            }
        }

        return false; // Formato inválido

    }

    static function guardarCita($detalle, $telefono, $cita){

        if($cita['nombre'] == "" || $cita['dia'] == "" || $cita['hora'] == "" || $cita['servicio'] == "" ){
            return "401"; //sin datos para agendarcita
        }else{

            $id = "";
            $sql = "SELECT id FROM cliente WHERE cli_telefono='$telefono'";
            $exec = pg_query($detalle, $sql);
            $result = pg_fetch_all($exec);
            if(isset($result[0]['id'])){
                $id = $result[0]['id'];
            }            

            if($id == ""){

                $sqlid = "SELECT nextval as id FROM nextval('cliente_id_seq'::regclass);";
                $exec = pg_query($detalle, $sqlid);
                $resId = pg_fetch_all($exec);
                $id = $resId[0]['id'];
                
                $query = "INSERT INTO cliente (id,cli_nombre,cli_apellido,cli_documento,cli_genero,cli_telefono,cli_estadocivil)
                        VALUES ('$id','".$cita['nombre']."','.','000','F','$telefono','1')";
                pg_query($detalle, $query);
                
                $err = pg_last_error($detalle);
                if($err != ""){
                    return "402"; // error al insertar cliente
                }
            }   

            $sql = "INSERT INTO agenda ( age_cliente, age_inidate, age_initime, age_findate, age_fintime, consultorio, age_usuari)
                    VALUES($id, '".$cita['dia']."', '".$cita['hora']."', '".$cita['dia']."', '".$cita['hora']."', 1, 10)";
            pg_query($detalle, $sql);
                
            $err = pg_last_error($detalle);
            if($err != ""){
                return "403"; //// error al insertar la cita
            }else{
                
                $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_CITA_CREA'";
                $exec = pg_query($detalle, $query);
                $par = pg_fetch_all($exec);

                if($par[0]['par_valor'] == "SI"){

                    $consultorio = "";
                    $sql = "SELECT con_nombre FROM consultorio c LIMIT 1;";
                    $exec = pg_query($detalle, $sql);
                    $result = pg_fetch_all($exec);
                    if(isset($result[0]['con_nombre'])){
                        $consultorio = $result[0]['con_nombre'];
                    }            

                    $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_ENC_CREA'";
                    $exec = pg_query($detalle, $query);
                    $enc = pg_fetch_all($exec);

                    $query ="SELECT par_valor FROM parametros WHERE par_confi='SMS_PIE_CREA'";
                    $exec = pg_query($detalle, $query);
                    $pie = pg_fetch_all($exec);

                    $dia = SMSController::helpDiaCita($cita['dia']);

                    $mensaje = "Bienvenido(@), ".$consultorio." ".$enc[0]['par_valor']." ".$dia." a las ".$cita['hora'].". ".$pie[0]['par_valor'] ;
                    $res = SMSController::helpSendSms($mensaje,$telefono);  
                    
                    echo "Mensaje: ".$mensaje;
                }

                return "200";

            }
        }

    }

}

?>
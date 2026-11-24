<?php

Class SMSController{

   static function helpSendSms($mensaje,$celular)
    {
        
        $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/priority',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                "toNumber":"57'.$celular.'", 
                "sms":"'.$mensaje.'",
                "flash":"",
                "sc":"890202",
                "request_dlvr_rcpt":""
            }',
                CURLOPT_HTTPHEADER => [
                'account: 10028472',
                'apiKey: geL1g52vnXikpOCcC5gEdkkfoPlJqE',
                'token: 4dd326f8f50c8449afd10785256fc09b',
                'Content-Type: application/json'
                ],
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
        
            return "OK";
    }

    static function helpDiaCita($fecha)
    {
        $var = explode("-",$fecha);
        $era = $var[0];
        $mes = $var[1];
        $dia = $var[2];
        $dateObj   = DateTime::createFromFormat('!m', $mes);
        $nomMes = $dateObj->format('F'); // March
        
        switch ($nomMes) {
            case 'January':
                $nomMes = "Enero";
                break;
            case 'February':
                $nomMes = "Febrero";
                break;
            case 'March':
                $nomMes = "Marzo";
                break;
            case 'April':
                $nomMes = "Abril";
                break;
            case 'May':
                $nomMes = "Mayo";
                break;
            case 'June':
                $nomMes = "Junio";
                break;
            case 'July':
                $nomMes = "Julio";
                break;
            case 'August':
                $nomMes = "Agosto";
                break;
            case 'September':
                $nomMes = "Septiembre";
                break;  
            case 'October':
                $nomMes = "Octubre";
                break;
            case 'November':
                $nomMes = "Noviembre";
                break;
            case 'December':
                $nomMes = "Diciembre";
                break;
        }

        switch (date('l', strtotime($fecha))) {
            case 'Monday':
                $nomDia = "Lunes";
                break;
            case 'Tuesday':
                $nomDia = "Martes";
                break;
            case 'Wednesday':
                $nomDia = "Miercoles";
                break;
            case 'Thursday':
                $nomDia = "Jueves";
                break;
            case 'Friday':
                $nomDia = "Viernes";
                break;
            case 'Saturday':
                $nomDia = "Sabado";
                break;
            case 'Sunday':
                $nomDia = "Domingo";
                break;
        }

        return  $nomDia." ".$dia." de ".$nomMes." del ".$era;
    }


}




?>
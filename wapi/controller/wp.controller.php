<?php

Class WPController{

    //CONSUMIR API DE WP
    static public function whatsappAPI($json,$idNumero,$token){

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://graph.facebook.com/v22.0/'.$idNumero.'/messages',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // dd($response);

        return $response;

    }


    static public function jsonAPI($celular,$mensaje){

        $json = '{
        "messaging_product": "whatsapp",
        "recipient_type": "individual",
        "to": "'.$celular.'",
        "type": "text",
        "text": {
            "preview_url": true,
            "body": "'.str_replace(["\r", "\n"], '\n', trim(urldecode($mensaje))).'"
            }
        }';

        return $json;
    }
        
}

?>
<?php 
require_once "/var/www/html/api_consulapp/wapi/controller/bd.controller.php"; 
require_once "/var/www/html/api_consulapp/wapi/controller/ia.controller.php"; 
require_once "/var/www/html/api_consulapp/wapi/controller/wp.controller.php"; 
require_once "/var/www/html/api_consulapp/wapi/controller/sms.controller.php";
require_once "/var/www/html/api_consulapp/wapi/controller/ag.controller.php"; 
require_once "/var/www/html/api_consulapp/wapi/config/config.php"; 
/*=============================================
TOKEN que configuras en la plataforma de Meta
=============================================*/

$token = "20Lu06IA090309291017";

if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["hub_verify_token"])){

	if($_GET["hub_verify_token"] == $token){

		echo $_GET["hub_challenge"];

		exit;
	
	}else{

		echo "Token inválido";
        exit;
	}

}

// require_once "/var/www/html/api_consulapp/wapi/controller/bd.controller.php"; 
// require_once "/var/www/html/api_consulapp/wapi/controller/ia.controller.php"; 
// require_once "/var/www/html/api_consulapp/wapi/controller/wp.controller.php"; 
/*=============================================
Recibir respuesta de la API de WS
=============================================*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	date_default_timezone_set("America/Bogota");
	/*=============================================
    // Obtén el contenido JSON
    =============================================*/
   
    $input = file_get_contents('php://input');

    file_put_contents("webhook_log.txt", $input."\n\n", FILE_APPEND); 

	$data = json_decode($input);

	$type_message = "";
	$status_message = "";
	$client_message = "";
	$phone_message = "";
	$type_conversation = "";
	$caption = "";
	$idConversation = "";
	$expireConversation = "";
	$keyGpt = "";
	$orgGpt = "";
	$prompt = "";
	$jsonGPT = "";
	$jsonGPT = "";
	$responseGPT = "";
	$responseWP = "";
	$jsonWP = "";
	$apiWP = "";
	$resInsert = "";
	$orderConversation = "0";
	$historyChat = "";
	$idLastConveration = "";
	$resUpdateConversation = "";
	$botMsj = "";
	$responseAgenda = "";
	$iaApi = "";

	if(isset($data->entry[0]->changes[0]->value->messages)){

		$type_message = "client";
	}

	if(isset($data->entry[0]->changes[0]->value->statuses)){

		$type_message = "business";
		$status_message = $data->entry[0]->changes[0]->value->statuses[0]->status;
	}

	file_put_contents("webhook_log.txt", "type_message: $type_message\n\n", FILE_APPEND); 

	/*=============================================
	Capturar mensaje del cliente
	=============================================*/
	if($type_message == "client"){

		$phone_message = $data->entry[0]->changes[0]->value->messages[0]->from;

		/*=============================================
		Cuando capturamos un texto
		=============================================*/

		if(isset($data->entry[0]->changes[0]->value->messages[0]->text)){
			
			$client_message = $data->entry[0]->changes[0]->value->messages[0]->text->body;
			
			$type_conversation = "text";
		}

		/*=============================================
		Capturando una imagen
		=============================================*/

		if(isset($data->entry[0]->changes[0]->value->messages[0]->image)){

			if(isset($data->entry[0]->changes[0]->value->messages[0]->image->caption)){

				$caption = $data->entry[0]->changes[0]->value->messages[0]->image->caption;
			
			}else{

				$caption = "";
			}

			$client_message = '{"type":"image","mime":"'.$data->entry[0]->changes[0]->value->messages[0]->image->mime_type.'","id":"'.$data->entry[0]->changes[0]->value->messages[0]->image->id.'","caption":"'.$caption.'"}';
			
			$type_conversation = "image";
		}

		/*=============================================
		Capturando un video
		=============================================*/

		if(isset($data->entry[0]->changes[0]->value->messages[0]->video)){

			if(isset($data->entry[0]->changes[0]->value->messages[0]->video->caption)){

				$caption = $data->entry[0]->changes[0]->value->messages[0]->video->caption;
			
			}else{
				
				$caption = "";
			}

			$client_message = '{"type":"video","mime":"'.$data->entry[0]->changes[0]->value->messages[0]->video->mime_type.'","id":"'.$data->entry[0]->changes[0]->value->messages[0]->video->id.'","caption":"'.$caption.'"}';

			$type_conversation = "video";
		}

		/*=============================================
		Capturando un audio
		=============================================*/

		if(isset($data->entry[0]->changes[0]->value->messages[0]->audio)){

			$client_message = '{"type":"audio","mime":"'.$data->entry[0]->changes[0]->value->messages[0]->audio->mime_type.'","id":"'.$data->entry[0]->changes[0]->value->messages[0]->audio->id.'"}';
			
			$type_conversation = "audio";

		}

		/*=============================================
		Capturando un documento
		=============================================*/

		if(isset($data->entry[0]->changes[0]->value->messages[0]->document)){

			if(isset($data->entry[0]->changes[0]->value->messages[0]->document->caption)){

				$caption = $data->entry[0]->changes[0]->value->messages[0]->document->caption;
			
			}else{
				
				$caption = "";
			}

			$client_message = '{"type":"document","mime":"'.$data->entry[0]->changes[0]->value->messages[0]->document->mime_type.'","id":"'.$data->entry[0]->changes[0]->value->messages[0]->document->id.'","caption":"'.$caption.'"}';

			$type_conversation = "document";
		}

		file_put_contents("webhook_log.txt", "client_message: $client_message\n\n", FILE_APPEND); 
		file_put_contents("webhook_log.txt", "phone_message: $phone_message\n\n", FILE_APPEND);
		
		/*=============================================
		Abrimos conexion con la bd 
		=============================================*/
		$jsonConect = DBController::openConection(USERLOG,HOSTIP,DBNAME,USERCLAV);
		if($jsonConect['estado'] == "200"){
			$detalle = $jsonConect['detalle'];
			echo 'Conecto correctamete a la BD';
		}else{
			echo 'Conexion BD ('.DBNAME.'):'.$jsonConect['mensaje'];
		}

		/*=============================================
			CONSULTO EL ULTIMO ORDEN + 1 DE LA CONVERSACION
		=============================================*/
		$orderConversation = DBController::select($detalle,'max(coalesce(con_orden,0))+1 as orden','conversacion'," and con_telefono ='$phone_message'",'','');
		$orderConversation = json_decode($orderConversation)[0]->orden;
		($orderConversation == "")? $orderConversation = 1 : "";
		/*=============================================
		Guardo la Conversacion Cliente
		=============================================*/
		$resInsert = DBController::insert($detalle,'con_telefono,con_orden,con_msjcli,con_tipo,con_tipomsj','conversacion',"'$phone_message','$orderConversation','$client_message','$type_message','$type_conversation'");
		file_put_contents("webhook_log.txt", "resInsert: $resInsert\n\n", FILE_APPEND);
		
		/*=============================================
		VALIDAMOS QUE EL MENSAJE SEA TIPO TEXTO PARA ENVIARSELOA CHATGPT
		DE LO CONTRARIO LE ENVIAMOS UN MSJ AL CLIENTE DICIENDO QUE LO VAMOS
		A CONTACTAR CON UN ASESOR
		=============================================*/
		if($type_conversation == "text"){

			/*=============================================
			Consultamos los datos de la api de chatgpt
			=============================================*/
			// $apiChatGPT = DBController::select($detalle,'cha_key,cha_org','chatgpt','','','');
			$apiChatGPT = DBController::select($detalle,'cha_gemikey,cha_gemimodel','chatgpt','','','');

			$keyGpt = json_decode($apiChatGPT)[0]->cha_gemikey;
			$orgGpt = json_decode($apiChatGPT)[0]->cha_gemimodel;
			file_put_contents("webhook_log.txt", "keyGpt: $keyGpt\n\n", FILE_APPEND);
			file_put_contents("webhook_log.txt", "orgGpt: $orgGpt\n\n", FILE_APPEND);

			/*=============================================
			Consultamos el prompt del negocio
			=============================================*/
			$promptGPT = DBController::select($detalle,'pro_prompt','prompt','','','');

			$prompt = json_decode($promptGPT)[0]->pro_prompt;
			$prompt = str_replace("\"","",$prompt);
			$prompt = str_replace("\'","",$prompt);
			echo '<pre>$prompt '; print_r($prompt); echo '</pre>';
			file_put_contents("webhook_log.txt", "prompt: $prompt\n\n", FILE_APPEND);

			/*=============================================
			Validamos parametro para ver si es por chatGPT o GeminiIA
			=============================================*/
			$iaApi = DBController::select($detalle,'par_valor','parametros'," and par_confi='IA_API'",'','');
			$iaApi = json_decode($iaApi)[0]->par_valor;

			if($orderConversation > 1){
				$historyChat = DBController::select($detalle,'con_tipo,con_msjcli,con_msjemp','conversacion'," and con_telefono='$phone_message' and con_tipomsj='text'",'con_id','');
			}

			if($iaApi == "CHATGPT"){

				/*=============================================
				Creamos el json para chatGPT
				=============================================*/
				$jsonGPT = IAController::jsonIA($client_message,$prompt,$historyChat);
				file_put_contents("webhook_log.txt", "jsonGPT: $jsonGPT\n\n", FILE_APPEND);

				/*=============================================
				Consumimos el api de chatGPT
				=============================================*/
				$responseGPT = IAController::chatGPT($jsonGPT,$keyGpt,$orgGpt);
				
				file_put_contents("webhook_log.txt", "responseGPT: $responseGPT\n\n", FILE_APPEND);
				$responseGPT = str_replace('"', '*', $responseGPT);

			}else{

				/*=============================================
				Creamos el json para GEMINI
				=============================================*/
				$jsonGPT = IAController::jsonGeminiIA($client_message,$prompt,$historyChat);
				file_put_contents("webhook_log.txt", "jsonGPT: $jsonGPT\n\n", FILE_APPEND);

				/*=============================================
				Consumimos el api de chatGPT
				=============================================*/
				$responseGPT = IAController::chatGemini($jsonGPT,$keyGpt,$orgGpt);
				file_put_contents("webhook_log.txt", "responseGPT: $responseGPT\n\n", FILE_APPEND);
				$responseGPT = str_replace('"', '*', $responseGPT);
			}
			
			// /*=============================================
			// Procesamos la respuesta para capturar la agenda
			// =============================================*/
			$responseAgenda = AgendaController::setAgenda($responseGPT,$phone_message,$detalle);			
			file_put_contents("webhook_log.txt", "responseAgenda: $responseAgenda\n\n", FILE_APPEND);
			/*=============================================
			Guardo la Conversacion EMPRESA
			=============================================*/
			$orderConversation = $orderConversation+1;
			$resInsert = DBController::insert($detalle,'con_conid,con_telefono,con_orden,con_msjemp,con_tipo,con_tipomsj','conversacion',"'$idConversation','$phone_message','$orderConversation','$responseGPT','business','$type_conversation'");
			file_put_contents("webhook_log.txt", "resInsert: $resInsert\n\n", FILE_APPEND);

		}else{
			/*=============================================	
			Consultamos la plantilla de tipo de mensaje
			=============================================*/
			$botMsj = DBController::select($detalle,'bot_msj','bots'," and bot_nombre='tipo_mensaje'",'','');
			$responseGPT = json_decode($botMsj)[0]->bot_msj;	
			file_put_contents("webhook_log.txt", "responseGPT: $responseGPT\n\n", FILE_APPEND);
		}
		
		/*=============================================
		Consultamos los datos de la api de WP
		=============================================*/
		$apiWP = DBController::select($detalle,'wap_idnumero,wap_token','wapi'," and wap_estado ='A'",'','');

		$idNumWP = json_decode($apiWP)[0]->wap_idnumero;
		$tokenWP = json_decode($apiWP)[0]->wap_token;
		file_put_contents("webhook_log.txt", "idNumWP: $idNumWP\n\n", FILE_APPEND);
		file_put_contents("webhook_log.txt", "tokenWP: $tokenWP\n\n", FILE_APPEND);

		/*=============================================
		Armamos el JSON de la api de WP
		=============================================*/
		$jsonWP = WPController::jsonAPI($phone_message,$responseGPT);
		file_put_contents("webhook_log.txt", "jsonWP: $jsonWP\n\n", FILE_APPEND);
		/*=============================================
		Enviamos el mensaje atraves de la api de wp
		=============================================*/
		$responseWP = WPController::whatsappAPI($jsonWP,$idNumWP,$tokenWP);
		file_put_contents("webhook_log.txt", "responseWP: $responseWP\n\n", FILE_APPEND);

		/*=============================================
		Cerramos conexion a la base de datos
		=============================================*/
		DBController::close($detalle);
		
	}

	/*=============================================
	Capturar mensaje del negocio
	=============================================*/
	if($type_message == "business" && $status_message == "sent"){

		/*=============================================
		Capturar el número de teléfono
		=============================================*/

		$phone_message = $data->entry[0]->changes[0]->value->statuses[0]->recipient_id;
		file_put_contents("webhook_log.txt", "phone_message: $phone_message\n\n", FILE_APPEND);
		
		/*=============================================
		Capturar id conversación
		=============================================*/

		$idConversation = $data->entry[0]->changes[0]->value->statuses[0]->conversation->id;
		file_put_contents("webhook_log.txt", "idConversation: $idConversation\n\n", FILE_APPEND);

		/*=============================================
		Capturar fecha de vencimiento
		=============================================*/

		$expireConversation = $data->entry[0]->changes[0]->value->statuses[0]->conversation->expiration_timestamp;
		$expireConversation = new DateTime("@$expireConversation");
		$expireConversation = $expireConversation->format('Y-m-d H:i:s');
		file_put_contents("webhook_log.txt", "expireConversation: $expireConversation\n\n", FILE_APPEND);


		/*=============================================
		Abrimos conexion con la bd 
		=============================================*/
		$jsonConect = DBController::openConection(USERLOG,HOSTIP,DBNAME,USERCLAV);
		if($jsonConect['estado'] == "200"){
			$detalle = $jsonConect['detalle'];
			echo 'Conecto correctamete a la BD';
		}else{
			echo 'Conexion BD ('.DBNAME.'):'.$jsonConect['mensaje'];
		}

		/*=============================================
			Traigo el ultimo registro de bussines 
		=============================================*/
		$idLastConveration = DBController::select($detalle,'con_id','conversacion'," and con_tipo='$type_message'",'con_id desc limit 1','');
		file_put_contents("webhook_log.txt", "idLastConveration: $idLastConveration\n\n", FILE_APPEND);
		$idLastConveration = json_decode($idLastConveration)[0]->con_id;

		/*=============================================
			ACTUALIZO EL UTIMO REGISTRO DE BUSSINESS  
		=============================================*/
		$resUpdateConversation = DBController::update($detalle,"con_conid = '$idConversation',con_vence = '$expireConversation'",'conversacion'," and con_id='$idLastConveration'");
		file_put_contents("webhook_log.txt", "resUpdateConversation: $resUpdateConversation\n\n", FILE_APPEND);
		
	}

}

 ?>
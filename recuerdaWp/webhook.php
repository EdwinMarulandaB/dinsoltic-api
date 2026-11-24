<?php 

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	date_default_timezone_set("America/Bogota");
	/*=============================================
    // Obtén el contenido JSON
    =============================================*/
   
    $input = file_get_contents('php://input');

    file_put_contents("webhook_log.txt", $input."\n\n", FILE_APPEND); 

}

?>
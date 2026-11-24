<?php

Class IAController{

    //CONSUMIR API DE CHATGPT
    static public function chatGPT($messages,$token,$org){
		
		// echo '<pre>$messages '; print_r($messages); echo '</pre>';

		// return;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
		    "model": "gpt-4-0613",
		    "messages":'.$messages.'
		}',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$token,
		    'OpenAI-Organization: '.$org,
		    'Content-Type: application/json'
		  ),
		));

    /** ,max_tokens": 100 -> con esto limito las respuestas de chatgpt
     *Muy corta (una frase)	max_tokens: 20
     *Corta (1 párrafo)	max_tokens: 50
     *Media (2-3 párrafos)	max_tokens: 100-150
     *Larga (explicación total)	max_tokens: 300+
     */

		$response = curl_exec($curl);

		curl_close($curl);
		$response = json_decode($response);
		// echo '<pre>$response '; print_r($response); echo '</pre>';
        // die();
		// return;
		return $response->choices[0]->message->content;

	}

  static public function chatGemini($messages,$token,$org){
		
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/'.$org.':generateContent?key='.$token,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
                              "contents": '.$messages.'
                              }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response_data = json_decode($response, true);

    // print_r($response_data);
    if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "";
    }

	}

    //CONSTRUIR JSON DE CHATGPT
    static public function jsonIA($client_message,$prompt,$historyChat){

        if($historyChat != ""){
            
            $result = json_decode($historyChat);

            $messages = '[
                            {
                            "role": "system",
                            "content": "'.str_replace(["\r", "\n"], '\n', trim(strip_tags(urldecode($prompt)))).'"
                            },';

            foreach ($result as $key => $value) {
						//con_tipo,con_msjcli,con_msjemp
                if($value->con_tipo == "client"){

                    $messages .= '{
                                    "role": "user",
                                    "content": "'.str_replace(["\r", "\n"], '\n', trim($value->con_msjcli)).'"
                                    },';
                }

                if($value->con_tipo == "business"){

                    $messages .= '{
                                    "role": "system",
                                    "content": "'.str_replace(["\r", "\n"], '\n', trim($value->con_msjemp)).'"
                                    },';
                }
            
            }

            $messages = substr($messages,0,-1);
            $messages .= ']';
            
        }else{

            $messages = '[
                            {
                            "role": "system",
                            "content": "'.str_replace(["\r", "\n"], '\n', trim(strip_tags(urldecode($prompt)))).'"
                            },
                            {
                            "role": "user",
                            "content": "'.$client_message.'"
                            }
                        ]';
        }

        return $messages;
    }


    //CONSTRUIR JSON DE GEMINI
    static public function jsonGeminiIA($client_message,$prompt,$historyChat){

        if($historyChat != ""){
            
            $result = json_decode($historyChat);

            $messages = '[
                            {
                                "role": "model",
                                "parts": [
                                    {
                                        "text": "'.str_replace(["\r", "\n"], '\n', trim(strip_tags(urldecode($prompt)))).'"
                                    }
                                ]
                            },';

            foreach ($result as $key => $value) {
						//con_tipo,con_msjcli,con_msjemp
                if($value->con_tipo == "client"){

                    $messages .= '{
                                      "role": "user",
                                      "parts": [
                                          {
                                              "text": "'.str_replace(["\r", "\n"], '\n', trim($value->con_msjcli)).'"
                                          }
                                      ]
                                  },';
                }

                if($value->con_tipo == "business"){

                    $messages .= '{
                                      "role": "model",
                                      "parts": [
                                          {
                                              "text": "'.str_replace(["\r", "\n"], '\n', trim($value->con_msjemp)).'"
                                          }
                                      ]
                                  },';
                }
            
            }

            $messages = substr($messages,0,-1);
            $messages .= ']';
            
        }else{

            $messages = '[
                          {
                              "role": "model",
                              "parts": [
                                  {
                                      "text": "'.str_replace(["\r", "\n"], '\n', trim(strip_tags(urldecode($prompt)))).'"
                                  }
                              ]
                          },
                          {
                              "role": "user",
                              "parts": [
                                  {
                                      "text": "'.str_replace(["\r", "\n"], '\n', trim(strip_tags(urldecode($client_message)))).'"
                                  }
                              ]
                          }
                        ]';
        }

        return $messages;
    }
}

?>
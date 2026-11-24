<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$config = require __DIR__ . '/config.php';

$resultado=subirR2("/var/www/html/gloria/images/gloria-avatar.png", "pedidos/comprobante_prueba.png");

echo "<pre>envio de subida</pre>"; 

print_r($resultado);

function hmac($key, $data)
{
    return hash_hmac('sha256', $data, $key, true);
}

function subirR2($archivoLocal, $archivoDestino)
{

    global $config;

    $accessKey = $config['access_key'];
    $secretKey = $config['secret_key'];
    $bucket    = $config['bucket'];
    $endpoint  = $config['endpoint'];
    $region    = $config['region'];
    $accountId = $config['account_id'];

    echo "<pre>vamos : $archivoLocal</pre>"; 

    if(!file_exists($archivoLocal)) return false;

    echo "<pre>vamos ...</pre>"; 
    $contenido = file_get_contents($archivoLocal);

    $payloadHash = hash('sha256',$contenido);

    $service='s3';

    $host=$accountId.'.r2.cloudflarestorage.com';

    $amzDate=gmdate('Ymd\THis\Z');

    $dateStamp=gmdate('Ymd');

    $archivoDestino=implode('/',
        array_map(
            'rawurlencode',
            explode('/',$archivoDestino)
        )
    );

    $canonicalUri='/'.$bucket.'/'.$archivoDestino;

    $canonicalHeaders=
        'host:'.$host."\n".
        'x-amz-content-sha256:'.$payloadHash."\n".
        'x-amz-date:'.$amzDate."\n";

    $signedHeaders='host;x-amz-content-sha256;x-amz-date';

    $canonicalRequest=
        "PUT\n".
        $canonicalUri."\n".
        "\n".
        $canonicalHeaders."\n".
        $signedHeaders."\n".
        $payloadHash;

    $credentialScope=
        $dateStamp.'/'.$region.'/'.$service.'/aws4_request';

    $stringToSign=
        'AWS4-HMAC-SHA256'."\n".
        $amzDate."\n".
        $credentialScope."\n".
        hash('sha256',$canonicalRequest);

    $kDate=hmac('AWS4'.$secretKey,$dateStamp);

    $kRegion=hmac($kDate,$region);

    $kService=hmac($kRegion,$service);

    $kSigning=hmac($kService,'aws4_request');

    $signature=hash_hmac(
        'sha256',
        $stringToSign,
        $kSigning
    );

    $authorization=
        'AWS4-HMAC-SHA256 '.
        'Credential='.$accessKey.'/'.$credentialScope.', '.
        'SignedHeaders='.$signedHeaders.', '.
        'Signature='.$signature;

    $url=$endpoint.'/'.$bucket.'/'.$archivoDestino;

    $mime=mime_content_type($archivoLocal);

    $ch=curl_init($url);

    curl_setopt_array($ch,[

        CURLOPT_CUSTOMREQUEST=>"PUT",

        CURLOPT_POSTFIELDS=>$contenido,

        CURLOPT_RETURNTRANSFER=>true,

        CURLOPT_HTTPHEADER=>[

            'Host: '.$host,

            'Content-Type: '.$mime,

            'Content-Length: '.strlen($contenido),

            'x-amz-content-sha256: '.$payloadHash,

            'x-amz-date: '.$amzDate,

            'Authorization: '.$authorization

        ]

    ]);

    $respuesta=curl_exec($ch);
    
    echo "Respuesta: ".$respuesta;

    $codigo=curl_getinfo($ch,CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [

        'ok'=>($codigo==200),

        'http'=>$codigo,

        'respuesta'=>$respuesta

    ];

}



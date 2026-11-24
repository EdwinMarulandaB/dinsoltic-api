<?php

$config = require __DIR__ . '/config.php';

$accessKey = $config['access_key'];
$secretKey = $config['secret_key'];
$accountId = $config['account_id'];
$bucket = $config['bucket'];
$endpoint = 'https://' . $accountId . '.r2.cloudflarestorage.com';


// echo "accessKey: $accessKey ; secretKey: $secretKey ; accountId: $accountId ; bucket: $bucket ; endpoint: $endpoint";
// die();
//Luego cambiamos este sacando de la bd el codigo del pedido
$archivo = 'pedidos/comprobante_43.jpg';
$region  = 'auto';
$service = 's3';
$host = $accountId . '.r2.cloudflarestorage.com';


// ==========================================
// FECHA
// ==========================================

$amzDate = gmdate('Ymd\THis\Z');
$dateStamp = gmdate('Ymd');


// ==========================================
// URL ENCODE DEL ARCHIVO
// ==========================================

$encodedFile = implode(
    '/',
    array_map('rawurlencode', explode('/', $archivo))
);


// ==========================================
// URI CANÓNICA
// ==========================================

$canonicalUri = '/' . $bucket . '/' . $encodedFile;


// ==========================================
// QUERY STRING
// ==========================================

$canonicalQueryString = '';


// ==========================================
// HEADERS
// ==========================================

$canonicalHeaders =
    'host:' . $host . "\n" .
    'x-amz-content-sha256:UNSIGNED-PAYLOAD' . "\n" .
    'x-amz-date:' . $amzDate . "\n";

$signedHeaders =
    'host;x-amz-content-sha256;x-amz-date';


// ==========================================
// PAYLOAD
// ==========================================

$payloadHash = 'UNSIGNED-PAYLOAD';


// ==========================================
// REQUEST CANÓNICA
// ==========================================

$canonicalRequest =
    "GET\n" .
    $canonicalUri . "\n" .
    $canonicalQueryString . "\n" .
    $canonicalHeaders . "\n" .
    $signedHeaders . "\n" .
    $payloadHash;


// ==========================================
// STRING TO SIGN
// ==========================================

$algorithm = 'AWS4-HMAC-SHA256';

$credentialScope =
    $dateStamp . '/' .
    $region . '/' .
    $service . '/aws4_request';

$stringToSign =
    $algorithm . "\n" .
    $amzDate . "\n" .
    $credentialScope . "\n" .
    hash('sha256', $canonicalRequest);


// ==========================================
// CREAR FIRMA
// ==========================================

function hmac($key, $data)
{
    return hash_hmac('sha256', $data, $key, true);
}

$kDate = hmac(
    'AWS4' . $secretKey,
    $dateStamp
);

$kRegion = hmac(
    $kDate,
    $region
);

$kService = hmac(
    $kRegion,
    $service
);

$kSigning = hmac(
    $kService,
    'aws4_request'
);

$signature = hash_hmac(
    'sha256',
    $stringToSign,
    $kSigning
);


// ==========================================
// AUTHORIZATION
// ==========================================

$authorization =
    $algorithm . ' ' .
    'Credential=' . $accessKey . '/' . $credentialScope . ', ' .
    'SignedHeaders=' . $signedHeaders . ', ' .
    'Signature=' . $signature;


// ==========================================
// URL
// ==========================================

$url =
    $endpoint .
    '/' .
    $bucket .
    '/' .
    $encodedFile;


// ==========================================
// SOLICITAR ARCHIVO A R2
// ==========================================

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Host: ' . $host,
        'x-amz-content-sha256: UNSIGNED-PAYLOAD',
        'x-amz-date: ' . $amzDate,
        'Authorization: ' . $authorization
    ]
]);

$contenido = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);


// ==========================================
// VALIDAR RESPUESTA
// ==========================================

if ($httpCode != 200) {

    http_response_code(404);

    echo 'No se pudo obtener la imagen';

    exit;
}


// ==========================================
// MOSTRAR IMAGEN
// ==========================================

header('Content-Type: ' . $contentType);

echo $contenido;
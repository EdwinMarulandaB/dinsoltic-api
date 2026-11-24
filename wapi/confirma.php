<?php
// open_wa.php
// Uso: GET o POST a open_wa.php?number=573001234567&message=Confirmo+mi+cita

// Configuración (ajusta límites si lo deseas)
$MIN_DIGITS = 7;
$MAX_DIGITS = 15;
$MAX_MESSAGE_LENGTH = 2000; // límite razonable para el texto

// Obtener parámetros (acepta GET y POST)
$numberRaw = $_REQUEST['number'] ?? '';
$messageRaw = $_REQUEST['message'] ?? '';

// Normalizar número: quitar todo lo que no sea dígito
$number = preg_replace('/\D+/', '', $numberRaw);

// Validaciones básicas del número
if (empty($number)) {
    http_response_code(400);
    echo "Parámetro 'number' requerido.";
    exit;
}
$len = strlen($number);
if ($len < $MIN_DIGITS || $len > $MAX_DIGITS) {
    http_response_code(400);
    echo "Número inválido. Debe tener entre {$MIN_DIGITS} y {$MAX_DIGITS} dígitos (formato E.164 sin '+').";
    exit;
}

// Limpiar el mensaje y limitar su tamaño
$message = trim($messageRaw);
if (strlen($message) > $MAX_MESSAGE_LENGTH) {
    $message = mb_substr($message, 0, $MAX_MESSAGE_LENGTH);
}

// Construir URL wa.me con text codificado
// Si el mensaje está vacío, enlazamos sin el parámetro text
if ($message === '') {
    $waUrl = "https://wa.me/{$number}";
} else {
    // rawurlencode para mantener espacios como %20, y que WhatsApp interprete correctamente
    $waUrl = "https://wa.me/{$number}?text=" . rawurlencode($message);
}

// Redirigir al usuario (preferible). Algunos navegadores bloquean redirecciones hacia apps móviles; incluimos fallback.
header("Location: $waUrl");
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Redirigiendo a WhatsApp...</title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;background:#f6f8fb;color:#333;padding:20px}
  .card{max-width:720px;margin:40px auto;padding:24px;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(20,20,50,0.06)}
  a.btn{display:inline-block;padding:10px 16px;background:#25D366;color:#fff;border-radius:6px;text-decoration:none}
  small{color:#666}
</style>
</head>
<body>
  <div class="card">
    <h2>Redirigiendo a WhatsApp…</h2>
    <p>Si la redirección no funciona automáticamente, haga clic en el siguiente enlace:</p>
    <p><a class="btn" href="<?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?>">Abrir WhatsApp</a></p>
    <p><small>Enlace de destino: <?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?></small></p>
    <p><small>Nota: el número debe estar en formato internacional sin signos (p.ej. 573001234567).</small></p>
  </div>
</body>
</html>

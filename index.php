<?php
// Incluir el autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Credenciales de Twilio desde variables de entorno
$sid = $_ENV['TWILIO_ACCOUNT_SID'];
$token = $_ENV['TWILIO_AUTH_TOKEN'];

// Crear una nueva instancia del cliente de Twilio
$twilio = new Client($sid, $token);

try {
    // Enviar mensaje a WhatsApp
    $message = $twilio->messages->create(
        "whatsapp:" . $_ENV['TWILIO_WHATSAPP_TO'], // número destino
        [
            "from" => "whatsapp:" . $_ENV['TWILIO_WHATSAPP_FROM'], // número de Twilio
            "body" => "hola" // mensaje a enviar
        ]
    );
    
    echo "Mensaje enviado con éxito! SID: " . $message->sid;
} catch (Exception $e) {
    echo "Error al enviar el mensaje: " . $e->getMessage();
}

// Crear respuesta para el webhook
$response = new MessagingResponse();
$response->message("hola");

// Establecer el header de contenido como XML
header("content-type: text/xml");

// Imprimir la respuesta del webhook
echo $response;
?>
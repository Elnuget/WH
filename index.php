<?php
// Incluir el autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;
use GuzzleHttp\Client as GuzzleClient;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Función para obtener respuesta de DeepSeek
function getDeepSeekResponse($message) {
    $client = new GuzzleClient();
    
    try {
        $response = $client->post('https://api.deepseek.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['DEEPSEEK_API_KEY'],
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente amigable. Proporciona respuestas cortas y concisas.'],
                    ['role' => 'user', 'content' => $message]
                ],
                'max_tokens' => 100
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return $result['choices'][0]['message']['content'] ?? "Lo siento, no pude procesar tu mensaje.";
    } catch (Exception $e) {
        return "Lo siento, hubo un error al procesar tu mensaje.";
    }
}

// Obtener el mensaje recibido de WhatsApp
$receivedMessage = $_POST['Body'] ?? '';

// Obtener respuesta de DeepSeek
$aiResponse = getDeepSeekResponse($receivedMessage);

// Credenciales de Twilio desde variables de entorno
$sid = $_ENV['TWILIO_ACCOUNT_SID'];
$token = $_ENV['TWILIO_AUTH_TOKEN'];

// Crear una nueva instancia del cliente de Twilio
$twilio = new Client($sid, $token);

try {
    // Enviar mensaje a WhatsApp
    $message = $twilio->messages->create(
        "whatsapp:" . $_ENV['TWILIO_WHATSAPP_TO'],
        [
            "from" => "whatsapp:" . $_ENV['TWILIO_WHATSAPP_FROM'],
            "body" => $aiResponse
        ]
    );
    
    echo "Mensaje enviado con éxito! SID: " . $message->sid;
} catch (Exception $e) {
    echo "Error al enviar el mensaje: " . $e->getMessage();
}

// Crear respuesta para el webhook
$response = new MessagingResponse();
$response->message($aiResponse);

// Establecer el header de contenido como XML
header("content-type: text/xml");

// Imprimir la respuesta del webhook
echo $response;
?>
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
                    ['role' => 'system', 'content' => 'Eres un asistente virtual de Eteria, empresa ubicada en Quito, Ecuador, especializada en soluciones de automatización con IA. Tus respuestas deben ser:

1. Breves y directas
2. En español
3. Con 1-2 emojis máximo
4. Enfocadas en soluciones de IA para WhatsApp y chat web

Información importante:
- WhatsApp: +593 98 316 3609
- Email: cangulo009@outlook.es
- Costos:
  * Conversación iniciada: $0.079 USD
  * Mensaje normal: $0.01 USD
  * Instalación web + WhatsApp: $100 USD

'],
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
$fromNumber = $_POST['From'] ?? ''; // Obtener el número del remitente

// Obtener respuesta de DeepSeek
$aiResponse = getDeepSeekResponse($receivedMessage);

// Credenciales de Twilio desde variables de entorno
$sid = $_ENV['TWILIO_ACCOUNT_SID'];
$token = $_ENV['TWILIO_AUTH_TOKEN'];

// Crear una nueva instancia del cliente de Twilio
$twilio = new Client($sid, $token);

try {
    // Enviar mensaje a WhatsApp al número del remitente
    $message = $twilio->messages->create(
        $fromNumber, // El número ya viene con el prefijo whatsapp: de Twilio
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
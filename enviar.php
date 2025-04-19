<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensaje WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Enviar Mensaje WhatsApp</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        require_once __DIR__ . '/vendor/autoload.php';
                        use Twilio\Rest\Client;

                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                            $dotenv->load();

                            $numero = $_POST['numero'] ?? '';

                            if (!empty($numero)) {
                                try {
                                    $sid = $_ENV['TWILIO_ACCOUNT_SID'];
                                    $token = $_ENV['TWILIO_AUTH_TOKEN'];
                                    $twilio = new Client($sid, $token);

                                    // Formatear el número si es necesario
                                    if (!str_starts_with($numero, '+')) {
                                        $numero = '+' . $numero;
                                    }

                                    $message = $twilio->messages->create(
                                        "whatsapp:" . $numero,
                                        [
                                            "from" => "whatsapp:" . $_ENV['TWILIO_WHATSAPP_FROM'],
                                            "body" => "",
                                            "contentSid" => "HX5daeed1f7315c02e48cd1b8a476c1092"
                                        ]
                                    );
                                    echo '<div class="alert alert-success">Mensaje enviado con éxito!</div>';
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error al enviar el mensaje: ' . $e->getMessage() . '</div>';
                                }
                            }
                        }
                        ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número de WhatsApp (con código de país):</label>
                                <input type="text" class="form-control" id="numero" name="numero" placeholder="Ej: +593983468115" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar Saludo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
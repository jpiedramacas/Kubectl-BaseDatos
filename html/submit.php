<?php

require 'vendor/autoload.php'; // Carga el autoloader de Composer para incluir las dependencias de AWS SDK

use Aws\Sns\SnsClient; // Importa la clase SnsClient desde el SDK de AWS
use Aws\Exception\AwsException; // Importa la clase AwsException desde el SDK de AWS

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y validar datos de entrada
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if ($name && $email && $message) {
        // Reemplaza 'your-sns-topic-arn' con el ARN de tu tema de SNS
        $snsTopicArn = 'arn:aws:sns:us-east-1:XXXXX:test';

        // Inicializa el cliente de SNS
        $snsClient = new SnsClient([
            'version' => 'latest',
            'region' => 'us-east-1' // Reemplaza con tu región de AWS deseada
        ]);

        // Crea el mensaje para enviar al tema de SNS
        $messageToSend = json_encode([
            'email' => $email,
            'name' => $name,
            'message' => $message
        ]);

        try {
            // Publica el mensaje en el tema de SNS
            $snsClient->publish([
                'TopicArn' => $snsTopicArn,
                'Message' => $messageToSend
            ]);

            // Inserta los datos del formulario en la base de datos MySQL
            $mysqli = new mysqli("mysql", "my_user", "my_password", "my_database");

            // Verifica la conexión
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Prepara y enlaza los parámetros para la consulta SQL de inserción
            $stmt = $mysqli->prepare("INSERT INTO form_data (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message);

            // Ejecuta la consulta
            $stmt->execute();

            echo "Message sent successfully.";
        } catch (AwsException $e) {
            echo "Error sending message: " . $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            echo "Database error: " . $e->getMessage();
        }
    } else {
        echo "Invalid input.";
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
?>

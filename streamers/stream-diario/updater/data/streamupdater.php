<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Constantes
define('DATASET_API', 'https://api.apify.com/v2/actor-tasks/cheiicosky~stream/run-sync-get-dataset-items?token=apify_api_9o9U84YbdfdHIdu1BC0j1JMke8Pd8k3ZDWI3');
define('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions');
define('DEEPSEEK_API_KEY', 'sk-9eba01aae3c948789a253dbc03c2d7da');
define('DOWNLOAD_DIR', __DIR__ . '/img/postimg/');
define('OUTPUT_JSON', __DIR__ . '/top_ranking.json');

/**
 * Procesa y comprime una imagen para reducir su tamaño
 */
function processImage(string $imagePath, int $maxWidth = 1024, int $quality = 80): string {
    // Obtener información de la imagen
    $imageInfo = getimagesize($imagePath);
    $mimeType = $imageInfo['mime'];
    
    // Crear imagen según el tipo
    switch ($mimeType) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($imagePath);
            break;
        default:
            throw new Exception("Formato de imagen no soportado: $mimeType");
    }
    
    // Obtener dimensiones originales
    $originalWidth = imagesx($image);
    $originalHeight = imagesy($image);
    
    // Calcular nuevas dimensiones manteniendo la proporción
    if ($originalWidth > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int)($originalHeight * ($maxWidth / $originalWidth));
    } else {
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;
    }
    
    // Crear nueva imagen redimensionada
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preservar transparencia para PNG (aunque luego convertiremos a JPEG)
    if ($mimeType === 'image/png') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Redimensionar
    imagecopyresampled(
        $resizedImage, $image,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $originalWidth, $originalHeight
    );
    
    // Guardar en buffer
    ob_start();
    imagejpeg($resizedImage, null, $quality);
    $processedImage = ob_get_clean();
    
    // Liberar memoria
    imagedestroy($image);
    imagedestroy($resizedImage);
    
    return $processedImage;
}

/**
 * Descarga la última imagen del dataset
 * (Función original sin cambios)
 */
function downloadLatestPostImage(): ?string {
    $client = new Client(['verify' => false]);

    if (!file_exists(DOWNLOAD_DIR)) {
        mkdir(DOWNLOAD_DIR, 0755, true);
    }

    try {
        $response = $client->get(DATASET_API);
        $data = json_decode((string)$response->getBody(), true);

        foreach ($data as $profile) {
            if (empty($profile['latestPosts'])) continue;
            
            $latestPost = $profile['latestPosts'][0];
            if (empty($latestPost['displayUrl']) || empty($latestPost['timestamp'])) continue;

            // Generar nombre único
            $timestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $latestPost['timestamp']);
            $filename = $timestamp->format('Y-m-d_His_u') . '.jpg';
            $filePath = DOWNLOAD_DIR . $filename;
            $tempPath = DOWNLOAD_DIR . 'temp_' . md5($filename);

            // Descargar imagen a temp
            $client->get($latestPost['displayUrl'], ['sink' => $tempPath]);

            // Comparar hashes
            $needsUpdate = true;
            if (file_exists($filePath)) {
                $existingHash = md5_file($filePath);
                $newHash = md5_file($tempPath);
                $needsUpdate = ($existingHash !== $newHash);
            }

            if ($needsUpdate) {
                rename($tempPath, $filePath);
                echo "Imagen descargada/actualizada: $filename\n";
                return $filePath;
            } else {
                unlink($tempPath);
                echo "Imagen sin cambios: $filename\n";
                return $filePath; 
            }
        }

    } catch (RequestException $e) {
        error_log("Error API dataset: " . $e->getMessage());
    }

    return null;
}

// 1. Descargar imagen
$imagePath = downloadLatestPostImage();
if (!$imagePath) die("No se descargó ninguna imagen para procesar.\n");

// 2. Procesar imagen (comprimir y redimensionar)
try {
    $processedImage = processImage($imagePath, 1024, 80);
    $imageBase64 = base64_encode($processedImage);
} catch (Exception $e) {
    die("Error procesando imagen: " . $e->getMessage() . "\n");
}

// 3. Preparar prompts (sin cambios)
$systemPrompt = <<<EOD
Analiza la imagen y devuelve ÚNICAMENTE un JSON válido con el formato:

{
    "ranking": [
        {
            "posicion": "01",
            "titulo": "Ejemplo",
            "streamer": "Streamer",
            "plataforma": "Plataforma",
            "pais": "País",
            "inicio": "2023-01-01 12:00:00",
            "fin": "2023-01-01 14:00:00",
            "duracion": "2 horas",
            "audiencia_media": 15000,
            "minuto_de_oro": {
                "audiencia": 25000,
                "hora": "13:30"
            },
            "avatar": "Descripción"
        }
    ]
}
EOD;

$userPrompt = <<<EOD
Analiza esta imagen y extrae la información del ranking de emisiones según el formato solicitado.
EOD;

// 4. Configurar solicitud API (sin cambios)
try {
    $messages = [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => [
                [
                    "type" => "text",
                    "text" => $userPrompt
                ],
                [
                    "type" => "image_url",
                    "image_url" => [
                        "url" => "https://billarrd.com/top/streamers/stream-diario/updater/data/img/postimg/$tempPath"
                    ]
                ]
            ]
        ]
    ];

    $postData = [
        "model" => "deepseek-chat",
        "messages" => $messages,
        "response_format" => ["type" => "json_object"],
        "max_tokens" => 4000
    ];

    // 5. Enviar solicitud (sin cambios)
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => DEEPSEEK_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . DEEPSEEK_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($postData, JSON_UNESCAPED_SLASHES)
    ]);

    file_put_contents('api_request.json', json_encode($postData, JSON_PRETTY_PRINT));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_errno($curl)) {
        throw new Exception('cURL error: ' . curl_error($curl));
    }
    
    curl_close($curl);

    // 6. Validar y guardar respuesta (sin cambios)
    if ($httpCode != 200) {
        throw new Exception("HTTP Error $httpCode: $response");
    }

    $jsonResponse = json_decode($response);
    if (!$jsonResponse) {
        throw new Exception("Respuesta JSON inválida: " . $response);
    }

    file_put_contents(OUTPUT_JSON, json_encode($jsonResponse, JSON_PRETTY_UNICODE | JSON_UNESCAPED_SLASHES));
    echo "Ranking guardado exitosamente en " . OUTPUT_JSON . "\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
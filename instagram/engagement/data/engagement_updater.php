<?php
require 'vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
                       
define('DATASET_API', 'https://api.apify.com/v2/actor-tasks/cheiicosky~top-instagram/run-sync-get-dataset-items?token=apify_api_9o9U84YbdfdHIdu1BC0j1JMke8Pd8k3ZDWI3');
define('IMAGE_DIR', __DIR__ . '/img/profileimg');

// Función para descargar imágenes
function download_profile_image($image_url, $username) {
    try {
        $sanitized = preg_replace('/[^a-zA-Z0-9]/', '_', $username);
        $filename = $sanitized . '.jpg';
        $filepath = IMAGE_DIR . '/' . $filename;
        
        // Crear directorio si no existe
        if (!is_dir(IMAGE_DIR)) {
            mkdir(IMAGE_DIR, 0755, true);
        }
        
        // Verificar si ya existe
        if (file_exists($filepath)) {
            return 'img/profileimg/' . $filename;
        }

        // Descargar directamente
        $image_data = file_get_contents($image_url);
        if ($image_data !== false) {
            file_put_contents($filepath, $image_data);
            return 'img/profileimg/' . $filename;
        }
    } catch (Exception $e) {
        error_log("Error descargando imagen para $username: " . $e->getMessage());
    }
    return 'img/profileimg/default.jpg';
}
// Modificar process_user_data
function process_user_data($dataset) {
    $processed = [];
    $utc = new DateTimeZone('UTC');
    $sevenDaysAgo = (new DateTime('now', $utc))->modify('-7 days');
    
    foreach ($dataset as $user) {
        
        // Filtrar publicaciones válidas (no fijadas y dentro de últimos 7 días)
        $validPosts = array_filter($user['latestPosts'] ?? [], function($post) use ($sevenDaysAgo, $utc) {
            // Excluir publicaciones fijadas
            if ($post['isPinned'] ?? false) return false;
            
            // Validar timestamp
            if (!isset($post['timestamp'])) return false;
            
            try {
                $postDate = new DateTime($post['timestamp'], $utc);
            } catch (Exception $e) {
                return false;
            }
            
            return $postDate >= $sevenDaysAgo;
        });
        
        // Excluir usuarios con menos de 7 publicaciones válidas
        if (count($validPosts) < 7) continue;
        
        // Calcular métricas
        $totalLikes = array_sum(array_column($validPosts, 'likesCount'));
        $totalComments = array_sum(array_column($validPosts, 'commentsCount'));
        $followers = $user['followersCount'] ?? 0;
        
        $avgLikes = $totalLikes / 7;  // Usamos 7 posts garantizados
        $avgComments = $totalComments / 7;
        $engagement = $followers > 0 ? round((($avgLikes + $avgComments) / $followers) * 100, 2) : 0;
        
        // Descargar imagen y obtener ruta local
        $local_image = !empty($user['profilePicUrlHD']) 
            ? download_profile_image($user['profilePicUrlHD'], $user['username'])
            : '/images/default.jpg';

        $processed[] = [
            'id' => $user['id'] ?? 'N/A',
            'username' => $user['username'] ?? 'Usuario desconocido',
            'usuario' => $user['fullName'] ?? 'Nombre no disponible',
            'imagen_url' => $local_image, // Usar ruta local
            'url_perfil' => $user['url'] ?? '#',
            'seguidores_totales' => $followers,
            'siguiendo' => $user['followsCount'] ?? 0,
            'engagement' => $engagement,
            'total_likes' => $totalLikes,
            'total_comentarios' => $totalComments,
            'avg_likes' => $avgLikes,
            'avg_comentarios' => $avgComments,
            'posts_considerados' => 7  // Siempre 7 por el filtro
        ];
    
    }
    return $processed;
}

// ... (El resto del código anterior se mantiene igual)

function generate_engagement_rankings($processed_data) {
    // Dividir en grupos según seguidores
    $group1 = array_filter($processed_data, function($user) {
        return $user['seguidores_totales'] <= 500000;
    });
    
    $group2 = array_filter($processed_data, function($user) {
        return $user['seguidores_totales'] > 500000 && $user['seguidores_totales'] <= 1000000;
    });
    
    $group3 = array_filter($processed_data, function($user) {
        return $user['seguidores_totales'] > 1000000;
    });

    // Procesar cada grupo
    process_group($group1, 'ranking_0_500k.json');
    process_group($group2, 'ranking_500k_1M.json');
    process_group($group3, 'ranking_1M+.json');
}

function process_group($group_data, $filename) {
    if (empty($group_data)) return;
    
    // Ordenar por engagement
    usort($group_data, function($a, $b) {
        return $b['engagement'] <=> $a['engagement'];
    });

    // Cargar datos históricos específicos para este grupo
    $history_file = "history_$filename";
    $old_data = file_exists($history_file) 
        ? json_decode(file_get_contents($history_file), true)
        : [];

    // Calcular cambios de posición
    foreach ($group_data as $index => &$item) {
        $item['posicion'] = $index + 1;
        $old_item = current(array_filter($old_data, function($old) use ($item) {
            return $old['id'] === $item['id'];
        }));
        
        $item['posicion_anterior'] = $old_item['posicion'] ?? 'Nuevo';
        $cambio = isset($old_item['posicion']) ? $old_item['posicion'] - $item['posicion'] : 'Nuevo';
        $item['cambio_posicion'] = is_numeric($cambio) ? ($cambio > 0 ? "↑$cambio" : "↓".abs($cambio)) : $cambio;
        
        if (isset($old_item['engagement']) && $old_item['engagement'] > 0) {
            $variacion = (($item['engagement'] - $old_item['engagement']) / $old_item['engagement']) * 100;
            $item['variacion_porcentual'] = round($variacion, 2) . '%';
        } else {
            $item['variacion_porcentual'] = 'N/A';
        }
    }

    // Guardar nuevos datos
    file_put_contents($filename, 
        json_encode($group_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    
    // Mantener histórico para la próxima ejecución
    file_put_contents($history_file, 
        json_encode($group_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Modificar la sección principal
function fetch_dataset_items() {
    $client = new Client();
    try {
        $response = $client->get(DATASET_API);
        return json_decode($response->getBody(), true);
    } catch (RequestException $e) {
        throw new Exception("Error al obtener datos de la API: " . $e->getMessage());
    }
}
try {
    $dataset = fetch_dataset_items();
    $processed_data = process_user_data($dataset);
    
    if (empty($processed_data)) {
        echo "No hay usuarios que cumplan con los criterios\n";
    } else {
        generate_engagement_rankings($processed_data);
        echo "Rankings actualizados! Usuarios calificados: " . count($processed_data) . "\n";
    }
} catch (Exception $e) {
    die("Error en el proceso principal: " . $e->getMessage());
}
?>
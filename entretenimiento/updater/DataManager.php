<?php
require 'vendor/autoload.php';
require __DIR__.'/config.users.php';
require __DIR__.'/config.api.php';

use GuzzleHttp\Client;

class DataManager {
    private $client;
    private $api_config;
    private $data_files;
    private $channels;

    public function __construct() {
        $this->client = new Client();
        $this->api_config = $GLOBALS['youtube_api_config'];
        $this->data_files = $GLOBALS['data_files'];
        $this->channels = $GLOBALS['youtube_channels'];
    }

    public function updateAllChannels() {
        $all_data = $this->loadAllData();
        
        foreach ($this->channels as $channelId => $name) {
            $current_data = $this->fetchChannelData($channelId);
            
            if($current_data) {
                $stored_data = $all_data[$channelId] ?? $this->initializeChannelData($channelId);
                $updated_data = $this->calculateDailyChanges($stored_data, $current_data);
                $all_data[$channelId] = $this->processStatistics($updated_data);
            }
        }
        
        $this->saveAllData($all_data);
    }
    
    private function initializeChannelData($channelId) {
        return [
            'id' => $channelId,
            'usuario' => $this->channels[$channelId] ?? $channelId,
            'views' => 0,
            'subscribers' => 0,
            'videos' => 0,
            'created' => null,
            'avatar' => null,
            'daily' => [],
            'posicion' => 0,
            'posicion_anterior' => null,
            'cambio' => 0,
            'cambio_porcentual' => 0
        ];
    }

    private function fetchChannelData($channelId) {
        try {
            $response = $this->client->get($this->api_config['endpoint'].'channels', [
                'query' => [
                    'part' => 'snippet,statistics',
                    'id' => $channelId,
                    'key' => $this->api_config['api_key']
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $this->parseChannelData($data);
            
        } catch(Exception $e) {
            error_log("Error fetching data for $channelId: ".$e->getMessage());
            return null;
        }
    }

    private function parseChannelData($api_data) {
        if(empty($api_data['items'])) return null;
        
        $channel = $api_data['items'][0];
        return [
            'timestamp' => time(),
            'views' => (int)$channel['statistics']['viewCount'],
            'subscribers' => $channel['statistics']['subscriberCount'],
            'videos' => (int)$channel['statistics']['videoCount'],
            'created' => date('Y-m-d', strtotime($channel['snippet']['publishedAt'])),
            'avatar' => $channel['snippet']['thumbnails']['high']['url']
        ];
    }
    
    private function calculateDailyChanges($stored, $current) {
        $currentSubs = (int)$current['subscribers'];
        $storedSubs = (int)($stored['subscribers'] ?? 0);
        
        $daily_entry = [
            'date' => date('Y-m-d'),
            'views' => $current['views'] - ($stored['views'] ?? 0),
            'subs' => $currentSubs - $storedSubs
        ];
        
        $stored['daily'] = array_slice($stored['daily'] ?? [], 0, 29);
        array_unshift($stored['daily'], $daily_entry);
        
        return array_merge($stored, [
            'views' => $current['views'],
            'subscribers' => $current['subscribers'],
            'videos' => $current['videos'],
            'created' => $current['created'],
            'avatar' => $current['avatar']
        ]);
    }

    private function processStatistics($data) {
        $data['visitas_semanales'] = array_sum(array_column(array_slice($data['daily'], 0, 7), 'views'));
        $data['visitas_mensuales'] = array_sum(array_column(array_slice($data['daily'], 0, 30), 'views'));
        $data['vistas_totales'] = $data['views'];
        $data['suscriptores_semanales'] = array_sum(array_column(array_slice($data['daily'], 0, 7), 'subs'));
        $data['suscriptores_mensuales'] = array_sum(array_column(array_slice($data['daily'], 0, 30), 'subs'));
        
        return $data;
    }
    
    private function loadAllData() {
        $file_path = $this->data_files['base_path'] . $this->data_files['channels'];
        
        if (!file_exists($file_path)) {
            $this->initializeDataFile();
        }
        
        $data = json_decode(file_get_contents($file_path), true);
        return is_array($data) ? $data : [];
    }

    private function saveAllData($data) {
        $file_path = $this->data_files['base_path'] . $this->data_files['channels'];
        file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function initializeDataFile() {
        $initial_data = [];
        foreach ($this->channels as $channelId => $name) {
            $initial_data[$channelId] = $this->initializeChannelData($channelId);
        }
        $this->saveAllData($initial_data);
    }
}

// Ejecución
$manager = new DataManager();
$manager->updateAllChannels();
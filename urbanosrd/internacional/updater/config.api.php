<?php
// Configuración API YouTube
$youtube_api_config = [
    'api_key' => 'AIzaSyApvlIjp8t_h7RvpmOuETh5uJE1wqMdBEY',
    'endpoint' => 'https://www.googleapis.com/youtube/v3/',
    'cache_ttl' => 3600 // 1 hora de caché
];

// Configuración de archivos de datos
$data_files = [
    'base_path' => __DIR__.'/data/',
    'channels' => 'channels_data.json'
];
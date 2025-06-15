<?php
require __DIR__.'/config.users.php';
require __DIR__.'/config.api.php';

class RankingManager {
    private $data_files;

    public function __construct() {
        $this->data_files = $GLOBALS['data_files'];
    }

    public function generateRankings() {
        $all_data = $this->loadData();
        
        foreach(['visitas_semanales', 'visitas_mensuales', 'vistas_totales'] as $metric) {
            $this->updateRanking($all_data, $metric);
        }
    }

    private function updateRanking($data, $metric) {
        $file_map = [
            'visitas_semanales' => 'viewssemanal_ranking_data.json',
            'visitas_mensuales' => 'viewsmensuales_ranking_data.json',
            'vistas_totales' => 'viewstotales_ranking_data.json'
        ];
    
        $sorted = $data;
        usort($sorted, function($a, $b) use ($metric) {
            return $b[$metric] <=> $a[$metric];
        });
    
        $old_ranking = $this->loadPreviousRanking($file_map[$metric]);
        $new_ranking = [];
        
        foreach ($sorted as $index => $entry) {
            $channel_id = $entry['id'];
            $new_position = $index + 1;
            
            $prev_data = array_filter($old_ranking, function($item) use ($channel_id) {
                return $item['id'] === $channel_id;
            });
            $prev_data = reset($prev_data);
    
            // Fusionar todos los datos del canal con los nuevos campos de ranking
            $new_entry = array_merge($entry, [
                'posicion' => $new_position,
                'posicion_anterior' => $prev_data['posicion'] ?? null,
                'cambio' => $prev_data ? ($prev_data['posicion'] - $new_position) : 0
            ]);
    
            // Calcular cambio porcentual si aplica
            if ($prev_data && isset($prev_data[$metric])) {
                $prev_value = $prev_data[$metric];
                $current_value = $entry[$metric];
                
                if ($prev_value > 0) {
                    $porcentaje = (($current_value - $prev_value)/$prev_value)*100;
                    $new_entry['cambio_porcentual'] = round($porcentaje, 2)."%";
                }
            }
    
            $new_ranking[] = $new_entry;
        }
    
        $this->saveRankingFile($new_ranking, $file_map[$metric]);
    }
    
    private function loadPreviousRanking($filename) {
        $file_path = $this->data_files['base_path'].$filename;
        return file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : [];
    }
    
    private function saveRankingFile($data, $filename) {
        $file_path = $this->data_files['base_path'].$filename;
        file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    private function loadData() {
        $file_path = $this->data_files['base_path'].$this->data_files['channels'];
        return json_decode(file_get_contents($file_path), true);
    }
}

$rankingManager = new RankingManager();
$rankingManager->generateRankings();
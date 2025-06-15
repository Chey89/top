<?php
function semanal_display_ranking() {
    $semanal_ranking_data = [];
    if (file_exists('/home/sabomdzv/billarrd.com/top/urbanosrd/local/updater/data/viewssemanal_ranking_data.json')) {
        $semanal_ranking_data = json_decode(file_get_contents('/home/sabomdzv/billarrd.com/top/urbanosrd/local/updater/data/viewssemanal_ranking_data.json'), true);
    }

    $ultima_actualizacion = date("d-m-Y");
    
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
        <meta name="description" content="Top Ranking de los mejores YouTubers de RD">
        <title>Top Ranking YouTubers RD</title>
        <link rel="stylesheet" href="/top/assets/css/styles.css">
        <link rel="shortcut icon" href="https://socialblade.com/favicon.ico">
        <!-- Meta tags optimizados -->
    </head>
    <body>
    
    <header class="main-header">
    <div class="header-content">
        <div class="logo">TopRD</div>
        <button class="hamburger" aria-label="Menú">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav class="main-nav">
                <a href="#" class="nav-link">Inicio</a>
                <a href="https://billarrd.com/top/sobre-nosotros" class="nav-link">Sobre nosotros</a>
                <a href="https://billarrd.com/top/contacto/" class="nav-link">Contacto</a>
                <div class="dropdown">
                    <a class="nav-link"><p>Categorías ▾</p></a>
                    <div class="dropdown-menu">
                        <div class="subdropdown">
                            <a class="nav-link"><p>Entretenimiento ▾</p></a>
                            <div class="subdropdown-menu">
                                <a href="https://billarrd.com/top/entretenimiento/views-semanales/" class="nav-link"><p>Views Semanales</p></a>
                                <a href="https://billarrd.com/top/entretenimiento/views-mensuales/" class="nav-link"><p>Views Mensuales</p></a>
                                <a href="https://billarrd.com/top/entretenimiento/views-totales/" class="nav-link"><p>Views Totales</p></a>
                            </div>
                        </div>
                        <div class="subdropdown">
                            <a class="nav-link"><p>Artistas ▾</p></a>
                            <div class="subdropdown-menu">
                                <a href="https://billarrd.com/top/artistas/views-semanales/" class="nav-link"><p>Views Semanales</p></a>
                                <a href="https://billarrd.com/top/artistas/views-mensuales/" class="nav-link"><p>Views Mensuales</p></a>
                                <a href="https://billarrd.com/top/artistas/views-totales/" class="nav-link"><p>Views Totales</p></a>
                            </div>
                        </div>                        
                        <!-- Repetir misma estructura para Gamer y Artistas -->
                    </div>
                </div>                
        </nav>
    </div>
</header>

    <div class="banner" id="banner-header"></div>
    <div class="banner-lateral left" id="latera-1"></div>
    <div class="banner-lateral right" id="latera-2"></div>

    <main class="main-content">
        <div class="container">
            <h1 class="main-title">Entretenimiento Views Semanal</h1>
            <div class="update-info">Actualizado: '.htmlspecialchars($ultima_actualizacion).'</div>
            
            <div class="ranking-grid">';

    if (!empty($semanal_ranking_data)) {
        foreach ($semanal_ranking_data as $index => $canal) {
            $hidden_class = $index >= 5 ? 'hidden' : '';
            $posicion_icon = ($index == 0) ? '🏆' : (($index == 1) ? '🥈' : (($index == 2) ? '🥉' : $index + 1));
            $cambio_clase = $canal['cambio'] > 0 ? 'up' : ($canal['cambio'] < 0 ? 'down' : '');
            $cambio_icono = $canal['cambio'] > 0 ? '▲' : ($canal['cambio'] < 0 ? '▼' : '─');
            
            echo '<article class="card '.$hidden_class.'">
                <div class="card-header">
                    <span class="position-badge">'.$posicion_icon.'</span>
                    <img src="'.htmlspecialchars($canal['avatar']).'" 
                         alt="'.htmlspecialchars($canal['usuario']).'" 
                         class="channel-img"
                         loading="lazy">
                    <div class="channel-main">
                        <h2 class="channel-name">'.htmlspecialchars($canal['usuario']).'</h2>
                        <div class="stats-preview">
                            <span class="weekly-views">'.number_format($canal['visitas_semanales']).'</span>
                            <span class="change-indicator '.$cambio_clase.'">'.$cambio_icono.'</span>
                            <div class="canal-diferencia ' . (!empty($canal['views_sumados']) ? 'views-sumados' : (!empty($canal['views_restados']) ? 'views-restados' : 'sin-cambio')) . '">
                            ' . (!empty($canal['views_sumados']) ? htmlspecialchars($canal['views_sumados']) : (!empty($canal['views_restados']) ? htmlspecialchars($canal['views_restados']) : '─')) . '
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-details">
                    <button class="toggle-details" data-index="'.$index.'">Más Detalles</button>
                    <div class="extra-info hidden">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span>Views Mensuales:</span>
                                <span>'.number_format($canal['visitas_mensuales']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Views Totales:</span>
                                <span>'.number_format($canal['vistas_totales']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Suscriptores Semanales:</span>
                                <span>'.number_format($canal['suscriptores_semanales']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Suscriptores Totales:</span>
                                <span>'.number_format($canal['subscribers']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Videos Subidos:</span>
                                <span>'.number_format($canal['videos']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Canal Creado:</span>
                                <span>'.htmlspecialchars($canal['created']).'</span>
                            </div>                            
                        </div>
                        <div class="channel-actions">
                            <a href="https://socialblade.com/youtube/handle/'.$canal['id'].'" 
                               class="action-btn sb-btn" 
                               target="_blank"
                               rel="noopener">SocialBlade</a>
                            <a href="https://www.youtube.com/@'.$canal['id'].'" 
                               class="action-btn yt-btn" 
                               target="_blank"
                               rel="noopener">YouTube</a>
                            <div class="banner" id="banner-card-'.$index.'"></div>
                        </div>
                        </div>
                </div>
            </article>';
        }
        echo '</div>
            <button class="load-more" id="ver-mas-btn">Mostrar más</button>';
    } else {
        echo '<p class="no-data">No hay datos disponibles</p>';
    }
    
    echo '</div></main>
    <div class="banner" id="banner-footer"></div>
    
    <footer class="main-footer">
        &copy; 2025 Ranking YouTubers RD. Todos los derechos reservados.
    </footer>
    
    <script src="/top/assets/js/scripts.js"></script>
    </body>
    </html>';
}

semanal_display_ranking();
?>
<?php
function engagement_display_ranking() {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    
    $engagement_ranking_data = [];
    if (file_exists('engagement_ranking_data.json')) {
        $engagement_ranking_data = json_decode(file_get_contents('engagement_ranking_data.json'), true);
    }

    $ultima_actualizacion = date("d-m-Y");
    
    echo '<!DOCTYPE html>
    <html lang="es">
   <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
        <meta name="description" content="Top Ranking de los mejores YouTubers de RD">
        <title>Top Ranking YouTubers RD</title>
        <link rel="stylesheet" href="styles.css">
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
                                <a href="https://billarrd.com/top/entretenimiento/suscriptores-semanales" class="nav-link"><p>Suscriptores Semanales</p></a>
                                <a href="https://billarrd.com/top/entretenimiento/suscriptores-semanales" class="nav-link"><p>Suscriptores Totales</p></a>
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
            <h1 class="main-title">Instagram Top Engagement Semanal</h1>
            <div class="update-info">Actualizado: '.htmlspecialchars($ultima_actualizacion).'</div>
            
            <div class="ranking-grid">';

    if (!empty($engagement_ranking_data)) {
        foreach ($engagement_ranking_data as $index => $canal) { // ✅ Variable corregida
            $hidden_class = $index >= 5 ? 'hidden' : '';
            $posicion_icon = ($index == 0) ? '🏆' : (($index == 1) ? '🥈' : (($index == 2) ? '🥉' : $index + 1));
            $cambio_clase = $canal['cambio_posicion'] > 0 ? 'up' : ($canal['cambio_posicion'] < 0 ? 'down' : ''); // ✅ Clave corregida
            $cambio_icono = $canal['cambio_posicion'] > 0 ? '▲' : ($canal['cambio_posicion'] < 0 ? '▼' : '─'); // ✅ Clave corregida
            
            echo '<article class="card '.$hidden_class.'">
                <div class="card-header">
                    <span class="position-badge">'.$posicion_icon.'</span>
                    <img src="'.$canal['imagen_url'].'"
                         alt="'.htmlspecialchars($canal['usuario']).'" 
                         class="channel-img"
                         loading="lazy">
                    <div class="channel-main">
                        <h2 class="channel-name">@'.htmlspecialchars($canal['username']).'</h2>
                        <div class="stats-preview">
                            <span class="weekly-views">'.number_format($canal['engagement'], 2).'</span>
                            <span class="change-indicator '.$cambio_clase.'">'.$cambio_icono.'</span>
                          
                        </div>
                    </div>
                </div>

                <div class="card-details">
                    <button class="toggle-details" data-index="'.$index.'">Más Detalles</button>
                    <div class="extra-info hidden">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span>Seguidores:</span>
                                <span>'.number_format($canal['seguidores_totales']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Siguiendo:</span>
                                <span>'.number_format($canal['siguiendo']).'</span>
                            </div>                            
                            <div class="stat-item">
                                <span>Total Likes:</span>
                                <span>'.number_format($canal['total_likes']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Total Comentarios:</span>
                                <span>'.number_format($canal['total_comentarios']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Promedio Likes:</span>
                                <span>'.number_format($canal['avg_likes']).'</span>
                            </div>
                            <div class="stat-item">
                                <span>Promedio Comentarios:</span>
                                <span>'.number_format($canal['avg_comentarios']).'</span>
                            </div>                            
                        </div>
                        <div class="channel-actions">
                            <a href="https://socialblade.com/instagram/user/'.$canal['username'].'" 
                               class="action-btn sb-btn" 
                               target="_blank"
                               rel="noopener">SocialBlade</a>
                            <a href="https://www.instagram.com/'.$canal['username'].'" 
                               class="action-btn yt-btn" 
                               target="_blank"
                               rel="noopener">Instagram</a>
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
    
    <script src="scripts.js"></script>    </body>
    </html>';
}

engagement_display_ranking();
?>
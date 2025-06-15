'use strict';

// Función para detectar si el usuario está en un dispositivo móvil
function esDispositivoMovil() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Función para seleccionar una imagen aleatoria de una carpeta
function obtenerImagenAleatoria(carpeta, totalImagenes) {
    const indiceAleatorio = Math.floor(Math.random() * totalImagenes) + 1;
    return `${carpeta}/${indiceAleatorio}.png`;
}

// Función única para mostrar banners
function mostrarBanner(contenedorId, config) {
    const esMovil = esDispositivoMovil();
    const carpeta = esMovil ? 
        (config.mobile && config.mobile.carpeta) || config.desktop.carpeta : 
        config.desktop.carpeta;
    const totalImagenes = esMovil ? 
        (config.mobile && config.mobile.total) || config.desktop.total : 
        config.desktop.total;

    const imagenAleatoria = obtenerImagenAleatoria(carpeta, totalImagenes);
    const banner = document.getElementById(contenedorId);
    
    if (banner) {
        banner.innerHTML = `<img src="${imagenAleatoria}" alt="Publicidad" style="max-width: 100%; height: auto;">`;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    let activeCard = null;
    
    // Configuración de banners
    const bannersConfig = [
        {
            id: "banner-header",
            desktop: {
                carpeta: "/top/assets/img/Ads-Desktop/Cabecera-Horizontales-320x50",
                total: 3
            },
            mobile: {
                carpeta: "/top/assets/img/Ads-Moviles/Cabecera-Horizontales-320x50",
                total: 3
            }
        },
        {
            id: "banner-footer",
            desktop: {
                carpeta: "/top/assets/img/Ads-Desktop/Footer-Cuadrado-300x250",
                total: 6
            },
            mobile: {
                carpeta: "/top/assets/img/Ads-Moviles/Footer-Cuadrado-300x250",
                total: 6
            }
        },
        {
            id: "latera-1",
            desktop: {
                carpeta: "/top/assets/img/Ads-Desktop/Laterales-300x480",
                total: 3
            }
        },
        {
            id: "latera-2",
            desktop: {
                carpeta: "/top/assets/img/Ads-Desktop/Laterales-300x480",
                total: 3
            }
        }
    ];

    // Inicializar banners
    function inicializarBanners() {
        bannersConfig.forEach(config => {
            const bannerParams = {
                desktop: config.desktop,
                mobile: config.mobile || config.desktop
            };

            // Cargar banner inicial
            mostrarBanner(config.id, bannerParams);

            // Actualizar cada 30 segundos
            setInterval(() => mostrarBanner(config.id, bannerParams), 30000);
        });

        // Banners en tarjetas
        document.querySelectorAll(".card").forEach((card, index) => {
            const bannerId = `banner-card-${index}`;
            const bannerContainer = card.querySelector(".banner");
            
            if (bannerContainer) {
                bannerContainer.id = bannerId;
                const cardBannerConfig = {
                    desktop: {
                        carpeta: "/top/assets/img/Ads-Desktop/Cards-Horizontales-320x100",
                        total: 3
                    },
                    mobile: {
                        carpeta: "/top/assets/img/Ads-Moviles/Cards-Horizontales-320x100",
                        total: 3
                    }
                };
                
                mostrarBanner(bannerId, cardBannerConfig);
                setInterval(() => mostrarBanner(bannerId, cardBannerConfig), 30000);
            }
        });
    }

    // Menú hamburguesa
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".main-nav");
    
    if (hamburger && navMenu) {
        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });
    }

    // Gestión de cards
    document.querySelectorAll(".card-details .toggle-details").forEach(button => {
        button.addEventListener("click", event => {
            event.preventDefault();
            const card = button.closest(".card");
            const extraInfo = card ? card.querySelector(".extra-info") : null;

            if (activeCard && activeCard !== card) {
                activeCard.querySelector(".extra-info").classList.add("hidden");
                activeCard.querySelector(".toggle-details").style.display = "inline-block";
            }

            if (extraInfo) {
                extraInfo.classList.toggle("hidden");
                button.style.display = extraInfo.classList.contains("hidden") ? "inline-block" : "none";
                activeCard = extraInfo.classList.contains("hidden") ? null : card;
            }
        });
    });

    // Botón "Mostrar más"
    const verMasBtn = document.getElementById("ver-mas-btn");
    if (verMasBtn) {
        verMasBtn.addEventListener("click", () => {
            document.querySelectorAll(".card.hidden").forEach(card => card.classList.remove("hidden"));
            verMasBtn.style.display = "none";
        });
    }

    // Dropdowns
    document.querySelectorAll('.dropdown > .nav-link, .subdropdown > .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            this.parentElement.classList.toggle("active");
        });
    });

    // Inicializar banners después de la carga completa
    window.addEventListener('load', inicializarBanners);
});
    function hola(s){
        window.open('https://www.youtube.com/@'+s, '_blank');
    }
    document.addEventListener("DOMContentLoaded", () => {
        let activeCard = null;
    
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".main-nav"); // Cambiado de ".nav-menu" a ".main-nav"

	hamburger.addEventListener("click", () => {
	     // Se activa la animaci車n del bot車n y el men迆 m車vil
	     hamburger.classList.toggle("active"); 
	     navMenu.classList.toggle("active"); // Se utiliza la clase "active" que ya est芍 definida en los CSS
    });
    
        // Expand/Collapse card details
        document.querySelectorAll(".card-details .toggle-details").forEach(button => {
            button.addEventListener("click", event => {
                event.preventDefault();
    
                if (activeCard) { 
                    activeCard.querySelector(".extra-info").classList.add("hidden");
                    activeCard.querySelector(".card-details .toggle-details").style.display = "inline-block";
                }
    
                const card = button.closest(".card");
                const extraInfo = card.querySelector(".extra-info");
    
                if (activeCard !== card) { 
                    extraInfo.classList.remove("hidden");
                    button.style.display = "none";
                    activeCard = card;
                    
                } else {
                    activeCard = null;
                }
            });
        });
    
        // Collapse active card on scroll
        window.addEventListener("scroll", () => {
            if (activeCard) { 
                 // activeCard.querySelector(".extra-info").classList.add("hidden");
                 // activeCard.querySelector(".more-info .btn-more-info").style.display = "inline-block";
                 // activeCard = null;
            }
        });
    
        // Show hidden cards
        const verMasBtn = document.getElementById("ver-mas-btn");
        if (verMasBtn) {
            verMasBtn.addEventListener("click", () => {
                document.querySelectorAll(".card.hidden").forEach(card => { 
                    card.classList.remove("hidden");
                });
                verMasBtn.style.display = "none";
            });
        }
        
    
    });



    // Funci車n para detectar si el usuario est芍 en un dispositivo m車vil
    function esDispositivoMovil() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    // Funci車n para seleccionar una imagen aleatoria de una carpeta
    function obtenerImagenAleatoria(carpeta, totalImagenes) {
        const indiceAleatorio = Math.floor(Math.random() * totalImagenes) + 1;
        return `${carpeta}/${indiceAleatorio}.png`; // Asume que las im芍genes est芍n nombradas como 1.png, 2.png, etc.
    }
    
    // Funci車n para mostrar el banner en un contenedor espec赤fico
    function mostrarBanner(contenedorId, carpetaDesktop, carpetaMovil, totalImagenesDesktop, totalImagenesMovil) {
        // Determinar la carpeta y total de im芍genes seg迆n el dispositivo
        const esMovil = esDispositivoMovil();
        const carpetaSeleccionada = esMovil ? carpetaMovil : carpetaDesktop;
        const totalImagenes = esMovil ? totalImagenesMovil : totalImagenesDesktop;

        // Obtener la URL de una imagen aleatoria
        const imagenAleatoria = obtenerImagenAleatoria(carpetaSeleccionada, totalImagenes);

        // Insertar la imagen en el contenedor del banner
        const banner = document.getElementById(contenedorId);
        if (banner) {
            banner.innerHTML = `<img src="${imagenAleatoria}" alt="Publicidad" style="max-width: 100%; height: auto;">`;
        }
    }
    
    // Ejecutar la funci車n para todos los banners configurados
    window.onload = function () {
        // Configuraci車n de los banners
        const banners = [
            {
                contenedorId: "banner-header",
                carpetaDesktop: "img/Ads-Desktop/Cabecera-Horizontales-320x50",
                carpetaMovil: "img/Ads-Moviles/Cabecera-Horizontales-320x50",
                totalImagenesDesktop: 3,
                totalImagenesMovil: 3
            },
            {
                contenedorId: "banner-footer",
                carpetaDesktop: "img/Ads-Desktop/Footer-Cuadrado-300x250",
                carpetaMovil: "img/Ads-Moviles/Footer-Cuadrado-300x250",
                totalImagenesDesktop: 6, // Cambia seg迆n el total de im芍genes disponibles
                totalImagenesMovil: 6  // Cambia seg迆n el total de im芍genes disponibles
            },
            {
                contenedorId: "latera-1",
                carpetaDesktop: "img/Ads-Desktop/Laterales-300x480",
                totalImagenesDesktop: 3, // Cambia seg迆n el total de im芍genes disponibles
            },
            {
                contenedorId: "latera-2",
                carpetaDesktop: "img/Ads-Desktop/Laterales-300x480",
                totalImagenesDesktop: 3, // Cambia seg迆n el total de im芍genes disponibles
            }              
            
        ];
        
        // Mostrar banners al cargar y configurarlos para actualizarse cada 30 segundos
        banners.forEach(bannerConfig => {
            const { contenedorId, carpetaDesktop, carpetaMovil, totalImagenesDesktop, totalImagenesMovil } = bannerConfig;
            mostrarBanner(contenedorId, carpetaDesktop, carpetaMovil, totalImagenesDesktop, totalImagenesMovil);
            setInterval(() => {
                mostrarBanner(contenedorId, carpetaDesktop, carpetaMovil, totalImagenesDesktop, totalImagenesMovil);
            }, 10000);
        });
    };
    

// Funci車n para generar banners con IDs 迆nicos
function mostrarBanner(contenedorId, carpetaDesktop, carpetaMovil, totalImagenesDesktop, totalImagenesMovil) {
    const esMovil = esDispositivoMovil();
    const carpetaSeleccionada = esMovil ? carpetaMovil : carpetaDesktop;
    const totalImagenes = esMovil ? totalImagenesMovil : totalImagenesDesktop;

    const imagenAleatoria = obtenerImagenAleatoria(carpetaSeleccionada, totalImagenes);

    const banner = document.getElementById(contenedorId);
    if (banner) {
        banner.innerHTML = `<img src="${imagenAleatoria}" alt="Publicidad" style="max-width: 100%; height: auto;">`;
    }
}

// Ajustar la configuraci車n de banners para tarjetas
document.querySelectorAll(".card").forEach((card, index) => {
    const bannerId = `banner-card-${index}`; // ID 迆nico para cada tarjeta
    const bannerContainer = card.querySelector(".banner");

    if (bannerContainer) {
        bannerContainer.setAttribute("id", bannerId); // Asignar el ID 迆nico al contenedor

        // Configurar y mostrar el banner
        mostrarBanner(
            bannerId,
            "img/Ads-Desktop/Cards-Horizontales-320x100",
            "img/Ads-Moviles/Cards-Horizontales-320x100",
            3, // Total de im芍genes disponibles para Desktop
            3  // Total de im芍genes disponibles para M車vil
        );

        // Actualizar el banner din芍micamente cada 30 segundos
        setInterval(() => {
            mostrarBanner(
                bannerId,
                "img/Ads-Desktop/Cards-Horizontales-320x100",
                "img/Ads-Moviles/Cards-Horizontales-320x100",
                3,
                3
            );
        }, 10000); // Cambia cada 30 segundos
    }
});

// Seleccionamos todos los enlaces directos de un dropdown
document.querySelectorAll('.dropdown > .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault(); // Prevenimos la acci車n por defecto (si fuera un enlace real)
        this.parentElement.classList.toggle('active'); // Alternamos la clase 'active'
    });
});

document.querySelectorAll('.subdropdown > .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault(); // Prevenimos la acci車n por defecto (si fuera un enlace real)
        this.parentElement.classList.toggle('active'); // Alternamos la clase 'active'
    });
});



window.addEventListener('resize', initLaterales);
initLaterales();
// script.js
// Obtener el botón
const scrollToTopButton = document.getElementById('scrollToTop');

// Mostrar el botón cuando el usuario hace scroll hacia abajo
window.onscroll = function () {
    if (document.body.scrollTop > 0 || document.documentElement.scrollTop > 0) {
        scrollToTopButton.style.display = "block"; // Muestra el botón
    } else {
        scrollToTopButton.style.display = "none"; // Oculta el botón
    }
};

// Al hacer clic en el botón, desplazar hacia arriba
scrollToTopButton.onclick = function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
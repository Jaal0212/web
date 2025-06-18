
window.addEventListener('beforeunload', function (e) {
    // Crear una solicitud AJAX para cerrar sesión
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "../../PHP/logout.php", true); // Llamar al archivo logout.php
    xhr.send();
});
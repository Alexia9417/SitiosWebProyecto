document.querySelector('.enviar-mensaje').addEventListener('click', function () {
    const mensaje = document.querySelector('.comunicacion textarea').value;
    enviarMensajeGerencia(mensaje, false);
});

document.querySelector('.enviar-queja-Gerencia').addEventListener('click', function() {
    // Usa la variable nombreMesero en lugar de intentar ejecutar PHP en JavaScript
    enviarMensajeGerencia(`Mesero ${nombreMesero} está llamando a gerencia`, true);
});

document.querySelector('.enviar-queja').addEventListener('click', function () {
    const queja = document.querySelector('.quejas textarea').value;
    enviarQueja(queja);
});

document.querySelectorAll('.enviar-queja-Gerencia')[1].addEventListener('click', function () {
    const queja = document.querySelector('.quejas textarea').value;
    enviarMensajeGerencia(`Queja Escalada a gerencia: ${queja}`, false)
});

// Obtener el modal
var modal = document.getElementById("myModal");
var modalMessage = document.getElementById("modal-message");
var span = document.getElementsByClassName("close")[0];

function showModal(message) {
    modalMessage.textContent = message;
    modal.style.display = "block";
}

span.onclick = function () {
    modal.style.display = "none";
}

window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

function enviarMensajeGerencia(mensaje, esLlamada) {
    fetch('guardar_mensaje_gerencia.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idMesero=${idMesero}&mensaje=${encodeURIComponent(mensaje)}&esLlamada=${esLlamada}`

    })
        .then(response => response.text())
        .then(data => {
            showModal(data);
            // Limpiar el textarea después de enviar el mensaje
            document.querySelector('.comunicacion textarea').value = '';
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function enviarQueja(queja) {
    fetch('guardar_queja.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idUsuario=${idMesero}&comentario=${encodeURIComponent(queja)}`
    })
        .then(response => response.text())
        .then(data => {
            showModal(data);
            // Limpiar el textarea después de enviar el mensaje
            document.querySelector('.quejas textarea').value = '';
        })
        .catch(error => {
            console.error('Error:', error);
        });
}


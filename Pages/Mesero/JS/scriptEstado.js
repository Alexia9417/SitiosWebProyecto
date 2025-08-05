// Variables globales para el estado del mesero
let estres = 0;
let energia = 100;
let eficiencia = 100;

// Función para actualizar la interfaz de usuario
function actualizarInterfaz() {
    const estresSpan = document.querySelector('.panel.estres span');
    const energiaSpan = document.querySelector('.panel.energia span');
    const eficienciaSpan = document.querySelector('.panel.eficiencia span');

    const estresBarra = document.querySelector('.panel.estres .barra div');
    const energiaBarra = document.querySelector('.panel.energia .barra div');
    const eficienciaBarra = document.querySelector('.panel.eficiencia .barra div');

    if (estresSpan && energiaSpan && eficienciaSpan && estresBarra && energiaBarra && eficienciaBarra) {
        estresSpan.textContent = `${estres.toFixed(1)}%`;
        energiaSpan.textContent = `${energia}%`;
        eficienciaSpan.textContent = `${eficiencia.toFixed(1)}%`;

        // Cambiar ancho dinámicamente (sobrescribe PHP inline)
        estresBarra.style.width = `${estres}%`;
        energiaBarra.style.width = `${energia}%`;
        eficienciaBarra.style.width = `${eficiencia}%`;
    }

    // Habilitar/deshabilitar botón de refuerzos
    const botonRefuerzos = document.querySelector('.btn-emergencia:nth-child(2)');
    if (botonRefuerzos) {
        botonRefuerzos.disabled = estres < 80;
        if (estres >= 80) {
            botonRefuerzos.style.opacity = "1";
            botonRefuerzos.style.cursor = "pointer";
        } else {
            botonRefuerzos.style.opacity = "0.5";
            botonRefuerzos.style.cursor = "not-allowed";
        }
    }
}

// Función para disminuir energía, eficiencia y aumentar estrés
function disminuirEnergiaYEficiencia() {
    energia = Math.max(0, energia - 1);
    eficiencia = Math.max(0, eficiencia - 0.5);
    estres = Math.min(100, estres + 1); // ✅ Aumenta 1 cada 5 seg
    console.log("Estrés:", estres); // Para depuración
    actualizarInterfaz();
    guardarEstado();
}

// Funciones para botones
function descanso() {
    energia = Math.min(100, energia + 3);
    actualizarInterfaz();
    guardarEstado();
}

function tomarAgua() {
    energia = Math.min(100, energia + 2);
    eficiencia = Math.min(100, eficiencia + 2);
    actualizarInterfaz();
    guardarEstado();
}

function comerAlgo() {
    energia = Math.min(100, energia + 3);
    eficiencia = Math.min(100, eficiencia + 3);
    actualizarInterfaz();
    guardarEstado();
}

function reportarEnfermedad() {
    estres = Math.min(100, estres + 5);
    energia = Math.max(0, energia - 3);
    eficiencia = Math.max(0, eficiencia - 3);
    actualizarInterfaz();
    guardarEstado();
}

function interactuarConNotificaciones() {
    estres = Math.min(100, estres + 2);
    actualizarInterfaz();
    guardarEstado();
}

function solicitarRefuerzos() {
    if (estres >= 80) {
        alert("Solicitud de refuerzos enviada. ¡Alguien te ayudará pronto!");
        estres = Math.max(0, estres - 20);
        actualizarInterfaz();
        guardarEstado();
    } else {
        alert("No puedes solicitar refuerzos hasta que el estrés alcance 80%.");
    }
}

// Guardar y cargar estado
function guardarEstado() {
    localStorage.setItem('estres', estres.toFixed(1));
    localStorage.setItem('energia', energia);
    localStorage.setItem('eficiencia', eficiencia.toFixed(1));
}

function cargarEstado() {
    const savedEstres = localStorage.getItem('estres');
    const savedEnergia = localStorage.getItem('energia');
    const savedEficiencia = localStorage.getItem('eficiencia');

    if (savedEstres !== null) estres = parseFloat(savedEstres);
    if (savedEnergia !== null) energia = parseFloat(savedEnergia);
    if (savedEficiencia !== null) eficiencia = parseFloat(savedEficiencia);
}

function restablecerEstado() {
    estres = 0;
    energia = 100;
    eficiencia = 100;
    guardarEstado();
}

// ✅ Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarEstado();
    actualizarInterfaz();

    // Vincular botones
    document.querySelector('.btn-cuidado:nth-child(1)')?.addEventListener('click', descanso);
    document.querySelector('.btn-cuidado:nth-child(2)')?.addEventListener('click', tomarAgua);
    document.querySelector('.btn-cuidado:nth-child(3)')?.addEventListener('click', comerAlgo);
    document.querySelector('.btn-emergencia:nth-child(1)')?.addEventListener('click', reportarEnfermedad);
    document.querySelector('.btn-emergencia:nth-child(2)')?.addEventListener('click', solicitarRefuerzos);
    document.querySelector('.notificaciones')?.addEventListener('click', interactuarConNotificaciones);

    // ✅ Intervalo para aumentar estrés y bajar energía/eficiencia
    setInterval(disminuirEnergiaYEficiencia, 5000); // Cada 5 segundos
});

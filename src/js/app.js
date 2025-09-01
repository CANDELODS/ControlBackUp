document.addEventListener('DOMContentLoaded', function () {
    iniciarApp();
});

function iniciarApp() {
    prepararCheckboxes();
    manejarEnvio();
}
//Esta función asegura que todos los checkboxes tengan un value correcto (1 o 0)
//en el momento en que se envíe el formulario,
//Esto nos ahorra el usar inputs hidden, lo cual nos ayuda a
//evitar datos duplicados o inconsistentes.
function prepararCheckboxes() {
    const checkboxes = document.querySelectorAll('.checkboxes');

    checkboxes.forEach((checkbox) => {
        //Al cargar la página, se establece el valor inicial del checkbox
        //(por defecto es 'on' si está marcado, y si no, no envía nada).
        // Aquí lo cambiamos para que siempre sea '1' si está marcado, '0' si no lo está.
        checkbox.value = checkbox.checked ? '1' : '0';

        //Cambiamos el valor dinámicamente al marcar/desmarcar
        //Por medio del evento change
        checkbox.addEventListener('change', function () {
            this.value = this.checked ? '1' : '0';
        });
    });
}

//Se asegura que justo antes de enviar el formulario (con el botón submit),
//los checkboxes vuelvan a ajustar sus valores a 1 o 0, por si algún cambio no se capturó.
function manejarEnvio() {
    const formulario = document.querySelector('.formulario-copia');
    const checkboxes = formulario.querySelectorAll('.checkboxes');

    formulario.addEventListener('submit', function () {
        checkboxes.forEach(cb => {
            // Fuerza que todos se marquen para ser enviados
            cb.checked = true;
        });
    });
}
//Validar dias domingos en el input type date
const inputFecha = document.querySelector('.formularioFiltro__date');
inputFecha.addEventListener('change', function () {
    //Convertimos el value de inputFecha en un objeto Date para
    //Poder usar el método getDay()
    const fechaSeleccionada = new Date(inputFecha.value);
    const dia = fechaSeleccionada.getDay(); // 6 = Domingo, 0 = Lunes
    if (dia === 6) {
        alert('No puedes seleccionar un domingo.');
        inputFecha.value = ''; // Limpiar el input
    }
});

// MENÚ MOBILE
const abrirMenu = document.querySelector('#abrirMenu');
const cerrarMenu = document.querySelector('#cerrarMenu');
const navegacion = document.querySelector('#navegacion');
const links = document.querySelectorAll('.navegacion__enlace');

//Función para abrir el menú
abrirMenu.addEventListener('click', function () {
    //Agrego la clase .mostrar a la nav
    navegacion.classList.add('mostrar');
});

//Función para cerrar el menú
cerrarMenu.addEventListener('click', function () {
    //Elimino la clase .mostrar a la nav
    navegacion.classList.remove('mostrar');
});

//Función para cerrar el menú al hacer click en un enlace
links.forEach(link => {
    //Agrego el evento click a cada enlace
    link.addEventListener('click', function () {
        //Agrego la clase .esconder a la navegacion
        navegacion.classList.add('esconder');
        //Elimino la clase .mostrar a la navegacion
        navegacion.classList.remove('mostrar');
        //Elimino la clase .esconder a la navegacion después de 300ms
        setTimeout(() => {
            navegacion.classList.remove('esconder');
        }, 300);
    });
});
//FIN MENÚ MOBILE

//Mensaje de confirmación
function confirmDelete(message) {
    //Si no hay mensaje se mostrará el texto por defecto
    return confirm(message || "¿Estás seguro de eliminar este elemento?");
}

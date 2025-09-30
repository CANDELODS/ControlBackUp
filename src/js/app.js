document.addEventListener('DOMContentLoaded', function () {
    iniciarApp();

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

});

function iniciarApp() {
    prepararCheckboxes();
    // manejarEnvio();
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

// ----------------------
// Código para manejar validaciones y valores de checkboxes
// ----------------------
const formulario = document.querySelector('.formulario-copia');

if (formulario) {
    // Validación antes de enviar (lista de equipos sin copia)
    formulario.addEventListener('submit', function (e) {
        const filas = formulario.querySelectorAll('tbody tr');
        let equiposSinCopia = [];

        filas.forEach(fila => {
            const nombreEquipo = fila.querySelector('td[data-label="Nombre"]').innerText.trim();
            const local = fila.querySelector('.copia-local:not([disabled])');
            const nube = fila.querySelector('.copia-nube:not([disabled])');

            if (local || nube) {
                const localMarcado = local && local.checked;
                const nubeMarcado = nube && nube.checked;

                if (!localMarcado && !nubeMarcado) {
                    equiposSinCopia.push(nombreEquipo);
                }
            }
        });

        if (equiposSinCopia.length > 0) {
            const mensaje = "⚠️ Los siguientes equipos no tienen marcada ni Local ni Nube:\n\n- "
                + equiposSinCopia.join("\n- ") +
                "\n\n¿Deseas continuar de todas formas?";

            if (!confirm(mensaje)) {
                e.preventDefault(); // ❌ Cancela el envío
            }
        }
    });

    // Mantener la lógica de 1/0 opcional (no necesario ahora porque usamos hidden inputs),
    // pero lo dejamos por compatibilidad: forzamos que el checkbox tenga value "1" cuando esté marcado.
    const checkboxes = formulario.querySelectorAll('.checkboxes');
    checkboxes.forEach(cb => {
        cb.value = cb.checked ? "1" : "1"; // dejamos '1' por defecto; el hidden controla el 0 cuando no se envía el checkbox
        cb.addEventListener('change', function () {
            // No es esencial: el checkbox solo se envía cuando está marcado y tendrá value="1".
            // Si quieres que el checkbox también envíe 0 al desmarcar (NO recomendable con hidden+same-key),
            // habría que reestructurar totalmente la forma de enviar. Con hidden+same-key es correcto.
            this.value = this.checked ? "1" : "1";
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

//Mensaje de confirmación
function confirmDelete(message) {
    //Si no hay mensaje se mostrará el texto por defecto
    return confirm(message || "¿Estás seguro de eliminar este elemento?");
}

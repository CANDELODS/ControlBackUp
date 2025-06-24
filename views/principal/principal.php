<div class="fijar">
    <?php require_once __DIR__ . '/../templates/navegacion.php'; ?>

    <main class="background">
        <div class="principal">
            <h1 class="principal__h1">Control BackUp</h1>
            <h4 class="principal__h4">Lleva el control de tus copias de seguridad (Backup)</h4>
            <p class="principal__p">Esta herramienta te permitirá mejorar la calidad de tus indicadores ya sean
                diarios o mensuales, además te permitirá gestionar los equipos de la compañía
                y ser mas eficiente y eficaz a la hora de revisar las copias de seguridad. <br>
                Si quieres diligenciar las copias incrementales da click en el link de arriba
                a la derecha que dice Incremental, lo mismo para las copias Completas,
                para gestionar los equipos (Crear, eliminar, editar) se debe hacer click en
                el link Equipos, por último, si quieres ver consejos técnicos y cosas a tener
                encuenta en la revisión de copias de seguridad puedes dirigirte al link
                llamado Consejos, si estas en movil primero has click en el icono del menú
                para poder ver los enlaces descritos anteriormente</p>
            <h6 class="principal__h6">¿Te gustaría empezar viendo los consejos?</h6>
            <a href="/consejos" class="principal__enlace">Consejos</a>
        </div>

        <div class="descargarI">
            <h6 class="descargarI__h6">
                O ¿Quieres descargar el informe mensual completo?
            </h6>
            <picture>
                <source srcset="build/img/flechas.avif" type="image/avif">
                <source srcset="build/img/flechas.webp" type="image/webp">
                <img src="build/img/flechas.png" alt="Logo Ladrillera"
                    class="descargarI__img" loading="lazy" width="200" heigth="300">
            </picture>
        </div>
    </main>

    <?php require_once __DIR__ . '/../templates/footer.php'; ?>
</div>
<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">En esta sección aprenderás todo lo necesario para administrar<br> las copias de seguridad como un profesional, empezando por la instalación de<br> la herramienta Cobian Backup y terminando por la solución de diferentes conflictos.</p>
        </div>
    </div>
    <div class="consejos">
        <div class="consejos__grid">
            <div class="box">
                <picture>
                    <source srcset="/build/img/cobian-backup.avif" type="image/avif">
                    <source srcset="/build/img/cobian-backup.webp" type="image/webp"> <img src="/build/img/cobian-backup.png" alt="Imagen Cobian Backup" class="box__img" loading="lazy" width="200" heigth="300">
                </picture>
                <div class="box__contenido">
                    <h2 class="box__h2">Instalación de Cobian Backup</h2>
                    <p class="box__p">Aprende a instalar y configurar Cobian Backup para realizar copias de seguridad de manera eficiente y segura.</p>
                    <a href="/consejos/instalacion-cobian" class="box__btn">Ver más</a>
                </div>
            </div>
            <div class="box">
                <picture>
                    <source srcset="/build/img/vsc.avif" type="image/avif">
                    <source srcset="/build/img/vsc.webp" type="image/webp"> <img src="/build/img/vsc.png" alt="Imagen Volumen Shadow Copy" class="box__img" loading="lazy" width="200" heigth="300">
                </picture>
                <div class="box__contenido">
                    <h2 class="box__h2">Error "no se ha podido iniciar el solicitador de Volume Shadow Copy"</h2>
                    <p class="box__p">Aprenderemos como solventar un error muy común al momento de realizar una instalación, referente al Volumen Shadow Copy (VSC).
                        Bastará con instalar previamente la versión de .Net Framework 3.5 con lo cual podremos sortear este inconveniente tan común, que lo que permite es poder respaldar archivos que se encuentren abiertos o en uso.</p>
                        <a href="/consejos/error-VSC" class="box__btn">Ver más</a>
                </div>
            </div>
            <div class="box">
                <picture>
                    <source srcset="/build/img/tarea.avif" type="image/avif">
                    <source srcset="/build/img/tarea.webp" type="image/webp"> <img src="/build/img/tarea.png" alt="Imagen De ¿Cómo crear una tarea en Cobian Backup?" class="box__img" loading="lazy" width="200" heigth="300">
                </picture>
                <div class="box__contenido">
                    <h2 class="box__h2">¿Cómo crear una tarea en Cobian Backup?</h2>
                    <p class="box__p">Aprende a automatizar tus copias de seguridad creando tareas programadas en Cobian Backup, asegurando la protección continua de tus datos.</p> 
                    <a href="/consejos/crear-tarea" class="box__btn">Ver más</a>
                </div>
            </div>
        </div>
    </div>
</main>
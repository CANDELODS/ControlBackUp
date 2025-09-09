<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprende a automatizar tus copias de seguridad creando tareas programadas en Cobian Backup,<br>asegurando la protección continua de tus datos.</p>
        </div>
        <div class="main__crear">
            <a href="/consejos" class="main__btn">Regresar</a>
        </div>
    </div>

    <div class="cards">

        <div class="card">
            <div class="card__grid">
                <div class="card__img-Div">
                    <picture>
                        <source srcset="/build/img/tarea2.avif" type="image/avif">
                        <source srcset="/build/img/tarea2.webp" type="image/webp">
                        <img src="/build/img/tarea2.png" alt="Imagen Que Refleja Una Tarea" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>

                <div class="card__contenido">
                    <h3 class="card__h3">Creación de tareas</h3>
                    <p class="card__p">Las tareas nos permitirán automatizar la ejecución de nuestras copias de seguridad, aprender a crearlas es de suma importancia para guardar exactamente la información que necesitamos.</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">Se ha realizado un instructivo con el paso a paso para crear
                        <b>tareas</b> en Cobian Backup, en este se tocarán todos los apartados para conocer perfectamente todo lo que podemos hacer y configurar,
                        para verlo, presiona el enlace que dice <span class="card__span">"Ver Instructivo"</span>.
                    </p>
                    <a href="#" class="card__a">Ver Instructivo</a>
                </div>
            </div> <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
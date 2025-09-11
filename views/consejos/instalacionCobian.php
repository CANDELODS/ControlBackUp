<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprende a instalar y configurar Cobian Backup para realizar<br>copias de seguridad de manera eficiente y segura.</p>
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
                        <source srcset="/build/img/checkList.avif" type="image/avif">
                        <source srcset="/build/img/checkList.webp" type="image/webp">
                        <img src="/build/img/checkList.png" alt="Imagen De Un CheckList" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>

                <div class="card__contenido">
                    <h3 class="card__h3">Instalación Cobian Backup</h3>
                    <p class="card__p">Empecemos con la instalación del programa Cobian Backup, el cual nos permitirá automatizar la ejecución de las copias de seguridad por medio de <b>tareas</b> las cuales se repetiran a la hora y los días que deseamos.</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">En este caso en particular, la empresa cuenta con un instructivo para
                        la instalación de este programa, para verlo, presiona el enlace que dice <span class="card__span">"Ver Instructivo"</span>.
                    </p>
                    <a href="/instructivos/Instalación Cobian Backup.pdf" target="_blank" rel="noopener noreferrer" class="card__a">Ver Instructivo</a>
                </div>
            </div> <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
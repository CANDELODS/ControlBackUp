<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprende a recuperar la información de un día en específico.</p>
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
                        <source srcset="/build/img/recuperar2.avif" type="image/avif">
                        <source srcset="/build/img/recuperar2.webp" type="image/webp">
                        <img src="/build/img/recuperar2.png" alt="Imagen Que Refleja La Recuperación De La Información De Un Sistema Infortmático" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>

                <div class="card__contenido">
                    <h3 class="card__h3">Recuperación de información</h3>
                    <p class="card__p">Las copias de seguridad son un respaldo de la información, por lo cual, aprendamos como usar ese respaldo en caso de que sea necesario.</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">La empresa cuenta con un instructivo para
                        la <b>restauración</b> de la información, el cual explica paso a paso como recuperarla,
                        para verlo, presiona el enlace que dice <span class="card__span">"Ver Instructivo"</span>.
                    </p>
                    <a href="/instructivos/Restauración de archivos de un día en específico.pdf" target="_blank" rel="noopener noreferrer" class="card__a">Ver Instructivo</a>
                </div>
            </div> <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
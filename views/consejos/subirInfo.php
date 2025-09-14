<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprenderemos como subir correctamente las copias locales de los equipos críticos a la nube,<br>
            todo esto por medio de la aplicación Backup Manager.</p>
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
                        <source srcset="/build/img/nube2.avif" type="image/avif">
                        <source srcset="/build/img/nube2.webp" type="image/webp">
                        <img src="/build/img/nube2.png" alt="Imagen Que Refleja La Subida De Información A La Nube" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>

                <div class="card__contenido">
                    <h3 class="card__h3">Subir Copias A La Nube Con Backup Manager</h3>
                    <p class="card__p">La información de los equipos críticos se sube día a día a la nube, aprende cómo hacerlo correctamente, ya que si no se hace con precaución las consecuencias pondrán en tela de juicio el departamento de sistemas e implicarán incrementos monetarios con el proveedor.</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">Se ha realizado un instructivo con el paso a paso para <b>subir</b> las copias correctamente,
                        para verlo, presiona el enlace que dice <span class="card__span">"Ver Instructivo"</span>.
                    </p>
                    <a href="/instructivos/Subir copias al backup manager desde el servidor 9.13docx.pdf" target="_blank" rel="noopener noreferrer" class="card__a">Ver Instructivo</a>
                </div>
            </div> <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
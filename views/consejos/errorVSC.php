<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprenderemos como solventar un error muy común al momento de realizar una instalación, referente al Volumen Shadow Copy (VSC).
                Bastará con instalar previamente la versión de .Net Framework 3.5 con lo cual podremos sortear este inconveniente tan común, que lo que permite es poder respaldar archivos que se encuentren abiertos o en uso.</p>
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
                        <source srcset="/build/img/solucion.avif" type="image/avif">
                        <source srcset="/build/img/solucion.webp" type="image/webp">
                        <img src="/build/img/solucion.png" alt="Imagen Que Refleja La Solución De Un Conflicto" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>

                <div class="card__contenido">
                    <h3 class="card__h3">Solución Error VSC</h3>
                    <p class="card__p">El volumen Shadow Copy, o Servicio de Instantáneas de Volumen (VSS), es una tecnología de Windows que permite crear "instantáneas" o copias de seguridad de archivos y volúmenes del sistema, incluso cuando estos están en uso. Utiliza un método de "copia al escribir" para guardar los cambios en la unidad.</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">Se ha realizado un instructivo con el paso a paso para solucionar,
                        el error <b>"No se ha podido iniciar el solicitador de Volume Shadow Copy"</b>, el cual es bastante recurrente cuando usamos
                        Cobian Backup, para verlo, presiona el enlace que dice <span class="card__span">"Ver Instructivo"</span>.
                    </p>
                    <a href="#" class="card__a">Ver Instructivo</a>
                </div>
            </div> <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
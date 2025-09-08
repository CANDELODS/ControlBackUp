<?php require_once __DIR__ . '/../templates/navegacion.php'; ?> <main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprende a instalar y configurar Cobian Backup para realizar<br>copias de seguridad de manera eficiente y segura.</p>
            <h2 class="main__h2">Paso 1</h2>
        </div>
    </div>

    <div class="cards">

        <div class="card">
            <div class="card__grid">
                <div class="card__img-Div">
                    <picture>
                        <source srcset="/build/img/cobian-backup.avif" type="image/avif">
                        <source srcset="/build/img/cobian-backup.webp" type="image/webp">
                        <img src="/build/img/cobian-backup.png" alt="Imagen Cobian Backup" class="card__img" loading="lazy" width="200" heigth="300">
                    </picture>
                </div>
    
                <div class="card__contenido">
                    <h3 class="card__h3">Instalación Cobian Backup</h3>
                    <p class="card__p">Empecemos con la instalación del programa Cobian Backup</p>
                    <h4 class="card__h4">Guía</h4>
                    <p class="card__p--guia">En este caso en particular, la empresa cuenta con un instructivo para
                        la instalación de este programa, para verlo, presiona el enlace que dice "Ver Instructivo"
                    </p>
                    <a href="#" class="card__a">Ver Instructivo</a>
                </div>
            </div>  <!--Fin .card__grid-->
        </div> <!--Fin .card-->
    </div>
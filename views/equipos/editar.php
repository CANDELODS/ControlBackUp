<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<div class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Cambia o actualiza los atributos del equipo</p>
        </div>
        <div class="main__crear">
            <a href="/equipos" class="main__btn">Regresar</a>
        </div>
    </div>

    <main>
<!-- Es el mismo formulario que usamos para crear, pero su action es diferente -->
<!--Quitamos el action del formulario para que no nos borre el parámetro id en la URL-->
        <form method="post" class="eformulario">
        <!-- Importamos las alertas -->
        <?php
        require_once __DIR__ . '/../templates/alertas.php';
        ?>

        <?php include_once __DIR__ . '/formulario.php'; ?>

        <input type="submit" class="eformulario__submit eformulario__submit--c" value="Actualizar Equipo">
        </form>
    </main>

</div>

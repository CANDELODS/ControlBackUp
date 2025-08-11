<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprovecha este apartado para editar y eliminar<br>
                las copias, usa el buscador para encontrar<br>
                más fácil la copia deseada, y si quieres<br>
                quitar ese filtro haz click en el botón<br>
                "Borrar Filtro" para poder ver todas las copias
                Nuevamente.</p>
        </div>
        <div class="main__crear">
            <a href="/principal" class="main__btn main__btn--c">Regresar</a>
        </div>
    </div>
<!--Este formulario nos permitirá filtrar las copias, mandamos las fechas por la URL y la obtenemos en el CopiasController.php-->
    <div class="main__filtros">
        <form class="formularioFiltro" method="GET" action="/copias">
            <label class="formularioFiltro__label" for="fecha">Selecciona una fecha para filtrar:</label>
            <input class="formularioFiltro__date" id="fecha" type="date" name="fecha" value="<?php echo $_GET['fecha'] ?? ''; ?>">
            <input class="formularioFiltro__submit" type="submit" value="Buscar">
        </form>
<!--Este enlace nos servirá para volver a ver todos los resultados cuando el usuario haya filtrado los datos-->
        <a href="/copias?page=1" class="main__btn main__btn--bf">Borrar Filtro</a>

    </div>

    <div class="tabla--scrollX">
        <!-- Verifficamos si hay copias para mostrar -->
        <?php if (!empty($copias)) { ?>
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Fecha</th>
                        <th scope="col" class="table__th">Tipo De Copia</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <!-- Iteramos copia por copia -->
                    <?php foreach ($copias as $copia) { ?>
                        <tr class="table__tr">
                            <td data-label="Fecha" class="table__td">
                                 <!-- Mostramos la fecha de cada copia -->
                                <?php echo $copia->fecha; ?>
                            </td>
                            <td data-label="Tipo De Copia" class="table__td">
                                 <!-- Mostramos el tiopoDeCopia de cada copia -->
                                <?php echo $copia->tipoDeCopia; ?>
                            </td>
                            <td class="table__td--acciones">
                                 <!-- Enlace para redirigir al usuario a la vista de editar-copia, además se manda el id de la copiaEncabezao a editar
                                 por medio de la URL -->
                                <a class="table__accion table__accion--editar" href="editar-copia?id=<?php echo $copia->id; ?>">Editar</a>
                                 <!-- Botón para eliminar un equipo, además tiene un input de tipo hidden el cual manda el id del equipo
                                 al servidor y así poder eliminar el equipo -->
                                <form method="post" action="eliminar-copia" class="table__form">
                                    <input type="hidden" name="id" value="<?php echo $copia->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?> <!--Fin foreach($copias as $copia)-->
                </tbody>
            </table>
        <?php } else { ?>
            <!--En CopiasController mandamos a la vista la variable $sin_resultados, la cual es true si no hay
            Resultados y false si se encontraron resultados al usar el filtro  -->
            <?php if ($sin_resultados) { ?>
                 <p class="alerta alerta__error">No se encontraron copias con la fecha ingresada.</p>
                <?php } else { ?>
            <p class="text-center">No Hay Copias Para Listar</p>
            <?php } ?>
        <?php } ?><!--Fin if(!empty($copias))-->
    </div>
    <!-- Mostramos los enlaces de la paginación -->
    <?php
    echo $paginacion;
    ?>
</main>
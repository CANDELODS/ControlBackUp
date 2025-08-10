<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Aprovecha este apartado para agrega nuevos equipos,<br>
                eliminar o editar los que ya tienes y tener un mejor control de ellos</p>
        </div>
        <div class="main__crear">
            <a href="/crear-equipo" class="main__btn main__btn--c">Crear Equipo</a>
        </div>
    </div>
    <div class="tabla--scrollX">
        <!-- Verificamos si hay equipos para mostrar -->
        <?php if (!empty($equipos)) { ?>
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Nombre</th>
                        <th scope="col" class="table__th">Área</th>
                        <th scope="col" class="table__th">Local</th>
                        <th scope="col" class="table__th">Nube</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
                <tbody class="table__tbody">
                    <!-- Iteramos equipo por equipo -->
                    <?php foreach ($equipos as $equipo) { ?>
                        <tr class="table__tr">
                            <td data-label="Nombre" class="table__td">
                                <!-- Mostramos el nombre de cada equipo -->
                                <?php echo $equipo->nombreEquipo; ?>
                            </td>
                            <td data-label="Área" class="table__td">
                                <!-- Mostramos el área de cada equipo -->
                                <?php echo $equipo->idAreas->nombreArea; ?>
                            </td>
                            <td data-label="Local" class="table__td">
                                <!-- Mostramos si el equipo hace copia local -->
                                <?php echo $equipo->local; ?>
                            </td>
                            <td data-label="Nube" class="table__td">
                                <!-- Mostramos si el equipo hace copia en nube -->
                                <?php echo $equipo->nube; ?>
                            </td>
                            <td class="table__td--acciones">
                                <!-- Enlace para redirigir al usuario a la vista de editar-equipo, además se manda el id del equipo a editar
                                 por medio de la URL -->
                                <a class="table__accion table__accion--editar" href="editar-equipo?id=<?php echo $equipo->id;?>">Editar</a>
                                <!-- Botón para eliminar un equipo, además tiene un input de tipo hidden el cual manda el id del equipo
                                 al servidor y así poder eliminar el equipo -->
                                <form method="post" action="eliminar-equipo" class="table__form">
                                    <input type="hidden" name="id" value="<?php echo $equipo->id; ?>">
                                    <button class="table__accion table__accion--eliminar" type="submit">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?> <!--Fin foreach($equipos as $equipo)-->
                </tbody>
            </table>
        <?php } else { ?> 
            <p class="text-center">No Hay Equipos Para Listar</p>
        <?php } ?> <!--Fin if(!empty($equipos))-->
    </div>
<!-- Mostramos los enlaces de la paginación -->
<?php
    echo $paginacion;
?>
</main>
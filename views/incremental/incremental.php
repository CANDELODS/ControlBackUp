<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Una copia de seguridad incremental solo copia los datos<br>
                modificados desde la última copia de seguridad.</p>
        </div>
        <div class="main__editar">
            <p class="main__editarc">Edita una copia anterior:</p>
            <a href="/copias" class="main__btn main__btn--e">Cargar Copia</a>
        </div>
    </div>
    <form action="" class="formulario-copia" method="post">
        <div class="tabla--scroll">
            <!-- Verifficamos si hay equipos para mostrar -->
            <?php if (!empty($equipos)) { ?>
                <table class="table">
                    <thead class="table__thead">
                        <tr class="table__trhead">
                            <th scope="col" class="table__th">Nombre</th>
                            <th scope="col" class="table__th">Área</th>
                            <th scope="col" class="table__th">Local</th>
                            <th scope="col" class="table__th">Nube</th>
                            <th scope="col" class="table__th">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="table__tbody">
                        <!-- Iteramos equipo por equipo -->
                        <?php foreach ($equipos as $equipo) { ?>
                            <tr class="table__tr">
                                <td data-label="Nombre" class="table__td">
                                    <!-- Mostramos el nombre de cada equipo y creamos un arreglo con sus ids y lo llenamos con sus values -->
                                    <input type="hidden" name="idEquipos[]" value="<?php echo $equipo->id; ?>">
                                    <?php echo $equipo->nombreEquipo; ?>
                                </td>
                                <td data-label="Área" class="table__td">
                                     <!-- Mostramos el área de cada equipo -->
                                    <?php echo $equipo->idAreas->nombreArea; ?>
                                </td>
                                <td data-label="Local" class="table__td">
                                    <!-- Si el equipo NO hace copia local entonces deshabilitamos el checkbox, creamos el
                                     arreglo copiaLocal y lo llenamos con su value que en este caso el 0 representa un NO,
                                     De lo contrario, habilitamos el checkbox y cuando el usuario le haga click cambiamos
                                     su value de 0 (No) a 1 (Si) -->
                                    <?php if ($equipo->local === '0') : ?>
                                        <input type="hidden" name="copiaLocal[]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-local"
                                            name="copiaLocal[]"
                                            value="0">
                                    <?php endif; ?>
                                </td>
                                <td data-label="Nube" class="table__td">
                                    <!-- Si el equipo NO hace copia en nube entonces deshabilitamos el checkbox, creamos el
                                     arreglo copiaNube y lo llenamos con su value que en este caso el 0 representa un NO,
                                     De lo contrario, habilitamos el checkbox y cuando el usuario le haga click cambiamos
                                     su value de 0 (No) a 1 (Si) -->
                                    <?php if ($equipo->nube === '0') : ?>
                                        <input type="hidden" name="copiaNube[]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-nube"
                                            name="copiaNube[]"
                                            value="0">
                                    <?php endif; ?>
                                </td>
                                <td class="table__td">
                                    <!-- Creamos el arreglo de observaciones el cual se llenará con el texto digitado por el usuario -->
                                    <textarea name="observaciones[]" class="formulario-copia__textarea"
                                        placeholder="Escribe Aquí Las Observaciones"></textarea>
                                </td>
                            </tr>
                        <?php } ?> <!--Fin foreach($equipos as $equipo)-->
                    </tbody>
                </table>
            <?php } else { ?> 
                <p class="text-center">No Hay Equipos Para Listar</p>
            <?php } ?> <!--Fin if(!empty($equipos))-->
        </div>
        <div class="main__fi">
            <!-- Mostramos la fecha del día actual y seteamos la hora del servidor -->
             <?php date_default_timezone_set('America/Bogota'); ?> 
            <p class="main__f"><?php echo date('Y-m-d');?></p>
            <input type="submit" class="formulario-copia__btn" value="Guardar">
        </div>
    </form>
<!-- No necesitamos paginación en este archivo ya que estamos usando un scroll en la tabla -->
    <div class="main__descargarI">
        <h6 class="main__h6">Descargar Informes</h6>
        <a href="/incremental-descargar-diaria" class="main__btn main__btn--d">Diaria</a>
        <a href="/incremental-descargar-mensual" class="main__btn main__btn--m">Mensual</a>
    </div>
</main>
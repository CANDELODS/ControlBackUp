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

    <form action="" class="formulario-copia" method="post" onsubmit="return confirmDelete('¿Estás seguro de que deseas guardar?')">
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
                        <?php
                        // Usamos un índice de fila que sólo incrementa cuando imprimimos una fila visible
                        $rowIndex = 0;
                        foreach ($equipos as $equipo) {
                            // Verificamos si el equipo está habilitado (1 = SI / 0 = NO) para así mostrarlo en una fila o no
                            if ($equipo->habilitado !== '1') {
                                continue;
                            }
                        ?>
                            <tr class="table__tr">
                                <td data-label="Nombre" class="table__td">
                                    <!-- Envío del id con clave numérica para mantener el mapeo -->
                                    <input type="hidden" name="idEquipos[<?php echo $rowIndex; ?>]" value="<?php echo (int)$equipo->id; ?>">
                                    <?php echo htmlspecialchars($equipo->nombreEquipo); ?>
                                </td>

                                <td data-label="Área" class="table__td">
                                    <!-- Mostramos el área de cada equipo -->
                                    <?php echo htmlspecialchars($equipo->idAreas->nombreArea); ?>
                                </td>

                                <td data-label="Local" class="table__td">
                                    <!-- Si el equipo NO hace copia local entonces deshabilitamos el checkbox,
                                         creamos el arreglo copiaLocal (con clave $rowIndex) y lo llenamos con value 0.
                                         De lo contrario, habilitamos el checkbox (value 1) y también dejamos el hidden 0
                                         para que cuando NO esté marcado llegue 0. -->
                                    <?php if ($equipo->local === '0') : ?>
                                        <input type="hidden" name="copiaLocal[<?php echo $rowIndex; ?>]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <input type="hidden" name="copiaLocal[<?php echo $rowIndex; ?>]" value="0">
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-local"
                                            name="copiaLocal[<?php echo $rowIndex; ?>]"
                                            value="1">
                                    <?php endif; ?>
                                </td>

                                <td data-label="Nube" class="table__td">
                                    <!-- Si el equipo NO hace copia en nube entonces deshabilitamos el checkbox,
                                         creamos el arreglo copiaNube (con clave $rowIndex) y lo llenamos con value 0.
                                         De lo contrario, habilitamos el checkbox (value 1) y también dejamos el hidden 0. -->
                                    <?php if ($equipo->nube === '0') : ?>
                                        <input type="hidden" name="copiaNube[<?php echo $rowIndex; ?>]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <input type="hidden" name="copiaNube[<?php echo $rowIndex; ?>]" value="0">
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-nube"
                                            name="copiaNube[<?php echo $rowIndex; ?>]"
                                            value="1">
                                    <?php endif; ?>
                                </td>

                                <td class="table__td">
                                    <!-- Observaciones con la misma clave $rowIndex -->
                                    <textarea name="observaciones[<?php echo $rowIndex; ?>]" class="formulario-copia__textarea"
                                        placeholder="Escribe Aquí Las Observaciones"></textarea>
                                </td>
                            </tr>
                        <?php
                            $rowIndex++;
                        } // foreach equipos
                        ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-center">No Hay Equipos Para Listar</p>
            <?php } ?> <!--Fin if(!empty($equipos))-->
        </div>
        <div class="main__fi">
            <!-- Mostramos la fecha del día actual y seteamos la hora del servidor -->
            <?php date_default_timezone_set('America/Bogota'); ?>
            <p class="main__f"><?php echo date('Y-m-d'); ?></p>
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

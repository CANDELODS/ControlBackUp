<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Una copia de seguridad incremental solo copia los datos<br>
                modificados desde la última copia de seguridad.</p>
        </div>
        <div class="main__crear">
            <a href="/copias" class="main__btn main__btn--c">Regresar</a>
        </div>
    </div>
    <form action="" class="formulario-copia" method="post" onsubmit="return confirmDelete('¿Estás seguro de que deseas guardar?')">
        <div class="tabla--scroll">
            <!-- Verifficamos si hay equipos para mostrar -->
            <?php if (!empty($equipos)) { ?>
                <table class="table">
                    <thead class="table__thead">
                        <tr>
                            <th scope="col" class="table__th">Nombre</th>
                            <th scope="col" class="table__th">Área</th>
                            <th scope="col" class="table__th">Local</th>
                            <th scope="col" class="table__th">Nube</th>
                            <th scope="col" class="table__th">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="table__tbody">
                        <!-- Iteramos equipo por equipo, con índice para asegurar claves en los arrays -->
                        <?php foreach ($equipos as $index => $equipo) { ?>
                            <tr class="table__tr">
                                <td data-label="Nombre" class="table__td">
                                    <!-- Envío del id con clave numérica para mantener el mapeo -->
                                    <input type="hidden" name="idEquipos[<?php echo $index; ?>]" value="<?php echo $equipo->id; ?>">
                                    <?php echo $equipo->nombreEquipo; ?>
                                </td>

                                <td data-label="Área" class="table__td">
                                    <!-- Mostramos el área de cada equipo -->
                                    <?php echo $equipo->idAreas->nombreArea; ?>
                                </td>

                                <td data-label="Local" class="table__td">
                                    <!-- Si el equipo NO hace copia local entonces deshabilitamos el checkbox,
                     creamos el arreglo copiaLocal (con clave $index) y lo llenamos con value 0 -->
                                    <?php if ($equipo->local === '0') : ?>
                                        <input type="hidden" name="copiaLocal[<?php echo $index; ?>]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <!-- Obtenemos el valor guardado (si existe) para este equipo en la copia que estamos editando -->
                                        <?php
                                        $local = '0';
                                        foreach ($copiasDetalle as $detalle) {
                                            if (isset($detalle->idEquipos) && $detalle->idEquipos == $equipo->id) {
                                                $local = $detalle->copiaLocal ?? '0';
                                                break;
                                            }
                                        }
                                        ?>
                                        <!-- Hidden primero con la misma clave; si checkbox está marcado, su valor (1) prevalecerá -->
                                        <input type="hidden" name="copiaLocal[<?php echo $index; ?>]" value="0">
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-local"
                                            name="copiaLocal[<?php echo $index; ?>]"
                                            value="1"
                                            <?php if ($local === '1') echo 'checked'; ?>>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Nube" class="table__td">
                                    <!-- Si el equipo NO hace copia en nube entonces deshabilitamos el checkbox,
                     creamos el arreglo copiaNube (con clave $index) y lo llenamos con value 0 -->
                                    <?php if ($equipo->nube === '0') : ?>
                                        <input type="hidden" name="copiaNube[<?php echo $index; ?>]" value="0">
                                        <input type="checkbox" class="formulario-copia__input--check checkboxes" disabled>
                                    <?php else : ?>
                                        <?php
                                        $nube = '0';
                                        foreach ($copiasDetalle as $detalle) {
                                            if (isset($detalle->idEquipos) && $detalle->idEquipos == $equipo->id) {
                                                $nube = $detalle->copiaNube ?? '0';
                                                break;
                                            }
                                        }
                                        ?>
                                        <input type="hidden" name="copiaNube[<?php echo $index; ?>]" value="0">
                                        <input type="checkbox"
                                            class="formulario-copia__input--check checkboxes copia-nube"
                                            name="copiaNube[<?php echo $index; ?>]"
                                            value="1"
                                            <?php if ($nube === '1') echo 'checked'; ?>>
                                    <?php endif; ?>
                                </td>

                                <td class="table__td">
                                    <?php
                                    $observacion = '';
                                    foreach ($copiasDetalle as $detalle) {
                                        if (isset($detalle->idEquipos) && $detalle->idEquipos == $equipo->id) {
                                            $observacion = $detalle->observaciones ?? '';
                                            break;
                                        }
                                    }
                                    ?>
                                    <!-- Observaciones con la misma clave $index -->
                                    <textarea name="observaciones[<?php echo $index; ?>]" class="formulario-copia__textarea"
                                        placeholder="Escribe Aquí Las Observaciones"><?php echo htmlspecialchars($observacion); ?></textarea>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            <?php } else { ?>
                <p class="text-center">No Hay Equipos Para Listar</p>
            <?php } ?> <!--Fin if(!empty($equipos))-->
        </div>
        <div class="main__fi">
            <!-- Mostramos la fecha del día actual -->
            <?php date_default_timezone_set('America/Bogota'); ?>
            <p class="main__f"><?php echo date('Y-m-d'); ?></p>
            <input type="submit" class="formulario-copia__btn" value="Guardar">
        </div>
    </form>
    <!-- No necesitamos paginación en este archivo ya que estamos usando un scroll en la tabla -->
</main>
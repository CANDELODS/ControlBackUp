<?php require_once __DIR__ . '/../templates/navegacion.php'; ?>
<main class="main">
    <div class="main__contenedor">
        <div class="main__intro">
            <h1 class="main__h1"><?php echo $titulo; ?></h1>
            <p class="main__p">Es una copia completa de todos los datos en un sistema<br>
                informático.</p>
        </div>
        <div class="main__editar">
            <p class="main__editarc">Edita una copia anterior:</p>
            <a href="/copias" class="main__btn main__btn--e">Cargar Copia</a>
        </div>
    </div>

    <form action="" class="formulario-copia" method="post" onsubmit="return confirmDelete('¿Estás seguro de que deseas guardar?')">
        <div class="tabla--scroll">
            <!-- Verificamos si hay equipos para mostrar -->
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
                        $rowIndex = 0;
                        foreach ($equipos as $equipo) {
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
                                    <?php echo htmlspecialchars($equipo->idAreas->nombreArea); ?>
                                </td>

                                <td data-label="Local" class="table__td">
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
                                    <textarea name="observaciones[<?php echo $rowIndex; ?>]" class="formulario-copia__textarea"
                                        placeholder="Escribe Aquí Las Observaciones"></textarea>
                                </td>
                            </tr>
                        <?php
                            $rowIndex++;
                        } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-center">No Hay Equipos Para Listar</p>
            <?php } ?>
        </div>

        <div class="main__fi">
            <?php date_default_timezone_set('America/Bogota'); ?>
            <p class="main__f"><?php echo date('Y-m-d'); ?></p>
            <input type="submit" class="formulario-copia__btn" value="Guardar">
        </div>
    </form>

    <div class="main__descargarI">
        <h6 class="main__h6">Descargar Informes</h6>
        <a href="/completa-descargar-diaria" class="main__btn main__btn--d">Diaria</a>
        <a href="/completa-descargar-mensual" class="main__btn main__btn--m">Mensual</a>
    </div>
</main>

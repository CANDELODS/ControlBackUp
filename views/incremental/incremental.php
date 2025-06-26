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
            <a href="#" class="main__btn main__btn--e">Cargar Copia</a>
        </div>
    </div>
    <form action="" class="formulario-copia" method="post">
        <div class="tabla--scroll">
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
                        <?php foreach ($equipos as $equipo) { ?>
                            <tr class="table__tr">
                                <td data-label="Nombre" class="table__td">
                                    <input type="hidden" name="idEquipos[]" value="<?php echo $equipo->id; ?>">
                                    <?php echo $equipo->nombreEquipo; ?>
                                </td>
                                <td data-label="Área" class="table__td">
                                    <?php echo $equipo->idAreas->nombreArea; ?>
                                </td>
                                <td data-label="Local" class="table__td">
                                    <!-- Si el equipo NO hace copia local -->
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
                                    <!-- Si el equipo NO hace copia en nube -->
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
                                    <textarea name="observaciones[]" class="formulario-copia__textarea"
                                        placeholder="Escribe Aquí Las Observaciones"></textarea>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-center">No Hay Equipos Para Listar</p>
            <?php } ?>
        </div>
        <div class="main__fi">
            <p class="main__f"><?php echo date('Y-m-d');?></p>
            <input type="submit" class="formulario-copia__btn" value="Guardar">
        </div>
    </form>

</main>
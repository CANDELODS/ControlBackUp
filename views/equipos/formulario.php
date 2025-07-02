<div class="eformulario__campo">
    <label class="eformulario__label" for="nombreEquipo">Nombre del equipo</label>
    <input type="text"
        id="nombreEquipo"
        name="nombreEquipo"
        class="eformulario__input"
        placeholder="Nombre del equipo"
        value="<?php echo $equipos->nombreEquipo ?? ''; ?>">
        <!-- Lo que hay en el value nos servirá para rellenar el campo en caso de que se envíe el formulario con algún error -->
</div>

<div class="eformulario__campo">
    <label class="eformulario__label" for="idAreas">Área del equipo</label>
    <select
        id="idAreas"
        name="idAreas"
        class="eformulario__select">
        <!-- Obtenemos el id del área (Edición) y la mostramos, si no hay id (Creación) mostramos el mensaje Seleccione El Área -->
        <option selected value="<?php echo s($equipos->idAreas->id ?? ''); ?>">
            <?php echo $equipos->idAreas->nombreArea ?? '-- Seleccione El Área --'; ?>
        </option>
        <!-- Iteramos cada área -->
        <?php foreach ($areas as $area) { ?>
            <!-- Si el id del área en el equipo que estamos iterando es el mismo que el del la tabla de área
            entonces agregamos el atributo selected al option (Edición), mandamos el valor de ese id (Value) y lo mostramos en la vista -->
            <option <?php echo ($equipos->idAreas === $area->id) ? 'selected' : '' ?>
                value="<?php echo $area->id; ?>">
                <?php echo $area->nombreArea; ?>
            </option>
        <?php } ?>

    </select>
</div>

<div class="eformulario__campo">
    <label class="eformulario__label" for="local">Copia Local</label>
    <input type="hidden" name="local"
        value="0"
        id="local">
    <input type="checkbox"
        id="local"
        class="eformulario__input eformulario__input--check"
        name="local"
        value="1"
        <?php if ($equipos->local === '1') { ?>
        checked>
<?php } else { ?>
    >
<?php } ?>
<!-- Si el equipo en su atributo 'local' es = 1 (Si) entonces agregamos el atributo checked 
 al checkbox, de lo contrario no lo ponemos (Edición)-->
</div>

<div class="eformulario__campo">
    <label class="eformulario__label" for="nube">Copia Nube</label>
    <input type="hidden" name="nube"
        value="0"
        id="nube">
    <input type="checkbox"
        id="nube"
        class="eformulario__input eformulario__input--check"
        name="nube"
        value="1"
        <?php if ($equipos->nube === '1') { ?>
        checked>
<?php } else { ?>
    >
<?php } ?>
<!-- Si el equipo en su atributo 'nube' es = 1 (Si) entonces agregamos el atributo checked 
 al checkbox, de lo contrario no lo ponemos (Edición)-->
</div>
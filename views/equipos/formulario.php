<div class="eformulario__campo">
    <label class="eformulario__label" for="nombreEquipo">Nombre del equipo</label>
    <input type="text"
        id="nombreEquipo"
        name="nombreEquipo"
        class="eformulario__input"
        placeholder="Nombre del equipo"
        value="<?php echo $equipos->nombreEquipo ?? ''; ?>">

</div>

<div class="eformulario__campo">
    <label class="eformulario__label" for="idAreas">Área del equipo</label>
    <select
        id="idAreas"
        name="idAreas"
        class="eformulario__select">
        <option selected value="<?php echo s($equipos->idAreas->id ?? ''); ?>">
            <?php echo $equipos->idAreas->nombreArea ?? '-- Seleccione El Área --'; ?>
        </option>
        <?php foreach ($areas as $area) { ?>
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
</div>
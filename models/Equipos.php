<?php

namespace Model;

class Equipos extends ActiveRecord
{
    protected static $tabla = 'equipos';
    protected static $columnasDB = ['id', 'nombreEquipo', 'idAreas', 'local', 'nube'];

    public $id;
    public $nombreEquipo;
    public $idAreas;
    public $local;
    public $nube;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombreEquipo = $args['nombreEquipo'] ?? '';
        $this->idAreas = $args['idAreas'] ?? null;
        $this->local = $args['local'] ?? null;
        $this->nube = $args['nube'] ?? null;
    }

    public function validar() {
    if(!$this->nombreEquipo) {
        self::$alertas['error'][] = 'El Nombre Es Obligatorio';
    }
    if(!$this->idAreas) {
        self::$alertas['error'][] = 'El Área Es Obligatoria';
    }
    if(!$this->local && !$this->nube) {
        self::$alertas['error'][] = 'Debe Seleccionar Al Menos Un Tipo De Copia De Seguridad';
    }
    return self::$alertas;
}
}

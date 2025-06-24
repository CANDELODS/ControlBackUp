<?php

namespace Model;

class CopiasEncabezado extends ActiveRecord
{
    protected static $tabla = 'copiasEncabezado';
    protected static $columnasDB = ['id', 'fecha', 'tipoDeCopia'];

    public $id;
    public $fecha;
    public $tipoDeCopia;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->tipoDeCopia = $args['tipoDeCopia'] ?? '';
    }
}

<?php

namespace Model;

class CopiasDetalle extends ActiveRecord
{
    protected static $tabla = 'copiasDetalle';
    protected static $columnasDB = ['id', 'idCopiasEncabezado', 'idEquipos', 'copiaLocal', 'copiaNube', 'observaciones'];

    public $id;
    public $idCopiasEncabezado;
    public $idEquipos;
    public $copiaLocal;
    public $copiaNube;
    public $observaciones;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->idCopiasEncabezado = $args['idCopiasEncabezado'] ?? '';
        $this->idEquipos = $args['idEquipos'] ?? '';
        $this->copiaLocal = $args['copiaLocal'] ?? '';
        $this->copiaNube = $args['copiaNube'] ?? '';
        $this->observaciones = $args['observaciones'] ?? '';
    }
}

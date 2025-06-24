<?php

namespace Model;

class Areas extends ActiveRecord
{
    protected static $tabla = 'areas';
    protected static $columnasDB = ['id', 'nombreArea'];

    public $id;
    public $nombreArea;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombreArea = $args['nombreArea'] ?? '';
    }
}

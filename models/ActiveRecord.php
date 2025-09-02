<?php

namespace Model;

class ActiveRecord
{

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Alertas y Mensajes
    protected static $alertas = [];

    // Definir la conexión a la BD - includes/database.php
    public static function setDB($database)
    {
        self::$db = $database;
    }

    // Setear un tipo de Alerta
    public static function setAlerta($tipo, $mensaje)
    {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Obtener las alertas
    public static function getAlertas()
    {
        return static::$alertas;
    }

    // Validación que se hereda en modelos
    public function validar()
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Consulta SQL para crear un objeto en Memoria (Active Record)
    public static function consultarSQL($query)
    {
        // Consultar la base de datos
        $resultado = self::$db->query($query);

        // Iterar los resultados
        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        // liberar la memoria
        $resultado->free();

        // retornar los resultados
        return $array;
    }

    // Crea el objeto en memoria que es igual al de la BD
    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    // Identificar y unir los atributos de la BD
    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    // Sanitizar los datos antes de guardarlos en la BD
    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }

    // Sincroniza BD con Objetos en memoria
    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    // Registros - CRUD
    public function guardar()
    {
        $resultado = '';
        if (!is_null($this->id)) {
            // actualizar
            $resultado = $this->actualizar();
        } else {
            // Creando un nuevo registro
            $resultado = $this->crear();
        }
        return $resultado;
    }

    // Obtener todos los Registros
    public static function all($orden = 'DESC')
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id ${orden}";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //Obtener registros dependiendo de una condición
    public static function allWhere($tablaB, $tipodeCopia, $idCopiaEncabezado, $orden = 'DESC')
    {
        $query = "SELECT copiasdetalle.id, copiasdetalle.idCopiasEncabezado, copiasdetalle.idEquipos,
        copiasdetalle.copiaLocal, copiasdetalle.copiaNube, copiasdetalle.observaciones 
        FROM " . static::$tabla . " INNER JOIN ${tablaB} ON copiasdetalle.idCopiasEncabezado = copiasencabezado.id 
        WHERE copiasencabezado.tipoDeCopia = ${tipodeCopia} AND copiasdetalle.idCopiasEncabezado = ${idCopiaEncabezado} 
        ORDER BY id ${orden}";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //Obtener todos los registro y ordenar por nombreArea
    public static function allA($orden = 'DESC')
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY nombreArea ${orden}";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busca un registro por su id
    public static function find($id)
    {
        // Evita SQL inválido si llega vacío o no numérico
        if ($id === null || $id === '' || !is_numeric($id)) {
            return null;
        }

        $id    = (int) $id;                     // cast seguro
        $tabla = static::$tabla ?? '';

        if ($tabla === '') {
            throw new \RuntimeException('Tabla no definida en el modelo ' . static::class);
        }

        $query = "SELECT * FROM {$tabla} WHERE id = {$id} LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    //Traer el total de registros
    public static function total()
    {
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        //No usamos consultarSQL ya que no queremos crear un objeto del modelo
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        //Con array_shift extraemos el primer registro del arreglo
        return array_shift($total);
    }

    //Traer el total de registros dependiendo de UNA SOLA condicion
    public static function totalWhere($columna, $valor)
    {
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ${columna} = ${valor}";
        //No usamos consultarSQL ya que no queremos crear un objeto del modelo
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        //Con array_shift extraemos el primer registro del arreglo
        return array_shift($total);
    }

    //Obtener el último ID insertado
    public static function ultimoId()
    {
        $query = "SELECT id FROM " . static::$tabla . " ORDER BY id DESC LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    // Obtener Registros con cierta cantidad
    public static function get($limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT ${limite} ORDER BY id DESC";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    //Paginar Registros
    public static function paginar($ordenar, $porPagina, $offset)
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY ${ordenar} ASC LIMIT ${porPagina} OFFSET ${offset} ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //Paginar Registros con una condición
    public static function paginarWhere($columna, $valor, $ordenar, $porPagina, $offset)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = ${valor} ORDER BY ${ordenar} ASC LIMIT ${porPagina} OFFSET ${offset} ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Busqueda Where con Columna 
    public static function where($columna, $valor)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    //Búsqueda Where con múltiples condiciones
    //Busca un solo registro en una tabla basado en dos (o más) condiciones.
    public static function where2($condiciones)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        $i = 0;

        foreach ($condiciones as $columna => $valor) {
            if ($i > 0) $query .= " AND ";
            // Escapar valores (aunque ya deberías estar usando una conexión segura)
            $valorEscapado = self::$db->real_escape_string($valor);
            $query .= "$columna = '$valorEscapado'";
            $i++;
        }

        $query .= " LIMIT 1";

        $resultado = self::consultarSQL($query);
        return array_shift($resultado); // Devuelve un solo objeto o null
    }

    // Busqueda Where con Columna sin array_shift 
    public static function whereSAS($columna, $valor)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} = '${valor}'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Búsqueda Where con LIKE (ideal para filtros por fecha o texto parcial)
    public static function whereLike($columna, $valor, $copia = 1)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} LIKE '%${valor}%' AND tipoDeCopia = ${copia}";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Total de registros filtrados
    public static function totalWhereLike($columna, $valor)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ${columna} LIKE '%${valor}%'";
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    //Total de registros filtrados dependiendo del tipo de copia
    public static function totalWhereLike3($columna, $valor, $copia)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT COUNT(*) FROM " . static::$tabla . " WHERE ${columna} LIKE '%${valor}%' AND tipoDeCopia =${copia}";
        $resultado = self::$db->query($query);
        $total = $resultado->fetch_array();
        return array_shift($total);
    }

    // Búsqueda LIKE con paginación
    public static function whereLikePaginado($columna, $valor, $ordenar, $porPagina, $offset)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} LIKE '%${valor}%' ORDER BY ${ordenar} ASC LIMIT ${porPagina} OFFSET ${offset}";
        return self::consultarSQL($query);
    }

    //MESES
    // Contar meses distintos de una columna (ej: fecha)
    //En pocas palabras cuenta cuántos meses distintos tienen registros dependiendo del tipo de copia.
    public static function totalMeses($columna, $whereCol = null, $whereVal = null)
    {
        $columna = self::$db->escape_string($columna);

        $query = "SELECT COUNT(DISTINCT DATE_FORMAT($columna, '%Y-%m')) AS total
              FROM " . static::$tabla;

        if ($whereCol && $whereVal !== null) {
            $whereCol = self::$db->escape_string($whereCol);
            $whereVal = self::$db->escape_string($whereVal);
            $query .= " WHERE $whereCol = '{$whereVal}'";
        }

        $res = self::$db->query($query);
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    //Obtener todos los copiaDetalle de un mes específico
    public static function allWhereMes($mes, $tipoDeCopia)
    {
        $query = "SELECT d.*
              FROM copiasDetalle d
              INNER JOIN copiasEncabezado e 
                      ON e.id = d.idCopiasEncabezado
              WHERE DATE_FORMAT(e.fecha, '%Y-%m') = '{$mes}'
              AND e.tipoDeCopia = {$tipoDeCopia}";
              
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Obtener meses paginados (agrupados por mes)
    public static function mesesPaginados($columna, $limite, $offset, $whereCol = null, $whereVal = null, $orden = 'ASC')
    {
        $columna = self::$db->escape_string($columna);
        $orden   = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';
        $limite  = (int) $limite;
        $offset  = (int) $offset;

        $query = "SELECT 
                MIN(id) AS id,
                DATE_FORMAT($columna, '%Y-%m') AS fecha,
                tipoDeCopia
              FROM " . static::$tabla;

        if ($whereCol && $whereVal !== null) {
            $whereCol = self::$db->escape_string($whereCol);
            $whereVal = self::$db->escape_string($whereVal);
            $query .= " WHERE $whereCol = '{$whereVal}'";
        }

        $query .= " GROUP BY DATE_FORMAT($columna, '%Y-%m'), tipoDeCopia
                ORDER BY fecha {$orden}
                LIMIT {$limite} OFFSET {$offset}";

        return self::consultarSQL($query);
    }

    // Total por mes específico (ej: 2025-07)
    public static function totalPorMes($columna, $mes, $whereCol = null, $whereVal = null)
    {
        $columna = self::$db->escape_string($columna);
        $mes     = self::$db->escape_string($mes);

        $query = "SELECT COUNT(DISTINCT DATE_FORMAT($columna, '%Y-%m')) AS total
              FROM " . static::$tabla . "
              WHERE DATE_FORMAT($columna, '%Y-%m') = '{$mes}'";

        if ($whereCol && $whereVal !== null) {
            $whereCol = self::$db->escape_string($whereCol);
            $whereVal = self::$db->escape_string($whereVal);
            $query .= " AND $whereCol = '{$whereVal}'";
        }

        $res = self::$db->query($query);
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    // Obtener registros de un mes específico (agrupados)
    public static function porMesPaginado($columna, $mes, $limite, $offset, $whereCol = null, $whereVal = null, $orden = 'ASC')
    {
        $columna = self::$db->escape_string($columna);
        $mes     = self::$db->escape_string($mes);
        $orden   = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';
        $limite  = (int) $limite;
        $offset  = (int) $offset;

        $query = "SELECT 
                MIN(id) AS id,
                DATE_FORMAT($columna, '%Y-%m') AS fecha,
                tipoDeCopia
              FROM " . static::$tabla . "
              WHERE DATE_FORMAT($columna, '%Y-%m') = '{$mes}'";

        if ($whereCol && $whereVal !== null) {
            $whereCol = self::$db->escape_string($whereCol);
            $whereVal = self::$db->escape_string($whereVal);
            $query .= " AND $whereCol = '{$whereVal}'";
        }

        $query .= " GROUP BY DATE_FORMAT($columna, '%Y-%m'), tipoDeCopia
                ORDER BY fecha {$orden}
                LIMIT {$limite} OFFSET {$offset}";

        return self::consultarSQL($query);
    }
    //FIN MESES

    //COMPLETAS
    // Total por mes específico (ej: 2025-07)
    public static function totalPorMesC($columna, $mes)
    {
        $columna = self::$db->escape_string($columna);
        $mes     = self::$db->escape_string($mes);

        $query = "SELECT COUNT(DISTINCT DATE_FORMAT($columna, '%Y-%m')) AS total
              FROM " . static::$tabla . "
              WHERE DATE_FORMAT($columna, '%Y-%m') = '{$mes}'";

        $res = self::$db->query($query);
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    // Obtener registros de un mes específico (agrupados)
    public static function porMesPaginadoC($columna, $mes, $limite, $offset, $orden = 'ASC')
    {
        $columna = self::$db->escape_string($columna);
        $mes     = self::$db->escape_string($mes);
        $orden   = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';
        $limite  = (int) $limite;
        $offset  = (int) $offset;

        $query = "SELECT 
                MIN(id) AS id,
                DATE_FORMAT($columna, '%Y-%m') AS fecha
              FROM " . static::$tabla . "
              WHERE DATE_FORMAT($columna, '%Y-%m') = '{$mes}'";

        $query .= " GROUP BY DATE_FORMAT($columna, '%Y-%m')
                ORDER BY fecha {$orden}
                LIMIT {$limite} OFFSET {$offset}";

        return self::consultarSQL($query);
    }

    // Contar meses distintos de una columna (ej: fecha)
    //En pocas palabras cuenta cuántos meses distintos tienen registros dependiendo del tipo de copia.
    public static function totalMesesC($columna)
    {
        $columna = self::$db->escape_string($columna);

        $query = "SELECT COUNT(DISTINCT DATE_FORMAT($columna, '%Y-%m')) AS total
              FROM " . static::$tabla;

        $res = self::$db->query($query);
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    // Obtener meses paginados (agrupados por mes)
    public static function mesesPaginadosC($columna, $limite, $offset, $orden = 'ASC')
    {
        $columna = self::$db->escape_string($columna);
        $orden   = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';
        $limite  = (int) $limite;
        $offset  = (int) $offset;

        $query = "SELECT 
                MIN(id) AS id,
                DATE_FORMAT($columna, '%Y-%m') AS fecha
              FROM " . static::$tabla;

        $query .= " GROUP BY DATE_FORMAT($columna, '%Y-%m')
                ORDER BY fecha {$orden}
                LIMIT {$limite} OFFSET {$offset}";

        return self::consultarSQL($query);
    }

    //Obtener todos los copiaDetalle de un mes específico
    public static function allWhereMesC($mes)
    {
        $query = "SELECT d.*
              FROM copiasDetalle d
              INNER JOIN copiasEncabezado e 
                      ON e.id = d.idCopiasEncabezado
              WHERE DATE_FORMAT(e.fecha, '%Y-%m') = '{$mes}'";

        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    // Búsqueda Where con LIKE (ideal para filtros por fecha o texto parcial)
    public static function whereLikeC($columna, $valor)
    {
        $valor = self::$db->escape_string($valor);
        $query = "SELECT * FROM " . static::$tabla . " WHERE ${columna} LIKE '%${valor}%' ";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    //FIN COMPLETAS

    // crea un nuevo registro
    public function crear()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Insertar en la base de datos
        $query = " INSERT INTO " . static::$tabla . " ( ";
        $query .= join(', ', array_keys($atributos));
        $query .= " ) VALUES (' ";
        $query .= join("', '", array_values($atributos));
        $query .= " ') ";

        // debuguear($query); // Descomentar si no te funciona algo

        // Resultado de la consulta
        $resultado = self::$db->query($query);
        return [
            'resultado' =>  $resultado,
            'id' => self::$db->insert_id
        ];
    }

    // Actualizar el registro
    public function actualizar()
    {
        // Sanitizar los datos
        $atributos = $this->sanitizarAtributos();

        // Iterar para ir agregando cada campo de la BD
        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key}='{$value}'";
        }

        // Consulta SQL
        $query = "UPDATE " . static::$tabla . " SET ";
        $query .=  join(', ', $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1 ";

        // Actualizar BD
        $resultado = self::$db->query($query);
        return $resultado;
    }

    // Eliminar un Registro por su ID
    public function eliminar()
    {
        $query = "DELETE FROM "  . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        $resultado = self::$db->query($query);
        return $resultado;
    }
}

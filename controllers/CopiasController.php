<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Areas;
use Model\CopiasDetalle;
use Model\CopiasEncabezado;
use Model\Equipos;
use Model\Usuario;
use MVC\Router;

class CopiasController
{
    public static function copias(Router $router)
    {
        if (!isAuth()) {
            header('Location: /login');
        }
        //Obtenemos la fecha de la URL
        $fecha = $_GET['fecha'] ?? '';
        //Inicializamos la paginación obteniendola página actual
        $pagina_actual = $_GET['page'] ?? 1;
        //Veriricamos que sea un número y que sea positivo
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);
        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /copias?page=1');
        }
        //Cantidad de registros que queremos mostraren la vista
        $registros_por_pagina = 8;
        //Cambiará su valor si al filtrar por fecha no se encuentran resultados
        $sin_resultados = false;

        //Si se filtra por fecha (Input Type Date) entonce:
        if ($fecha) {
            //Contamos el total de registros filtrados (Where (tabla) like (fecha))
            $total_registros = CopiasEncabezado::totalWhereLike('fecha', $fecha);
            //Si no hay resultados entonces:
            if (!$total_registros) {
                //Cambiamos el valor de la variable para mostrar una alerta en la vista
                $sin_resultados = true;
                //Contamos el total de registros (Sin filtro)
                $total_registros = CopiasEncabezado::total();
                //Instanciamos la paginación con sus respectivos parámetros
                $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
                //Ordenamos y páginamos los registros
                $copias = CopiasEncabezado::paginar('fecha', $registros_por_pagina, $paginacion->offset());
            }
            //Se encontraron registros, por lo cual instanciamos la paginación con sus atributos
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            //Ordenamos y paginamos los datos filtrados
            $copias = CopiasEncabezado::whereLikePaginado('fecha', $fecha, 'fecha', $registros_por_pagina, $paginacion->offset());
        }
        //No se usó un filtro (No se utilizó el input type date)
        else {
            //Contamos todos los registros, instanciamos la paginación, ordenamos y paginamos los registros
            $total_registros = CopiasEncabezado::total();
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            $copias = CopiasEncabezado::paginar('fecha', $registros_por_pagina, $paginacion->offset());
        }

        // Validación extra: Ejmplo, totalPaginas = 20 y $pagina_Actual = 21
        //Si el totalPaginas es 20, el usuario no puede estar en la 21 ya que no existe
        if ($paginacion->totalPaginas() < $pagina_actual) {
            header('Location: /copias?page=1');
        }
        //Convertimos los 0 y 1 de la columna tipoDeCopia en strings para usarlos en la vista
        foreach ($copias as $copia) {
            //Si hay algo (Por ejemplo 1) será incremental, de lo contrario (0) será completa
            $copia->tipoDeCopia = $copia->tipoDeCopia ? 'Incremental' : 'Completa';
        }
        $copiasDetalle = new CopiasDetalle;
        $copiasDetalle = CopiasDetalle::all();
        //Renderizamos la vista y mandamos las variables
        $router->render('copias/copias', [
            'titulo' => 'Copias',
            'alertas' => Usuario::getAlertas(),
            'copias' => $copias,
            'copiasDetalle' => $copiasDetalle,
            'paginacion' => $paginacion->paginacion(),
            'sin_resultados' => $sin_resultados
        ]);
    }

public static function editar(Router $router)
{
    if (!isAuth()) {
        header('Location: /login');
        exit;
    }

    // Validar y obtener el ID de la copia a editar
    $id = $_GET['id'] ?? null;
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if (!$id) {
        header('Location: /copias');
        exit;
    }

    // Buscar la copiaEncabezado
    $copia = CopiasEncabezado::find($id);
    if (!$copia) {
        header('Location: /copias');
        exit;
    }

    // Obtener detalles asociados a esta copia
    $copiasDetalle = CopiasDetalle::where('idCopiasEncabezado', $id);

    // Obtener todos los equipos
    $equipos = Equipos::all('ASC');

    // Obtener todas las áreas
    $areas = Areas::all();

    // Enriquecer equipos con sus áreas (para mostrar nombre en la vista)
    foreach ($equipos as $equipo) {
        $equipo->idAreas = Areas::find($equipo->idAreas);
    }

    // Preparar alertas si se desea usar más adelante
    $alertas = [];

    // Renderizar la vista
    $router->render('copias/editar', [
        'titulo' => 'Editar Copia',
        'alertas' => $alertas,
        'equipos' => $equipos,
        'areas' => $areas,
        'copiasDetalle' => $copiasDetalle,
        'copia' => $copia
    ]);
}


    // public static function eliminar(Router $router)
    // {
    //     if (!isAuth()) {
    //         header('Location: /login');
    //     }
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $id = $_POST['id'];
    //         //Buscamos el objeto del equipo a eliminar por medio del id
    //         $equipos = Equipos::find($id);
    //         if (empty($equipos)) {
    //             header('Location: /equipos');
    //         }
    //         $resultado = $equipos->eliminar();
    //         if ($resultado) {
    //             header('Location: /equipos');
    //         }
    //     }
    // }
}

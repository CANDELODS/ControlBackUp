<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Areas;
use Model\Equipos;
use Model\Usuario;
use MVC\Router;

class EquiposController
{
    public static function equipos(Router $router)
    {
        //Verificamos si el usuario está autenticado
        if (!isAuth()) {
            header('Location: /login');
        }
        //PAGINAR
        //Obtenemos la página desde la URL y verificamos que sea un número y que no sea negativo
        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);
        //La función filter var devuelve un boolean, por lo cual
        //Si devuelve false no pasará la validación, igualmente si
        //El número es negativo
        if(!$pagina_actual || $pagina_actual < 1){
            header('Location: /equipos?page=1');
        }
        $registros_por_pagina = 8;
        $total_registros = Equipos::total();
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);

        //Instanciamos el usuario y obtenemos las alertas
        $alertas = Usuario::getAlertas();
        //Instanciamos el modelo de equipos
        $equipos = new Equipos;
        // Traemos todos los equipos
        $equipos = Equipos::paginar($registros_por_pagina, $paginacion->offset());
        if($paginacion->totalPaginas() < $pagina_actual){
            header('Location: /equipos?page=1');
        }
        //CRUCE DE DATOS PARA OBTENER EL NOMBRE DEL ÁREA
        //Este ForEach Tiene Como Fin Cruzar Información De La BD Sin Necesidad De Crar Un Join Desde
        //El Active Record, Este Itera Cada Evento Y Crea Una LLave La Cual Compara Con Las Que
        //Que Hay En El Modelo De Esa Llave Creada Por Medio De La Función Find De Active Record
        //Con Esto Ya Podemos Acceder A Las Propiedades De Cada Llave Y Mostrarlas, Ver (Views-equipos->equipos.php)
        foreach ($equipos as $equipo) {
            //Se Crea Una LLave De idAreas Dentro Del Objeto De Equipos Y La Buscamos Por Su Id(En La Tabla De Areas)
            $equipo->idAreas = Areas::find($equipo->idAreas);
            //Verificamos si hay algo en el atributo local y nube de nuestro objeto,
            //Si hay algo, lo convertimos a un string para mostrarlo en la vista
            $equipo->local = $equipo->local ? 'Sí' : 'No';
            $equipo->nube = $equipo->nube ? 'Sí' : 'No';
        }
        // Renderizamos la vista y enviamos las variables a la vista
        $router->render('equipos/equipos', [
            'titulo' => 'Equipos',
            'alertas' => $alertas,
            'equipos' => $equipos,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function crear(Router $router)
    {
        if (!isAuth()) {
            header('Location: /login');
        }
        $alertas = [];
        //Instanciamos las áreas
        $areas = new Areas;
        //Obtenemos todas las áreas
        //Esto nos permite tener un listado de áreas para seleccionar al crear un equipo
        $areas = Areas::allA('ASC');
        $equipos = new Equipos;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincronizamos los datos obtenido del formulario con los del modelo de equipos
            $equipos->sincronizar($_POST);
            // Validar los datos
            $alertas = $equipos->validar();
            //Si no hay alertas, guardar el equipo
            if (empty($alertas)) {
                $resultado = $equipos->guardar();
                if ($resultado) {
                    header('Location: /equipos');
                }
            }
        }

        // Renderizamos la vista y enviamos las variables a la vista
        $router->render('equipos/crear', [
            'titulo' => 'Crear Equipo',
            'alertas' => $alertas,
            'equipos' => $equipos,
            'areas' => $areas
        ]);
    }

    public static function editar(Router $router)
    {
        if (!isAuth()) {
            header('Location: /login');
        }
        $alertas = [];
        $areas = new Areas;
        $areas = Areas::allA('ASC');
        //Obtenemos el id del equipo a editar
        $id = $_GET['id'];
        //Validamos que el id sea un número
        $id = filter_var($id, FILTER_VALIDATE_INT);
        //Si el id no es un número, redirigimos a la lista de equipos
        if (!$id) {
            header('Location: /equipos');
        }
        //obtener el equipo a editar
        $equipos = Equipos::find($id);
        //Si no existe el equipo, redirigimos a la lista de equipos
        if (!$equipos) {
            header('Location: /equipos');
        }
        //Cruzar información de áreas
        //No usamos un foreach ya que solo estamos editando un equipo
        $equipos->idAreas = Areas::find($equipos->idAreas);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sincronizamos los datos obtenido del formulario con los del modelo de equipos
            $equipos->sincronizar($_POST);
            // Validar los datos
            $alertas = $equipos->validar();
            //Si no hay alertas, guardar el equipo
            if (empty($alertas)) {
                $resultado = $equipos->guardar();
                if ($resultado) {
                    header('Location: /equipos');
                }
            }
        }
        // Renderizamos la vista y enviamos las variables a la vista
        $router->render('equipos/editar', [
            'titulo' => 'Editar Equipo',
            'alertas' => $alertas,
            'equipos' => $equipos,
            'areas' => $areas
        ]);
    }

    public static function eliminar(Router $router)
    {
        if (!isAuth()) {
            header('Location: /login');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            //Buscamos el objeto del equipo a eliminar por medio del id
            $equipos = Equipos::find($id);
            if (empty($equipos)) {
                header('Location: /equipos');
            }
            $resultado = $equipos->eliminar();
            if ($resultado) {
                header('Location: /equipos');
            }
        }
    }
}

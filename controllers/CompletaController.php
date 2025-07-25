<?php

namespace Controllers;

use MVC\Router;
use Model\Areas;
use Model\CopiasDetalle;
use Model\CopiasEncabezado;
use Model\Equipos;
use Model\Usuario;

class CompletaController
{
    public static function completa(Router $router)
    {
        //Verificamos si el usuario está autenticado
        if (!isAuth()) {
            header('Location: /login');
        }
        //Instanciamos los modelos
        $equipos = new Equipos;
        $copiasEncabezado = new CopiasEncabezado;
        // Traemos todos los equipos
        $equipos = Equipos::all('ASC');
        //CRUCE DE DATOS PARA OBTENER EL NOMBRE DEL ÁREA
        foreach ($equipos as $equipo) {
            $equipo->idAreas = Areas::find($equipo->idAreas);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Paso 1: Generar el encabezado de la copia (Sesión del dia)
            //Obtener fecha actual
            $copiasEncabezado->fecha = date('Y-m-d');
            //Asignamos el valor de 0 para indicar que es una copia completa
            $copiasEncabezado->tipoDeCopia = '0';
            //Guardamos el encabezado de la copia
            $copiasEncabezado->guardar();
            // Paso 2: Obtener el ID generado
            $ultimoID = CopiasEncabezado::ultimoId()->id;

            // Paso 3: Recorrer los datos del formulario para crear registros
            $idsEquipos = $_POST['idEquipos'] ?? [];
            $copiasLocal = $_POST['copiaLocal'] ?? [];
            $copiasNube = $_POST['copiaNube'] ?? [];
            $observaciones = $_POST['observaciones'] ?? [];

            //Estamos iterando cada equipo recibido. La variable $i nos sirve para
            //acceder al índice y así obtener el dato correspondiente de los otros arrays.
            foreach ($idsEquipos as $i => $idEquipo) {
                //Instanciamos el modelo de CopiasDetalle
                $copiasDetalle = new CopiasDetalle([
                    //Se crea un objeto por cada equipo que contiene toda la información de una fila de la tabla.
                    'idCopiasEncabezado' => $ultimoID,
                    'idEquipos' => $idEquipo,
                    'copiaLocal' => $copiasLocal[$i] ?? '0',
                    'copiaNube' => $copiasNube[$i] ?? '0',
                    'observaciones' => $observaciones[$i] ?? '',
                ]);
                $copiasDetalle->guardar();
            }
        }

        // Render a la vista 
        $router->render('completa/completa', [
            'titulo' => 'Completa',
            'equipos' => $equipos
        ]);
    }

}


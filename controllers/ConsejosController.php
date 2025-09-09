<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class ConsejosController
{
    public static function consejos(Router $router)
    {
        if (!isAuth()) {
            header('Location: /');
        }
        // Render a la vista 
        $router->render('consejos/consejos', [
            'titulo' => 'Consejos'
        ]);
    }

    public static function consejosICB(Router $router)
    {
        if (!isAuth()) {
            header('Location: /');
        }
        // Render a la vista 
        $router->render('consejos/instalacionCobian', [
            'titulo' => 'Instalación de Cobian Backup'
        ]);
    }

        public static function consejosEVSC(Router $router)
    {
        if (!isAuth()) {
            header('Location: /');
        }
        // Render a la vista 
        $router->render('consejos/errorVSC', [
            'titulo' => 'Solución Error VSC'
        ]);
    }

        public static function consejosCT(Router $router)
    {
        if (!isAuth()) {
            header('Location: /');
        }
        // Render a la vista 
        $router->render('consejos/crearTarea', [
            'titulo' => '¿Cómo crear una tarea en Cobian Backup?'
        ]);
    }
}
